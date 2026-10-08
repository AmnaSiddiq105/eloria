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


// =====================================================
// GET ORDER NUMBER
// =====================================================

$order_number =
    trim($_GET['order'] ?? '');


if ($order_number === '') {

    header("Location: index.php");

    exit;
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

    <title>ÉLORIA | Order Confirmed</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="assets/css/home.css"
    >


    <style>

        body {

            background:
                linear-gradient(
                    135deg,
                    #fffafa,
                    #f7f0f0,
                    #f1e8e8
                );

            color: #332323;

        }


        .success-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding:
                140px 20px 80px;

        }


        .success-card {

            width: 100%;

            max-width: 650px;

            background:
                rgba(255,255,255,0.92);

            border:
                1px solid #eadcdc;

            border-radius: 30px;

            padding: 55px 35px;

            text-align: center;

            box-shadow:
                0 20px 60px
                rgba(70,40,40,0.12);

        }


        .success-icon {

            width: 85px;

            height: 85px;

            margin:
                0 auto 25px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                #f0e4e4;

            color:
                #674747;

            font-size: 38px;

        }


        .success-card h1 {

            font-family: Georgia, serif;

            font-size: 42px;

            margin-bottom: 15px;

        }


        .success-card p {

            color: #777;

            line-height: 1.7;

        }


        .order-number {

            display: inline-block;

            margin: 20px 0;

            padding: 14px 22px;

            border-radius: 12px;

            background: #faf3f3;

            color: #543838;

            font-weight: 700;

            letter-spacing: 1px;

        }


        .success-buttons {

            display: flex;

            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;

            margin-top: 25px;

        }


        .success-btn {

            display: inline-block;

            padding: 13px 24px;

            border-radius: 12px;

            text-decoration: none;

            font-weight: 600;

            transition: .3s ease;

        }


        .success-btn.primary {

            background:
                #543737;

            color: #fff;

        }


        .success-btn.secondary {

            border:
                1px solid #d9caca;

            color:
                #543737;

            background:
                #fff;

        }


        .success-btn:hover {

            transform:
                translateY(-2px);

        }


    </style>

</head>


<body>


<?php include "includes/navbar.php"; ?>


<main class="success-page">

    <div class="success-card">


        <div class="success-icon">

            ✓

        </div>


        <h1>
            Order Confirmed
        </h1>


        <p>

            Thank you for shopping with
            <strong>ÉLORIA</strong>.

            Your order has been successfully
            placed.

        </p>


        <div class="order-number">

            Order:
            <?= htmlspecialchars($order_number); ?>

        </div>


        <p>

            A confirmation email has been
            requested for your order.

            We will contact you regarding
            delivery.

        </p>


        <div class="success-buttons">

            <a
                href="shop.php"
                class="success-btn primary"
            >
                Continue Shopping →
            </a>


            <a
                href="index.php"
                class="success-btn secondary"
            >
                Back to Home
            </a>

        </div>


    </div>

</main>


<?php include "includes/footer.php"; ?>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>