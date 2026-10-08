<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$user_id = $_SESSION['user_id'];

/* =========================================================
   WISHLIST
========================================================= */

$wishlistIds = [];

$wishlistQuery = mysqli_query(
    $conn,
    "SELECT product_id FROM wishlist WHERE user_id = " . (int)$user_id
);

if ($wishlistQuery) {
    while ($row = mysqli_fetch_assoc($wishlistQuery)) {
        $wishlistIds[] = (int)$row['product_id'];
    }
}

/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$category = isset($_GET['category'])
    ? trim($_GET['category'])
    : '';

$sort = isset($_GET['sort'])
    ? $_GET['sort']
    : 'latest';

/* =========================================================
   CATEGORY LIST
========================================================= */

$categories = [];

$categoryQuery = mysqli_query(
    $conn,
    "SELECT id, name, slug
     FROM categories
     ORDER BY id ASC"
);

if ($categoryQuery) {
    while ($cat = mysqli_fetch_assoc($categoryQuery)) {
        $categories[] = $cat;
    }
}

/* =========================================================
   PRODUCT QUERY
========================================================= */

$where = ["p.status = 1"];

if ($search !== '') {
    $safeSearch = mysqli_real_escape_string($conn, $search);

    $where[] = "(
        p.name LIKE '%$safeSearch%'
        OR p.description LIKE '%$safeSearch%'
        OR c.name LIKE '%$safeSearch%'
    )";
}

if ($category !== '') {
    $safeCategory = mysqli_real_escape_string($conn, $category);

    $where[] = "c.slug = '$safeCategory'";
}

$whereSQL = implode(" AND ", $where);

/* =========================================================
   SORTING
========================================================= */

switch ($sort) {

    case 'price-low':
        $orderBy = "p.price ASC";
        break;

    case 'price-high':
        $orderBy = "p.price DESC";
        break;

    case 'name':
        $orderBy = "p.name ASC";
        break;

    default:
        $orderBy = "p.created_at DESC, p.id DESC";
        break;
}

/* =========================================================
   GET PRODUCTS
========================================================= */

$productSQL = "
    SELECT
        p.id,
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
        c.name AS category_name,
        c.slug AS category_slug

    FROM products p

    LEFT JOIN categories c
        ON p.category_id = c.id

    WHERE $whereSQL

    ORDER BY $orderBy
";

$productQuery = mysqli_query($conn, $productSQL);

$products = [];

if ($productQuery) {
    while ($product = mysqli_fetch_assoc($productQuery)) {
        $products[] = $product;
    }
}

/* =========================================================
   IMAGE KEYWORDS
   Product name/category ke according relevant image
========================================================= */

function getProductImage($product)
{
    $name = strtolower(trim($product['name'] ?? ''));
    $category = strtolower(trim($product['category_name'] ?? ''));

    /*
    ---------------------------------------------------------
    If admin/database already has an image
    ---------------------------------------------------------
    */

    $databaseImage = trim((string)($product['image'] ?? ''));

    if (!empty($databaseImage)) {

        if (
            filter_var($databaseImage, FILTER_VALIDATE_URL) &&
            preg_match('/^https?:\/\//i', $databaseImage)
        ) {
            return $databaseImage;
        }

        $databaseImage = ltrim($databaseImage, "/\\");

        if (
            strpos($databaseImage, 'assets/images/products/') === 0
        ) {
            return $databaseImage;
        }

        return 'assets/images/products/' . basename($databaseImage);
    }

    /*
    ---------------------------------------------------------
    Product-specific keywords
    ---------------------------------------------------------
    */

    $keywords = 'luxury beauty product';

    if (strpos($name, 'blush') !== false) {
        $keywords = 'pink blush cosmetics beauty';
    }

    elseif (strpos($name, 'rose') !== false) {
        $keywords = 'rose pink perfume beauty';
    }

    elseif (strpos($name, 'bloom') !== false) {
        $keywords = 'pink flowers perfume luxury beauty';
    }

    elseif (strpos($name, 'pink') !== false) {
        $keywords = 'pink perfume cosmetics luxury';
    }

    elseif (strpos($name, 'pearl') !== false) {
        $keywords = 'pearl jewelry elegant luxury';
    }

    elseif (strpos($name, 'noir') !== false) {
        $keywords = 'black perfume luxury elegant';
    }

    elseif (strpos($name, 'midnight') !== false) {
        $keywords = 'black perfume dark luxury';
    }

    elseif (strpos($name, 'black') !== false) {
        $keywords = 'black luxury perfume cosmetics';
    }

    elseif (strpos($name, 'moon') !== false) {
        $keywords = 'moonlight jewelry silver luxury';
    }

    elseif (strpos($name, 'gold') !== false) {
        $keywords = 'gold jewelry luxury elegant';
    }

    elseif (strpos($name, 'shine') !== false) {
        $keywords = 'gold jewelry luxury sparkle';
    }

    elseif (strpos($name, 'charm') !== false) {
        $keywords = 'charm jewelry pink luxury';
    }

    elseif (strpos($name, 'muse') !== false) {
        $keywords = 'pink perfume feminine luxury';
    }

    elseif (strpos($name, 'glow') !== false) {
        $keywords = 'pink beauty skincare glow';
    }

    elseif (strpos($name, 'daily') !== false) {
        $keywords = 'everyday beauty pink cosmetics';
    }

    elseif (strpos($name, 'everyday') !== false) {
        $keywords = 'everyday beauty cosmetics pink';
    }

    elseif (strpos($name, 'soft') !== false) {
        $keywords = 'soft pink beauty perfume';
    }

    elseif (strpos($name, 'petal') !== false) {
        $keywords = 'pink petals perfume beauty';
    }

    /*
    ---------------------------------------------------------
    Category fallback
    ---------------------------------------------------------
    */

    if ($keywords === 'luxury beauty product') {

        if ($category === 'accessories') {
            $keywords = 'elegant jewelry pearls luxury';
        }

        elseif ($category === 'best sellers') {
            $keywords = 'luxury perfume beauty products';
        }

        elseif ($category === 'essentials') {
            $keywords = 'minimal beauty cosmetics pink';
        }

        elseif ($category === 'new arrivals') {
            $keywords = 'new luxury beauty perfume pink';
        }
    }

    /*
    ---------------------------------------------------------
    Unique image per product
    ---------------------------------------------------------
    */

    $lock = abs(crc32(
        ($product['slug'] ?? '') . '-' . ($product['id'] ?? '')
    ));

    /*
    Online image service.
    Query is based on product name/category.
    */

    return 'https://loremflickr.com/900/1100/' .
        rawurlencode($keywords) .
        '?lock=' . $lock;
}

/* =========================================================
   PAGE TITLE
========================================================= */

$pageTitle = "Shop | ÉLORIA";

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =================================================
           GLOBAL
        ================================================= */

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

            background:
                radial-gradient(
                    circle at 15% 10%,
                    rgba(255, 225, 239, 0.55),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 20%,
                    rgba(228, 211, 239, 0.48),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #fffafc 0%,
                    #f9f0f5 45%,
                    #f3edf7 100%
                );

            color: #30252c;

            font-family: "Montserrat", sans-serif;

            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        select,
        input {
            font-family: inherit;
        }

        /* =================================================
           HERO
        ================================================= */

        .shop-hero {
            position: relative;

            min-height: 390px;

            display: flex;
            align-items: center;
            justify-content: center;

            text-align: center;

            padding: 100px 20px 70px;

            overflow: hidden;

            background:
                linear-gradient(
                    rgba(255, 249, 252, 0.72),
                    rgba(248, 237, 245, 0.82)
                ),
                radial-gradient(
                    circle at center,
                    #ead6e3,
                    #f8eef4 55%,
                    #fdf9fb
                );
        }

        .shop-hero::before {
            content: "";

            position: absolute;

            width: 380px;
            height: 380px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.35);

            filter: blur(2px);

            top: -180px;
            left: -100px;
        }

        .shop-hero::after {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            background: rgba(217, 191, 218, 0.18);

            right: -80px;
            bottom: -150px;

            filter: blur(5px);
        }

        .hero-content {
            position: relative;
            z-index: 2;

            max-width: 760px;
        }

        .hero-small {
            display: block;

            margin-bottom: 14px;

            font-size: 10px;

            letter-spacing: 4px;

            text-transform: uppercase;

            color: #927080;
        }

        .shop-hero h1 {
            font-family: "Cormorant Garamond", serif;

            font-size: clamp(55px, 8vw, 92px);

            line-height: 0.9;

            font-weight: 500;

            color: #4b3743;

            letter-spacing: 1px;
        }

        .hero-line {
            width: 55px;
            height: 1px;

            margin: 25px auto;

            background: #9d7b8d;
        }

        .shop-hero p {
            max-width: 550px;

            margin: auto;

            font-size: 12px;

            line-height: 2;

            color: #806d77;

            font-weight: 400;
        }

        /* =================================================
           SHOP CONTAINER
        ================================================= */

        .shop-container {
            width: min(1400px, 92%);

            margin: 0 auto;

            padding: 65px 0 100px;
        }

        /* =================================================
           SEARCH / FILTER BAR
        ================================================= */

        .shop-toolbar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 45px;

            padding: 18px 20px;

            border: 1px solid rgba(151, 119, 136, 0.15);

            border-radius: 18px;

            background: rgba(255, 255, 255, 0.56);

            backdrop-filter: blur(18px);

            box-shadow:
                0 12px 40px rgba(87, 58, 73, 0.06);
        }

        .search-box {
            flex: 1;

            max-width: 440px;

            position: relative;
        }

        .search-box input {
            width: 100%;

            height: 46px;

            border: 1px solid rgba(130, 100, 116, 0.18);

            border-radius: 30px;

            outline: none;

            padding: 0 48px 0 18px;

            background: rgba(255, 255, 255, 0.72);

            color: #44343d;

            font-size: 11px;
        }

        .search-box input:focus {
            border-color: #aa8598;

            box-shadow:
                0 0 0 3px rgba(170, 133, 152, 0.08);
        }

        .search-box button {
            position: absolute;

            right: 6px;
            top: 5px;

            width: 36px;
            height: 36px;

            border: 0;

            border-radius: 50%;

            background: #5e4654;

            color: white;

            cursor: pointer;

            transition: 0.3s ease;
        }

        .search-box button:hover {
            transform: scale(1.06);

            background: #806274;
        }

        .toolbar-right {
            display: flex;

            align-items: center;

            gap: 12px;
        }

        .toolbar-select {
            height: 46px;

            padding: 0 38px 0 15px;

            border: 1px solid rgba(130, 100, 116, 0.18);

            border-radius: 30px;

            background: rgba(255, 255, 255, 0.72);

            color: #59444f;

            font-size: 10px;

            outline: none;

            cursor: pointer;
        }

        /* =================================================
           CATEGORY FILTERS
        ================================================= */

        .category-list {
            display: flex;

            justify-content: center;

            flex-wrap: wrap;

            gap: 10px;

            margin-bottom: 48px;
        }

        .category-pill {
            padding: 11px 20px;

            border-radius: 30px;

            border: 1px solid rgba(135, 105, 121, 0.18);

            background: rgba(255, 255, 255, 0.48);

            color: #806b76;

            font-size: 10px;

            letter-spacing: 0.5px;

            transition: all 0.3s ease;
        }

        .category-pill:hover,
        .category-pill.active {
            color: white;

            background: #6b5060;

            border-color: #6b5060;

            transform: translateY(-2px);

            box-shadow:
                0 8px 20px rgba(86, 60, 74, 0.15);
        }

        /* =================================================
           RESULTS
        ================================================= */

        .results-heading {
            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 25px;
        }

        .results-heading h2 {
            font-family: "Cormorant Garamond", serif;

            font-size: 30px;

            font-weight: 500;

            color: #493742;
        }

        .results-count {
            font-size: 10px;

            color: #927c88;

            letter-spacing: 1px;
        }

        /* =================================================
           PRODUCT GRID
        ================================================= */

        .products-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 26px;
        }

        /* =================================================
           PRODUCT CARD
        ================================================= */

        .product-card {
            position: relative;

            overflow: hidden;

            border-radius: 18px;

            background: rgba(255, 255, 255, 0.64);

            border: 1px solid rgba(144, 111, 128, 0.12);

            box-shadow:
                0 12px 35px rgba(73, 48, 62, 0.06);

            transition:
                transform 0.4s ease,
                box-shadow 0.4s ease;
        }

        .product-card:hover {
            transform: translateY(-7px);

            box-shadow:
                0 22px 50px rgba(73, 48, 62, 0.12);
        }

        /* =================================================
           IMAGE
        ================================================= */

        .product-image {
            position: relative;

            height: 390px;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #f9edf3,
                    #eee4f1
                );
        }

        .product-image img {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;

            transition:
                transform 0.7s ease,
                opacity 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.055);
        }

        /* =================================================
           BADGES
        ================================================= */

        .product-badges {
            position: absolute;

            top: 14px;
            left: 14px;

            display: flex;

            flex-direction: column;

            gap: 6px;

            z-index: 3;
        }

        .badge {
            padding: 6px 10px;

            border-radius: 20px;

            background: rgba(255, 255, 255, 0.82);

            backdrop-filter: blur(8px);

            color: #674d5c;

            font-size: 8px;

            text-transform: uppercase;

            letter-spacing: 1px;
        }

        .badge.new {
            background: #654b59;

            color: white;
        }

        /* =================================================
           WISHLIST
        ================================================= */

        .wishlist-btn {
            position: absolute;

            top: 14px;
            right: 14px;

            z-index: 5;

            width: 39px;
            height: 39px;

            display: flex;

            align-items: center;
            justify-content: center;

            border: 1px solid rgba(255,255,255,0.6);

            border-radius: 50%;

            background: rgba(255,255,255,0.72);

            backdrop-filter: blur(10px);

            cursor: pointer;

            font-size: 17px;

            color: #725967;

            transition: 0.3s ease;
        }

        .wishlist-btn:hover {
            transform: scale(1.08);

            background: white;
        }

        .wishlist-btn.active {
            color: #9d5274;
        }

        /* =================================================
           IMAGE FALLBACK
        ================================================= */

        .image-fallback {
            position: absolute;

            inset: 0;

            display: none;

            align-items: center;

            justify-content: center;

            flex-direction: column;

            gap: 7px;

            text-align: center;

            background:
                linear-gradient(
                    135deg,
                    #f9edf3,
                    #eee4f3
                );
        }

        .image-fallback span {
            font-family: "Cormorant Garamond", serif;

            font-size: 30px;

            letter-spacing: 4px;

            color: #725868;
        }

        .image-fallback small {
            font-size: 8px;

            text-transform: uppercase;

            letter-spacing: 2px;

            color: #9a818e;
        }

        .product-image.no-image .image-fallback {
            display: flex;
        }

        .product-image.no-image img {
            display: none;
        }

        /* =================================================
           PRODUCT INFO
        ================================================= */

        .product-info {
            padding: 20px 19px 22px;
        }

        .product-category {
            display: block;

            margin-bottom: 7px;

            color: #a07e8e;

            font-size: 8px;

            text-transform: uppercase;

            letter-spacing: 1.5px;
        }

        .product-name {
            font-family: "Cormorant Garamond", serif;

            font-size: 25px;

            line-height: 1.1;

            font-weight: 600;

            color: #45333d;

            margin-bottom: 8px;
        }

        .product-description {
            min-height: 38px;

            margin-bottom: 15px;

            color: #8b7680;

            font-size: 9px;

            line-height: 1.7;

            display: -webkit-box;

            -webkit-line-clamp: 2;

            -webkit-box-orient: vertical;

            overflow: hidden;
        }

        .product-price-row {
            display: flex;

            align-items: center;

            gap: 9px;

            margin-bottom: 17px;
        }

        .product-price {
            font-family: "Cormorant Garamond", serif;

            font-size: 23px;

            font-weight: 600;

            color: #59414e;
        }

        .old-price {
            color: #ad9aa4;

            font-size: 10px;

            text-decoration: line-through;
        }

        .view-product {
            width: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            height: 43px;

            border-radius: 25px;

            background: #604957;

            color: white;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            transition: all 0.3s ease;
        }

        .view-product:hover {
            background: #806272;

            transform: translateY(-2px);

            box-shadow:
                0 8px 20px rgba(83, 60, 72, 0.15);
        }

        /* =================================================
           EMPTY STATE
        ================================================= */

        .empty-shop {
            grid-column: 1 / -1;

            padding: 90px 20px;

            text-align: center;

            border-radius: 20px;

            background: rgba(255,255,255,0.5);

            border: 1px solid rgba(140,110,125,0.12);
        }

        .empty-shop h3 {
            font-family: "Cormorant Garamond", serif;

            font-size: 35px;

            font-weight: 500;

            color: #58424e;

            margin-bottom: 10px;
        }

        .empty-shop p {
            color: #927d88;

            font-size: 11px;

            margin-bottom: 22px;
        }

        .clear-search {
            display: inline-flex;

            padding: 11px 22px;

            border-radius: 25px;

            background: #654d5b;

            color: white;

            font-size: 9px;

            letter-spacing: 1px;

            text-transform: uppercase;
        }

        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 1150px) {

            .products-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .product-image {
                height: 350px;
            }
        }

        @media (max-width: 850px) {

            .shop-toolbar {
                flex-direction: column;

                align-items: stretch;
            }

            .search-box {
                max-width: none;
            }

            .toolbar-right {
                justify-content: space-between;
            }

            .products-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

                gap: 18px;
            }

            .product-image {
                height: 330px;
            }
        }

        @media (max-width: 520px) {

            .shop-container {
                width: 94%;

                padding-top: 40px;
            }

            .shop-hero {
                min-height: 330px;

                padding-top: 80px;
            }

            .shop-hero h1 {
                font-size: 60px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-image {
                height: 420px;
            }

            .results-heading {
                align-items: flex-end;
            }

            .results-heading h2 {
                font-size: 27px;
            }

            .toolbar-right {
                width: 100%;
            }

            .toolbar-select {
                flex: 1;
            }
        }

        /* =========================================================
   NAVBAR PROTECTION
   Shop page navbar must remain exactly like home.php
========================================================= */

header,
nav,
.navbar,
.navbar *,
.site-header,
.site-header * {
    box-sizing: border-box;
}

.navbar,
.site-header {
    font-family: inherit;
}

.navbar a,
.site-header a {
    text-decoration: none;
}

.navbar ul,
.navbar li,
.site-header ul,
.site-header li {
    margin: 0;
    padding: 0;
    list-style: none;
}

    </style>
<link rel="stylesheet" href="assets/css/home.css">
</head>

<body>

<?php
/*
|--------------------------------------------------------------------------
| EXISTING ÉLORIA NAVBAR
|--------------------------------------------------------------------------
*/

include "includes/navbar.php";
?>


<!-- =====================================================
     SHOP HERO
====================================================== -->

<section class="shop-hero">

    <div class="hero-content">

        <span class="hero-small">
            The ÉLORIA Collection
        </span>

        <h1>Shop</h1>

        <div class="hero-line"></div>

        <p>
            Discover thoughtfully selected pieces designed
            to bring a little softness, elegance and luxury
            into your everyday moments.
        </p>

    </div>

</section>


<!-- =====================================================
     SHOP
====================================================== -->

<main class="shop-container">


    <!-- SEARCH + SORT -->

    <div class="shop-toolbar">

        <form
            method="GET"
            action="shop.php"
            class="search-box"
        >

            <?php if ($category !== ''): ?>

                <input
                    type="hidden"
                    name="category"
                    value="<?php echo htmlspecialchars($category); ?>"
                >

            <?php endif; ?>

            <input
                type="text"
                name="search"
                placeholder="Search your ÉLORIA piece..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">
                ⌕
            </button>

        </form>


        <div class="toolbar-right">

            <select
                class="toolbar-select"
                onchange="changeSort(this.value)"
            >

                <option value="latest"
                    <?php echo $sort === 'latest' ? 'selected' : ''; ?>>
                    Latest
                </option>

                <option value="price-low"
                    <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>
                    Price: Low to High
                </option>

                <option value="price-high"
                    <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>
                    Price: High to Low
                </option>

                <option value="name"
                    <?php echo $sort === 'name' ? 'selected' : ''; ?>>
                    Name
                </option>

            </select>

        </div>

    </div>


    <!-- =================================================
         CATEGORIES
    ================================================== -->

    <div class="category-list">

        <a
            href="shop.php"
            class="category-pill <?php echo $category === '' ? 'active' : ''; ?>"
        >
            All Pieces
        </a>


        <?php foreach ($categories as $cat): ?>

            <a
                href="shop.php?category=<?php echo urlencode($cat['slug']); ?>"
                class="category-pill <?php echo $category === $cat['slug'] ? 'active' : ''; ?>"
            >
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>

        <?php endforeach; ?>

    </div>


    <!-- =================================================
         RESULTS HEADING
    ================================================== -->

    <div class="results-heading">

        <h2>
            <?php

            if ($category !== '') {

                foreach ($categories as $cat) {

                    if ($cat['slug'] === $category) {
                        echo htmlspecialchars($cat['name']);
                        break;
                    }
                }

            } elseif ($search !== '') {

                echo 'Search Results';

            } else {

                echo 'All Pieces';

            }

            ?>
        </h2>


        <span class="results-count">

            <?php echo count($products); ?>

            <?php echo count($products) === 1 ? 'piece' : 'pieces'; ?>

        </span>

    </div>


    <!-- =================================================
         PRODUCTS
    ================================================== -->

    <div class="products-grid">


        <?php if (empty($products)): ?>

            <div class="empty-shop">

                <h3>
                    Nothing found
                </h3>

                <p>
                    We couldn't find a piece matching your search.
                </p>

                <a
                    href="shop.php"
                    class="clear-search"
                >
                    View All Pieces
                </a>

            </div>


        <?php else: ?>


            <?php foreach ($products as $product): ?>

                <?php

                $image = getProductImage($product);

                $isWishlist =
                    in_array(
                        (int)$product['id'],
                        $wishlistIds
                    );

                ?>

                <article class="product-card">


                    <!-- IMAGE -->

                    <div class="product-image">

                        <img
                            src="<?php echo htmlspecialchars(
                                $image,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            alt="<?php echo htmlspecialchars(
                                $product['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            loading="lazy"

                            onerror="
                                this.style.display='none';
                                this.parentElement.classList.add('no-image');
                            "
                        >


                        <div class="image-fallback">

                            <span>ÉLORIA</span>

                            <small>
                                Image Coming Soon
                            </small>

                        </div>


                        <!-- BADGES -->

                        <div class="product-badges">

                            <?php if ((int)$product['is_new'] === 1): ?>

                                <span class="badge new">
                                    New
                                </span>

                            <?php endif; ?>


                            <?php if ((int)$product['is_featured'] === 1): ?>

                                <span class="badge">
                                    Loved
                                </span>

                            <?php endif; ?>

                        </div>


                        <!-- WISHLIST -->

                        <button
                            type="button"
                            class="wishlist-btn <?php echo $isWishlist ? 'active' : ''; ?>"
                            data-product-id="<?php echo (int)$product['id']; ?>"
                            title="Add to wishlist"
                        >
                            <?php echo $isWishlist ? '♥' : '♡'; ?>
                        </button>

                    </div>


                    <!-- INFO -->

                    <div class="product-info">

                        <span class="product-category">

                            <?php echo htmlspecialchars(
                                $product['category_name'] ?? 'ÉLORIA'
                            ); ?>

                        </span>


                        <h3 class="product-name">

                            <?php echo htmlspecialchars(
                                $product['name']
                            ); ?>

                        </h3>


                        <p class="product-description">

                            <?php

                            echo htmlspecialchars(
                                !empty($product['description'])
                                    ? $product['description']
                                    : 'A beautiful piece from the ÉLORIA collection.'
                            );

                            ?>

                        </p>


                        <div class="product-price-row">

                            <span class="product-price">

                                Rs.
                                <?php echo number_format(
                                    (float)$product['price']
                                ); ?>

                            </span>


                            <?php if (
                                !empty($product['old_price']) &&
                                (float)$product['old_price'] > (float)$product['price']
                            ): ?>

                                <span class="old-price">

                                    Rs.
                                    <?php echo number_format(
                                        (float)$product['old_price']
                                    ); ?>

                                </span>

                            <?php endif; ?>

                        </div>


                        <a
                            href="product.php?id=<?php echo (int)$product['id']; ?>"
                            class="view-product"
                        >
                            View Piece
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</main>


<?php
/*
|--------------------------------------------------------------------------
| EXISTING ÉLORIA FOOTER
|--------------------------------------------------------------------------
*/

if (file_exists("includes/footer.php")) {
    include "includes/footer.php";
}
?>


<script>

    /* =====================================================
       SORT
    ===================================================== */

    function changeSort(value) {

        const url = new URL(window.location.href);

        url.searchParams.set("sort", value);

        window.location.href = url.toString();
    }


    /* =====================================================
       WISHLIST
    ===================================================== */

    document.querySelectorAll(".wishlist-btn").forEach(button => {

        button.addEventListener("click", function () {

            const productId = this.dataset.productId;

            fetch("wishlist_action.php", {

                method: "POST",

                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },

                body:
                    "product_id=" +
                    encodeURIComponent(productId)

            })

            .then(response => response.json())

            .then(data => {

                if (data.success) {

                    this.classList.toggle("active");

                    this.textContent =
                        data.action === "added"
                            ? "♥"
                            : "♡";

                }

                else {

                    console.log(
                        data.message || "Wishlist error"
                    );

                }

            })

            .catch(error => {

                console.error(
                    "Wishlist error:",
                    error
                );

            });

        });

    });

</script>


</body>
</html>