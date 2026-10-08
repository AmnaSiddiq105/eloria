
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
   SEARCH
========================= */
$search = trim($_GET['search'] ?? '');

/* =========================
   TOTAL CUSTOMERS
========================= */
$total_query = "
    SELECT COUNT(*) AS total_customers
    FROM users
";

$total_result = $conn->query($total_query);
$total_customers = $total_result->fetch_assoc()['total_customers'] ?? 0;

/* =========================
   NEW CUSTOMERS - LAST 30 DAYS
========================= */
$new_query = "
    SELECT COUNT(*) AS new_customers
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";

$new_result = $conn->query($new_query);
$new_customers = $new_result->fetch_assoc()['new_customers'] ?? 0;

/* =========================
   CUSTOMER LIST
========================= */

if ($search !== '') {

    $sql = "
        SELECT
            id,
            name,
            email,
            created_at
        FROM users
        WHERE
            name LIKE ?
            OR email LIKE ?
        ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    $search_value = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $search_value,
        $search_value
    );

    $stmt->execute();

    $customers = $stmt->get_result();

} else {

    $sql = "
        SELECT
            id,
            name,
            email,
            created_at
        FROM users
        ORDER BY created_at DESC
    ";

    $customers = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Customers | ÉLORIA Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="assets/css/admin-sidebar.css">
    <style>

        /* =====================================================
           ÉLORIA HOME STYLE
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

            color: #29252a;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(244, 207, 225, 0.45),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 15%,
                    rgba(220, 210, 244, 0.45),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffdfd 0%,
                    #fff8fb 45%,
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
                1px solid rgba(255, 255, 255, 0.9);

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

            letter-spacing: 0.2px;

            transition:
                color 0.3s ease,
                background 0.3s ease,
                transform 0.3s ease;
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

        .logout a {

            color: #9a7d89;
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

            padding: 42px 42px 55px;

            min-height: 100vh;

            max-width: 1500px;
        }

        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-end;

            margin-bottom: 30px;

            gap: 20px;
        }

        .page-title h1 {

            font-family: "Cormorant Garamond", serif;

            font-size: clamp(42px, 4vw, 58px);

            line-height: 0.9;

            font-weight: 500;

            letter-spacing: -1px;

            color: #302a30;

            margin-bottom: 13px;
        }

        .page-title p {

            color: #91858e;

            font-size: 11px;

            letter-spacing: 0.5px;

            line-height: 1.7;
        }

        /* =====================================================
           STATS
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;

            margin-bottom: 24px;
        }

        .stat-card {

            position: relative;

            overflow: hidden;

            background:
                rgba(255, 255, 255, 0.72);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.95);

            border-radius: 22px;

            padding: 23px;

            box-shadow:
                0 15px 40px rgba(65, 45, 65, 0.07);

            display: flex;

            align-items: center;

            gap: 17px;

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }

        .stat-card::after {

            content: "";

            position: absolute;

            width: 120px;
            height: 120px;

            right: -45px;
            top: -55px;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    rgba(246, 208, 226, 0.35),
                    rgba(221, 211, 244, 0.35)
                );
        }

        .stat-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 20px 45px rgba(65, 45, 65, 0.10);
        }

        .stat-icon {

            position: relative;

            z-index: 2;

            width: 56px;
            height: 56px;

            flex-shrink: 0;

            border-radius: 17px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #f5d9e7,
                    #e6ddf4
                );

            color: #95687f;

            font-size: 22px;
        }

        .stat-info {

            position: relative;

            z-index: 2;
        }

        .stat-info span {

            display: block;

            font-size: 10px;

            color: #9b8d95;

            letter-spacing: 0.3px;

            margin-bottom: 5px;
        }

        .stat-info strong {

            font-family: "Cormorant Garamond", serif;

            font-size: 31px;

            font-weight: 600;

            color: #393138;
        }

        /* =====================================================
           CONTENT CARD
        ===================================================== */

        .content-card {

            background:
                rgba(255, 255, 255, 0.78);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid rgba(255, 255, 255, 0.95);

            border-radius: 24px;

            padding: 26px;

            box-shadow:
                0 18px 50px rgba(65, 45, 65, 0.07);
        }

        /* =====================================================
           TOP BAR
        ===================================================== */

        .top-bar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 23px;

            flex-wrap: wrap;
        }

        .top-bar h3 {

            font-family: "Cormorant Garamond", serif;

            color: #3b3339;

            font-size: 27px;

            font-weight: 600;
        }

        .top-bar h3 i {

            color: #a7778e;

            font-size: 19px;

            margin-right: 5px;
        }

        /* =====================================================
           SEARCH
        ===================================================== */

        .search-form {

            display: flex;

            align-items: stretch;

            gap: 7px;
        }

        .search-box {

            width: 275px;

            padding: 11px 15px;

            border:
                1px solid #eadfe5;

            border-radius: 12px;

            outline: none;

            background:
                rgba(255, 255, 255, 0.85);

            color: #4b4148;

            font-family: "Montserrat", sans-serif;

            font-size: 11px;

            transition: 0.3s;
        }

        .search-box::placeholder {

            color: #b0a3aa;
        }

        .search-box:focus {

            border-color: #d5a8bd;

            box-shadow:
                0 0 0 4px rgba(219, 181, 201, 0.12);
        }

        .search-btn {

            border: none;

            background:
                linear-gradient(
                    135deg,
                    #b77f99,
                    #9679ac
                );

            color: white;

            min-width: 45px;

            border-radius: 12px;

            cursor: pointer;

            font-size: 13px;

            transition: 0.3s;

            box-shadow:
                0 8px 18px rgba(142, 103, 130, 0.15);
        }

        .search-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 23px rgba(142, 103, 130, 0.20);
        }

        .clear-btn {

            text-decoration: none;

            padding: 0 13px;

            border-radius: 12px;

            background:
                #f7eef3;

            color: #997487;

            font-size: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: 0.3s;
        }

        .clear-btn:hover {

            background: #f0dce7;

            color: #7e5a6d;
        }

        /* =====================================================
           TABLE
        ===================================================== */

        .table-wrapper {

            overflow-x: auto;

            border-radius: 16px;
        }

        table {

            width: 100%;

            border-collapse: separate;

            border-spacing: 0;

            min-width: 720px;
        }

        thead th {

            text-align: left;

            padding: 14px 15px;

            background:
                linear-gradient(
                    135deg,
                    #fcf5f8,
                    #f7f4fb
                );

            color: #8d7883;

            font-size: 9px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: 1px;

            border-bottom:
                1px solid #eee5ea;
        }

        thead th:first-child {

            border-top-left-radius: 12px;
        }

        thead th:last-child {

            border-top-right-radius: 12px;
        }

        tbody td {

            padding: 16px 15px;

            border-bottom:
                1px solid #f2eaee;

            color: #665b62;

            font-size: 11px;

            background:
                rgba(255,255,255,0.30);
        }

        tbody tr {

            transition: 0.25s ease;
        }

        tbody tr:hover td {

            background:
                rgba(251, 242, 247, 0.72);
        }

        tbody tr:last-child td {

            border-bottom: none;
        }

        /* =====================================================
           CUSTOMER
        ===================================================== */

        .customer-info {

            display: flex;

            align-items: center;

            gap: 12px;
        }

        .avatar {

            width: 42px;
            height: 42px;

            flex-shrink: 0;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #d9aabe,
                    #a697c5
                );

            color: white;

            display: flex;

            align-items: center;
            justify-content: center;

            font-family: "Cormorant Garamond", serif;

            font-weight: 700;

            font-size: 19px;

            box-shadow:
                0 6px 15px rgba(150, 112, 137, 0.16);
        }

        .customer-name {

            font-weight: 600;

            color: #40373e;

            font-size: 12px;

            margin-bottom: 2px;
        }

        .customer-id {

            display: block;

            font-size: 9px;

            color: #aa9ba4;

            letter-spacing: 0.3px;
        }

        .email {

            color: #71666d;

            font-size: 11px;
        }

        .date {

            color: #82757d;

            font-size: 10px;
        }

        /* =====================================================
           DETAILS BUTTON
        ===================================================== */

        .details-btn {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            text-decoration: none;

            padding: 8px 13px;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #f6e6ee,
                    #eee8f7
                );

            color: #8b6479;

            font-size: 10px;

            font-weight: 600;

            transition: 0.3s;

            border:
                1px solid rgba(220, 199, 211, 0.55);
        }

        .details-btn:hover {

            background:
                linear-gradient(
                    135deg,
                    #b77f99,
                    #9679ac
                );

            color: white;

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(145, 104, 130, 0.16);
        }

        /* =====================================================
           EMPTY
        ===================================================== */

        .empty {

            text-align: center;

            padding: 65px 20px;

            color: #a09299;
        }

        .empty i {

            font-size: 42px;

            color: #d8bccb;

            display: block;

            margin-bottom: 13px;
        }

        .empty h4 {

            font-family: "Cormorant Garamond", serif;

            color: #65535e;

            font-size: 25px;

            font-weight: 600;

            margin-bottom: 5px;
        }

        .empty p {

            font-size: 11px;

            line-height: 1.7;
        }

        .empty strong {

            color: #956b82;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1050px) {

            .sidebar {

                left: 15px;

                width: 210px;
            }

            .main {

                margin-left: 245px;

                padding: 32px 25px;
            }

            .stats {

                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 780px) {

            .sidebar {

                position: relative;

                left: auto;
                top: auto;

                width: calc(100% - 30px);

                height: auto;

                margin: 15px;

                border-radius: 22px;

                padding: 22px;
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

            .page-header {

                align-items: flex-start;

                margin-bottom: 24px;
            }

            .page-title h1 {

                font-size: 46px;
            }

            .content-card {

                padding: 18px;
            }

            .top-bar {

                align-items: flex-start;

                flex-direction: column;
            }

            .search-form {

                width: 100%;
            }

            .search-box {

                flex: 1;

                width: auto;
            }
        }

        @media (max-width: 480px) {

            .stat-card {

                padding: 18px;
            }

            .stat-icon {

                width: 48px;
                height: 48px;

                font-size: 19px;
            }

            .top-bar h3 {

                font-size: 24px;
            }

            .search-form {

                flex-wrap: wrap;
            }

            .search-box {

                width: 100%;

                flex: 1 1 100%;
            }

            .search-btn,
            .clear-btn {

                height: 42px;
            }
        }

    </style>
<link rel="stylesheet" href="assets/admin-sidebar.css">
</head>

<body>
<?php include "includes/sidebar.php"; ?> 
<!-- =====================================================
     SIDEBAR
===================================================== -->
<?php include "includes/sidebar.php"; ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main">

    <!-- HEADER -->

    <div class="page-header">

        <div class="page-title">

            <h1>Customers</h1>

            <p>
                Manage and view your registered ÉLORIA customers.
            </p>

        </div>

    </div>


    <!-- =================================================
         STATS
    ================================================== -->

    <div class="stats">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-people-fill"></i>

            </div>

            <div class="stat-info">

                <span>Total Customers</span>

                <strong>
                    <?= number_format($total_customers) ?>
                </strong>

            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-person-plus-fill"></i>

            </div>

            <div class="stat-info">

                <span>New Customers · Last 30 Days</span>

                <strong>
                    <?= number_format($new_customers) ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =================================================
         CUSTOMER TABLE
    ================================================== -->

    <div class="content-card">

        <div class="top-bar">

            <h3>

                <i class="bi bi-person-lines-fill"></i>

                Registered Customers

            </h3>


            <form
                method="GET"
                action="customers.php"
                class="search-form"
            >

                <input
                    type="text"
                    name="search"
                    class="search-box"
                    placeholder="Search name or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button
                    type="submit"
                    class="search-btn"
                >

                    <i class="bi bi-search"></i>

                </button>

                <?php if ($search !== ''): ?>

                    <a
                        href="customers.php"
                        class="clear-btn"
                    >

                        <i class="bi bi-x-lg"></i>

                    </a>

                <?php endif; ?>

            </form>

        </div>


        <?php if ($customers && $customers->num_rows > 0): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Customer</th>

                            <th>Email</th>

                            <th>Joined</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($customer = $customers->fetch_assoc()): ?>

                        <?php

                            $name = $customer['name'];

                            $initial = strtoupper(
                                substr(
                                    trim($name),
                                    0,
                                    1
                                )
                            );

                        ?>

                        <tr>

                            <td>

                                <div class="customer-info">

                                    <div class="avatar">

                                        <?= htmlspecialchars($initial) ?>

                                    </div>

                                    <div>

                                        <div class="customer-name">

                                            <?= htmlspecialchars($name) ?>

                                        </div>

                                        <span class="customer-id">

                                            Customer #<?= (int)$customer['id'] ?>

                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <span class="email">

                                    <?= htmlspecialchars($customer['email']) ?>

                                </span>

                            </td>


                            <td>

                                <span class="date">

                                    <?= date(
                                        "d M Y",
                                        strtotime($customer['created_at'])
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <a
                                    href="customer-details.php?id=<?= (int)$customer['id'] ?>"
                                    class="details-btn"
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

            <div class="empty">

                <i class="bi bi-people"></i>

                <h4>No customers found</h4>

                <p>

                    <?php if ($search !== ''): ?>

                        No customer matches
                        "<strong><?= htmlspecialchars($search) ?></strong>".

                    <?php else: ?>

                        No registered customers are available yet.

                    <?php endif; ?>

                </p>

            </div>

        <?php endif; ?>

    </div>

</main>

</body>

</html>
