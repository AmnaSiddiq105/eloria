
<?php
session_start();
require_once "../db.php";

/* =========================
   ADMIN LOGIN CHECK
========================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   CUSTOMER ID
========================= */
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    header("Location: customers.php");
    exit;
}

/* =========================
   GET CUSTOMER
========================= */
$customer_sql = "
    SELECT
        id,
        name,
        email,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
";

$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();

$customer_result = $customer_stmt->get_result();

if ($customer_result->num_rows === 0) {
    header("Location: customers.php");
    exit;
}

$customer = $customer_result->fetch_assoc();

$customer_stmt->close();


/* =========================
   CUSTOMER ORDER STATS
========================= */

$stats_sql = "
    SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(
            CASE
                WHEN order_status != 'Cancelled'
                THEN total
                ELSE 0
            END
        ), 0) AS total_spent,
        MAX(created_at) AS latest_order
    FROM orders
    WHERE user_id = ?
";

$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();

$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$stats_stmt->close();


$total_orders = (int)($stats['total_orders'] ?? 0);
$total_spent = (float)($stats['total_spent'] ?? 0);
$latest_order = $stats['latest_order'] ?? null;


/* =========================
   GET CUSTOMER ORDERS
========================= */

$orders_sql = "
    SELECT
        id,
        order_number,
        subtotal,
        delivery_charges,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();

$orders = $orders_stmt->get_result();


/* =========================
   INITIAL
========================= */

$initial = strtoupper(
    substr(
        trim($customer['name']),
        0,
        1
    )
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($customer['name']) ?> | ÉLORIA Admin
    </title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    

<link rel="stylesheet" href="assets/admin-sidebar.css">


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            font-family: "Montserrat", sans-serif;

            min-height: 100vh;

            color: #302a30;

            background:
                radial-gradient(
                    circle at 8% 8%,
                    rgba(244, 207, 225, 0.45),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 92% 15%,
                    rgba(220, 210, 244, 0.45),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffdfd 0%,
                    #fff8fb 48%,
                    #f8f5fc 100%
                );
        }

        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            left: 20px;
            top: 20px;

            width: 235px;

            height: calc(100vh - 40px);

            padding: 30px 18px;

            background:
                rgba(255, 255, 255, 0.78);

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border:
                1px solid rgba(255, 255, 255, 0.92);

            border-radius: 28px;

            box-shadow:
                0 20px 55px rgba(75, 55, 75, 0.10);

            z-index: 1000;
        }

        /* =====================================================
           BRAND
        ===================================================== */

        .brand {

            text-align: center;

            padding-bottom: 28px;

            margin-bottom: 25px;

            border-bottom:
                1px solid rgba(80, 60, 80, 0.08);
        }

        .brand h2 {

            font-family: "Cormorant Garamond", serif;

            font-size: 34px;

            font-weight: 600;

            letter-spacing: 3px;

            color: #302b30;

            line-height: 1;
        }

        .brand span {

            display: block;

            margin-top: 8px;

            font-size: 9px;

            letter-spacing: 2.5px;

            color: #9b7d8e;

            text-transform: uppercase;
        }

        /* =====================================================
           MENU
        ===================================================== */

        .menu {

            display: flex;

            flex-direction: column;

            gap: 7px;
        }

        .menu a {

            position: relative;

            text-decoration: none;

            color: #746a72;

            padding: 13px 15px;

            border-radius: 14px;

            display: flex;

            align-items: center;

            gap: 12px;

            font-size: 12px;

            font-weight: 500;

            transition: 0.3s ease;
        }

        .menu a i {

            width: 20px;

            text-align: center;

            font-size: 16px;

            color: #a9869a;

            transition: 0.3s;
        }

        .menu a:hover {

            background:
                linear-gradient(
                    135deg,
                    rgba(248, 221, 234, 0.65),
                    rgba(231, 222, 246, 0.65)
                );

            color: #3b333a;

            transform: translateX(3px);
        }

        .menu a:hover i {

            color: #9b6885;
        }

        .menu a.active {

            background:
                linear-gradient(
                    135deg,
                    #f5dce8,
                    #e8def4
                );

            color: #392f37;

            box-shadow:
                0 8px 20px rgba(130, 95, 125, 0.10);
        }

        .menu a.active i {

            color: #9a6583;
        }

        /* =====================================================
           LOGOUT
        ===================================================== */

        .logout {

            margin-top: 28px;

            padding-top: 20px;

            border-top:
                1px solid rgba(80, 60, 80, 0.08);
        }

        .logout a:hover {

            background:
                rgba(242, 224, 232, 0.65);

            color: #7e5669;
        }

        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 275px;

            padding: 40px 42px 60px;

            min-height: 100vh;

            max-width: 1550px;
        }

        /* =====================================================
           BACK BUTTON
        ===================================================== */

        .back-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            text-decoration: none;

            color: #927086;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 0.2px;

            margin-bottom: 20px;

            padding: 9px 14px;

            border-radius: 11px;

            background:
                rgba(255,255,255,0.68);

            border:
                1px solid rgba(255,255,255,0.9);

            transition: 0.3s;
        }

        .back-btn:hover {

            color: #76566a;

            background:
                #f8eaf1;

            transform: translateX(-3px);
        }

        /* =====================================================
           CUSTOMER HEADER
        ===================================================== */

        .customer-header {

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,0.86),
                    rgba(255,247,251,0.82)
                );

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border:
                1px solid rgba(255,255,255,0.95);

            border-radius: 26px;

            padding: 30px;

            box-shadow:
                0 18px 50px rgba(65,45,65,0.07);

            display: flex;

            align-items: center;

            gap: 22px;

            margin-bottom: 23px;
        }

        .customer-header::after {

            content: "";

            position: absolute;

            width: 220px;
            height: 220px;

            right: -80px;
            top: -100px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    rgba(244,205,223,0.35),
                    rgba(220,211,243,0.35)
                );
        }

        .big-avatar {

            position: relative;

            z-index: 2;

            width: 84px;
            height: 84px;

            flex-shrink: 0;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #d8aabd,
                    #a497c4
                );

            color: white;

            display: flex;

            align-items: center;
            justify-content: center;

            font-family: "Cormorant Garamond", serif;

            font-size: 36px;

            font-weight: 700;

            box-shadow:
                0 10px 25px rgba(142,105,130,0.18);
        }

        .customer-header > div:last-child {

            position: relative;

            z-index: 2;
        }

        .customer-header h1 {

            font-family: "Cormorant Garamond", serif;

            color: #302a30;

            font-size: 42px;

            line-height: 1;

            font-weight: 600;

            margin-bottom: 9px;
        }

        .customer-header p {

            color: #82747d;

            font-size: 11px;

            margin-bottom: 6px;
        }

        .customer-header p i {

            color: #aa7d93;

            margin-right: 5px;
        }

        .customer-id {

            color: #a4939c;

            font-size: 9px;

            letter-spacing: 0.5px;
        }

        /* =====================================================
           STATS
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 18px;

            margin-bottom: 23px;
        }

        .stat-card {

            position: relative;

            overflow: hidden;

            background:
                rgba(255,255,255,0.74);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255,255,255,0.95);

            border-radius: 21px;

            padding: 21px;

            box-shadow:
                0 15px 40px rgba(65,45,65,0.07);

            display: flex;

            align-items: center;

            gap: 16px;

            transition: 0.3s;
        }

        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 20px 45px rgba(65,45,65,0.10);
        }

        .stat-card::after {

            content: "";

            position: absolute;

            width: 100px;
            height: 100px;

            right: -40px;
            top: -45px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    rgba(246,208,226,0.30),
                    rgba(221,211,244,0.30)
                );
        }

        .stat-icon {

            position: relative;

            z-index: 2;

            width: 53px;
            height: 53px;

            flex-shrink: 0;

            border-radius: 16px;

            background:
                linear-gradient(
                    135deg,
                    #f5d9e7,
                    #e6ddf4
                );

            color: #95687f;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 21px;
        }

        .stat-info {

            position: relative;

            z-index: 2;
        }

        .stat-info span {

            display: block;

            color: #9b8d95;

            font-size: 9px;

            letter-spacing: 0.3px;

            margin-bottom: 4px;
        }

        .stat-info strong {

            display: block;

            font-family: "Cormorant Garamond", serif;

            color: #393138;

            font-size: 29px;

            font-weight: 600;
        }

        /* =====================================================
           DETAILS GRID
        ===================================================== */

        .details-grid {

            display: grid;

            grid-template-columns:
                300px 1fr;

            gap: 22px;

            align-items: start;
        }

        /* =====================================================
           CARD
        ===================================================== */

        .card {

            background:
                rgba(255,255,255,0.77);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255,255,255,0.95);

            border-radius: 23px;

            padding: 24px;

            box-shadow:
                0 18px 50px rgba(65,45,65,0.07);
        }

        .card-title {

            font-family: "Cormorant Garamond", serif;

            color: #3b3339;

            font-size: 27px;

            font-weight: 600;

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            gap: 8px;
        }

        .card-title i {

            color: #a7778e;

            font-size: 18px;
        }

        /* =====================================================
           CUSTOMER INFORMATION
        ===================================================== */

        .info-item {

            padding: 14px 0;

            border-bottom:
                1px solid #f0e8ed;
        }

        .info-item:last-child {

            border-bottom: none;

            padding-bottom: 0;
        }

        .info-label {

            display: block;

            color: #a08f99;

            font-size: 8px;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 6px;
        }

        .info-value {

            color: #554b52;

            font-size: 11px;

            line-height: 1.6;

            word-break: break-word;
        }

        /* =====================================================
           TABLE
        ===================================================== */

        .table-wrapper {

            overflow-x: auto;

            border-radius: 14px;
        }

        table {

            width: 100%;

            border-collapse: separate;

            border-spacing: 0;

            min-width: 700px;
        }

        th {

            text-align: left;

            padding: 13px;

            background:
                linear-gradient(
                    135deg,
                    #fcf5f8,
                    #f7f4fb
                );

            color: #8d7883;

            font-size: 8px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 1px;

            border-bottom:
                1px solid #eee5ea;
        }

        th:first-child {

            border-top-left-radius: 12px;
        }

        th:last-child {

            border-top-right-radius: 12px;
        }

        td {

            padding: 14px 13px;

            border-bottom:
                1px solid #f2eaee;

            color: #665b62;

            font-size: 10px;

            background:
                rgba(255,255,255,0.30);
        }

        tbody tr {

            transition: 0.25s;
        }

        tbody tr:hover td {

            background:
                rgba(251,242,247,0.72);
        }

        tbody tr:last-child td {

            border-bottom: none;
        }

        /* =====================================================
           ORDER NUMBER
        ===================================================== */

        .order-number {

            font-weight: 600;

            color: #91677d;

            font-size: 10px;

            white-space: nowrap;
        }

        /* =====================================================
           AMOUNT
        ===================================================== */

        .amount {

            font-weight: 600;

            color: #51454d;

            white-space: nowrap;
        }

        .date {

            color: #83767e;

            font-size: 9px;

            white-space: nowrap;
        }

        /* =====================================================
           STATUS
        ===================================================== */

        .status {

            display: inline-flex;

            align-items: center;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 8px;

            font-weight: 600;

            white-space: nowrap;
        }

        .status.pending {

            background: #fff3d8;

            color: #9a7218;
        }

        .status.processing {

            background: #eee7ff;

            color: #7050a0;
        }

        .status.shipped {

            background: #e2f3fb;

            color: #327493;
        }

        .status.delivered {

            background: #e4f6eb;

            color: #34754e;
        }

        .status.cancelled {

            background: #ffe6eb;

            color: #a33e54;
        }

        /* =====================================================
           PAYMENT
        ===================================================== */

        .payment {

            font-size: 8px;

            padding: 5px 9px;

            border-radius: 8px;

            background:
                #f4edf6;

            color: #745d79;

            display: inline-block;

            white-space: nowrap;
        }

        /* =====================================================
           VIEW BUTTON
        ===================================================== */

        .view-btn {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 11px;

            border-radius: 9px;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #f6e6ee,
                    #eee8f7
                );

            color: #8b6479;

            font-size: 8px;

            font-weight: 600;

            transition: 0.3s;

            border:
                1px solid rgba(220,199,211,0.55);
        }

        .view-btn:hover {

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #b77f99,
                    #9679ac
                );

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(145,104,130,0.16);
        }

        /* =====================================================
           EMPTY ORDERS
        ===================================================== */

        .empty-orders {

            text-align: center;

            padding: 50px 20px;

            color: #a09299;
        }

        .empty-orders i {

            display: block;

            font-size: 42px;

            color: #d8bccb;

            margin-bottom: 12px;
        }

        .empty-orders h4 {

            font-family: "Cormorant Garamond", serif;

            color: #65535e;

            font-size: 25px;

            font-weight: 600;

            margin-bottom: 5px;
        }

        .empty-orders p {

            font-size: 10px;

            line-height: 1.7;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .details-grid {

                grid-template-columns: 1fr;
            }

            .stats {

                grid-template-columns:
                    repeat(3, 1fr);
            }
        }

        @media (max-width: 950px) {

            .sidebar {

                width: 210px;

                left: 15px;
            }

            .main {

                margin-left: 245px;

                padding: 32px 25px;
            }

            .stats {

                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {

            .sidebar {

                position: relative;

                left: auto;
                top: auto;

                width: calc(100% - 30px);

                height: auto;

                margin: 15px;

                padding: 22px;

                border-radius: 22px;
            }

            .brand {

                margin-bottom: 18px;

                padding-bottom: 18px;
            }

            .menu {

                flex-direction: row;

                flex-wrap: wrap;

                justify-content: center;
            }

            .menu a {

                flex: 1 1 120px;

                justify-content: center;
            }

            .logout {

                width: 100%;

                margin-top: 8px;
            }

            .logout a {

                justify-content: center;
            }

            .main {

                margin-left: 0;

                padding: 10px 15px 35px;
            }

            .customer-header {

                padding: 25px;

                flex-direction: column;

                text-align: center;
            }

            .customer-header h1 {

                font-size: 36px;
            }

            .stats {

                grid-template-columns: 1fr;
            }

            .details-grid {

                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {

            .card {

                padding: 18px;
            }

            .customer-header {

                padding: 22px 16px;
            }

            .big-avatar {

                width: 72px;
                height: 72px;

                font-size: 31px;
            }

            .customer-header h1 {

                font-size: 32px;
            }

            .stat-card {

                padding: 18px;
            }

            .card-title {

                font-size: 24px;
            }
        }

    </style>

</head>

<body>
<?php include "includes/sidebar.php"; ?> 
<!-- =====================================================
     SIDEBAR
===================================================== -->




<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">

    <!-- BACK -->

    <a href="customers.php" class="back-btn">

        <i class="bi bi-arrow-left"></i>

        Back to Customers

    </a>


    <!-- =================================================
         CUSTOMER HEADER
    ================================================== -->

    <section class="customer-header">

        <div class="big-avatar">

            <?= htmlspecialchars($initial) ?>

        </div>

        <div>

            <h1>
                <?= htmlspecialchars($customer['name']) ?>
            </h1>

            <p>

                <i class="bi bi-envelope"></i>

                <?= htmlspecialchars($customer['email']) ?>

            </p>

            <span class="customer-id">

                Customer #<?= (int)$customer['id'] ?>

            </span>

        </div>

    </section>


    <!-- =================================================
         STATS
    ================================================== -->

    <section class="stats">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-bag-heart-fill"></i>

            </div>

            <div class="stat-info">

                <span>Total Orders</span>

                <strong>

                    <?= number_format($total_orders) ?>

                </strong>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-wallet2"></i>

            </div>

            <div class="stat-info">

                <span>Total Spent</span>

                <strong>

                    Rs. <?= number_format($total_spent, 2) ?>

                </strong>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-clock-history"></i>

            </div>

            <div class="stat-info">

                <span>Latest Order</span>

                <strong style="font-size:15px;">

                    <?php if ($latest_order): ?>

                        <?= date(
                            "d M Y",
                            strtotime($latest_order)
                        ) ?>

                    <?php else: ?>

                        No Orders

                    <?php endif; ?>

                </strong>

            </div>

        </div>

    </section>


    <!-- =================================================
         DETAILS + ORDER HISTORY
    ================================================== -->

    <div class="details-grid">


        <!-- CUSTOMER INFORMATION -->

        <section class="card">

            <h3 class="card-title">

                <i class="bi bi-person-vcard"></i>

                Customer Information

            </h3>


            <div class="info-item">

                <span class="info-label">
                    Customer ID
                </span>

                <span class="info-value">

                    #<?= (int)$customer['id'] ?>

                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Full Name
                </span>

                <span class="info-value">

                    <?= htmlspecialchars($customer['name']) ?>

                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Email Address
                </span>

                <span class="info-value">

                    <?= htmlspecialchars($customer['email']) ?>

                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Registered On
                </span>

                <span class="info-value">

                    <?= date(
                        "d F Y, h:i A",
                        strtotime($customer['created_at'])
                    ) ?>

                </span>

            </div>

        </section>


        <!-- ORDER HISTORY -->

        <section class="card">

            <h3 class="card-title">

                <i class="bi bi-bag-check"></i>

                Order History

            </h3>


            <?php if ($orders && $orders->num_rows > 0): ?>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Order</th>

                                <th>Date</th>

                                <th>Total</th>

                                <th>Payment</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while ($order = $orders->fetch_assoc()): ?>

                            <?php

                            $status_class =
                                strtolower(
                                    $order['order_status']
                                );

                            ?>

                            <tr>

                                <td>

                                    <span class="order-number">

                                        <?= htmlspecialchars(
                                            $order['order_number']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="date">

                                        <?= date(
                                            "d M Y",
                                            strtotime(
                                                $order['created_at']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="amount">

                                        Rs.
                                        <?= number_format(
                                            (float)$order['total'],
                                            2
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="payment">

                                        <?= htmlspecialchars(
                                            $order['payment_method']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars(
                                            $status_class
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $order['order_status']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="order-details.php?id=<?= (int)$order['id'] ?>"
                                        class="view-btn"
                                    >

                                        <i class="bi bi-eye"></i>

                                        View

                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-orders">

                    <i class="bi bi-bag-x"></i>

                    <h4>No Orders Yet</h4>

                    <p>
                        This customer has not placed any orders.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </div>

</main>

</body>

</html>

<?php
$orders_stmt->close();
$conn->close();
?>
