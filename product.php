<?php
session_start();
require_once "db.php";

$user_name = $_SESSION['user_name'] ?? 'User';



/* --------------------------------
   GET PRODUCT ID
--------------------------------- */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: shop.php");
    exit;
}

$product_id = (int) $_GET['id'];


/* --------------------------------
   FETCH PRODUCT
--------------------------------- */

$sql = "SELECT * FROM products WHERE id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed.");
}

mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: shop.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* --------------------------------
   PRODUCT DATA
--------------------------------- */

$name = $product['name'] ?? 'Product';

$price = $product['price'] ?? 0;

$old_price = $product['old_price'] ?? 0;

$description = $product['description']
    ?? 'Beautifully designed for your everyday style.';

$image = $product['image'] ?? '';

$sizes = $product['sizes'] ?? 'S,M,L,XL';


/* --------------------------------
   CATEGORY
--------------------------------- */

/*
| Your products table uses category_id.
| So we fetch the category name separately.
*/

$category = '';

if (
    isset($product['category_id']) &&
    !empty($product['category_id'])
) {

    $categoryStmt = mysqli_prepare(
        $conn,
        "SELECT name FROM categories WHERE id = ? LIMIT 1"
    );

    if ($categoryStmt) {

        $category_id = (int) $product['category_id'];

        mysqli_stmt_bind_param(
            $categoryStmt,
            "i",
            $category_id
        );

        mysqli_stmt_execute($categoryStmt);

        $categoryResult =
            mysqli_stmt_get_result($categoryStmt);

        if (
            $categoryResult &&
            mysqli_num_rows($categoryResult) > 0
        ) {

            $categoryRow =
                mysqli_fetch_assoc($categoryResult);

            $category =
                $categoryRow['name'] ?? '';

        }

        mysqli_stmt_close($categoryStmt);
    }
}


/* --------------------------------
   IMAGE PATH
--------------------------------- */

/*
| Product images are stored inside:
| assets/images/products/
*/

if (!empty($image)) {

    if (strpos($image, 'http') === 0) {

        $image_path = $image;

    } else {

        $image_path =
            "assets/images/products/" . $image;
    }

} else {

    /*
    | No placeholder image.
    | This prevents the 404 loop.
    */

    $image_path = "";

}


/* --------------------------------
   SIZE ARRAY
--------------------------------- */

$size_array = array_filter(
    array_map(
        'trim',
        explode(',', $sizes)
    )
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($name); ?> | ÉLORIA
    </title>


    <!-- Bootstrap -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >


    <!-- Product CSS -->

    <!-- <link
        rel="stylesheet"
        href="assets/css/product.css"
    > -->

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<link rel="stylesheet" href="assets/css/product.css">

</head>

<style>
header.navbar {
    width: 100%;
    background: #fffaf8 !important;
    border-bottom: 1px solid #eaded9;
    position: relative;
    z-index: 9999;
}

header.navbar .nav-inner {
    width: 92%;
    max-width: 1400px;
    min-height: 80px;
    margin: 0 auto;

    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

header.navbar .nav-logo {
    color: #3e2925 !important;
    text-decoration: none !important;
    font-family: Georgia, serif !important;
    font-size: 25px !important;
    letter-spacing: 4px !important;
}

header.navbar .nav-logo span {
    font-size: 32px !important;
}

header.navbar .desktop-nav {
    display: flex !important;
    align-items: center !important;
    gap: 35px !important;
}

header.navbar .desktop-nav a {
    color: #604b46 !important;
    text-decoration: none !important;
    font-size: 14px !important;
    padding: 8px 0 !important;
}

header.navbar .desktop-nav a:hover,
header.navbar .desktop-nav a.active {
    color: #9a6f63 !important;
}

header.navbar .nav-actions {
    display: flex !important;
    align-items: center !important;
    gap: 15px !important;
}

header.navbar .nav-icon {
    color: #4d3732 !important;
    text-decoration: none !important;
    font-size: 22px !important;
}

header.navbar .user-button {
    border: none !important;
    background: transparent !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    color: #4d3732 !important;
}

header.navbar .user-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ead8d2;
    display: flex;
    align-items: center;
    justify-content: center;
}

header.navbar .menu-toggle {
    display: none;
}

@media (max-width: 900px) {

    header.navbar .desktop-nav {
        display: none !important;
    }

    header.navbar .menu-toggle {
        display: block !important;
        border: none !important;
        background: transparent !important;
        font-size: 25px !important;
    }

    header.navbar .user-name {
        display: none;
    }
}
</style>


<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<?php include "includes/navbar.php"; ?>

<!-- <nav class="navbar navbar-expand-lg eloria-navbar">

    <div class="container">


        LOGO -->

        <!-- <a
            class="navbar-brand eloria-logo"
            href="index.php"
        >

            ÉLORIA

        </a> -->


        <!-- MOBILE BUTTON -->
<!-- 
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >

            <span class="navbar-toggler-icon"></span>

        </button> -->


        <!-- NAVIGATION -->

        <!-- <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav mx-auto">


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="index.php"
                    >
                        Home
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="shop.php"
                    >
                        Shop
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="about.php"
                    >
                        About
                    </a>

                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="contact.php"
                    >
                        Contact
                    </a>

                </li>

            </ul> -->


            <!-- NAV ICONS -->

            <!-- <div class="nav-icons">


                <a
                    href="wishlist.php"
                    class="nav-icon"
                    title="Wishlist"
                >
                    ♡
                </a>


                <a
                    href="cart.php"
                    class="nav-icon"
                    title="Cart"
                >
                    🛍
                </a>


            </div>

        </div>

    </div>

</nav>
 -->

<!-- ==========================================
     PRODUCT DETAILS
========================================== -->

<section class="product-details-section">

    <div class="container">


        <!-- BREADCRUMB -->

        <div class="product-breadcrumb">

            <a href="index.php">
                Home
            </a>

            <span>/</span>

            <a href="shop.php">
                Shop
            </a>

            <span>/</span>

            <span>
                <?= htmlspecialchars($name); ?>
            </span>

        </div>


        <div class="row g-5 align-items-center">


            <!-- ==================================
                 PRODUCT IMAGE
            =================================== -->

            <div class="col-lg-6">

                <div class="product-image-wrapper">


                    <?php if (!empty($image_path)): ?>

                        <img
                            src="<?= htmlspecialchars($image_path); ?>"
                            alt="<?= htmlspecialchars($name); ?>"
                            class="product-main-image"
                            onerror="this.style.display='none';"
                        >

                    <?php else: ?>

                        <div
                            class="product-image-placeholder"
                            style="
                                width:100%;
                                height:100%;
                                min-height:500px;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                flex-direction:column;
                                background:#f8e8e5;
                                color:#604b46;
                            "
                        >

                            <strong
                                style="
                                    font-family:Georgia,serif;
                                    font-size:36px;
                                    letter-spacing:3px;
                                "
                            >
                                ÉLORIA
                            </strong>

                            <span
                                style="
                                    margin-top:8px;
                                    font-size:13px;
                                    opacity:.7;
                                "
                            >
                                Image coming soon
                            </span>

                        </div>

                    <?php endif; ?>


                    <!-- IMAGE WISHLIST -->

                    <button
                        type="button"
                        class="image-wishlist"
                        id="wishlistBtn"
                        data-product-id="<?= $product_id; ?>"
                        title="Add to Wishlist"
                    >

                        ♡

                    </button>


                </div>

            </div>


            <!-- ==================================
                 PRODUCT INFORMATION
            =================================== -->

            <div class="col-lg-6">

                <div class="product-information">


                    <!-- CATEGORY -->

                    <?php if (!empty($category)): ?>

                        <span class="product-category">

                            <?= htmlspecialchars($category); ?>

                        </span>

                    <?php endif; ?>


                    <!-- PRODUCT NAME -->

                    <h1 class="product-title">

                        <?= htmlspecialchars($name); ?>

                    </h1>


                    <!-- RATING -->

                    <div class="product-rating">

                        <span>
                            ★★★★★
                        </span>

                        <small>
                            Loved by ÉLORIA customers
                        </small>

                    </div>


                    <!-- PRICE -->

                    <div class="product-price">

                        Rs.
                        <?= number_format(
                            (float) $price,
                            2
                        ); ?>


                        <?php if (
                            !empty($old_price) &&
                            $old_price > $price
                        ): ?>

                            <del
                                style="
                                    margin-left:12px;
                                    font-size:16px;
                                    color:#a99a96;
                                "
                            >

                                Rs.
                                <?= number_format(
                                    (float) $old_price,
                                    2
                                ); ?>

                            </del>

                        <?php endif; ?>

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="product-description">

                        <p>

                            <?= nl2br(
                                htmlspecialchars($description)
                            ); ?>

                        </p>

                    </div>


                    <div class="product-divider"></div>


                    <!-- ==================================
                         SIZE
                    =================================== -->

                    <?php if (!empty($size_array)): ?>

                        <div class="size-section">


                            <div class="size-heading">

                                <strong>
                                    Select Size
                                </strong>

                                <span>
                                    Size Guide
                                </span>

                            </div>


                            <div class="size-options">


                                <?php foreach (
                                    $size_array
                                    as $index => $size
                                ): ?>

                                    <button
                                        type="button"
                                        class="size-btn <?= $index === 0 ? 'active' : ''; ?>"
                                        data-size="<?= htmlspecialchars($size); ?>"
                                    >

                                        <?= htmlspecialchars($size); ?>

                                    </button>

                                <?php endforeach; ?>


                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- ==================================
                         QUANTITY
                    =================================== -->

                    <div class="quantity-section">


                        <strong>
                            Quantity
                        </strong>


                        <div class="quantity-control">


                            <button
                                type="button"
                                id="minusBtn"
                            >

                                −

                            </button>


                            <input
                                type="number"
                                id="quantity"
                                value="1"
                                min="1"
                                max="10"
                                readonly
                            >


                            <button
                                type="button"
                                id="plusBtn"
                            >

                                +

                            </button>


                        </div>

                    </div>


                    <!-- ==================================
                         ADD TO CART
                    =================================== -->

                    <button
                        type="button"
                        class="add-cart-btn"
                        id="addToCartBtn"
                        data-product-id="<?= (int) $product['id']; ?>"
                    >

                        <span>
                            Add to Cart
                        </span>

                        <span>
                            →
                        </span>

                    </button>


                    <!-- ==================================
                         WISHLIST
                    =================================== -->

                    <button
                        type="button"
                        class="wishlist-btn"
                        id="wishlistTextBtn"
                        data-product-id="<?= $product_id; ?>"
                    >

                        ♡
                        Add to Wishlist

                    </button>


                    <!-- BACK TO SHOP -->

                    <a
                        href="shop.php"
                        class="back-shop"
                    >

                        ← Continue Shopping

                    </a>


                </div>

            </div>

        </div>

    </div>

</section>


<!-- ==========================================
     FOOTER
========================================== -->

<footer class="eloria-footer">

    <div class="container">

        <div class="footer-content">


            <div>

                <h3>
                    ÉLORIA
                </h3>

                <p>
                    Elegance made for you.
                </p>

            </div>


            <div class="footer-links">

                <a href="shop.php">
                    Shop
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="contact.php">
                    Contact
                </a>

            </div>


        </div>


        <div class="footer-bottom">

            © <?= date('Y'); ?> ÉLORIA.
            All rights reserved.

        </div>

    </div>

</footer>


<!-- Bootstrap -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    console.log("ÉLORIA product page loaded");

    const minusBtn = document.getElementById("minusBtn");
    const plusBtn = document.getElementById("plusBtn");
    const quantityInput = document.getElementById("quantity");
    const addToCartBtn = document.getElementById("addToCartBtn");

    const sizeButtons = document.querySelectorAll(".size-btn");


    /* =========================
       SIZE
    ========================= */

    sizeButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            sizeButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            this.classList.add("active");

        });

    });


    /* =========================
       MINUS
    ========================= */

    if (minusBtn) {

        minusBtn.addEventListener("click", function () {

            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity > 1) {
                quantity--;
            }

            quantityInput.value = quantity;

        });

    }


    /* =========================
       PLUS
    ========================= */

    if (plusBtn) {

        plusBtn.addEventListener("click", function () {

            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity < 10) {
                quantity++;
            }

            quantityInput.value = quantity;

        });

    }


    /* =========================
       ADD TO CART
    ========================= */

    if (addToCartBtn) {

        addToCartBtn.addEventListener("click", function () {

            const productId = this.getAttribute("data-product-id");

            const quantity = quantityInput
                ? parseInt(quantityInput.value) || 1
                : 1;

            const selectedSize =
                document.querySelector(".size-btn.active");

            let size = "";

            if (selectedSize) {
                size = selectedSize.getAttribute("data-size");
            }


            /* SIZE REQUIRED */

            if (sizeButtons.length > 0 && !selectedSize) {

                alert("Please select a size first.");

                return;
            }


            /* BUTTON LOADING */

            addToCartBtn.disabled = true;

            addToCartBtn.innerHTML = "Adding...";


            /* SEND TO PHP */

            const formData = new URLSearchParams();

            formData.append("product_id", productId);
            formData.append("quantity", quantity);
            formData.append("size", size);


            fetch("add_to_cart.php", {

                method: "POST",

                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },

                body: formData.toString()

            })
            .then(function (response) {

                return response.text();

            })
            .then(function (text) {

                console.log("PHP RESPONSE:", text);


                let data;

                try {

                    data = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "PHP did not return JSON:",
                        text
                    );

                    throw error;
                }


                /* SUCCESS */

                if (data.success === true) {

                    console.log("PRODUCT ADDED:", data);


                    /* NAVBAR COUNT */

                    const cartCount =
                        document.querySelector(".cart-count");

                    if (cartCount) {
                        cartCount.textContent =
                            data.cart_count;
                    }


                    /* BUTTON */

                    addToCartBtn.innerHTML =
                        "Added to Cart ✓";


                    setTimeout(function () {

                        addToCartBtn.innerHTML =
                            "Add to Cart →";

                        addToCartBtn.disabled = false;

                    }, 1500);

                }


                /* PHP ERROR */

                else {

                    console.error(
                        "PHP ERROR:",
                        data.message
                    );

                    alert(
                        data.message ||
                        "Unable to add product to cart."
                    );

                    addToCartBtn.innerHTML =
                        "Add to Cart →";

                    addToCartBtn.disabled = false;

                }

            })
            .catch(function (error) {

                console.error(
                    "ADD TO CART ERROR:",
                    error
                );

                alert(
                    "Something went wrong while adding to cart."
                );

                addToCartBtn.innerHTML =
                    "Add to Cart →";

                addToCartBtn.disabled = false;

            });

        });

    }

});
</script>


<!-- Product JS -->

<script
    src="assets/js/product.js"
></script>




</body>

</html>