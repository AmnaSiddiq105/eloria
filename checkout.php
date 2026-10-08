<?php

session_start();

require_once "db.php";


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


// =====================================================
// GET USER NAME
// =====================================================

$user_name = $_SESSION['user_name'] ?? '';


// =====================================================
// GET CART ITEMS
// =====================================================

$cart_sql = "
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,
        c.size,
        p.name,
        p.price,
        p.image
    FROM cart c
    INNER JOIN products p
        ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.id DESC
";

$cart_stmt = $conn->prepare($cart_sql);

if (!$cart_stmt) {
    die("Cart query error: " . $conn->error);
}

$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();

$cart_result = $cart_stmt->get_result();


// =====================================================
// CHECK EMPTY CART
// =====================================================

if ($cart_result->num_rows === 0) {

    header("Location: cart.php");
    exit;
}


// =====================================================
// CALCULATE TOTALS
// =====================================================

$cart_items = [];

$subtotal = 0;

while ($item = $cart_result->fetch_assoc()) {

    $item_total =
        (float) $item['price'] *
        (int) $item['quantity'];

    $item['item_total'] = $item_total;

    $cart_items[] = $item;

    $subtotal += $item_total;
}


// =====================================================
// DELIVERY CHARGES
// =====================================================

if ($subtotal >= 5000) {
    $delivery_charges = 0;
} else {
    $delivery_charges = 250;
}


// =====================================================
// FINAL TOTAL
// =====================================================

$total = $subtotal + $delivery_charges;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ÉLORIA | Checkout</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Home CSS -->

    <link
        rel="stylesheet"
        href="assets/css/home.css"
    >


    <style>

        /* =================================================
           CHECKOUT PAGE
        ================================================= */

        body {
            background:
                linear-gradient(
                    135deg,
                    #fffafa,
                    #f8f3f2,
                    #f3eeee
                );

            color: #2b1c1c;
        }


        .checkout-page {
            padding: 150px 0 80px;
            min-height: 100vh;
        }


        .checkout-title {
            text-align: center;
            margin-bottom: 50px;
        }


        .checkout-title h1 {
            font-family: Georgia, serif;
            font-size: 48px;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }


        .checkout-title p {
            color: #777;
            font-size: 15px;
        }


        /* =================================================
           CARDS
        ================================================= */

        .checkout-card {

            background: rgba(255,255,255,0.85);

            border: 1px solid #eadede;

            border-radius: 24px;

            padding: 30px;

            box-shadow:
                0 15px 40px rgba(70,40,40,0.08);

            margin-bottom: 25px;
        }


        .checkout-card h3 {

            font-family: Georgia, serif;

            font-size: 25px;

            margin-bottom: 25px;

            color: #3b2525;
        }


        /* =================================================
           FORM
        ================================================= */

        .form-label {

            font-weight: 600;

            font-size: 14px;

            margin-bottom: 8px;

            color: #4b3636;
        }


        .form-control,
        .form-select {

            border: 1px solid #dfcece;

            border-radius: 12px;

            padding: 13px 15px;

            background: #fff;

            box-shadow: none;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: #9b7777;

            box-shadow:
                0 0 0 3px rgba(155,119,119,0.12);

        }


        /* =================================================
           ORDER ITEMS
        ================================================= */

        .checkout-item {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 15px 0;

            border-bottom: 1px solid #eee4e4;
        }


        .checkout-item:last-child {
            border-bottom: none;
        }


        .checkout-image {

            width: 75px;

            height: 90px;

            border-radius: 12px;

            overflow: hidden;

            background: #f5eeee;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .checkout-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;
        }


        .checkout-image .no-image {

            font-size: 11px;

            color: #999;

            text-align: center;

        }


        .checkout-item-info {
            flex: 1;
        }


        .checkout-item-name {

            font-weight: 600;

            color: #332323;

            margin-bottom: 5px;
        }


        .checkout-item-meta {

            font-size: 13px;

            color: #777;

            margin-bottom: 3px;
        }


        .checkout-item-total {

            font-weight: 600;

            color: #3e2929;

            white-space: nowrap;
        }


        /* =================================================
           SUMMARY
        ================================================= */

        .summary-row {

            display: flex;

            justify-content: space-between;

            padding: 10px 0;

            color: #666;

            font-size: 15px;
        }


        .summary-row.total {

            border-top: 1px solid #e4d7d7;

            margin-top: 10px;

            padding-top: 18px;

            font-size: 20px;

            font-weight: 700;

            color: #2f2020;
        }


        .free-delivery {

            color: #6d8b6d;

            font-weight: 600;
        }


        /* =================================================
           PAYMENT
        ================================================= */

        .payment-box {

            border: 1px solid #e2d5d5;

            border-radius: 14px;

            padding: 16px;

            background: #fff;

            margin-top: 10px;
        }


        .payment-box label {

            display: flex;

            align-items: center;

            gap: 10px;

            cursor: pointer;

            font-weight: 600;
        }


        .payment-box input {
            accent-color: #684444;
        }


        /* =================================================
           PLACE ORDER BUTTON
        ================================================= */

        .place-order-btn {

            width: 100%;

            border: none;

            border-radius: 14px;

            padding: 16px 20px;

            background:
                linear-gradient(
                    135deg,
                    #4b3030,
                    #795454
                );

            color: #fff;

            font-size: 16px;

            font-weight: 600;

            letter-spacing: .5px;

            transition: .3s ease;

            margin-top: 20px;
        }


        .place-order-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(70,40,40,0.20);
        }


        /* =================================================
           NOTE
        ================================================= */

        .checkout-note {

            background: #faf4f4;

            border-radius: 12px;

            padding: 13px 15px;

            font-size: 13px;

            color: #777;

            margin-top: 15px;
        }


        /* =================================================
           RESPONSIVE
        ================================================= */

        @media (max-width: 768px) {

            .checkout-page {
                padding-top: 120px;
            }

            .checkout-title h1 {
                font-size: 38px;
            }

            .checkout-card {
                padding: 22px;
            }

        }

        /* =================================================
   CHECKOUT PRODUCT IMAGE
================================================= */

.checkout-image {
    width: 75px;
    height: 90px;

    border-radius: 12px;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #f8edf3,
            #eee5f2
        );

    flex-shrink: 0;

    position: relative;

    display: flex;

    align-items: center;

    justify-content: center;
}

.checkout-image img {
    width: 100%;
    height: 100%;

    object-fit: cover;

    display: block;

    transition: transform 0.4s ease;
}

.checkout-item:hover .checkout-image img {
    transform: scale(1.04);
}

/* =================================================
   FALLBACK
================================================= */

.checkout-image-fallback {
    position: absolute;

    inset: 0;

    display: none;

    align-items: center;
    justify-content: center;

    flex-direction: column;

    gap: 4px;

    text-align: center;

    background:
        linear-gradient(
            135deg,
            #f8edf3,
            #eee5f2
        );
}

.checkout-image-fallback span {
    font-family: "Cormorant Garamond", serif;

    font-size: 18px;

    letter-spacing: 2px;

    color: #76586a;
}

.checkout-image-fallback small {
    max-width: 65px;

    font-size: 7px;

    line-height: 1.3;

    color: #9a808e;

    text-transform: uppercase;

    letter-spacing: .5px;
}

/* Show fallback when image fails */

.checkout-image.image-error
.checkout-image-fallback,

.checkout-image:not(:has(img))
.checkout-image-fallback {
    display: flex;
}

.checkout-image.image-error img {
    display: none;
}

    </style>

</head>


<body>


<?php include "includes/navbar.php"; ?>


<main class="checkout-page">

    <div class="container">


        <!-- =================================================
             TITLE
        ================================================= -->

        <div class="checkout-title">

            <h1>Checkout</h1>

            <p>
                Complete your details to place your ÉLORIA order.
            </p>

        </div>


        <form
            action="place_order.php"
            method="POST"
            id="checkoutForm"
        >

            <div class="row g-4">


                <!-- =================================================
                     LEFT SIDE
                ================================================= -->

                <div class="col-lg-7">


                    <!-- CUSTOMER INFORMATION -->

                    <div class="checkout-card">

                        <h3>Customer Information</h3>


                        <div class="row g-3">


                            <!-- NAME -->

                            <div class="col-12">

                                <label
                                    for="customer_name"
                                    class="form-label"
                                >
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="customer_name"
                                    name="customer_name"
                                    value="<?= htmlspecialchars($user_name); ?>"
                                    placeholder="Enter your full name"
                                    required
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="example@gmail.com"
                                    required
                                >

                            </div>


                            <!-- PHONE -->

                            <div class="col-md-6">

                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    Phone Number
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    placeholder="03XXXXXXXXX"
                                    required
                                >

                            </div>


                            <!-- CITY -->

                            <div class="col-12">

                                <label
                                    for="city"
                                    class="form-label"
                                >
                                    City
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="city"
                                    name="city"
                                    placeholder="Karachi"
                                    required
                                >

                            </div>


                            <!-- ADDRESS -->

                            <div class="col-12">

                                <label
                                    for="address"
                                    class="form-label"
                                >
                                    Delivery Address
                                </label>

                                <textarea
                                    class="form-control"
                                    id="address"
                                    name="address"
                                    rows="4"
                                    placeholder="Enter your complete delivery address"
                                    required
                                ></textarea>

                            </div>


                        </div>

                    </div>


                    <!-- =================================================
                         PAYMENT
                    ================================================= -->

                    <div class="checkout-card">

                        <h3>Payment Method</h3>


                        <div class="payment-box">

                            <label>

                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="Cash on Delivery"
                                    checked
                                >

                                <span>
                                    Cash on Delivery
                                </span>

                            </label>

                        </div>


                        <div class="checkout-note">

                            Your order will be confirmed after
                            submitting this form. You will pay
                            when your order is delivered.

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     RIGHT SIDE
                ================================================= -->

                <div class="col-lg-5">


                    <!-- ORDER SUMMARY -->

                    <div class="checkout-card">

                        <h3>Your Order</h3>


                        <?php foreach ($cart_items as $item): ?>

                            <div class="checkout-item">


                                <!-- IMAGE -->
<!-- PRODUCT IMAGE -->
<div class="checkout-image">

    <?php
    $checkoutImage = trim((string)($item['image'] ?? ''));

    /*
    |--------------------------------------------------------------------------
    | IMAGE SOURCE
    |--------------------------------------------------------------------------
    | Supports:
    | 1. Online URL
    | 2. Local filename
    | 3. Full local path
    |--------------------------------------------------------------------------
    */

    if (!empty($checkoutImage)) {

        // ONLINE IMAGE
        if (
            filter_var($checkoutImage, FILTER_VALIDATE_URL) &&
            preg_match('/^https?:\/\//i', $checkoutImage)
        ) {

            $checkoutImageSrc = $checkoutImage;

        }

        // FULL LOCAL PATH
        elseif (
            strpos($checkoutImage, 'assets/images/products/') === 0
        ) {

            $checkoutImageSrc = $checkoutImage;

        }

        // ONLY FILE NAME
        else {

            $checkoutImageSrc =
                'assets/images/products/' .
                basename($checkoutImage);
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | IMAGE EMPTY
        |--------------------------------------------------------------------------
        | Try product slug if available.
        |--------------------------------------------------------------------------
        */

        $checkoutImageSrc = '';
    }
    ?>

    <?php if (!empty($checkoutImageSrc)): ?>

        <img
            src="<?php echo htmlspecialchars(
                $checkoutImageSrc,
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            alt="<?php echo htmlspecialchars(
                $item['name'],
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            loading="lazy"
            onerror="
                this.style.display='none';
                this.parentElement.classList.add('image-error');
            "
        >

    <?php endif; ?>

    <!-- FALLBACK -->
    <div class="checkout-image-fallback">

        <span>ÉLORIA</span>

        <small>
            <?php echo htmlspecialchars($item['name']); ?>
        </small>

    </div>

</div>


                                <!-- INFO -->

                                <div class="checkout-item-info">

                                    <div class="checkout-item-name">

                                        <?= htmlspecialchars($item['name']); ?>

                                    </div>


                                    <div class="checkout-item-meta">

                                        Quantity:
                                        <?= (int) $item['quantity']; ?>

                                    </div>


                                    <?php if (!empty($item['size'])): ?>

                                        <div class="checkout-item-meta">

                                            Size:
                                            <?= htmlspecialchars($item['size']); ?>

                                        </div>

                                    <?php endif; ?>


                                </div>


                                <!-- TOTAL -->

                                <div class="checkout-item-total">

                                    Rs.
                                    <?= number_format(
                                        $item['item_total'],
                                        2
                                    ); ?>

                                </div>


                            </div>

                        <?php endforeach; ?>


                        <!-- SUBTOTAL -->

                        <div class="summary-row">

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                Rs.
                                <?= number_format(
                                    $subtotal,
                                    2
                                ); ?>
                            </strong>

                        </div>


                        <!-- DELIVERY -->

                        <div class="summary-row">

                            <span>
                                Delivery
                            </span>

                            <?php if ($delivery_charges == 0): ?>

                                <strong class="free-delivery">
                                    FREE
                                </strong>

                            <?php else: ?>

                                <strong>
                                    Rs.
                                    <?= number_format(
                                        $delivery_charges,
                                        2
                                    ); ?>
                                </strong>

                            <?php endif; ?>

                        </div>


                        <!-- TOTAL -->

                        <div class="summary-row total">

                            <span>
                                Total
                            </span>

                            <strong>
                                Rs.
                                <?= number_format(
                                    $total,
                                    2
                                ); ?>
                            </strong>

                        </div>


                        <!-- PLACE ORDER -->

                        <button
                            type="submit"
                            class="place-order-btn"
                        >
                            Place Order
                            &nbsp; →
                        </button>


                    </div>


                </div>

            </div>

        </form>

    </div>

</main>


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const form =
        document.getElementById("checkoutForm");

    if (!form) {
        return;
    }


    form.addEventListener("submit", function (event) {

        const phone =
            document.getElementById("phone").value.trim();

        const phonePattern =
            /^[0-9+\-\s]{10,15}$/;


        if (!phonePattern.test(phone)) {

            event.preventDefault();

            alert(
                "Please enter a valid phone number."
            );

            document
                .getElementById("phone")
                .focus();

            return;

        }

    });

});

<?php

$checkoutImage = trim((string)($item['image'] ?? ''));

if (!empty($checkoutImage)) {

    // Online URL
    if (
        filter_var($checkoutImage, FILTER_VALIDATE_URL) &&
        preg_match('/^https?:\/\//i', $checkoutImage)
    ) {
        $checkoutImageSrc = $checkoutImage;
    }

    // Already complete local path
    elseif (
        strpos($checkoutImage, 'assets/images/products/') === 0
    ) {
        $checkoutImageSrc = $checkoutImage;
    }

    // Filename only
    else {
        $checkoutImageSrc =
            'assets/images/products/' .
            basename($checkoutImage);
    }

} else {

    // Try slug.jpg
    $checkoutImageSrc =
        'assets/images/products/' .
        $item['slug'] .
        '.jpg';
}

?>

</script>


</body>

</html>