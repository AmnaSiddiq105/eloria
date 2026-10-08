
 
 <?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

$name = "";
$category_id = "";
$slug = "";
$description = "";
$price = "";
$old_price = "";
$stock = "";
$is_featured = 0;
$is_new = 0;
$status = 1;


/* =========================
   FETCH CATEGORIES
========================= */

$categories = [];

$category_query = "
    SELECT id, name
    FROM categories
    ORDER BY name ASC
";

$category_result = $conn->query($category_query);

if ($category_result) {

    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}


/* =========================
   ADD PRODUCT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    $category_id = (int)($_POST["category_id"] ?? 0);

    $slug = trim($_POST["slug"] ?? "");

    $description = trim($_POST["description"] ?? "");

    $price = trim($_POST["price"] ?? "");

    $old_price = trim($_POST["old_price"] ?? "");

    $stock = trim($_POST["stock"] ?? "");

    $is_featured =
        isset($_POST["is_featured"])
            ? 1
            : 0;

    $is_new =
        isset($_POST["is_new"])
            ? 1
            : 0;

    $status =
        isset($_POST["status"])
            ? 1
            : 0;


    /* =========================
       VALIDATION
    ========================= */

    if ($name === "") {

        $errors[] =
            "Product name is required.";
    }


    if ($category_id <= 0) {

        $errors[] =
            "Please select a category.";
    }


    if (
        $price === "" ||
        !is_numeric($price) ||
        $price < 0
    ) {

        $errors[] =
            "Please enter a valid price.";
    }


    if (
        $old_price !== "" &&
        (
            !is_numeric($old_price) ||
            $old_price < 0
        )
    ) {

        $errors[] =
            "Please enter a valid old price.";
    }


    if (
        $stock === "" ||
        !is_numeric($stock) ||
        $stock < 0
    ) {

        $errors[] =
            "Please enter a valid stock quantity.";
    }


    /* =========================
       AUTO GENERATE SLUG
    ========================= */

    if ($slug === "") {

        $slug =
            strtolower(
                trim($name)
            );

        $slug =
            preg_replace(
                '/[^a-z0-9]+/i',
                '-',
                $slug
            );

        $slug =
            trim($slug, '-');
    }


    /* =========================
       CHECK DUPLICATE SLUG
    ========================= */

    if (empty($errors)) {

        $check_sql = "
            SELECT id
            FROM products
            WHERE slug = ?
            LIMIT 1
        ";

        $check_stmt =
            $conn->prepare($check_sql);


        if ($check_stmt) {

            $check_stmt->bind_param(
                "s",
                $slug
            );

            $check_stmt->execute();

            $check_result =
                $check_stmt->get_result();


            if (
                $check_result->num_rows > 0
            ) {

                $errors[] =
                    "This product slug already exists.";
            }


            $check_stmt->close();
        }
    }


    /* =========================
       IMAGE UPLOAD
    ========================= */

    $image_name = null;


    if (
        empty($errors) &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"]
        !== UPLOAD_ERR_NO_FILE
    ) {

        $image =
            $_FILES["image"];


        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        $extension =
            strtolower(
                pathinfo(
                    $image["name"],
                    PATHINFO_EXTENSION
                )
            );


        if (
            !in_array(
                $extension,
                $allowed_extensions,
                true
            )
        ) {

            $errors[] =
                "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } elseif (
            $image["size"] >
            5 * 1024 * 1024
        ) {

            $errors[] =
                "Image size must be less than 5MB.";

        } else {

            $upload_directory =
                "../assets/images/";


            if (
                !is_dir(
                    $upload_directory
                )
            ) {

                mkdir(
                    $upload_directory,
                    0777,
                    true
                );
            }


            $image_name =
                time() . "_" .
                preg_replace(
                    '/[^a-zA-Z0-9._-]/',
                    '_',
                    $image["name"]
                );


            $image_path =
                $upload_directory .
                $image_name;


            if (
                !move_uploaded_file(
                    $image["tmp_name"],
                    $image_path
                )
            ) {

                $errors[] =
                    "Failed to upload product image.";

                $image_name = null;
            }
        }
    }


    /* =========================
       INSERT PRODUCT
    ========================= */

    if (empty($errors)) {

        $price_value =
            (float)$price;


        $old_price_value =
            $old_price !== ""
                ? (float)$old_price
                : null;


        $stock_value =
            (int)$stock;


        $insert_sql = "
            INSERT INTO products
            (
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
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";


        $insert_stmt =
            $conn->prepare(
                $insert_sql
            );


        if ($insert_stmt) {

            $insert_stmt->bind_param(
                "isssddsiiii",
                $category_id,
                $name,
                $slug,
                $description,
                $price_value,
                $old_price_value,
                $image_name,
                $stock_value,
                $is_featured,
                $is_new,
                $status
            );


            if (
                $insert_stmt->execute()
            ) {

                $insert_stmt->close();

                header(
                    "Location: products.php?added=1"
                );

                exit;

            } else {

                $errors[] =
                    "Unable to add product: " .
                    $insert_stmt->error;

                $insert_stmt->close();
            }

        } else {

            $errors[] =
                "Database error: " .
                $conn->error;
        }
    }
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

<title>Add Product | ÉLORIA Admin</title>


<!-- GOOGLE FONTS -->

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>


<!-- BOOTSTRAP ICONS -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<link rel="stylesheet" href="assets/css/admin-sidebar.css">
<style>

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }


    html {
        scroll-behavior: smooth;
    }


    body {

        min-height: 100vh;

        font-family:
            "Montserrat",
            sans-serif;

        color: #302733;

        background:
            radial-gradient(
                circle at top left,
                rgba(248,213,227,.48),
                transparent 32%
            ),
            radial-gradient(
                circle at bottom right,
                rgba(220,210,248,.42),
                transparent 34%
            ),
            linear-gradient(
                135deg,
                #fffdfd 0%,
                #fff8fb 48%,
                #f7f4ff 100%
            );
    }


    /* =========================
       SIDEBAR
    ========================= */

    .sidebar {

        position: fixed;

        left: 24px;
        top: 24px;

        width: 235px;

        height:
            calc(100vh - 48px);

        padding: 30px 18px;

        background:
            rgba(255,255,255,.76);

        border:
            1px solid
            rgba(255,255,255,.88);

        border-radius: 28px;

        box-shadow:
            0 20px 55px
            rgba(80,55,80,.09);

        backdrop-filter:
            blur(18px);

        -webkit-backdrop-filter:
            blur(18px);

        z-index: 1000;
    }


    .brand {

        text-align: center;

        margin-bottom: 38px;
    }


    .brand h2 {

        margin: 0;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 34px;

        font-weight: 600;

        letter-spacing: 3px;

        color: #342c35;
    }


    .brand span {

        display: block;

        margin-top: 2px;

        font-size: 9px;

        letter-spacing: 3px;

        color: #a47d91;

        font-weight: 600;
    }


    .nav-link-admin {

        display: flex;

        align-items: center;

        gap: 13px;

        padding: 13px 15px;

        margin-bottom: 8px;

        border-radius: 15px;

        text-decoration: none;

        color: #756875;

        font-size: 13px;

        font-weight: 500;

        transition:
            background .3s ease,
            color .3s ease,
            transform .3s ease,
            box-shadow .3s ease;
    }


    .nav-link-admin i {

        width: 20px;

        text-align: center;

        font-size: 14px;
    }


    .nav-link-admin:hover {

        background:
            linear-gradient(
                135deg,
                rgba(247,220,231,.75),
                rgba(232,224,249,.75)
            );

        color: #493844;

        transform:
            translateX(3px);
    }


    .nav-link-admin.active {

        background:
            linear-gradient(
                135deg,
                #f7dce8,
                #e8def7
            );

        color: #493844;

        box-shadow:
            0 8px 22px
            rgba(130,90,120,.09);
    }


    .logout-link {

        position: absolute;

        left: 18px;
        right: 18px;
        bottom: 20px;
    }


    /* =========================
       MAIN
    ========================= */

    .main {

        margin-left: 283px;

        min-height: 100vh;

        padding:
            48px 42px 60px;
    }


    .top-bar {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        margin-bottom: 28px;
    }


    .heading-label {

        margin-bottom: 5px;

        color: #ae7d91;

        font-size: 10px;

        font-weight: 600;

        letter-spacing: 3px;

        text-transform: uppercase;
    }


    .page-title h1 {

        margin: 0;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 48px;

        line-height: 1;

        font-weight: 600;

        color: #302832;
    }


    .page-title p {

        margin: 10px 0 0;

        color: #897d88;

        font-size: 13px;
    }


    .back-btn {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        padding: 12px 18px;

        border-radius: 14px;

        text-decoration: none;

        background:
            rgba(255,255,255,.78);

        color: #71596a;

        border:
            1px solid #eadfe7;

        font-size: 12px;

        font-weight: 600;

        box-shadow:
            0 8px 25px
            rgba(89,43,100,.05);

        transition:
            .25s ease;
    }


    .back-btn:hover {

        background:
            linear-gradient(
                135deg,
                #f7dce8,
                #e8def7
            );

        color: #493844;

        transform:
            translateY(-2px);
    }


    /* =========================
       ALERT
    ========================= */

    .alert-error {

        padding: 15px 18px;

        margin-bottom: 20px;

        border-radius: 15px;

        background:
            #fff0f4;

        border:
            1px solid #f3cbd8;

        color: #9c385b;

        font-size: 12px;
    }


    .alert-error ul {

        margin: 0;

        padding-left: 18px;
    }


    .alert-error li {

        margin: 3px 0;
    }


    /* =========================
       FORM CARD
    ========================= */

    .form-card {

        max-width: 1100px;

        background:
            rgba(255,255,255,.80);

        border:
            1px solid
            rgba(255,255,255,.92);

        border-radius: 26px;

        padding: 32px;

        box-shadow:
            0 18px 50px
            rgba(80,55,80,.08);

        backdrop-filter:
            blur(15px);
    }


    /* =========================
       SECTION TITLES
    ========================= */

    .section-title {

        display: flex;

        align-items: center;

        gap: 10px;

        padding-bottom: 14px;

        margin-bottom: 22px;

        border-bottom:
            1px solid #eee5eb;

        color: #493b45;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 25px;

        font-weight: 600;
    }


    .section-title i {

        width: 34px;

        height: 34px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 11px;

        background:
            linear-gradient(
                135deg,
                #f7dce8,
                #e9e1f8
            );

        color: #87627c;

        font-size: 15px;
    }


    /* =========================
       FORM GRID
    ========================= */

    .form-grid {

        display: grid;

        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );

        gap: 20px;
    }


    .full {

        grid-column:
            1 / -1;
    }


    .form-group {

        display: flex;

        flex-direction: column;

        gap: 8px;
    }


    .form-group label {

        color: #5c4a57;

        font-size: 11px;

        font-weight: 700;

        letter-spacing: .2px;
    }


    .required {

        color: #b45f7c;
    }


    .form-control {

        width: 100%;

        min-height: 46px;

        padding: 12px 14px;

        border:
            1px solid #e8dde5;

        border-radius: 13px;

        outline: none;

        background:
            rgba(255,255,255,.78);

        color: #40343d;

        font-family:
            "Montserrat",
            sans-serif;

        font-size: 12px;

        transition:
            border .25s ease,
            box-shadow .25s ease,
            background .25s ease;
    }


    .form-control::placeholder {

        color: #b0a3ad;
    }


    .form-control:focus {

        border-color:
            #c898ad;

        background: #fff;

        box-shadow:
            0 0 0 4px
            rgba(200,152,173,.10);
    }


    select.form-control {

        cursor: pointer;
    }


    textarea.form-control {

        min-height: 125px;

        resize: vertical;

        line-height: 1.7;
    }


    .form-help {

        color: #a1949e;

        font-size: 10px;

        line-height: 1.5;
    }


    /* =========================
       OPTIONS
    ========================= */

    .options {

        display: flex;

        flex-wrap: wrap;

        gap: 10px;
    }


    .option-box {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        min-height: 43px;

        padding: 9px 14px;

        border:
            1px solid #e8dde5;

        border-radius: 13px;

        background:
            rgba(255,255,255,.75);

        color: #695864;

        font-size: 11px;

        font-weight: 600;

        cursor: pointer;

        transition:
            border .25s ease,
            background .25s ease,
            transform .25s ease;
    }


    .option-box:hover {

        border-color:
            #c9a0b2;

        background:
            #fff9fc;

        transform:
            translateY(-1px);
    }


    .option-box input {

        width: 16px;

        height: 16px;

        accent-color:
            #9c7187;

        cursor: pointer;
    }


    .option-box i {

        color: #9b7087;

        font-size: 12px;
    }


    /* =========================
       IMAGE UPLOAD
    ========================= */

    .upload-box {

        padding: 28px;

        text-align: center;

        border:
            1.5px dashed #d9c5d1;

        border-radius: 20px;

        background:
            linear-gradient(
                135deg,
                rgba(255,249,252,.82),
                rgba(248,245,255,.82)
            );

        transition:
            border .25s ease,
            background .25s ease;
    }


    .upload-box:hover {

        border-color:
            #bd8fa5;

        background:
            linear-gradient(
                135deg,
                rgba(255,246,250,.95),
                rgba(246,242,255,.95)
            );
    }


    .upload-icon {

        width: 58px;
        height: 58px;

        margin:
            0 auto 13px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 17px;

        background:
            linear-gradient(
                135deg,
                #f7dce8,
                #e7def7
            );

        color: #87627c;

        font-size: 23px;

        box-shadow:
            0 8px 20px
            rgba(130,90,120,.08);
    }


    .upload-title {

        color: #4d3c48;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 22px;

        font-weight: 600;
    }


    .upload-box input {

        display: block;

        width: 100%;

        max-width: 450px;

        margin:
            14px auto 0;

        padding: 8px;

        font-family:
            "Montserrat",
            sans-serif;

        font-size: 11px;

        color: #766673;
    }


    .image-preview {

        display: none;

        max-width: 190px;

        max-height: 190px;

        margin:
            18px auto 0;

        object-fit: cover;

        border-radius: 16px;

        border:
            4px solid #fff;

        box-shadow:
            0 10px 28px
            rgba(70,35,80,.12);
    }


    /* =========================
       ACTIONS
    ========================= */

    .form-actions {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        margin-top: 30px;

        padding-top: 25px;

        border-top:
            1px solid #eee5eb;
    }


    .cancel-btn,
    .save-btn {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        min-height: 45px;

        padding:
            0 20px;

        border-radius: 13px;

        font-family:
            "Montserrat",
            sans-serif;

        font-size: 11px;

        font-weight: 700;

        text-decoration: none;

        cursor: pointer;

        transition:
            transform .25s ease,
            box-shadow .25s ease,
            background .25s ease;
    }


    .cancel-btn {

        background:
            rgba(255,255,255,.82);

        color: #765f6d;

        border:
            1px solid #e5d9e1;
    }


    .cancel-btn:hover {

        background:
            #f7edf3;

        color: #604b57;

        transform:
            translateY(-2px);
    }


    .save-btn {

        border: none;

        color: #fff;

        background:
            linear-gradient(
                135deg,
                #b87996,
                #8e78b8
            );

        box-shadow:
            0 10px 24px
            rgba(142,120,184,.20);
    }


    .save-btn:hover {

        color: #fff;

        transform:
            translateY(-2px);

        box-shadow:
            0 14px 30px
            rgba(142,120,184,.28);
    }


    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 1200px) {

        .sidebar {

            width: 215px;
        }


        .main {

            margin-left: 255px;

            padding-left: 30px;

            padding-right: 30px;
        }
    }


    @media (max-width: 900px) {

        .sidebar {

            position: relative;

            left: auto;

            top: auto;

            width:
                calc(100% - 32px);

            height: auto;

            min-height: auto;

            margin: 16px;

            padding: 22px;
        }


        .brand {

            margin-bottom: 22px;
        }


        .logout-link {

            position: static;

            margin-top: 8px;
        }


        .main {

            margin-left: 0;

            padding:
                25px 20px 45px;
        }


        .form-grid {

            grid-template-columns: 1fr;
        }


        .full {

            grid-column: auto;
        }
    }


    @media (max-width: 600px) {

        .top-bar {

            align-items: flex-start;

            flex-direction: column;
        }


        .page-title h1 {

            font-size: 40px;
        }


        .back-btn {

            width: 100%;

            justify-content: center;
        }


        .form-card {

            padding: 20px;

            border-radius: 21px;
        }


        .section-title {

            font-size: 22px;
        }


        .form-actions {

            flex-direction: column;
        }


        .cancel-btn,
        .save-btn {

            width: 100%;
        }


        .upload-box {

            padding: 22px 15px;
        }
    }

</style>
<link rel="stylesheet" href="assets/admin-sidebar.css">

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


<!-- PAGE HEADER -->

<div class="top-bar">


    <div class="page-title">

        <div class="heading-label">
            ÉLORIA COLLECTION
        </div>

        <h1>
            Add New Product
        </h1>

        <p>
            Add a beautiful new product to your ÉLORIA collection.
        </p>

    </div>


    <a
        href="products.php"
        class="back-btn"
    >

        <i class="bi bi-arrow-left"></i>

        Back to Products

    </a>


</div>



<!-- ERRORS -->

<?php if (!empty($errors)): ?>

    <div class="alert-error">

        <ul>

            <?php foreach ($errors as $error): ?>

                <li>
                    <?= htmlspecialchars($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>



<!-- FORM CARD -->

<div class="form-card">


    <form
        method="POST"
        enctype="multipart/form-data"
    >


        <!-- =========================
             PRODUCT INFORMATION
        ========================= -->

        <div class="section-title">

            <i class="bi bi-info-circle-fill"></i>

            Product Information

        </div>


        <div class="form-grid">


            <!-- NAME -->

            <div class="form-group">

                <label>

                    Product Name

                    <span class="required">
                        *
                    </span>

                </label>


                <input
                    type="text"
                    name="name"
                    id="productName"
                    class="form-control"
                    value="<?= htmlspecialchars($name) ?>"
                    placeholder="e.g. Rose Satin Dress"
                    required
                >

            </div>



            <!-- CATEGORY -->

            <div class="form-group">

                <label>

                    Category

                    <span class="required">
                        *
                    </span>

                </label>


                <select
                    name="category_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        Select Category
                    </option>


                    <?php foreach (
                        $categories
                        as $category
                    ): ?>

                        <option
                            value="<?= (int)$category['id'] ?>"
                            <?= $category_id == $category['id']
                                ? 'selected'
                                : '' ?>
                        >

                            <?= htmlspecialchars(
                                $category['name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>



            <!-- SLUG -->

            <div class="form-group full">

                <label>
                    Product Slug
                </label>


                <input
                    type="text"
                    name="slug"
                    id="productSlug"
                    class="form-control"
                    value="<?= htmlspecialchars($slug) ?>"
                    placeholder="product-slug"
                >


                <span class="form-help">

                    Leave empty to generate automatically
                    from product name.

                </span>

            </div>



            <!-- DESCRIPTION -->

            <div class="form-group full">

                <label>
                    Description
                </label>


                <textarea
                    name="description"
                    class="form-control"
                    placeholder="Write a short description about this product..."
                ><?= htmlspecialchars($description) ?></textarea>

            </div>


        </div>



        <!-- =========================
             PRICING & INVENTORY
        ========================= -->

        <div
            class="section-title"
            style="margin-top:35px;"
        >

            <i class="bi bi-tags-fill"></i>

            Pricing & Inventory

        </div>


        <div class="form-grid">


            <!-- PRICE -->

            <div class="form-group">

                <label>

                    Price

                    <span class="required">
                        *
                    </span>

                </label>


                <input
                    type="number"
                    name="price"
                    class="form-control"
                    value="<?= htmlspecialchars($price) ?>"
                    placeholder="0.00"
                    min="0"
                    step="0.01"
                    required
                >

            </div>



            <!-- OLD PRICE -->

            <div class="form-group">

                <label>
                    Old Price
                </label>


                <input
                    type="number"
                    name="old_price"
                    class="form-control"
                    value="<?= htmlspecialchars($old_price) ?>"
                    placeholder="Optional original price"
                    min="0"
                    step="0.01"
                >

            </div>



            <!-- STOCK -->

            <div class="form-group">

                <label>

                    Stock

                    <span class="required">
                        *
                    </span>

                </label>


                <input
                    type="number"
                    name="stock"
                    class="form-control"
                    value="<?= htmlspecialchars($stock) ?>"
                    placeholder="0"
                    min="0"
                    required
                >

            </div>



            <!-- STATUS -->

            <div class="form-group">

                <label>
                    Product Status
                </label>


                <div class="options">

                    <label class="option-box">

                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            <?= $status
                                ? 'checked'
                                : '' ?>
                        >

                        <i class="bi bi-check-circle-fill"></i>

                        Active Product

                    </label>

                </div>

            </div>


        </div>



        <!-- =========================
             PRODUCT IMAGE
        ========================= -->

        <div
            class="section-title"
            style="margin-top:35px;"
        >

            <i class="bi bi-image-fill"></i>

            Product Image

        </div>


        <div class="upload-box">


            <div class="upload-icon">

                <i class="bi bi-cloud-arrow-up-fill"></i>

            </div>


            <div class="upload-title">

                Upload Product Image

            </div>


            <p
                class="form-help"
                style="margin-top:7px;"
            >

                JPG, JPEG, PNG or WEBP
                · Maximum 5MB

            </p>


            <input
                type="file"
                name="image"
                id="imageInput"
                accept=".jpg,.jpeg,.png,.webp"
            >


            <img
                id="imagePreview"
                class="image-preview"
                alt="Image Preview"
            >


        </div>



        <!-- =========================
             PRODUCT LABELS
        ========================= -->

        <div
            class="section-title"
            style="margin-top:35px;"
        >

            <i class="bi bi-stars"></i>

            Product Labels

        </div>


        <div class="options">


            <label class="option-box">

                <input
                    type="checkbox"
                    name="is_new"
                    value="1"
                    <?= $is_new
                        ? 'checked'
                        : '' ?>
                >

                <i class="bi bi-sparkles"></i>

                New Arrival

            </label>


            <label class="option-box">

                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    <?= $is_featured
                        ? 'checked'
                        : '' ?>
                >

                <i class="bi bi-heart-fill"></i>

                Featured Product

            </label>


        </div>



        <!-- =========================
             ACTIONS
        ========================= -->

        <div class="form-actions">


            <a
                href="products.php"
                class="cancel-btn"
            >

                <i class="bi bi-x-lg"></i>

                Cancel

            </a>


            <button
                type="submit"
                class="save-btn"
            >

                <i class="bi bi-check2-circle"></i>

                Add Product

            </button>


        </div>


    </form>


</div>
```

</main>

<script>

/* =========================
   AUTO SLUG
========================= */

const productName =
    document.getElementById("productName");

const productSlug =
    document.getElementById("productSlug");


productName.addEventListener(
    "input",
    function () {

        if (
            productSlug.dataset.edited === "true"
        ) {
            return;
        }


        let slug =
            this.value
                .toLowerCase()
                .trim()
                .replace(
                    /[^a-z0-9]+/g,
                    "-"
                )
                .replace(
                    /^-+|-+$/g,
                    ""
                );


        productSlug.value =
            slug;

    }
);


productSlug.addEventListener(
    "input",
    function () {

        this.dataset.edited =
            "true";

    }
);


/* =========================
   IMAGE PREVIEW
========================= */

const imageInput =
    document.getElementById("imageInput");

const imagePreview =
    document.getElementById("imagePreview");


imageInput.addEventListener(
    "change",
    function () {

        const file =
            this.files[0];


        if (!file) {

            imagePreview.style.display =
                "none";

            imagePreview.src =
                "";

            return;
        }


        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                imagePreview.src =
                    event.target.result;

                imagePreview.style.display =
                    "block";
            };


        reader.readAsDataURL(file);

    }
);

</script>

</body>

</html>
