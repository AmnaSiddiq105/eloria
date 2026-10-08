
<?php
session_start();
require_once "../db.php";

/* =========================
   ADMIN CHECK
========================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   PRODUCT ID
========================= */
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: products.php");
    exit;
}

/* =========================
   FETCH PRODUCT
========================= */
$product_stmt = $conn->prepare("
    SELECT
        id,
        category_id,
        name,
        slug,
        description,
        price,
        old_price,
        image,
        stock,
        is_featured,
        is_new,
        status
    FROM products
    WHERE id = ?
    LIMIT 1
");

$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();

$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

$product_stmt->close();

if (!$product) {
    header("Location: products.php");
    exit;
}

/* =========================
   FETCH CATEGORIES
========================= */
$categories = [];

$category_result = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

/* =========================
   VARIABLES
========================= */
$error = "";
$success = "";

/* =========================
   UPDATE PRODUCT
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $old_price = trim($_POST['old_price'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);

    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;

    /* =========================
       VALIDATION
    ========================= */

    if ($name === "") {
        $error = "Product name is required.";
    } elseif ($category_id <= 0) {
        $error = "Please select a category.";
    } elseif ($price === "" || !is_numeric($price) || $price < 0) {
        $error = "Please enter a valid price.";
    } elseif ($stock < 0) {
        $error = "Stock cannot be negative.";
    }

    /* =========================
       AUTO SLUG
    ========================= */

    if ($slug === "") {
        $slug = strtolower($name);

        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
    } else {
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');
    }

    /* =========================
       DUPLICATE SLUG CHECK
    ========================= */

    if ($error === "") {

        $slug_stmt = $conn->prepare("
            SELECT id
            FROM products
            WHERE slug = ?
            AND id != ?
            LIMIT 1
        ");

        $slug_stmt->bind_param("si", $slug, $product_id);
        $slug_stmt->execute();

        $slug_result = $slug_stmt->get_result();

        if ($slug_result->num_rows > 0) {
            $error = "This product slug already exists. Please use another slug.";
        }

        $slug_stmt->close();
    }

    /* =========================
       IMAGE
    ========================= */

    $image_name = $product['image'];

    if (
        $error === "" &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "There was an error uploading the image.";
        } else {

            $tmp_name = $_FILES['image']['tmp_name'];
            $original_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

            $extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );

            if (!in_array($extension, $allowed_extensions)) {
                $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error = "Image size must be less than 5MB.";
            } elseif (!getimagesize($tmp_name)) {
                $error = "Please upload a valid image.";
            } else {

                $upload_directory = "../assets/images/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $new_image_name =
                    time() . "_" .
                    uniqid() . "." .
                    $extension;

                $destination =
                    $upload_directory . $new_image_name;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $image_name = $new_image_name;
                } else {
                    $error = "Failed to upload the new image.";
                }
            }
        }
    }

    /* =========================
       UPDATE DATABASE
    ========================= */

    if ($error === "") {

        $price_value = (float)$price;

        if ($old_price === "") {

            $update_stmt = $conn->prepare("
                UPDATE products
                SET
                    category_id = ?,
                    name = ?,
                    slug = ?,
                    description = ?,
                    price = ?,
                    old_price = NULL,
                    image = ?,
                    stock = ?,
                    is_featured = ?,
                    is_new = ?,
                    status = ?
                WHERE id = ?
            ");

            $update_stmt->bind_param(
                "isssdsiiiii",
                $category_id,
                $name,
                $slug,
                $description,
                $price_value,
                $image_name,
                $stock,
                $is_featured,
                $is_new,
                $status,
                $product_id
            );

        } else {

            $old_price_value = (float)$old_price;

            $update_stmt = $conn->prepare("
                UPDATE products
                SET
                    category_id = ?,
                    name = ?,
                    slug = ?,
                    description = ?,
                    price = ?,
                    old_price = ?,
                    image = ?,
                    stock = ?,
                    is_featured = ?,
                    is_new = ?,
                    status = ?
                WHERE id = ?
            ");

            $update_stmt->bind_param(
                "isssddsiiiii",
                $category_id,
                $name,
                $slug,
                $description,
                $price_value,
                $old_price_value,
                $image_name,
                $stock,
                $is_featured,
                $is_new,
                $status,
                $product_id
            );
        }

        if ($update_stmt->execute()) {

            $update_stmt->close();

            /* Delete old image only when new image was uploaded */
            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] === UPLOAD_ERR_OK &&
                !empty($product['image']) &&
                $product['image'] !== $image_name
            ) {

                $old_image_path =
                    "../assets/images/" . $product['image'];

                if (file_exists($old_image_path)) {
                    @unlink($old_image_path);
                }
            }

            header("Location: products.php?updated=1");
            exit;

        } else {

            $error = "Unable to update product. Please try again.";

            $update_stmt->close();
        }
    }

    /* Keep entered values on error */
    $product['name'] = $name;
    $product['category_id'] = $category_id;
    $product['slug'] = $slug;
    $product['description'] = $description;
    $product['price'] = $price;
    $product['old_price'] = $old_price;
    $product['stock'] = $stock;
    $product['is_featured'] = $is_featured;
    $product['is_new'] = $is_new;
    $product['status'] = $status;
    $product['image'] = $image_name;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Product | ÉLORIA Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
<link rel="stylesheet" href="assets/admin-sidebar.css">
    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;

            font-family: "Montserrat", sans-serif;

            color: #302a31;

            background:
                radial-gradient(
                    circle at top left,
                    rgba(249, 213, 229, .55),
                    transparent 32%
                ),
                radial-gradient(
                    circle at top right,
                    rgba(225, 214, 250, .48),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffafb 0%,
                    #fdf7fb 45%,
                    #f7f5ff 100%
                );
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            top: 20px;
            left: 20px;

            width: 245px;
            height: calc(100vh - 40px);

            padding: 28px 17px;

            background: rgba(255, 255, 255, .76);

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            border: 1px solid rgba(255, 255, 255, .85);

            border-radius: 28px;

            box-shadow:
                0 20px 55px rgba(90, 55, 78, .09);

            z-index: 1000;
        }

        .brand {
            text-align: center;
            margin-bottom: 35px;
        }

        .brand h2 {
            margin: 0;

            font-family: "Cormorant Garamond", serif;

            font-size: 34px;
            font-weight: 600;

            letter-spacing: 2px;

            color: #2e2930;
        }

        .brand span {
            display: block;

            margin-top: -2px;

            font-size: 9px;
            font-weight: 600;

            letter-spacing: 3px;

            color: #a78399;
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 13px 15px;

            color: #675965;

            text-decoration: none;

            font-size: 13px;
            font-weight: 500;

            border-radius: 15px;

            transition: all .3s ease;
        }

        .nav-menu a i {
            width: 20px;

            font-size: 17px;

            color: #a88199;

            transition: .3s ease;
        }

        .nav-menu a:hover {
            background:
                linear-gradient(
                    135deg,
                    rgba(250, 220, 234, .7),
                    rgba(233, 225, 250, .65)
                );

            color: #3e3340;

            transform: translateX(3px);
        }

        .nav-menu a:hover i {
            color: #9a6d87;
        }

        .nav-menu a.active {
            background:
                linear-gradient(
                    135deg,
                    #f7dce9,
                    #e9e0f8
                );

            color: #3e3040;

            font-weight: 600;

            box-shadow:
                0 8px 22px rgba(151, 112, 137, .10);
        }

        .nav-menu a.active i {
            color: #9b6683;
        }

        .logout-link {
            margin-top: 22px;

            border-top: 1px solid rgba(120, 90, 110, .08);

            padding-top: 16px !important;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 285px;

            padding: 40px 38px 50px;

            min-height: 100vh;
        }

        .topbar {
            display: flex;

            align-items: center;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 28px;
        }

        .topbar h1 {
            margin: 0;

            font-family: "Cormorant Garamond", serif;

            font-size: 42px;
            line-height: 1;

            font-weight: 600;

            color: #302a31;
        }

        .topbar p {
            margin: 9px 0 0;

            color: #8b7d88;

            font-size: 12px;

            letter-spacing: .2px;
        }

        .back-btn {
            display: inline-flex;

            align-items: center;
            gap: 8px;

            padding: 11px 17px;

            color: #66505e;

            background: rgba(255,255,255,.76);

            border: 1px solid rgba(193, 166, 183, .22);

            border-radius: 14px;

            text-decoration: none;

            font-size: 12px;
            font-weight: 600;

            box-shadow:
                0 8px 22px rgba(80, 50, 70, .05);

            transition: .3s ease;
        }

        .back-btn:hover {
            color: #3c3039;

            background: #fff;

            transform: translateY(-2px);

            box-shadow:
                0 12px 28px rgba(80, 50, 70, .09);
        }

        /* =========================
           ALERT
        ========================= */

        .alert {
            border: none;

            border-radius: 15px;

            padding: 14px 18px;

            font-size: 13px;

            box-shadow:
                0 8px 22px rgba(120, 50, 70, .05);
        }

        .alert-danger {
            color: #8a4357;

            background:
                linear-gradient(
                    135deg,
                    rgba(255, 231, 237, .92),
                    rgba(255, 242, 246, .92)
                );
        }

        /* =========================
           FORM CARD
        ========================= */

        .form-card {
            background: rgba(255,255,255,.80);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border: 1px solid rgba(255,255,255,.86);

            border-radius: 28px;

            padding: 34px;

            box-shadow:
                0 20px 55px rgba(77, 48, 68, .075);
        }

        .section-title {
            display: flex;

            align-items: center;
            gap: 10px;

            margin-bottom: 22px;

            font-family: "Cormorant Garamond", serif;

            font-size: 24px;

            font-weight: 600;

            color: #443540;
        }

        .section-title i {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            width: 34px;
            height: 34px;

            border-radius: 11px;

            background:
                linear-gradient(
                    135deg,
                    #f6dce8,
                    #e8e0f8
                );

            color: #9a6d86;

            font-size: 15px;
        }

        .form-label {
            margin-bottom: 8px;

            color: #554651;

            font-size: 12px;
            font-weight: 600;

            letter-spacing: .15px;
        }

        .form-control,
        .form-select {
            min-height: 48px;

            padding: 11px 14px;

            color: #40363e;

            background: rgba(255,255,255,.92);

            border: 1px solid #eadfe7;

            border-radius: 14px;

            font-family: "Montserrat", sans-serif;

            font-size: 12px;

            box-shadow: none;

            transition: .25s ease;
        }

        .form-control::placeholder {
            color: #b2a5af;
        }

        .form-control:focus,
        .form-select:focus {
            color: #40363e;

            background: #fff;

            border-color: #d6a9bf;

            box-shadow:
                0 0 0 4px rgba(218, 171, 196, .12);
        }

        textarea.form-control {
            min-height: 145px;

            resize: vertical;

            line-height: 1.7;
        }

        .text-muted {
            color: #9c8e99 !important;

            font-size: 10px;
        }

        hr {
            border-color: rgba(130, 100, 120, .10);

            opacity: 1;
        }

        /* =========================
           IMAGE
        ========================= */

        .current-image {
            width: 190px;
            height: 190px;

            object-fit: cover;

            border-radius: 22px;

            border: 1px solid #eadfe7;

            background: #fff;

            box-shadow:
                0 12px 30px rgba(70, 45, 62, .10);
        }

        .image-preview {
            width: 190px;
            height: 190px;

            object-fit: cover;

            margin-top: 16px;

            border-radius: 22px;

            border: 2px dashed #d7b3c8;

            background: #fff;

            display: none;
        }

        .upload-box {
            padding: 24px;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,248,251,.96),
                    rgba(249,246,255,.96)
                );

            border: 1px dashed #dbc7d5;

            border-radius: 20px;
        }

        .upload-box small {
            display: block;

            color: #988a95;

            font-size: 10px;

            line-height: 1.6;
        }

        /* =========================
           OPTIONS
        ========================= */

        .option-box {
            height: 100%;

            padding: 17px;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,250,252,.95),
                    rgba(250,248,255,.95)
                );

            border: 1px solid #eee4eb;

            border-radius: 17px;

            transition: .3s ease;
        }

        .option-box:hover {
            transform: translateY(-2px);

            border-color: #e1c5d5;

            box-shadow:
                0 10px 25px rgba(90, 55, 78, .06);
        }

        .form-check {
            display: flex;

            align-items: center;
        }

        .form-check-input {
            width: 19px;
            height: 19px;

            margin-top: 0;

            border: 1px solid #d9c5d2;

            cursor: pointer;

            box-shadow: none !important;
        }

        .form-check-input:checked {
            background-color: #b27d9b;

            border-color: #b27d9b;
        }

        .form-check-label {
            margin-left: 6px;

            color: #5b4b56;

            font-size: 12px;
            font-weight: 600;

            cursor: pointer;
        }

        /* =========================
           BUTTONS
        ========================= */

        .save-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-height: 48px;

            padding: 12px 24px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #b57f9c,
                    #9d89bb
                );

            border: none;

            border-radius: 14px;

            font-family: "Montserrat", sans-serif;

            font-size: 12px;
            font-weight: 600;

            box-shadow:
                0 10px 24px rgba(155, 113, 141, .20);

            transition: .3s ease;
        }

        .save-btn:hover {
            color: #fff;

            transform: translateY(-2px);

            box-shadow:
                0 14px 30px rgba(155, 113, 141, .27);
        }

        .cancel-btn {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-height: 48px;

            padding: 12px 23px;

            color: #776873;

            background: #f6f0f5;

            border-radius: 14px;

            text-decoration: none;

            font-size: 12px;
            font-weight: 600;

            transition: .3s ease;
        }

        .cancel-btn:hover {
            color: #51434d;

            background: #eee4ec;

            transform: translateY(-2px);
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .sidebar {
                width: 220px;
            }

            .main {
                margin-left: 255px;

                padding-left: 25px;
                padding-right: 25px;
            }
        }

        @media (max-width: 900px) {

            .sidebar {
                position: relative;

                top: auto;
                left: auto;

                width: calc(100% - 30px);

                height: auto;

                margin: 15px;

                border-radius: 24px;
            }

            .main {
                margin-left: 0;

                padding: 15px 20px 35px;
            }

            .nav-menu {
                display: grid;

                grid-template-columns:
                    repeat(3, 1fr);

                gap: 7px;
            }

            .logout-link {
                margin-top: 0;
            }
        }

        @media (max-width: 700px) {

            .nav-menu {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .topbar {
                align-items: flex-start;

                flex-direction: column;
            }

            .topbar h1 {
                font-size: 36px;
            }

            .form-card {
                padding: 24px 20px;
            }
        }

        @media (max-width: 500px) {

            .sidebar {
                width: calc(100% - 20px);

                margin: 10px;
            }

            .nav-menu {
                grid-template-columns: 1fr;
            }

            .main {
                padding: 10px 12px 30px;
            }

            .topbar h1 {
                font-size: 32px;
            }

            .form-card {
                padding: 20px 15px;

                border-radius: 22px;
            }

            .current-image,
            .image-preview {
                width: 160px;
                height: 160px;
            }

            .upload-box {
                padding: 18px;
            }

            .d-flex.justify-content-end {
                flex-direction: column-reverse;
            }

            .save-btn,
            .cancel-btn {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<?php include "includes/sidebar.php"; ?>


<!-- =========================
     MAIN
========================= -->

<main class="main">

    <div class="topbar">

        <div>

            <h1>Edit Product</h1>

            <p>
                Update your ÉLORIA product details and keep your collection fresh.
            </p>

        </div>

        <a href="products.php" class="back-btn">

            <i class="bi bi-arrow-left"></i>

            Back to Products

        </a>

    </div>


    <?php if ($error !== ""): ?>

        <div class="alert alert-danger mb-4">

            <i class="bi bi-exclamation-circle me-2"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >

        <!-- =========================
             BASIC INFORMATION
        ========================= -->

        <div class="section-title">

            <i class="bi bi-info-circle-fill"></i>

            Basic Information

        </div>


        <div class="row g-4">

            <div class="col-md-8">

                <label class="form-label">
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="productName"
                    class="form-control"
                    value="<?= htmlspecialchars($product['name']) ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Category
                </label>

                <select
                    name="category_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select Category
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= (int)$category['id'] ?>"
                            <?= ((int)$product['category_id'] === (int)$category['id']) ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($category['name']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="col-12">

                <label class="form-label">
                    Product Slug
                </label>

                <input
                    type="text"
                    name="slug"
                    id="productSlug"
                    class="form-control"
                    value="<?= htmlspecialchars($product['slug']) ?>"
                    placeholder="product-slug"
                >

                <small class="text-muted">
                    Example: velvet-satin-dress
                </small>

            </div>


            <div class="col-12">

                <label class="form-label">
                    Description
                </label>

                <textarea
                    name="description"
                    class="form-control"
                    placeholder="Write product description..."
                ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

            </div>

        </div>


        <hr class="my-5">


        <!-- =========================
             PRICE & STOCK
        ========================= -->

        <div class="section-title">

            <i class="bi bi-currency-dollar"></i>

            Price & Inventory

        </div>


        <div class="row g-4">

            <div class="col-md-4">

                <label class="form-label">
                    Current Price
                </label>

                <input
                    type="number"
                    name="price"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($product['price']) ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Old Price
                </label>

                <input
                    type="number"
                    name="old_price"
                    class="form-control"
                    step="0.01"
                    min="0"
                    value="<?= htmlspecialchars($product['old_price'] ?? '') ?>"
                    placeholder="Optional"
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Stock
                </label>

                <input
                    type="number"
                    name="stock"
                    class="form-control"
                    min="0"
                    value="<?= (int)$product['stock'] ?>"
                    required
                >

            </div>

        </div>


        <hr class="my-5">


        <!-- =========================
             PRODUCT IMAGE
        ========================= -->

        <div class="section-title">

            <i class="bi bi-image-fill"></i>

            Product Image

        </div>


        <div class="row g-4 align-items-center">

            <div class="col-md-4">

                <?php if (!empty($product['image'])): ?>

                    <img
                        src="../assets/images/<?= htmlspecialchars($product['image']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="current-image"
                        id="currentImage"
                    >

                <?php else: ?>

                    <div
                        class="current-image d-flex align-items-center justify-content-center"
                        id="currentImage"
                    >

                        <i
                            class="bi bi-image"
                            style="font-size:45px;color:#c7a9ca;"
                        ></i>

                    </div>

                <?php endif; ?>

            </div>


            <div class="col-md-8">

                <div class="upload-box">

                    <label class="form-label">
                        Replace Product Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        id="imageInput"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="mt-2">
                        JPG, JPEG, PNG or WEBP — maximum 5MB.
                    </small>

                    <img
                        id="imagePreview"
                        class="image-preview"
                        alt="Image Preview"
                    >

                </div>

            </div>

        </div>


        <hr class="my-5">


        <!-- =========================
             PRODUCT OPTIONS
        ========================= -->

        <div class="section-title">

            <i class="bi bi-stars"></i>

            Product Options

        </div>


        <div class="row g-3">

            <div class="col-md-4">

                <div class="option-box">

                    <div class="form-check">

                        <input
                            type="checkbox"
                            name="is_featured"
                            id="isFeatured"
                            class="form-check-input"
                            <?= ((int)$product['is_featured'] === 1) ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label"
                            for="isFeatured"
                        >
                            Featured Product
                        </label>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="option-box">

                    <div class="form-check">

                        <input
                            type="checkbox"
                            name="is_new"
                            id="isNew"
                            class="form-check-input"
                            <?= ((int)$product['is_new'] === 1) ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label"
                            for="isNew"
                        >
                            New Arrival
                        </label>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="option-box">

                    <div class="form-check">

                        <input
                            type="checkbox"
                            name="status"
                            id="status"
                            class="form-check-input"
                            <?= ((int)$product['status'] === 1) ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label"
                            for="status"
                        >
                            Active Product
                        </label>

                    </div>

                </div>

            </div>

        </div>


        <!-- =========================
             ACTIONS
        ========================= -->

        <div class="d-flex justify-content-end gap-3 mt-5">

            <a
                href="products.php"
                class="cancel-btn"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="save-btn"
            >

                <i class="bi bi-check2-circle me-2"></i>

                Update Product

            </button>

        </div>

    </form>

</main>


<script>

/* =========================
   AUTO SLUG
========================= */

const productName =
    document.getElementById("productName");

const productSlug =
    document.getElementById("productSlug");

productName.addEventListener("input", function () {

    if (!productSlug.dataset.manual) {

        let slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/^-+|-+$/g, "");

        productSlug.value = slug;
    }

});


productSlug.addEventListener("input", function () {

    this.dataset.manual = "true";

});


/* =========================
   IMAGE PREVIEW
========================= */

const imageInput =
    document.getElementById("imageInput");

const imagePreview =
    document.getElementById("imagePreview");

imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {

        imagePreview.style.display = "none";

        imagePreview.removeAttribute("src");

        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {

        imagePreview.src = event.target.result;

        imagePreview.style.display = "block";

    };

    reader.readAsDataURL(file);

});

</script>

</body>

</html>

