
<?php
session_start();
require_once "../db.php";

/* ===============================
   ADMIN LOGIN CHECK
================================ */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* ===============================
   GET ORDER ID
================================ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$orderId = (int) $_GET['id'];

/* ===============================
   UPDATE ORDER STATUS
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newStatus = trim($_POST['order_status'] ?? '');

    $allowedStatuses = [
        'Pending',
        'Processing',
        'Shipped',
        'Delivered',
        'Cancelled'
    ];

    if (in_array($newStatus, $allowedStatuses, true)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE orders SET order_status = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $newStatus,
            $orderId
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header(
        "Location: order-details.php?id=" .
        $orderId .
        "&updated=1"
    );

    exit;
}

/* ===============================
   GET ORDER
================================ */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        order_number,
        user_id,
        customer_name,
        email,
        phone,
        address,
        city,
        subtotal,
        delivery_charges,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at,
        updated_at
     FROM orders
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $orderId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$order = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===============================
   ORDER NOT FOUND
================================ */

if (!$order) {
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>Order Not Found | ÉLORIA</title>

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
<link rel="stylesheet" href="assets/admin-sidebar.css">
        <style>

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;

                min-height: 100vh;

                display: flex;

                align-items: center;
                justify-content: center;

                font-family: "Montserrat", sans-serif;

                background:
                    radial-gradient(
                        circle at top left,
                        rgba(249,213,229,.55),
                        transparent 32%
                    ),
                    radial-gradient(
                        circle at top right,
                        rgba(225,214,250,.48),
                        transparent 30%
                    ),
                    linear-gradient(
                        135deg,
                        #fffafb,
                        #fdf7fb,
                        #f7f5ff
                    );

                color: #302a31;
            }

            .box {
                width: min(480px, 90%);

                text-align: center;

                padding: 48px 35px;

                background: rgba(255,255,255,.82);

                backdrop-filter: blur(18px);

                border: 1px solid rgba(255,255,255,.9);

                border-radius: 28px;

                box-shadow:
                    0 20px 55px rgba(77,48,68,.08);
            }

            .icon {
                width: 70px;
                height: 70px;

                margin: 0 auto 20px;

                display: flex;

                align-items: center;
                justify-content: center;

                border-radius: 50%;

                background:
                    linear-gradient(
                        135deg,
                        #f6dce8,
                        #e8e0f8
                    );

                color: #9a6d86;

                font-size: 28px;
            }

            .box h2 {
                margin: 0 0 10px;

                font-family:
                    "Cormorant Garamond",
                    serif;

                font-size: 34px;

                color: #3b3038;
            }

            .box p {
                margin: 0;

                color: #8d7e89;

                font-size: 12px;

                line-height: 1.7;
            }

            .box a {
                display: inline-flex;

                align-items: center;
                justify-content: center;

                margin-top: 25px;

                padding: 12px 22px;

                border-radius: 14px;

                background:
                    linear-gradient(
                        135deg,
                        #b57f9c,
                        #9d89bb
                    );

                color: white;

                text-decoration: none;

                font-size: 12px;
                font-weight: 600;

                box-shadow:
                    0 10px 24px rgba(155,113,141,.20);
            }

        </style>

    </head>

    <body>

        <div class="box">

            <div class="icon">
                <i>!</i>
            </div>

            <h2>
                Order Not Found
            </h2>

            <p>
                The requested order could not be found.
            </p>

            <a href="orders.php">
                Back to Orders
            </a>

        </div>

    </body>

    </html>

    <?php
    exit;
}


/* ===============================
   GET ORDER ITEMS
================================ */

$items = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        oi.id,
        oi.product_id,
        oi.quantity,
        oi.price,
        p.name AS product_name,
        p.image AS product_image
     FROM order_items oi
     LEFT JOIN products p
        ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $orderId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

mysqli_stmt_close($stmt);


/* ===============================
   STATUS CLASS
================================ */

function statusClass($status)
{
    switch (strtolower($status)) {

        case 'pending':
            return 'pending';

        case 'processing':
            return 'processing';

        case 'shipped':
            return 'shipped';

        case 'delivered':
            return 'delivered';

        case 'cancelled':
            return 'cancelled';

        default:
            return 'default';
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

    <title>
        Order Details | ÉLORIA Admin
    </title>


    <!-- Google Fonts -->

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


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
<link rel="stylesheet" href="assets/admin-sidebar.css">

    <style>

        /* =========================
           GLOBAL
        ========================= */

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;

            min-height: 100vh;

            font-family:
                "Montserrat",
                sans-serif;

            color: #302a31;

            background:
                radial-gradient(
                    circle at top left,
                    rgba(249,213,229,.55),
                    transparent 32%
                ),
                radial-gradient(
                    circle at top right,
                    rgba(225,214,250,.48),
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

            height:
                calc(100vh - 40px);

            padding: 28px 17px;

            background:
                rgba(255,255,255,.76);

            backdrop-filter: blur(22px);

            -webkit-backdrop-filter:
                blur(22px);

            border:
                1px solid
                rgba(255,255,255,.85);

            border-radius: 28px;

            box-shadow:
                0 20px 55px
                rgba(90,55,78,.09);

            z-index: 1000;
        }


        .brand {
            display: block;

            text-align: center;

            text-decoration: none;

            color: #2e2930;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 34px;

            font-weight: 600;

            letter-spacing: 2px;

            margin-bottom: 30px;
        }

        .brand span {
            color: #b27d9b;
        }


        /* =========================
           ADMIN PROFILE
        ========================= */

        .admin-profile {
            text-align: center;

            padding: 17px 10px;

            margin-bottom: 24px;

            border-radius: 20px;

            background:
                linear-gradient(
                    135deg,
                    rgba(249,224,235,.65),
                    rgba(237,229,249,.62)
                );

            border:
                1px solid
                rgba(255,255,255,.85);
        }


        .admin-icon {
            width: 54px;
            height: 54px;

            margin:
                0 auto 9px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #dca8bd,
                    #a995c7
                );

            color: white;

            font-size: 22px;

            box-shadow:
                0 8px 20px
                rgba(160,125,151,.15);
        }


        .admin-profile h6 {
            margin-bottom: 3px;

            color: #493b46;

            font-size: 12px;

            font-weight: 700;
        }


        .admin-profile small {
            color: #9a8995;

            font-size: 10px;
        }


        /* =========================
           SIDEBAR MENU
        ========================= */

        .sidebar-menu {
            list-style: none;

            padding: 0;
            margin: 0;
        }


        .sidebar-menu li {
            margin-bottom: 6px;
        }


        .sidebar-menu a {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 13px 15px;

            border-radius: 15px;

            color: #675965;

            text-decoration: none;

            font-size: 12px;

            font-weight: 500;

            transition:
                all .3s ease;
        }


        .sidebar-menu a i {
            width: 19px;

            font-size: 16px;

            color: #a88199;
        }


        .sidebar-menu a:hover {
            color: #40333d;

            background:
                linear-gradient(
                    135deg,
                    rgba(250,220,234,.72),
                    rgba(233,225,250,.65)
                );

            transform:
                translateX(3px);
        }


        .sidebar-menu a.active {
            color: #3f303c;

            font-weight: 600;

            background:
                linear-gradient(
                    135deg,
                    #f7dce9,
                    #e9e0f8
                );

            box-shadow:
                0 8px 22px
                rgba(151,112,137,.10);
        }


        .sidebar-menu a.active i {
            color: #9b6683;
        }


        /* =========================
           LOGOUT
        ========================= */

        .logout {
            position: absolute;

            bottom: 20px;

            left: 17px;
            right: 17px;
        }


        .logout a {
            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 15px;

            color: #776873;

            text-decoration: none;

            font-size: 12px;

            border-radius: 14px;

            border-top:
                1px solid
                rgba(120,90,110,.08);

            transition: .3s ease;
        }


        .logout a:hover {
            color: #5b4051;

            background: #f7edf3;
        }


        /* =========================
           MAIN
        ========================= */

        .main-content {
            margin-left: 285px;

            padding:
                40px 38px 50px;

            min-height: 100vh;
        }


        /* =========================
           TOPBAR
        ========================= */

        .topbar {
            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 28px;
        }


        .page-heading h2 {
            margin: 0;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 42px;

            line-height: 1;

            font-weight: 600;

            color: #302a31;
        }


        .page-heading p {
            margin: 9px 0 0;

            color: #8b7d88;

            font-size: 12px;
        }


        .back-btn {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 11px 17px;

            border-radius: 14px;

            text-decoration: none;

            color: #66505e;

            background:
                rgba(255,255,255,.76);

            border:
                1px solid
                rgba(193,166,183,.22);

            font-size: 12px;

            font-weight: 600;

            box-shadow:
                0 8px 22px
                rgba(80,50,70,.05);

            transition: .3s ease;
        }


        .back-btn:hover {
            color: #3c3039;

            background: #fff;

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 28px
                rgba(80,50,70,.09);
        }


        /* =========================
           SUCCESS
        ========================= */

        .success-message {
            display: flex;

            align-items: center;

            gap: 9px;

            padding: 13px 16px;

            margin-bottom: 22px;

            border-radius: 15px;

            color: #28764f;

            background:
                linear-gradient(
                    135deg,
                    #e5f7ec,
                    #effbf4
                );

            border:
                1px solid #ccebd8;

            font-size: 12px;

            box-shadow:
                0 8px 20px
                rgba(40,118,79,.05);
        }


        /* =========================
           ORDER HEADER
        ========================= */

        .order-header {
            padding: 27px;

            margin-bottom: 22px;

            background:
                rgba(255,255,255,.80);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(255,255,255,.86);

            border-radius: 25px;

            box-shadow:
                0 20px 50px
                rgba(77,48,68,.07);
        }


        .order-header-top {
            display: flex;

            align-items: center;

            justify-content:
                space-between;

            gap: 20px;
        }


        .order-number {
            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 29px;

            font-weight: 600;

            color: #43353f;
        }


        .order-date {
            margin-top: 5px;

            color: #948691;

            font-size: 11px;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-flex;

            align-items: center;

            padding: 8px 15px;

            border-radius: 30px;

            font-size: 11px;

            font-weight: 600;
        }


        .status::before {
            content: "";

            width: 7px;
            height: 7px;

            margin-right: 8px;

            border-radius: 50%;

            background:
                currentColor;
        }


        .status.pending {
            color: #9b7500;

            background: #fff3cd;
        }


        .status.processing {
            color: #7255a6;

            background: #ebe3ff;
        }


        .status.shipped {
            color: #32739c;

            background: #e0f2ff;
        }


        .status.delivered {
            color: #288154;

            background: #def7e9;
        }


        .status.cancelled {
            color: #bd5266;

            background: #ffe2e7;
        }


        .status.default {
            color: #666;

            background: #eee;
        }


        /* =========================
           CONTENT GRID
        ========================= */

        .content-grid {
            display: grid;

            grid-template-columns:
                minmax(0,1.7fr)
                minmax(300px,.8fr);

            gap: 22px;

            align-items: start;
        }


        /* =========================
           CARD
        ========================= */

        .card-box {
            padding: 24px;

            margin-bottom: 22px;

            background:
                rgba(255,255,255,.82);

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

            border:
                1px solid
                rgba(255,255,255,.88);

            border-radius: 23px;

            box-shadow:
                0 15px 40px
                rgba(77,48,68,.065);
        }


        .card-title {
            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 20px;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 25px;

            font-weight: 600;

            color: #443540;
        }


        .card-title i {
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

            font-size: 14px;
        }


        /* =========================
           PRODUCTS
        ========================= */

        .product-row {
            display: flex;

            align-items: center;

            gap: 15px;

            padding: 15px 0;

            border-bottom:
                1px solid
                #f0e8f1;
        }


        .product-row:last-child {
            border-bottom: none;
        }


        .product-image {
            width: 72px;
            height: 82px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            overflow: hidden;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    #f9edf5,
                    #f4f0fb
                );

            border:
                1px solid #eee3eb;
        }


        .product-image img {
            width: 100%;
            height: 100%;

            object-fit: cover;
        }


        .product-placeholder {
            display: flex;

            align-items: center;

            justify-content: center;

            width: 100%;
            height: 100%;

            color: #b692b0;

            font-size: 24px;
        }


        .product-info {
            flex: 1;

            min-width: 150px;
        }


        .product-name {
            margin-bottom: 5px;

            color: #4d3b4e;

            font-size: 13px;

            font-weight: 650;
        }


        .product-id {
            color: #978b99;

            font-size: 10px;
        }


        .product-price,
        .product-qty {
            color: #817381;

            font-size: 11px;
        }


        .product-price {
            min-width: 80px;

            text-align: right;
        }


        .product-qty {
            min-width: 60px;

            text-align: center;
        }


        .product-total {
            min-width: 100px;

            text-align: right;

            color: #5b405c;

            font-size: 12px;

            font-weight: 700;
        }


        /* =========================
           SUMMARY
        ========================= */

        .summary-row {
            display: flex;

            justify-content:
                space-between;

            gap: 20px;

            padding: 10px 0;

            color: #756878;

            font-size: 12px;
        }


        .summary-row strong {
            color: #574458;
        }


        .summary-row.delivery {
            color: #4c8a67;
        }


        .summary-row.delivery strong {
            color: #4c8a67;
        }


        .summary-row.total {
            margin-top: 10px;

            padding-top: 17px;

            border-top:
                1px solid
                #e9dce9;

            color: #4d344f;

            font-size: 19px;

            font-weight: 700;
        }


        .summary-row.total strong {
            color: #4d344f;
        }


        /* =========================
           CUSTOMER INFO
        ========================= */

        .info-item {
            display: flex;

            gap: 13px;

            margin-bottom: 18px;
        }


        .info-item:last-child {
            margin-bottom: 0;
        }


        .info-icon {
            width: 39px;
            height: 39px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #f8e5ef,
                    #eee8fa
                );

            color: #8a5b82;

            font-size: 14px;
        }


        .info-label {
            margin-bottom: 3px;

            color: #9a8e9b;

            font-size: 9px;

            text-transform:
                uppercase;

            letter-spacing: .7px;
        }


        .info-value {
            color: #4d404f;

            font-size: 11px;

            font-weight: 600;

            line-height: 1.6;

            word-break:
                break-word;
        }


        /* =========================
           PAYMENT
        ========================= */

        .payment-box {
            padding: 15px;

            background:
                linear-gradient(
                    135deg,
                    #fcf5fa,
                    #faf7fd
                );

            border:
                1px solid #f0e5ef;

            border-radius: 15px;
        }


        .payment-line {
            display: flex;

            justify-content:
                space-between;

            gap: 15px;

            padding: 8px 0;

            font-size: 11px;
        }


        .payment-line span {
            color: #897d8a;
        }


        .payment-line strong {
            color: #544255;

            text-align: right;
        }


        /* =========================
           UPDATE STATUS
        ========================= */

        .update-form {
            display: flex;

            align-items: center;

            gap: 10px;
        }


        .update-form select {
            flex: 1;

            min-height: 45px;

            padding: 10px 12px;

            color: #59465a;

            background: white;

            border:
                1px solid #dfd1df;

            border-radius: 13px;

            font-family:
                "Montserrat",
                sans-serif;

            font-size: 11px;

            outline: none;

            transition: .25s;
        }


        .update-form select:focus {
            border-color: #d1a9bf;

            box-shadow:
                0 0 0 4px
                rgba(209,169,191,.11);
        }


        .update-btn {
            min-height: 45px;

            padding: 10px 17px;

            border: none;

            border-radius: 13px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #b57f9c,
                    #9d89bb
                );

            font-family:
                "Montserrat",
                sans-serif;

            font-size: 11px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 9px 20px
                rgba(155,113,141,.17);

            transition: .3s ease;
        }


        .update-btn:hover {
            color: white;

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 25px
                rgba(155,113,141,.25);
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 255px;

                padding-left: 25px;
                padding-right: 25px;
            }

            .content-grid {
                grid-template-columns:
                    1fr;
            }

        }


        @media (max-width: 900px) {

            .sidebar {
                position: relative;

                top: auto;
                left: auto;

                width:
                    calc(100% - 30px);

                height: auto;

                margin: 15px;

                border-radius: 24px;
            }

            .main-content {
                margin-left: 0;

                padding:
                    15px 20px 35px;
            }

            .sidebar-menu {
                display: grid;

                grid-template-columns:
                    repeat(3,1fr);

                gap: 7px;
            }

            .sidebar-menu li {
                margin-bottom: 0;
            }

            .logout {
                position: static;

                margin-top: 10px;
            }

            .logout a {
                border-top: none;
            }

        }


        @media (max-width: 700px) {

            .sidebar-menu {
                grid-template-columns:
                    repeat(2,1fr);
            }

            .topbar {
                align-items: flex-start;

                flex-direction: column;
            }

            .page-heading h2 {
                font-size: 36px;
            }

            .order-header-top {
                align-items: flex-start;

                flex-direction: column;
            }

            .product-row {
                flex-wrap: wrap;
            }

            .product-price,
            .product-qty,
            .product-total {
                min-width: auto;

                text-align: left;
            }

        }


        @media (max-width: 500px) {

            .sidebar {
                width:
                    calc(100% - 20px);

                margin: 10px;
            }

            .sidebar-menu {
                grid-template-columns: 1fr;
            }

            .main-content {
                padding:
                    10px 12px 30px;
            }

            .page-heading h2 {
                font-size: 32px;
            }

            .order-header,
            .card-box {
                padding: 19px;
            }

            .order-number {
                font-size: 25px;
            }

            .update-form {
                flex-direction: column;

                align-items:
                    stretch;
            }

            .update-btn {
                width: 100%;
            }

            .product-image {
                width: 62px;
                height: 72px;
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

<main class="main-content">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="page-heading">

            <h2>
                Order Details
            </h2>

            <p>
                View and manage customer order information.
            </p>

        </div>


        <a
            href="orders.php"
            class="back-btn"
        >

            <i class="bi bi-arrow-left"></i>

            Back to Orders

        </a>

    </div>


    <!-- =========================
         SUCCESS MESSAGE
    ========================= -->

    <?php if (isset($_GET['updated'])): ?>

        <div class="success-message">

            <i class="bi bi-check-circle-fill"></i>

            Order status updated successfully.

        </div>

    <?php endif; ?>


    <!-- =========================
         ORDER HEADER
    ========================= -->

    <div class="order-header">

        <div class="order-header-top">

            <div>

                <div class="order-number">

                    Order #

                    <?= htmlspecialchars(
                        $order['order_number']
                    ) ?>

                </div>


                <div class="order-date">

                    <i class="bi bi-calendar3"></i>

                    &nbsp;

                    Placed on

                    <?= date(
                        "d M Y, h:i A",
                        strtotime(
                            $order['created_at']
                        )
                    ) ?>

                </div>

            </div>


            <div>

                <span
                    class="status <?= statusClass(
                        $order['order_status']
                    ) ?>"
                >

                    <?= htmlspecialchars(
                        $order['order_status']
                    ) ?>

                </span>

            </div>

        </div>

    </div>


    <!-- =========================
         CONTENT GRID
    ========================= -->

    <div class="content-grid">


        <!-- =========================
             LEFT COLUMN
        ========================= -->

        <div>


            <!-- ORDER ITEMS -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-bag-heart"></i>

                    Ordered Products

                </div>


                <?php if (!empty($items)): ?>

                    <?php foreach ($items as $item): ?>

                        <?php

                        $itemTotal =
                            (float)$item['price']
                            *
                            (int)$item['quantity'];

                        ?>


                        <div class="product-row">


                            <!-- PRODUCT IMAGE -->

                            <div class="product-image">

                                <?php if (
                                    !empty(
                                        $item['product_image']
                                    )
                                ): ?>

                                    <?php

                                    $imagePath =
                                        "../assets/images/"
                                        .
                                        $item['product_image'];

                                    ?>


                                    <img
                                        src="<?= htmlspecialchars(
                                            $imagePath
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            $item['product_name']
                                        ) ?>"
                                        onerror="
                                            this.style.display='none';
                                            this.nextElementSibling.style.display='flex';
                                        "
                                    >


                                    <div
                                        class="product-placeholder"
                                        style="display:none;"
                                    >

                                        <i class="bi bi-image"></i>

                                    </div>


                                <?php else: ?>


                                    <div class="product-placeholder">

                                        <i class="bi bi-image"></i>

                                    </div>


                                <?php endif; ?>

                            </div>


                            <!-- PRODUCT INFO -->

                            <div class="product-info">

                                <div class="product-name">

                                    <?= htmlspecialchars(
                                        $item['product_name']
                                        ??
                                        'Product'
                                    ) ?>

                                </div>


                                <div class="product-id">

                                    Product ID:

                                    <?= htmlspecialchars(
                                        $item['product_id']
                                    ) ?>

                                </div>

                            </div>


                            <!-- PRICE -->

                            <div class="product-price">

                                Rs.

                                <?= number_format(
                                    $item['price'],
                                    0
                                ) ?>

                            </div>


                            <!-- QUANTITY -->

                            <div class="product-qty">

                                Qty:

                                <?= (int)
                                    $item['quantity']
                                ?>

                            </div>


                            <!-- TOTAL -->

                            <div class="product-total">

                                Rs.

                                <?= number_format(
                                    $itemTotal,
                                    0
                                ) ?>

                            </div>

                        </div>


                    <?php endforeach; ?>


                <?php else: ?>

                    <div
                        style="
                            color:#8b7c8d;
                            font-size:12px;
                            padding:10px 0;
                        "
                    >

                        <i class="bi bi-bag-x me-2"></i>

                        No products found for this order.

                    </div>

                <?php endif; ?>

            </div>


            <!-- =========================
                 ORDER SUMMARY
            ========================= -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-receipt"></i>

                    Order Summary

                </div>


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>

                        Rs.

                        <?= number_format(
                            $order['subtotal'],
                            0
                        ) ?>

                    </strong>

                </div>


                <div class="summary-row delivery">

                    <span>
                        Delivery Charges
                    </span>

                    <strong>

                        <?php if (
                            (float)
                            $order['delivery_charges']
                            == 0
                        ): ?>

                            FREE

                        <?php else: ?>

                            Rs.

                            <?= number_format(
                                $order[
                                    'delivery_charges'
                                ],
                                0
                            ) ?>

                        <?php endif; ?>

                    </strong>

                </div>


                <div class="summary-row total">

                    <span>
                        Grand Total
                    </span>

                    <strong>

                        Rs.

                        <?= number_format(
                            $order['total'],
                            0
                        ) ?>

                    </strong>

                </div>

            </div>


            <!-- =========================
                 UPDATE STATUS
            ========================= -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-arrow-repeat"></i>

                    Update Order Status

                </div>


                <form
                    method="POST"
                    class="update-form"
                >

                    <select
                        name="order_status"
                        required
                    >

                        <?php

                        $statuses = [
                            'Pending',
                            'Processing',
                            'Shipped',
                            'Delivered',
                            'Cancelled'
                        ];

                        foreach (
                            $statuses
                            as $status
                        ):

                        ?>

                            <option
                                value="<?= htmlspecialchars(
                                    $status
                                ) ?>"
                                <?= $order[
                                    'order_status'
                                ] === $status
                                    ? 'selected'
                                    : '' ?>
                            >

                                <?= htmlspecialchars(
                                    $status
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>


                    <button
                        type="submit"
                        class="update-btn"
                    >

                        <i class="bi bi-check2"></i>

                        Update Status

                    </button>

                </form>

            </div>

        </div>


        <!-- =========================
             RIGHT COLUMN
        ========================= -->

        <div>


            <!-- =========================
                 CUSTOMER INFORMATION
            ========================= -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-person-circle"></i>

                    Customer Information

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="bi bi-person"></i>

                    </div>


                    <div>

                        <div class="info-label">
                            Customer
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order[
                                    'customer_name'
                                ]
                            ) ?>

                        </div>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="bi bi-envelope"></i>

                    </div>


                    <div>

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order['email']
                            ) ?>

                        </div>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="bi bi-telephone"></i>

                    </div>


                    <div>

                        <div class="info-label">
                            Phone
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $order['phone']
                            ) ?>

                        </div>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-icon">

                        <i class="bi bi-geo-alt"></i>

                    </div>


                    <div>

                        <div class="info-label">
                            Delivery Address
                        </div>

                        <div class="info-value">

                            <?= nl2br(
                                htmlspecialchars(
                                    $order['address']
                                )
                            ) ?>


                            <?php if (
                                !empty(
                                    $order['city']
                                )
                            ): ?>

                                <br>

                                <?= htmlspecialchars(
                                    $order['city']
                                ) ?>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================
                 PAYMENT INFORMATION
            ========================= -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-credit-card"></i>

                    Payment Information

                </div>


                <div class="payment-box">


                    <div class="payment-line">

                        <span>
                            Method
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order[
                                    'payment_method'
                                ]
                            ) ?>

                        </strong>

                    </div>


                    <div class="payment-line">

                        <span>
                            Payment Status
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order[
                                    'payment_status'
                                ]
                            ) ?>

                        </strong>

                    </div>


                    <div class="payment-line">

                        <span>
                            Order Status
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order[
                                    'order_status'
                                ]
                            ) ?>

                        </strong>

                    </div>


                </div>

            </div>


            <!-- =========================
                 ORDER INFORMATION
            ========================= -->

            <div class="card-box">

                <div class="card-title">

                    <i class="bi bi-info-circle"></i>

                    Order Information

                </div>


                <div class="payment-line">

                    <span>
                        Order ID
                    </span>

                    <strong>

                        #<?= (int)
                            $order['id']
                        ?>

                    </strong>

                </div>


                <div class="payment-line">

                    <span>
                        Customer ID
                    </span>

                    <strong>

                        <?= (int)
                            $order['user_id']
                        ?>

                    </strong>

                </div>


                <div class="payment-line">

                    <span>
                        Created
                    </span>

                    <strong>

                        <?= date(
                            "d M Y",
                            strtotime(
                                $order[
                                    'created_at'
                                ]
                            )
                        ) ?>

                    </strong>

                </div>


                <div class="payment-line">

                    <span>
                        Last Updated
                    </span>

                    <strong>

                        <?= !empty(
                            $order[
                                'updated_at'
                            ]
                        )
                            ? date(
                                "d M Y",
                                strtotime(
                                    $order[
                                        'updated_at'
                                    ]
                                )
                            )
                            : '—'
                        ?>

                    </strong>

                </div>

            </div>

        </div>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
