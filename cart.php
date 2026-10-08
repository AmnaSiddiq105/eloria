<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$user_id = (int) $_SESSION['user_id'];

$user_name = $_SESSION['user_name'] ?? "Customer";


/*
|--------------------------------------------------------------------------
| UPDATE / REMOVE CART
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $cart_id = (int) ($_POST['cart_id'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | REMOVE ITEM
    |--------------------------------------------------------------------------
    */

    if ($action === 'remove' && $cart_id > 0) {

        $deleteStmt = $conn->prepare("
            DELETE FROM cart
            WHERE id = ?
            AND user_id = ?
        ");

        if ($deleteStmt) {

            $deleteStmt->bind_param(
                "ii",
                $cart_id,
                $user_id
            );

            $deleteStmt->execute();

            $deleteStmt->close();
        }

        header("Location: cart.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE QUANTITY
    |--------------------------------------------------------------------------
    */

    if ($action === 'update_quantity' && $cart_id > 0) {

        $new_quantity = (int) ($_POST['quantity'] ?? 1);

        if ($new_quantity < 1) {
            $new_quantity = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Get product stock first
        |--------------------------------------------------------------------------
        */

        $stockStmt = $conn->prepare("
            SELECT p.stock
            FROM cart c
            INNER JOIN products p
                ON c.product_id = p.id
            WHERE c.id = ?
            AND c.user_id = ?
            LIMIT 1
        ");

        if ($stockStmt) {

            $stockStmt->bind_param(
                "ii",
                $cart_id,
                $user_id
            );

            $stockStmt->execute();

            $stockResult = $stockStmt->get_result();

            if ($stockResult && $stockResult->num_rows > 0) {

                $stockRow = $stockResult->fetch_assoc();

                $stock = (int) $stockRow['stock'];


                /*
                |--------------------------------------------------------------------------
                | Don't allow quantity greater than stock
                |--------------------------------------------------------------------------
                */

                if ($stock > 0 && $new_quantity > $stock) {
                    $new_quantity = $stock;
                }
            }

            $stockStmt->close();
        }


        /*
        |--------------------------------------------------------------------------
        | Update cart
        |--------------------------------------------------------------------------
        */

        $updateStmt = $conn->prepare("
            UPDATE cart
            SET quantity = ?
            WHERE id = ?
            AND user_id = ?
        ");

        if ($updateStmt) {

            $updateStmt->bind_param(
                "iii",
                $new_quantity,
                $cart_id,
                $user_id
            );

            $updateStmt->execute();

            $updateStmt->close();
        }

        header("Location: cart.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE HELPER
|--------------------------------------------------------------------------
|
| Priority:
|
| 1. Database image
| 2. Existing local image
| 3. Automatic product image
| 4. Final placeholder handled in HTML
|
*/

function getCartProductImage($item)
{
    $image = trim($item['image'] ?? '');

    $slug = strtolower(
        trim($item['slug'] ?? '')
    );

    $name = strtolower(
        trim($item['name'] ?? '')
    );

    $productId = (int) (
        $item['product_id'] ?? 0
    );


    /*
    |--------------------------------------------------------------------------
    | 1. Database image
    |--------------------------------------------------------------------------
    */

    if (!empty($image)) {

        /*
        | Full URL
        */

        if (
            strpos($image, 'http://') === 0 ||
            strpos($image, 'https://') === 0
        ) {

            return $image;
        }


        /*
        | Local image
        */

        $localFile =
            __DIR__ .
            "/assets/images/products/" .
            $image;


        if (file_exists($localFile)) {

            return
                "assets/images/products/" .
                $image;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 2. Try slug.jpg
    |--------------------------------------------------------------------------
    */

    if (!empty($slug)) {

        $possibleExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        foreach ($possibleExtensions as $extension) {

            $possibleFile =
                __DIR__ .
                "/assets/images/products/" .
                $slug .
                "." .
                $extension;


            if (file_exists($possibleFile)) {

                return
                    "assets/images/products/" .
                    $slug .
                    "." .
                    $extension;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 3. Automatic image
    |--------------------------------------------------------------------------
    */

    $keyword = $slug;


    if (empty($keyword)) {
        $keyword = $name;
    }


    /*
    | Convert slug into search keywords
    |
    | blush-bloom
    | becomes
    | blush,bloom
    */

    $keyword = str_replace(
        ['-', '_'],
        ',',
        $keyword
    );


    /*
    | Automatic image.
    |
    | Product ID is used as lock so each
    | product gets a stable image.
    */

    if (!empty($keyword)) {

        return
            "https://loremflickr.com/700/850/" .
            rawurlencode($keyword) .
            "?lock=" .
            $productId;
    }


    /*
    |--------------------------------------------------------------------------
    | 4. Final placeholder
    |--------------------------------------------------------------------------
    */

    return
        "https://placehold.co/700x850/f8eeeb/604b46?text=ELORIA";
}


/*
|--------------------------------------------------------------------------
| GET CART ITEMS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,
        c.size,

        p.name,
        p.slug,
        p.price,
        p.old_price,
        p.image,
        p.stock,

        cat.name AS category_name

    FROM cart c

    INNER JOIN products p
        ON c.product_id = p.id

    LEFT JOIN categories cat
        ON p.category_id = cat.id

    WHERE c.user_id = ?

    ORDER BY c.created_at DESC
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die("Unable to load cart.");

}


$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| CALCULATE CART
|--------------------------------------------------------------------------
*/

$cartItems = [];

$subtotal = 0;


while ($row = $result->fetch_assoc()) {

    $row['quantity'] =
        (int) $row['quantity'];

    $row['price'] =
        (float) $row['price'];

    $row['stock'] =
        (int) $row['stock'];


    $row['item_total'] =
        $row['price'] *
        $row['quantity'];


    $subtotal +=
        $row['item_total'];


    $cartItems[] =
        $row;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| DELIVERY CHARGES
|--------------------------------------------------------------------------
*/

$deliveryCharges = 250;


/*
|--------------------------------------------------------------------------
| FREE DELIVERY
|--------------------------------------------------------------------------
*/

if ($subtotal >= 5000) {

    $deliveryCharges = 0;

}


/*
|--------------------------------------------------------------------------
| GRAND TOTAL
|--------------------------------------------------------------------------
*/

$total =
    $subtotal +
    $deliveryCharges;

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
        ÉLORIA | Shopping Cart
    </title>


    <!-- Google Fonts -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Home CSS -->

    <link
        rel="stylesheet"
        href="assets/css/home.css"
    >


    <!-- Cart CSS -->

    <link
        rel="stylesheet"
        href="assets/css/cart.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | Quantity Forms
        |--------------------------------------------------------------------------
        */

        .cart-quantity-form {
            display: inline;
            margin: 0;
            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Size
        |--------------------------------------------------------------------------
        */

        .cart-item-size {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin: 8px 0;
            padding: 6px 12px;

            background: #f8eeeb;

            border: 1px solid #eadbd6;

            color: #604b46;

            font-size: 12px;

            letter-spacing: 1px;

            text-transform: uppercase;
        }


        .cart-item-size strong {
            color: #3e2925;
        }


        /*
        |--------------------------------------------------------------------------
        | Quantity Button
        |--------------------------------------------------------------------------
        */

        .cart-quantity .quantity-btn {
            cursor: pointer;

            transition: 0.2s ease;
        }


        .cart-quantity .quantity-btn:hover {
            background: #ead8d2;

            color: #3e2925;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Button
        |--------------------------------------------------------------------------
        */

        .remove-cart-btn {
            cursor: pointer;

            border: none;

            background: transparent;
        }


        .remove-cart-btn:hover {
            color: #9a6f63;
        }


        /*
        |--------------------------------------------------------------------------
        | CART PRODUCT IMAGE
        |--------------------------------------------------------------------------
        */

        .cart-item-image {
            position: relative;

            width: 145px;
            min-width: 145px;

            height: 175px;

            overflow: hidden;

            background: #f8eeeb;

            border-radius: 8px;
        }


        .cart-item-image img {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: cover;

            transition:
                transform 0.5s ease,
                opacity 0.3s ease;
        }


        .cart-item:hover .cart-item-image img {
            transform: scale(1.04);
        }


        /*
        |--------------------------------------------------------------------------
        | Image fallback
        |--------------------------------------------------------------------------
        */

        .cart-image-placeholder {
            position: absolute;

            inset: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #f8eeeb,
                    #ead8d2
                );

            color: #604b46;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 28px;

            letter-spacing: 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Hide placeholder when image loads
        |--------------------------------------------------------------------------
        */

        .cart-image-placeholder {
            z-index: 0;
        }


        .cart-item-image img {
            position: relative;

            z-index: 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 600px) {

            .cart-item-image {

                width: 105px;
                min-width: 105px;

                height: 135px;
            }

        }

    </style>

</head>


<body>


<?php include "includes/navbar.php"; ?>


<!-- =========================================================
     CART HERO
========================================================= -->

<section class="cart-hero">

    <div class="cart-hero-content">

        <span class="cart-label">
            YOUR ÉLORIA BAG
        </span>

        <h1>
            Shopping <em>Cart.</em>
        </h1>

        <p>
            Your selected pieces, ready when you are.
        </p>

    </div>

</section>


<!-- =========================================================
     CART PAGE
========================================================= -->

<main class="cart-page">


<?php if (count($cartItems) > 0): ?>


    <div class="cart-layout">


        <!-- =================================================
             CART ITEMS
        ================================================== -->

        <section class="cart-items-section">


            <div class="cart-section-heading">

                <div>

                    <span>
                        YOUR SELECTION
                    </span>

                    <h2>
                        Shopping Bag
                    </h2>

                </div>

                <p>

                    <?php echo count($cartItems); ?>

                    <?php

                    echo count($cartItems) === 1
                        ? 'item'
                        : 'items';

                    ?>

                </p>

            </div>


            <?php foreach ($cartItems as $item): ?>


                <?php

                /*
                |--------------------------------------------------------------------------
                | PRODUCT IMAGE
                |--------------------------------------------------------------------------
                */

                $imagePath =
                    getCartProductImage($item);

                ?>


                <article
                    class="cart-item"
                    data-cart-id="<?php echo $item['cart_id']; ?>"
                >


                    <!-- =====================================
                         PRODUCT IMAGE
                    ====================================== -->

                    <div class="cart-item-image">

                        <img
                            src="<?php echo htmlspecialchars($imagePath); ?>"
                            alt="<?php echo htmlspecialchars($item['name']); ?>"
                            loading="lazy"
                            onerror="
                                this.onerror=null;
                                this.src='https://placehold.co/700x850/f8eeeb/604b46?text=ELORIA';
                            "
                        >

                        <div class="cart-image-placeholder">

                            <span>
                                ÉLORIA
                            </span>

                        </div>

                    </div>


                    <!-- =====================================
                         PRODUCT DETAILS
                    ====================================== -->

                    <div class="cart-item-details">


                        <span class="cart-item-category">

                            <?php

                            echo htmlspecialchars(
                                $item['category_name']
                                ?? 'Collection'
                            );

                            ?>

                        </span>


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $item['name']
                            );

                            ?>

                        </h3>


                        <!-- SIZE -->

                        <?php if (!empty($item['size'])): ?>

                            <div class="cart-item-size">

                                <span>
                                    Size:
                                </span>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $item['size']
                                    );

                                    ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                        <!-- PRICE -->

                        <p class="cart-item-price">

                            Rs.

                            <?php

                            echo number_format(
                                $item['price'],
                                2
                            );

                            ?>

                        </p>


                        <!-- STOCK -->

                        <?php if ($item['stock'] > 0): ?>

                            <span class="cart-stock">
                                In Stock
                            </span>

                        <?php else: ?>

                            <span class="cart-stock out">
                                Out of Stock
                            </span>

                        <?php endif; ?>


                    </div>


                    <!-- =====================================
                         QUANTITY
                    ====================================== -->

                    <div class="cart-quantity">


                        <!-- MINUS -->

                        <form
                            method="POST"
                            class="cart-quantity-form"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="update_quantity"
                            >

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo $item['cart_id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="quantity"
                                value="<?php echo max(
                                    1,
                                    $item['quantity'] - 1
                                ); ?>"
                            >

                            <button
                                type="submit"
                                class="quantity-btn"
                                <?php

                                echo $item['quantity'] <= 1
                                    ? 'disabled'
                                    : '';

                                ?>
                            >
                                −
                            </button>

                        </form>


                        <!-- CURRENT QUANTITY -->

                        <span class="quantity-value">

                            <?php
                            echo $item['quantity'];
                            ?>

                        </span>


                        <!-- PLUS -->

                        <form
                            method="POST"
                            class="cart-quantity-form"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="update_quantity"
                            >

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo $item['cart_id']; ?>"
                            >

                            <input
                                type="hidden"
                                name="quantity"
                                value="<?php echo $item['quantity'] + 1; ?>"
                            >

                            <button
                                type="submit"
                                class="quantity-btn"
                                <?php

                                echo (
                                    $item['stock'] > 0 &&
                                    $item['quantity'] >= $item['stock']
                                )
                                    ? 'disabled'
                                    : '';

                                ?>
                            >
                                +
                            </button>

                        </form>


                    </div>


                    <!-- =====================================
                         ITEM TOTAL
                    ====================================== -->

                    <div class="cart-item-total">

                        <span>
                            Total
                        </span>

                        <strong>

                            Rs.

                            <?php

                            echo number_format(
                                $item['item_total'],
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <!-- =====================================
                         REMOVE
                    ====================================== -->

                    <form
                        method="POST"
                        class="remove-cart-form"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="remove"
                        >

                        <input
                            type="hidden"
                            name="cart_id"
                            value="<?php echo $item['cart_id']; ?>"
                        >

                        <button
                            type="submit"
                            class="remove-cart-btn"
                            title="Remove item"
                            onclick="return confirm('Remove this item from your cart?');"
                        >
                            ×
                        </button>

                    </form>


                </article>


            <?php endforeach; ?>


        </section>


        <!-- =================================================
             ORDER SUMMARY
        ================================================== -->

        <aside class="cart-summary">


            <span class="summary-label">
                ORDER SUMMARY
            </span>


            <h2>
                Your Total
            </h2>


            <!-- SUBTOTAL -->

            <div class="summary-line">

                <span>
                    Subtotal
                </span>

                <strong>

                    Rs.

                    <?php

                    echo number_format(
                        $subtotal,
                        2
                    );

                    ?>

                </strong>

            </div>


            <!-- DELIVERY -->

            <div class="summary-line">

                <span>
                    Delivery
                </span>

                <strong>

                    <?php if ($deliveryCharges == 0): ?>

                        FREE

                    <?php else: ?>

                        Rs.

                        <?php

                        echo number_format(
                            $deliveryCharges,
                            2
                        );

                        ?>

                    <?php endif; ?>

                </strong>

            </div>


            <div class="summary-divider"></div>


            <!-- TOTAL -->

            <div class="summary-total">

                <span>
                    Total
                </span>

                <strong>

                    Rs.

                    <?php

                    echo number_format(
                        $total,
                        2
                    );

                    ?>

                </strong>

            </div>


            <!-- FREE DELIVERY MESSAGE -->

            <?php if ($subtotal < 5000): ?>

                <p class="free-delivery-note">

                    Add Rs.

                    <?php

                    echo number_format(
                        5000 - $subtotal,
                        2
                    );

                    ?>

                    more for

                    <strong>
                        FREE delivery
                    </strong>.

                </p>

            <?php else: ?>

                <p class="free-delivery-note">

                    ✦ You qualify for free delivery.

                </p>

            <?php endif; ?>


            <!-- CHECKOUT -->

            <a
                href="checkout.php"
                class="checkout-btn"
            >

                Proceed to Checkout

                <span>
                    →
                </span>

            </a>


            <!-- CONTINUE SHOPPING -->

            <a
                href="shop.php"
                class="continue-shopping"
            >

                ← Continue Shopping

            </a>


        </aside>


    </div>


<?php else: ?>


    <!-- =================================================
         EMPTY CART
    ================================================== -->

    <section class="empty-cart">


        <div class="empty-cart-icon">
            ♡
        </div>


        <span>
            YOUR BAG IS WAITING
        </span>


        <h2>
            Your cart is empty.
        </h2>


        <p>
            Discover something beautiful
            and add it to your ÉLORIA bag.
        </p>


        <a
            href="shop.php"
            class="empty-cart-btn"
        >

            Explore Collection

            <span>
                →
            </span>

        </a>


    </section>


<?php endif; ?>


</main>


<?php include "includes/footer.php"; ?>


</body>

</html>