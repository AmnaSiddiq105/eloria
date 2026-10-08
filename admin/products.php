
 
 <?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   DELETE PRODUCT
========================= */

if (isset($_GET['delete'])) {

    $product_id = (int) $_GET['delete'];

    if ($product_id > 0) {

        $delete_stmt = $conn->prepare(
            "DELETE FROM products WHERE id = ?"
        );

        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $product_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }

    header("Location: products.php?deleted=1");
    exit;
}


/* =========================
   TOGGLE PRODUCT STATUS
========================= */

if (isset($_GET['toggle'])) {

    $product_id = (int) $_GET['toggle'];

    if ($product_id > 0) {

        $toggle_stmt = $conn->prepare("
            UPDATE products
            SET status = IF(status = 1, 0, 1)
            WHERE id = ?
        ");

        if ($toggle_stmt) {
            $toggle_stmt->bind_param("i", $product_id);
            $toggle_stmt->execute();
            $toggle_stmt->close();
        }
    }

    header("Location: products.php");
    exit;
}


/* =========================
   SEARCH
========================= */

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $search_sql = "
        SELECT 
            p.id,
            p.category_id,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.old_price,
            p.image,
            p.stock,
            p.is_featured,
            p.is_new,
            p.status,
            p.created_at,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 
            p.name LIKE ?
            OR p.slug LIKE ?
            OR c.name LIKE ?
        ORDER BY p.created_at DESC
    ";

    $stmt = $conn->prepare($search_sql);

    $search_value = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $search_value,
        $search_value,
        $search_value
    );

    $stmt->execute();

    $products_result = $stmt->get_result();

} else {

    $products_sql = "
        SELECT 
            p.id,
            p.category_id,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.old_price,
            p.image,
            p.stock,
            p.is_featured,
            p.is_new,
            p.status,
            p.created_at,
            c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
    ";

    $products_result = $conn->query($products_sql);
}


/* =========================
   PRODUCT COUNTS
========================= */

$total_products = 0;
$active_products = 0;
$featured_products = 0;
$out_of_stock = 0;

$count_result = $conn->query("
    SELECT 
        COUNT(*) AS total_products,
        SUM(status = 1) AS active_products,
        SUM(is_featured = 1) AS featured_products,
        SUM(stock <= 0) AS out_of_stock
    FROM products
");

if ($count_result && $count_result->num_rows > 0) {

    $counts = $count_result->fetch_assoc();

    $total_products = (int) $counts['total_products'];
    $active_products = (int) $counts['active_products'];
    $featured_products = (int) $counts['featured_products'];
    $out_of_stock = (int) $counts['out_of_stock'];
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

<title>Products | ÉLORIA Admin</title>

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

<!-- Font Awesome -->
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
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

        color: #302733;

        background:
            radial-gradient(
                circle at top left,
                rgba(248, 213, 227, .48),
                transparent 32%
            ),
            radial-gradient(
                circle at bottom right,
                rgba(220, 210, 248, .42),
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
        height: calc(100vh - 48px);

        padding: 30px 18px;

        background:
            rgba(255,255,255,.76);

        border:
            1px solid rgba(255,255,255,.88);

        border-radius: 28px;

        box-shadow:
            0 20px 55px rgba(80,55,80,.09);

        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);

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

        transform: translateX(3px);
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
            0 8px 22px rgba(130,90,120,.09);
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

    .main-content {

        margin-left: 283px;

        padding:
            48px 42px 60px;
    }


    .page-heading {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        margin-bottom: 30px;
    }


    .heading-label {

        margin-bottom: 4px;

        color: #ae7d91;

        font-size: 10px;

        font-weight: 600;

        letter-spacing: 3px;

        text-transform: uppercase;
    }


    .page-heading h1 {

        margin: 0;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 48px;

        line-height: 1;

        font-weight: 600;

        color: #302832;
    }


    .page-heading p {

        margin: 10px 0 0;

        color: #897d88;

        font-size: 13px;
    }


    .add-product-btn {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        padding: 12px 19px;

        border-radius: 14px;

        text-decoration: none;

        color: #fff;

        font-size: 12px;

        font-weight: 600;

        background:
            linear-gradient(
                135deg,
                #b87996,
                #8e78b8
            );

        box-shadow:
            0 12px 25px rgba(142,120,184,.20);

        transition:
            transform .25s ease,
            box-shadow .25s ease;
    }


    .add-product-btn:hover {

        color: #fff;

        transform: translateY(-2px);

        box-shadow:
            0 16px 30px rgba(142,120,184,.28);
    }


    /* =========================
       STATISTICS
    ========================= */

    .stats-grid {

        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 18px;

        margin-bottom: 22px;
    }


    .stat-card {

        display: flex;

        align-items: center;

        gap: 15px;

        min-height: 108px;

        padding: 19px;

        border-radius: 22px;

        background:
            rgba(255,255,255,.74);

        border:
            1px solid rgba(255,255,255,.9);

        box-shadow:
            0 15px 40px rgba(80,55,80,.07);

        backdrop-filter: blur(14px);

        transition:
            transform .25s ease,
            box-shadow .25s ease;
    }


    .stat-card:hover {

        transform: translateY(-3px);

        box-shadow:
            0 18px 45px rgba(80,55,80,.10);
    }


    .stat-icon {

        flex-shrink: 0;

        width: 52px;
        height: 52px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 16px;

        background:
            linear-gradient(
                135deg,
                #f8dfe9,
                #ece3fa
            );

        color: #87627c;

        font-size: 19px;
    }


    .stat-card h3 {

        margin: 0;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 30px;

        line-height: 1;

        font-weight: 600;

        color: #382f39;
    }


    .stat-card p {

        margin: 5px 0 0;

        color: #8d818b;

        font-size: 11px;

        font-weight: 500;
    }


    /* =========================
       SEARCH TOOLBAR
    ========================= */

    .toolbar {

        padding: 16px;

        margin-bottom: 20px;

        border-radius: 21px;

        background:
            rgba(255,255,255,.72);

        border:
            1px solid rgba(255,255,255,.9);

        box-shadow:
            0 12px 35px rgba(80,55,80,.06);

        backdrop-filter: blur(14px);
    }


    .search-box {

        display: flex;

        gap: 9px;
    }


    .search-box input {

        flex: 1;

        min-width: 0;

        height: 45px;

        padding: 0 15px;

        border:
            1px solid #eadfe7;

        border-radius: 13px;

        outline: none;

        background: rgba(255,255,255,.72);

        color: #433943;

        font-family: "Montserrat", sans-serif;

        font-size: 12px;

        transition:
            border .25s ease,
            box-shadow .25s ease;
    }


    .search-box input::placeholder {

        color: #b0a3ae;
    }


    .search-box input:focus {

        border-color: #c898ad;

        box-shadow:
            0 0 0 4px rgba(200,152,173,.10);
    }


    .search-btn {

        height: 45px;

        padding: 0 19px;

        border: none;

        border-radius: 13px;

        background:
            linear-gradient(
                135deg,
                #9c7187,
                #8070a7
            );

        color: white;

        font-family: "Montserrat", sans-serif;

        font-size: 12px;

        font-weight: 600;

        transition:
            transform .25s ease,
            box-shadow .25s ease;
    }


    .search-btn:hover {

        transform: translateY(-1px);

        box-shadow:
            0 8px 20px rgba(128,112,167,.20);
    }


    .clear-btn {

        display: flex;

        align-items: center;

        justify-content: center;

        min-width: 65px;

        padding: 0 14px;

        border-radius: 13px;

        text-decoration: none;

        background: #f6edf3;

        color: #795e6c;

        font-size: 12px;

        font-weight: 600;

        transition: .25s;
    }


    .clear-btn:hover {

        background: #f0e1ea;

        color: #5f4655;
    }


    /* =========================
       TABLE CARD
    ========================= */

    .table-card {

        overflow: hidden;

        border-radius: 24px;

        background:
            rgba(255,255,255,.80);

        border:
            1px solid rgba(255,255,255,.9);

        box-shadow:
            0 18px 50px rgba(80,55,80,.08);

        backdrop-filter: blur(15px);
    }


    .table-responsive {

        overflow-x: auto;
    }


    table {

        width: 100%;

        min-width: 1100px;

        border-collapse: collapse;
    }


    thead {

        background:
            linear-gradient(
                135deg,
                rgba(249,233,240,.72),
                rgba(239,233,249,.72)
            );
    }


    th {

        padding: 17px 15px;

        color: #765f6d;

        font-size: 10px;

        font-weight: 700;

        text-transform: uppercase;

        letter-spacing: 1px;

        white-space: nowrap;
    }


    td {

        padding: 15px;

        border-top:
            1px solid #f1e8ee;

        vertical-align: middle;

        color: #5f535e;

        font-size: 12px;
    }


    tbody tr {

        transition:
            background .25s ease;
    }


    tbody tr:hover {

        background:
            rgba(255,248,251,.72);
    }


    /* =========================
       PRODUCT IMAGE
    ========================= */

    .product-image {

        width: 58px;
        height: 68px;

        object-fit: cover;

        border-radius: 14px;

        background: #f8edf3;

        border:
            1px solid #eee0e8;

        box-shadow:
            0 5px 15px rgba(80,50,70,.06);
    }


    .no-image {

        width: 58px;
        height: 68px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 14px;

        background:
            linear-gradient(
                135deg,
                #f8edf3,
                #f1edfa
            );

        color: #b494a7;

        font-size: 19px;
    }


    .product-name {

        margin-bottom: 4px;

        color: #40353f;

        font-size: 13px;

        font-weight: 700;
    }


    .product-slug {

        color: #aa9ca7;

        font-size: 10px;
    }


    .category-name {

        color: #796a75;

        font-size: 12px;

        font-weight: 500;
    }


    .price-main {

        color: #493a46;

        font-size: 13px;

        font-weight: 700;
    }


    .old-price {

        color: #b1a3ad;

        font-size: 10px;

        text-decoration: line-through;
    }


    /* =========================
       BADGES
    ========================= */

    .badge-soft {

        display: inline-block;

        padding: 6px 10px;

        margin: 2px;

        border-radius: 30px;

        font-size: 9px;

        font-weight: 700;

        letter-spacing: .2px;
    }


    .badge-new {

        background: #eee5fb;

        color: #7860a1;
    }


    .badge-featured {

        background: #fbe1eb;

        color: #a55c78;
    }


    .badge-active {

        background: #e0f3e8;

        color: #4e8765;
    }


    .badge-inactive {

        background: #f0e8ec;

        color: #967b87;
    }


    .badge-stock {

        background: #e3f0f8;

        color: #557f99;
    }


    .badge-out {

        background: #fbe1e3;

        color: #ae6268;
    }


    .empty-label {

        color: #ad9faa;

        font-size: 11px;
    }


    /* =========================
       ACTION BUTTONS
    ========================= */

    .action-buttons {

        display: flex;

        gap: 7px;
    }


    .action-btn {

        width: 34px;
        height: 34px;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 11px;

        text-decoration: none;

        font-size: 12px;

        transition:
            transform .2s ease,
            background .2s ease,
            color .2s ease;
    }


    .action-btn:hover {

        transform: translateY(-2px);
    }


    .edit-btn {

        background: #eee7fa;

        color: #7762a0;
    }


    .edit-btn:hover {

        background: #7762a0;

        color: white;
    }


    .delete-btn {

        background: #fae7ec;

        color: #a85d72;
    }


    .delete-btn:hover {

        background: #a85d72;

        color: white;
    }


    .toggle-btn {

        background: #e4f3eb;

        color: #548267;
    }


    .toggle-btn:hover {

        background: #548267;

        color: white;
    }


    /* =========================
       EMPTY STATE
    ========================= */

    .empty-state {

        padding: 75px 20px;

        text-align: center;

        color: #958791;
    }


    .empty-state i {

        display: block;

        margin-bottom: 16px;

        color: #c6aebb;

        font-size: 44px;
    }


    .empty-state h4 {

        margin: 0 0 7px;

        font-family:
            "Cormorant Garamond",
            serif;

        font-size: 27px;

        font-weight: 600;

        color: #4b3d47;
    }


    .empty-state p {

        margin: 0;

        font-size: 12px;

        color: #a0939d;
    }


    /* =========================
       RESPONSIVE
    ========================= */

    @media (max-width: 1200px) {

        .sidebar {

            width: 215px;
        }

        .main-content {

            margin-left: 255px;

            padding-left: 30px;
            padding-right: 30px;
        }

        .stats-grid {

            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }


    @media (max-width: 900px) {

        .sidebar {

            position: relative;

            left: auto;
            top: auto;

            width: calc(100% - 32px);

            height: auto;

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


        .main-content {

            margin-left: 0;

            padding:
                25px 20px 45px;
        }


        .page-heading {

            align-items: flex-start;

            flex-direction: column;
        }


        .add-product-btn {

            width: 100%;

            justify-content: center;
        }
    }


    @media (max-width: 600px) {

        .stats-grid {

            grid-template-columns: 1fr;
        }


        .search-box {

            flex-wrap: wrap;
        }


        .search-box input {

            flex-basis: 100%;
        }


        .search-btn {

            flex: 1;
        }


        .clear-btn {

            min-height: 45px;
        }


        .page-heading h1 {

            font-size: 40px;
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
     MAIN CONTENT
========================= -->

<main class="main-content">


<!-- PAGE HEADING -->

<div class="page-heading">

    <div>

        <div class="heading-label">
            ÉLORIA COLLECTION
        </div>

        <h1>Products</h1>

        <p>
            Manage and curate your ÉLORIA product collection.
        </p>

    </div>


    <a
        href="add-product.php"
        class="add-product-btn"
    >
        <i class="fa-solid fa-plus"></i>
        Add Product
    </a>

</div>


<!-- =========================
     STATISTICS
========================= -->

<div class="stats-grid">


    <div class="stat-card">

        <div class="stat-icon">
            <i class="fa-solid fa-box"></i>
        </div>

        <div>

            <h3>
                <?= $total_products ?>
            </h3>

            <p>
                Total Products
            </p>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <div>

            <h3>
                <?= $active_products ?>
            </h3>

            <p>
                Active Products
            </p>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon">
            <i class="fa-solid fa-star"></i>
        </div>

        <div>

            <h3>
                <?= $featured_products ?>
            </h3>

            <p>
                Featured Products
            </p>

        </div>

    </div>


    <div class="stat-card">

        <div class="stat-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <div>

            <h3>
                <?= $out_of_stock ?>
            </h3>

            <p>
                Out of Stock
            </p>

        </div>

    </div>

</div>


<!-- =========================
     SEARCH
========================= -->

<div class="toolbar">

    <form
        method="GET"
        class="search-box"
    >

        <input
            type="text"
            name="search"
            placeholder="Search product, slug or category..."
            value="<?= htmlspecialchars($search) ?>"
        >


        <button
            type="submit"
            class="search-btn"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

            Search

        </button>


        <?php if ($search !== ''): ?>

            <a
                href="products.php"
                class="clear-btn"
            >
                Clear
            </a>

        <?php endif; ?>

    </form>

</div>


<!-- =========================
     PRODUCTS TABLE
========================= -->

<div class="table-card">

    <div class="table-responsive">


        <?php if ($products_result && $products_result->num_rows > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Product</th>

                        <th>Category</th>

                        <th>Price</th>

                        <th>Stock</th>

                        <th>Labels</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>


                <?php while ($product = $products_result->fetch_assoc()): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <strong>
                                #<?= (int) $product['id'] ?>
                            </strong>

                        </td>


                        <!-- PRODUCT -->

                        <td>

                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:12px;
                                "
                            >


                                <?php if (!empty($product['image'])): ?>


                                    <img
                                        src="../assets/images/<?= htmlspecialchars($product['image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="product-image"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >


                                    <div
                                        class="no-image"
                                        style="display:none;"
                                    >

                                        <i class="fa-regular fa-image"></i>

                                    </div>


                                <?php else: ?>


                                    <div class="no-image">

                                        <i class="fa-regular fa-image"></i>

                                    </div>


                                <?php endif; ?>


                                <div>


                                    <div class="product-name">

                                        <?= htmlspecialchars(
                                            $product['name']
                                        ) ?>

                                    </div>


                                    <div class="product-slug">

                                        <?= htmlspecialchars(
                                            $product['slug']
                                        ) ?>

                                    </div>


                                </div>

                            </div>

                        </td>


                        <!-- CATEGORY -->

                        <td>

                            <span class="category-name">

                                <?= htmlspecialchars(
                                    $product['category_name']
                                    ?? 'Uncategorized'
                                ) ?>

                            </span>

                        </td>


                        <!-- PRICE -->

                        <td>

                            <span class="price-main">

                                Rs.
                                <?= number_format(
                                    (float)$product['price'],
                                    2
                                ) ?>

                            </span>


                            <?php if (
                                !empty($product['old_price']) &&
                                (float)$product['old_price']
                                >
                                (float)$product['price']
                            ): ?>

                                <br>

                                <span class="old-price">

                                    Rs.
                                    <?= number_format(
                                        (float)$product['old_price'],
                                        2
                                    ) ?>

                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- STOCK -->

                        <td>

                            <?php if (
                                (int)$product['stock'] > 0
                            ): ?>


                                <span
                                    class="badge-soft badge-stock"
                                >

                                    <?= (int)$product['stock'] ?>

                                    available

                                </span>


                            <?php else: ?>


                                <span
                                    class="badge-soft badge-out"
                                >

                                    Out of Stock

                                </span>


                            <?php endif; ?>

                        </td>


                        <!-- LABELS -->

                        <td>


                            <?php if (
                                (int)$product['is_new'] === 1
                            ): ?>

                                <span
                                    class="badge-soft badge-new"
                                >
                                    New
                                </span>

                            <?php endif; ?>


                            <?php if (
                                (int)$product['is_featured'] === 1
                            ): ?>

                                <span
                                    class="badge-soft badge-featured"
                                >
                                    Featured
                                </span>

                            <?php endif; ?>


                            <?php if (
                                (int)$product['is_new'] === 0 &&
                                (int)$product['is_featured'] === 0
                            ): ?>

                                <span class="empty-label">
                                    —
                                </span>

                            <?php endif; ?>


                        </td>


                        <!-- STATUS -->

                        <td>


                            <?php if (
                                (int)$product['status'] === 1
                            ): ?>


                                <span
                                    class="badge-soft badge-active"
                                >
                                    Active
                                </span>


                            <?php else: ?>


                                <span
                                    class="badge-soft badge-inactive"
                                >
                                    Inactive
                                </span>


                            <?php endif; ?>


                        </td>


                        <!-- ACTIONS -->

                        <td>


                            <div class="action-buttons">


                                <a
                                    href="edit-product.php?id=<?= (int)$product['id'] ?>"
                                    class="action-btn edit-btn"
                                    title="Edit Product"
                                >

                                    <i
                                        class="fa-solid fa-pen"
                                    ></i>

                                </a>


                                <a
                                    href="products.php?toggle=<?= (int)$product['id'] ?>"
                                    class="action-btn toggle-btn"
                                    title="Change Status"
                                >

                                    <i
                                        class="fa-solid fa-power-off"
                                    ></i>

                                </a>


                                <a
                                    href="products.php?delete=<?= (int)$product['id'] ?>"
                                    class="action-btn delete-btn"
                                    title="Delete Product"
                                    onclick="return confirm('Are you sure you want to permanently delete this product?');"
                                >

                                    <i
                                        class="fa-solid fa-trash"
                                    ></i>

                                </a>


                            </div>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="empty-state">

                <i class="fa-solid fa-box-open"></i>

                <h4>
                    No Products Found
                </h4>

                <p>

                    <?php if ($search !== ''): ?>

                        No products match your search.

                    <?php else: ?>

                        Your product collection is currently empty.

                    <?php endif; ?>

                </p>

            </div>


        <?php endif; ?>


    </div>

</div>


</main>

<script>

    /*
     * Delete confirmation
     */

    document.querySelectorAll('.delete-btn').forEach(function(button) {

        button.addEventListener('click', function(event) {

            const confirmed = confirm(
                'Are you sure you want to permanently delete this product?'
            );

            if (!confirmed) {

                event.preventDefault();

            }

        });

    });

</script>

</body>
</html>
