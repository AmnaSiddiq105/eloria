
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
   ADMIN INFO
================================ */
$adminName = $_SESSION['admin_name'] ?? 'Admin';


/* ===============================
   DASHBOARD STATISTICS
================================ */

// Total Orders
$totalOrders = 0;

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $totalOrders = (int)$row['total'];
}


// Total Sales
$totalSales = 0;

$result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS sales
     FROM orders
     WHERE order_status != 'Cancelled'"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $totalSales = (float)$row['sales'];
}


// Total Products
$totalProducts = 0;

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $totalProducts = (int)$row['total'];
}


// Total Customers
$totalCustomers = 0;

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $totalCustomers = (int)$row['total'];
}


/* ===============================
   RECENT ORDERS
================================ */

$recentOrders = [];

$query = "
    SELECT
        id,
        order_number,
        customer_name,
        email,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 8
";

$result = mysqli_query($conn, $query);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $recentOrders[] = $row;
    }
}


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

    <title>Dashboard | ÉLORIA</title>


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


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- BOOTSTRAP ICONS -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
<link rel="stylesheet" href="assets/css/admin-sidebar.css">

    <style>

        /* =========================
           RESET
        ========================= */

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

            color: #302a31;

            background:

                radial-gradient(
                    circle at 8% 8%,
                    rgba(245, 210, 226, .45),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 92% 18%,
                    rgba(222, 212, 246, .45),
                    transparent 32%
                ),

                linear-gradient(
                    135deg,
                    #fffafc 0%,
                    #f8f3fb 52%,
                    #f3f6ff 100%
                );

            overflow-x: hidden;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            top: 22px;
            left: 22px;

            width: 245px;

            height:
                calc(100vh - 44px);

            padding:
                30px 18px;

            background:
                rgba(255,255,255,.72);

            backdrop-filter:
                blur(22px);

            -webkit-backdrop-filter:
                blur(22px);

            border:
                1px solid
                rgba(255,255,255,.88);

            border-radius: 28px;

            box-shadow:
                0 18px 50px
                rgba(80,60,80,.09);

            z-index: 1000;
        }


        /* =========================
           BRAND
        ========================= */

        .brand {

            display: block;

            text-align: center;

            text-decoration: none;

            padding-bottom: 25px;

            margin-bottom: 25px;

            border-bottom:
                1px solid
                rgba(90,70,90,.08);
        }


        .brand h2 {

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 38px;

            line-height: 1;

            font-weight: 600;

            letter-spacing: 2px;

            color: #332c34;
        }


        .brand span {

            color: #b17d9e;
        }


        .brand small {

            display: block;

            margin-top: 8px;

            color: #9b8997;

            font-size: 9px;

            font-weight: 500;

            letter-spacing: 3px;

            text-transform: uppercase;
        }


        /* =========================
           ADMIN PROFILE
        ========================= */

        .admin-profile {

            text-align: center;

            padding: 17px 10px;

            margin-bottom: 22px;

            border-radius: 18px;

            background:
                rgba(255,255,255,.58);

            border:
                1px solid
                rgba(255,255,255,.85);
        }


        .admin-icon {

            width: 55px;
            height: 55px;

            margin:
                0 auto 10px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            color: #fff;

            font-size: 22px;

            background:
                linear-gradient(
                    135deg,
                    #e3b4ca,
                    #b3a0d4
                );

            box-shadow:
                0 8px 20px
                rgba(166,126,159,.20);
        }


        .admin-profile h6 {

            margin-bottom: 3px;

            color: #4a4049;

            font-size: 12px;

            font-weight: 600;
        }


        .admin-profile small {

            color: #9b8d99;

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

            margin-bottom: 7px;
        }


        .sidebar-menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            border-radius: 14px;

            color: #766a74;

            text-decoration: none;

            font-size: 12px;

            font-weight: 500;

            transition:
                .3s ease;
        }


        .sidebar-menu a i {

            width: 19px;

            text-align: center;

            color: #a68e9f;

            font-size: 16px;
        }


        .sidebar-menu a:hover {

            color: #4d424b;

            background:
                linear-gradient(
                    135deg,
                    rgba(249,222,234,.65),
                    rgba(232,223,247,.65)
                );

            transform:
                translateX(3px);
        }


        .sidebar-menu a.active {

            color: #4b4049;

            background:
                linear-gradient(
                    135deg,
                    #f8dce8,
                    #e8def7
                );

            box-shadow:
                0 7px 18px
                rgba(157,119,155,.10);
        }


        .sidebar-menu a.active i {

            color: #956b89;
        }


        /* =========================
           LOGOUT
        ========================= */

        .logout {

            position: absolute;

            left: 18px;
            right: 18px;
            bottom: 22px;

            padding-top: 16px;

            border-top:
                1px solid
                rgba(90,70,90,.08);
        }


        .logout a {

            color: #987e8c;
        }


        .logout a:hover {

            color: #a44e6c;

            background:
                rgba(250,226,235,.7);
        }


        /* =========================
           MAIN
        ========================= */

        .main-content {

            margin-left: 291px;

            min-height: 100vh;

            padding:
                43px 42px 55px;
        }


        /* =========================
           TOP BAR
        ========================= */

        .topbar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 28px;
        }


        .page-label {

            display: block;

            margin-bottom: 4px;

            color: #a07892;

            font-size: 10px;

            font-weight: 600;

            letter-spacing: 2.5px;

            text-transform: uppercase;
        }


        .page-title h2 {

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 44px;

            line-height: 1;

            font-weight: 600;

            color: #302a31;

            margin-bottom: 7px;
        }


        .page-title p {

            margin: 0;

            color: #938592;

            font-size: 12px;
        }


        /* =========================
           DATE BOX
        ========================= */

        .date-box {

            display: flex;

            align-items: center;

            gap: 8px;

            padding: 11px 16px;

            border:
                1px solid
                rgba(255,255,255,.9);

            border-radius: 13px;

            color: #7c6b78;

            background:
                rgba(255,255,255,.72);

            backdrop-filter:
                blur(15px);

            box-shadow:
                0 8px 25px
                rgba(81,52,85,.05);

            font-size: 11px;
        }


        .date-box i {

            color: #a77e98;

            font-size: 14px;
        }


        /* =========================
           STATISTICS
        ========================= */

        .stats-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 19px;

            margin-bottom: 27px;
        }


        .stat-card {

            position: relative;

            overflow: hidden;

            padding: 21px;

            background:
                rgba(255,255,255,.82);

            backdrop-filter:
                blur(17px);

            -webkit-backdrop-filter:
                blur(17px);

            border:
                1px solid
                rgba(255,255,255,.88);

            border-radius: 21px;

            box-shadow:
                0 13px 35px
                rgba(72,42,78,.07);

            transition:
                transform .3s ease,
                box-shadow .3s ease;
        }


        .stat-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 18px 42px
                rgba(72,42,78,.11);
        }


        .stat-card::after {

            content: "";

            position: absolute;

            width: 105px;
            height: 105px;

            right: -42px;
            top: -42px;

            border-radius: 50%;

            background:
                rgba(220,169,202,.12);
        }


        .stat-icon {

            width: 48px;
            height: 48px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 15px;

            color: #80517b;

            background:
                linear-gradient(
                    135deg,
                    #f3d7e7,
                    #eadcf4
                );

            font-size: 20px;
        }


        .stat-card h3 {

            position: relative;

            z-index: 1;

            margin-top: 17px;

            margin-bottom: 3px;

            color: #332d34;

            font-family:
                "Montserrat",
                sans-serif;

            font-size: 25px;

            font-weight: 600;
        }


        .stat-card p {

            margin: 0;

            color: #8d7e8e;

            font-size: 11px;
        }


        /* =========================
           ORDERS CARD
        ========================= */

        .orders-card {

            padding: 25px;

            background:
                rgba(255,255,255,.84);

            backdrop-filter:
                blur(18px);

            -webkit-backdrop-filter:
                blur(18px);

            border:
                1px solid
                rgba(255,255,255,.9);

            border-radius: 23px;

            box-shadow:
                0 14px 38px
                rgba(72,42,78,.07);
        }


        .orders-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 20px;
        }


        .orders-header h4 {

            margin: 0;

            color: #393139;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 25px;

            font-weight: 600;
        }


        .view-all {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            color: #9b6e90;

            text-decoration: none;

            font-size: 11px;

            font-weight: 600;

            transition: .3s;
        }


        .view-all:hover {

            color: #6f4c69;

            transform:
                translateX(2px);
        }


        /* =========================
           TABLE
        ========================= */

        .table-responsive {

            border-radius: 15px;

            overflow-x: auto;
        }


        .orders-table {

            width: 100%;

            min-width: 850px;

            border-collapse: collapse;
        }


        .orders-table th {

            padding:
                13px 12px;

            color: #817180;

            background:
                rgba(250,245,250,.8);

            border-bottom:
                1px solid
                #eadfea;

            font-size: 9px;

            font-weight: 600;

            letter-spacing: .8px;

            text-transform: uppercase;
        }


        .orders-table td {

            padding:
                15px 12px;

            color: #514650;

            border-bottom:
                1px solid
                #f0e8f1;

            vertical-align: middle;

            font-size: 11px;
        }


        .orders-table tbody tr {

            transition:
                background .25s ease;
        }


        .orders-table tbody tr:hover {

            background:
                rgba(255,247,252,.75);
        }


        .orders-table tbody tr:last-child td {

            border-bottom: none;
        }


        /* =========================
           ORDER DATA
        ========================= */

        .order-number {

            color: #68436b;

            font-size: 11px;

            font-weight: 600;
        }


        .customer-name {

            color: #4b4149;

            font-size: 11px;

            font-weight: 600;
        }


        .customer-email {

            margin-top: 3px;

            color: #988b98;

            font-size: 9px;
        }


        .order-date {

            color: #635660;

            white-space: nowrap;
        }


        .order-time {

            color: #a195a0;

            font-size: 9px;
        }


        .payment {

            color: #756875;

            text-transform: capitalize;
        }


        .order-total {

            color: #443b43;

            font-weight: 600;

            white-space: nowrap;
        }


        /* =========================
           STATUS
        ========================= */

        .status {

            display: inline-flex;

            align-items: center;

            padding:
                6px 10px;

            border-radius: 30px;

            font-size: 9px;

            font-weight: 600;

            white-space: nowrap;
        }


        .status::before {

            content: "";

            width: 5px;
            height: 5px;

            margin-right: 6px;

            border-radius: 50%;

            background:
                currentColor;
        }


        .status.pending {

            color: #9b7500;

            background: #fff3cd;
        }


        .status.processing {

            color: #6e51a8;

            background: #e8e0ff;
        }


        .status.shipped {

            color: #32739c;

            background: #dff1ff;
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
           ACTION BUTTON
        ========================= */

        .action-btn {

            width: 35px;
            height: 35px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            border-radius: 11px;

            color: #795178;

            background:
                linear-gradient(
                    135deg,
                    #f7e7f2,
                    #eee8f8
                );

            text-decoration: none;

            transition:
                transform .3s ease,
                background .3s ease,
                color .3s ease;
        }


        .action-btn:hover {

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #c68eaa,
                    #9c8ac6
                );

            transform:
                translateY(-2px);
        }


        /* =========================
           EMPTY ORDERS
        ========================= */

        .empty-orders {

            padding:
                55px 20px;

            color: #8b7c8d;

            text-align: center;
        }


        .empty-orders i {

            display: block;

            margin-bottom: 12px;

            color: #d9bfd8;

            font-size: 45px;
        }


        .empty-orders h5 {

            color: #554955;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 23px;

            font-weight: 600;
        }


        .empty-orders p {

            margin: 5px 0 0;

            font-size: 11px;
        }


        /* =========================
           MOBILE MENU BUTTON
        ========================= */

        .mobile-menu-btn {

            display: none;

            position: fixed;

            top: 18px;
            right: 18px;

            width: 43px;
            height: 43px;

            border: none;

            border-radius: 13px;

            color: #5c4c59;

            background:
                rgba(255,255,255,.92);

            box-shadow:
                0 8px 25px
                rgba(70,50,70,.10);

            cursor: pointer;

            z-index: 1200;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1150px) {

            .stats-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media (max-width: 850px) {

            .sidebar {

                width: 220px;
            }

            .main-content {

                margin-left: 260px;

                padding:
                    35px 25px;
            }
        }


        @media (max-width: 760px) {

            .mobile-menu-btn {

                display: flex;

                align-items: center;
                justify-content: center;
            }


            .sidebar {

                left: -270px;

                top: 15px;

                width: 245px;

                height:
                    calc(100vh - 30px);

                transition:
                    left .3s ease;
            }


            .sidebar.show {

                left: 15px;
            }


            .main-content {

                margin-left: 0;

                padding:
                    78px 18px 35px;
            }


            .page-title h2 {

                font-size: 38px;
            }


            .date-box {

                display: none;
            }
        }


        @media (max-width: 560px) {

            .stats-grid {

                grid-template-columns: 1fr;
            }


            .main-content {

                padding-left: 14px;
                padding-right: 14px;
            }


            .page-title h2 {

                font-size: 34px;
            }


            .orders-card {

                padding: 18px;
            }


            .orders-header h4 {

                font-size: 22px;
            }
        }
        

        /* ======================================== */


        ```css
/* =========================
   SIDEBAR
========================= */

.sidebar {
    width: 270px;
    min-height: 100vh;
    position: fixed;
    left: 20px;
    top: 20px;
    bottom: 20px;

    padding: 28px 20px;

    display: flex;
    flex-direction: column;

    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border: 1px solid rgba(230, 207, 223, 0.55);
    border-radius: 28px;

    box-shadow: 0 20px 50px rgba(90, 65, 90, 0.08);

    box-sizing: border-box;
}


/* BRAND */

.sidebar-brand {
    margin-bottom: 28px;
}

.brand-logo {
    display: block;
    color: #3d3540;
    text-decoration: none;

    font-family: 'Cormorant Garamond', serif;
    font-size: 34px;
    font-weight: 700;
    letter-spacing: 1px;
}

.brand-subtitle {
    display: block;
    margin-top: 2px;

    color: #a08091;

    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
}


/* ADMIN PROFILE */

.admin-profile {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 14px;
    margin-bottom: 25px;

    background: linear-gradient(
        135deg,
        rgba(249, 230, 239, 0.75),
        rgba(235, 229, 247, 0.7)
    );

    border-radius: 18px;
}

.admin-avatar {
    width: 42px;
    height: 42px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: linear-gradient(
        135deg,
        #e9bfd3,
        #cfc5e8
    );

    color: #ffffff;

    font-family: 'Cormorant Garamond', serif;
    font-size: 21px;
    font-weight: 700;
}

.admin-info {
    min-width: 0;
}

.admin-info strong {
    display: block;

    color: #403741;

    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-info span {
    display: block;
    margin-top: 3px;

    color: #a18898;

    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
}


/* NAVIGATION */

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 13px 15px;

    color: #756773;
    text-decoration: none;

    border-radius: 15px;

    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 500;

    transition: all 0.25s ease;
}

.nav-link:hover {
    color: #4e414b;

    background: rgba(246, 225, 235, 0.55);

    transform: translateX(3px);
}

.nav-link.active {
    color: #594653;

    background: linear-gradient(
        135deg,
        rgba(244, 213, 227, 0.9),
        rgba(226, 219, 242, 0.85)
    );

    box-shadow: 0 8px 20px rgba(190, 160, 185, 0.12);
}

.nav-icon {
    width: 25px;
    height: 25px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 9px;

    background: rgba(255, 255, 255, 0.7);

    font-size: 14px;
}


/* =========================
   PROFILE + LOGOUT
========================= */

.sidebar-bottom {
    margin-top: auto;

    padding-top: 20px;

    display: flex;
    flex-direction: column;

    gap: 8px;

    border-top: 1px solid rgba(220, 200, 215, 0.45);
}

.bottom-link {
    width: 100%;
    box-sizing: border-box;

    display: flex;
    align-items: center;
    gap: 12px;

    padding: 13px 15px;

    color: #756773;
    text-decoration: none;

    border-radius: 15px;

    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 500;

    transition: all 0.25s ease;
}

.bottom-link:hover {
    background: rgba(246, 225, 235, 0.65);

    color: #4e414b;

    transform: translateX(3px);
}

.logout-link {
    color: #9b6578;
}

.logout-link:hover {
    background: rgba(249, 220, 230, 0.75);
    color: #8b4f65;
}


/* MOBILE */

@media (max-width: 900px) {

    .sidebar {
        position: relative;
        left: 0;
        top: 0;
        bottom: auto;

        width: 100%;
        min-height: auto;

        margin: 15px;

        border-radius: 24px;
    }

    .sidebar-bottom {
        margin-top: 20px;
    }
}


.sidebar {
    width: 270px;
    height: calc(100vh - 40px);
    position: fixed;
    left: 20px;
    top: 20px;

    padding: 28px 20px;

    display: flex;
    flex-direction: column;

    box-sizing: border-box;
    overflow: hidden;

    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    border: 1px solid rgba(230, 207, 223, 0.55);
    border-radius: 28px;

    box-shadow: 0 20px 50px rgba(90, 65, 90, 0.08);
}





    </style>

</head>


<body>

<?php include "includes/sidebar.php"; ?> 
<!-- =========================
     MOBILE MENU
========================= -->

<button
    class="mobile-menu-btn"
    id="mobileMenuBtn"
    type="button"
    aria-label="Open menu"
>

    <i class="bi bi-list"></i>

</button>


<!-- =========================
     SIDEBAR
========================= -->


<?php include "includes/sidebar.php"; ?>





<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main-content">


    <!-- TOP BAR -->

    <div class="topbar">

        <div class="page-title">

            <span class="page-label">
                ÉLORIA Administration
            </span>

            <h2>
                Dashboard
            </h2>

            <p>
                Welcome back,
                <?= htmlspecialchars($adminName) ?>
                👋
            </p>

        </div>


        <div class="date-box">

            <i class="bi bi-calendar3"></i>

            <?= date("d M Y") ?>

        </div>

    </div>


    <!-- =========================
         STATISTICS
    ========================= -->

    <div class="stats-grid">


        <!-- TOTAL ORDERS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-bag-check"></i>

            </div>


            <h3>

                <?= number_format($totalOrders) ?>

            </h3>


            <p>
                Total Orders
            </p>

        </div>


        <!-- TOTAL SALES -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-currency-dollar"></i>

            </div>


            <h3>

                Rs.
                <?= number_format(
                    $totalSales,
                    0
                ) ?>

            </h3>


            <p>
                Total Sales
            </p>

        </div>


        <!-- TOTAL PRODUCTS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-box-seam"></i>

            </div>


            <h3>

                <?= number_format($totalProducts) ?>

            </h3>


            <p>
                Total Products
            </p>

        </div>


        <!-- TOTAL CUSTOMERS -->

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-people"></i>

            </div>


            <h3>

                <?= number_format($totalCustomers) ?>

            </h3>


            <p>
                Total Customers
            </p>

        </div>

    </div>


    <!-- =========================
         RECENT ORDERS
    ========================= -->

    <div class="orders-card">


        <div class="orders-header">

            <h4>
                Recent Orders
            </h4>


            <a
                href="orders.php"
                class="view-all"
            >

                View All

                <i class="bi bi-arrow-right"></i>

            </a>

        </div>


        <?php if (!empty($recentOrders)): ?>


            <div class="table-responsive">

                <table class="orders-table">


                    <thead>

                        <tr>

                            <th>
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Payment
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $recentOrders
                        as $order
                    ): ?>


                        <tr>


                            <!-- ORDER -->

                            <td>

                                <div class="order-number">

                                    #<?= htmlspecialchars(
                                        $order['order_number']
                                    ) ?>

                                </div>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $order['customer_name']
                                    ) ?>

                                </div>


                                <div class="customer-email">

                                    <?= htmlspecialchars(
                                        $order['email']
                                    ) ?>

                                </div>

                            </td>


                            <!-- DATE -->

                            <td>

                                <div class="order-date">

                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $order['created_at']
                                        )
                                    ) ?>

                                </div>


                                <div class="order-time">

                                    <?= date(
                                        "h:i A",
                                        strtotime(
                                            $order['created_at']
                                        )
                                    ) ?>

                                </div>

                            </td>


                            <!-- PAYMENT -->

                            <td>

                                <span class="payment">

                                    <?= htmlspecialchars(
                                        $order['payment_method']
                                    ) ?>

                                </span>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <span class="order-total">

                                    Rs.
                                    <?= number_format(
                                        $order['total'],
                                        0
                                    ) ?>

                                </span>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status <?= statusClass(
                                        $order['order_status']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $order['order_status']
                                    ) ?>

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="order-details.php?id=<?= $order['id'] ?>"
                                    class="action-btn"
                                    title="View Order"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="empty-orders">

                <i class="bi bi-bag-x"></i>

                <h5>
                    No Orders Yet
                </h5>

                <p>
                    Your recent orders will appear here.
                </p>

            </div>


        <?php endif; ?>


    </div>


</main>


<!-- =========================
     MOBILE SIDEBAR SCRIPT
========================= -->

<script>

const mobileMenuBtn =
    document.getElementById("mobileMenuBtn");

const sidebar =
    document.getElementById("sidebar");


mobileMenuBtn.addEventListener(
    "click",
    function () {

        sidebar.classList.toggle("show");

        const icon =
            mobileMenuBtn.querySelector("i");

        if (
            sidebar.classList.contains("show")
        ) {

            icon.className =
                "bi bi-x-lg";

        } else {

            icon.className =
                "bi bi-list";
        }

    }
);


document.addEventListener(
    "click",
    function (event) {

        if (

            window.innerWidth <= 760 &&

            sidebar.classList.contains("show") &&

            !sidebar.contains(event.target) &&

            !mobileMenuBtn.contains(event.target)

        ) {

            sidebar.classList.remove("show");

            mobileMenuBtn
                .querySelector("i")
                .className =
                "bi bi-list";
        }

    }
);

</script>


</body>

</html>
