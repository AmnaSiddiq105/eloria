<?php

session_start();

require_once "../db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}


/* =====================================================
   UPDATE ORDER STATUS
===================================================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_status'])
) {

    $order_id = (int) ($_POST['order_id'] ?? 0);

    $order_status = trim(
        $_POST['order_status'] ?? ''
    );

    $allowed_statuses = [
        'Pending',
        'Processing',
        'Shipped',
        'Delivered',
        'Cancelled'
    ];

    if (
        $order_id > 0 &&
        in_array($order_status, $allowed_statuses, true)
    ) {

        $update_sql = "
            UPDATE orders
            SET order_status = ?
            WHERE id = ?
        ";

        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {

            $update_stmt->bind_param(
                "si",
                $order_status,
                $order_id
            );

            $update_stmt->execute();

            $update_stmt->close();
        }
    }

    header("Location: orders.php");
    exit;
}


/* =====================================================
   GET ORDERS
===================================================== */

$sql = "
    SELECT
        id,
        order_number,
        customer_name,
        email,
        phone,
        city,
        subtotal,
        delivery_charges,
        total,
        payment_method,
        payment_status,
        order_status,
        created_at
    FROM orders
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load orders.");
}


/* =====================================================
   STATUS CLASS
===================================================== */

function statusClass($status)
{
    return match (strtolower(trim($status))) {

        'pending' =>
            'status-pending',

        'processing' =>
            'status-processing',

        'shipped' =>
            'status-shipped',

        'delivered' =>
            'status-delivered',

        'cancelled' =>
            'status-cancelled',

        default =>
            'status-pending'
    };
}


/* =====================================================
   STATISTICS
===================================================== */

$total_orders = $result->num_rows;

$pending = 0;
$processing = 0;
$delivered = 0;

$result->data_seek(0);

while ($stat = $result->fetch_assoc()) {

    $current = strtolower(
        trim($stat['order_status'])
    );

    if ($current === 'pending') {
        $pending++;
    }

    if ($current === 'processing') {
        $processing++;
    }

    if ($current === 'delivered') {
        $delivered++;
    }
}

$result->data_seek(0);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders | ÉLORIA Admin</title>


<!-- ÉLORIA FONTS -->

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
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<link rel="stylesheet" href="assets/admin-sidebar.css">
<style>

/* =========================================================
   ÉLORIA ORDERS PAGE
   SAME FEEL AS HOME PAGE
========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: "Montserrat", sans-serif;

    color: #282127;

    background:
        radial-gradient(
            circle at 5% 5%,
            rgba(244, 218, 234, .42),
            transparent 30%
        ),
        radial-gradient(
            circle at 95% 90%,
            rgba(225, 218, 241, .38),
            transparent 32%
        ),
        linear-gradient(
            135deg,
            #fffdfd 0%,
            #fbf7fa 50%,
            #f6f4fa 100%
        );

    min-height: 100vh;
}


/* =========================================================
   PAGE
========================================================= */

.page {
    /*
     * The sidebar is fixed on the left.
     * Keep the Orders content completely outside
     * the sidebar area instead of letting it sit behind it.
     */
    width: calc(100% - 390px);
    max-width: none;

    margin: 0 0 0 390px;

    padding: 45px 40px 70px;

    box-sizing: border-box;
}

/* =========================================================
   SIDEBAR + CONTENT LAYOUT FIX
========================================================= */

/* The shared sidebar is fixed, so the page must reserve its space. */
.sidebar {
    z-index: 1000;
}

/* Prevent the Orders table from forcing the whole page underneath
   or behind the fixed sidebar. */
.orders-box {
    width: 100%;
}

.table-wrapper {
    max-width: 100%;
    overflow-x: auto;
}

/* Keep the four statistic cards inside the available content area. */
.stats {
    width: 100%;
}

/* =========================================================
   RESPONSIVE SIDEBAR LAYOUT
========================================================= */

@media (max-width: 1200px) {
    .page {
        width: calc(100% - 350px);
        margin-left: 350px;
        padding: 35px 25px 60px;
    }

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 850px) {
    .page {
        width: 100%;
        margin-left: 0;
        padding: 25px 15px 50px;
    }
}



/* =========================================================
   HEADER
========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 35px;
}

.page-header h1 {

    margin: 0;

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 46px;

    font-weight: 500;

    letter-spacing: .5px;

    color: #241d23;
}

.page-header h1 i {

    font-size: 27px;

    color: #9a7187;

    margin-right: 8px;
}

.page-header p {

    margin: 5px 0 0;

    color: #9a8b94;

    font-size: 10px;

    letter-spacing: .3px;
}


/* =========================================================
   DASHBOARD BUTTON
========================================================= */

.dashboard-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 19px;

    border-radius: 30px;

    background:
        linear-gradient(
            135deg,
            #f8edf3,
            #f1edf7
        );

    color: #674f5d;

    border: 1px solid #eadde5;

    text-decoration: none;

    font-size: 10px;

    font-weight: 600;

    transition: all .3s ease;
}

.dashboard-btn:hover {

    color: #33272e;

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(80,55,70,.08);
}


/* =========================================================
   STATS
========================================================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}


.stat-card {

    position: relative;

    overflow: hidden;

    padding: 24px;

    background:
        rgba(255,255,255,.78);

    border:
        1px solid
        rgba(80,55,70,.07);

    border-radius: 20px;

    box-shadow:
        0 13px 35px
        rgba(65,45,55,.055);

    transition:
        transform .3s ease,
        box-shadow .3s ease;
}

.stat-card::after {

    content: "";

    position: absolute;

    width: 90px;
    height: 90px;

    right: -45px;
    top: -45px;

    border-radius: 50%;

    background:
        rgba(224,198,216,.18);
}

.stat-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 18px 40px
        rgba(65,45,55,.09);
}


.stat-icon {

    width: 43px;
    height: 43px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #f8eaf1,
            #f1edf7
        );

    color: #806174;

    margin-bottom: 15px;

    font-size: 15px;
}


.stat-number {

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 35px;

    font-weight: 600;

    line-height: 1;

    color: #30262d;

    margin-bottom: 6px;
}


.stat-label {

    color: #978991;

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: 1.1px;
}


/* =========================================================
   ORDERS CARD
========================================================= */

.orders-box {

    background:
        rgba(255,255,255,.80);

    border:
        1px solid
        rgba(70,45,60,.07);

    border-radius: 22px;

    box-shadow:
        0 15px 40px
        rgba(65,45,55,.06);

    overflow: hidden;
}


/* =========================================================
   TABLE WRAPPER
========================================================= */

.table-wrapper {

    width: 100%;

    overflow-x: auto;
}


/* =========================================================
   TABLE
========================================================= */

table {

    width: 100%;

    min-width: 1100px;

    border-collapse: collapse;
}


thead {

    background:
        linear-gradient(
            135deg,
            #faf2f6,
            #f7f3f9
        );
}


th {

    padding: 16px 15px;

    text-align: left;

    color: #96858f;

    font-size: 8px;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: 1.1px;

    white-space: nowrap;

    border-bottom:
        1px solid #eee5ea;
}


td {

    padding: 17px 15px;

    color: #62545c;

    font-size: 10px;

    vertical-align: middle;

    border-bottom:
        1px solid #f1ebee;
}


tbody tr {

    transition:
        background .25s ease;
}

tbody tr:hover {

    background:
        rgba(249,241,246,.65);
}


tbody tr:last-child td {

    border-bottom: none;
}


/* =========================================================
   ORDER NUMBER
========================================================= */

.order-number {

    color: #5c4050;

    font-weight: 600;

    font-size: 10px;

    margin-bottom: 4px;
}

td small {

    color: #aa9ba3;

    font-size: 8px;
}


/* =========================================================
   CUSTOMER
========================================================= */

.customer {

    color: #40333b;

    font-size: 10px;

    font-weight: 600;

    margin-bottom: 3px;
}

.email {

    color: #a0939a;

    font-size: 8px;
}


/* =========================================================
   AMOUNT
========================================================= */

.amount {

    color: #5b4050;

    font-family:
        "Cormorant Garamond",
        serif;

    font-size: 19px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-flex;

    align-items: center;

    padding: 6px 11px;

    border-radius: 30px;

    font-size: 8px;

    font-weight: 600;

    letter-spacing: .2px;

    white-space: nowrap;
}


.status-pending {

    background: #faf3e4;

    color: #9a7948;
}


.status-processing {

    background: #f0edf8;

    color: #76638c;
}


.status-shipped {

    background: #edf4f8;

    color: #58798a;
}


.status-delivered {

    background: #edf7f1;

    color: #578069;
}


.status-cancelled {

    background: #faedf1;

    color: #a36377;
}


/* =========================================================
   STATUS FORM
========================================================= */

.status-form {

    display: flex;

    align-items: center;

    gap: 6px;
}


.status-select {

    padding: 7px 9px;

    border:
        1px solid #e6dce2;

    border-radius: 9px;

    outline: none;

    background: #fff;

    color: #66535e;

    font-family:
        "Montserrat",
        sans-serif;

    font-size: 8px;

    cursor: pointer;

    transition: .25s ease;
}

.status-select:focus {

    border-color: #bda4b2;

    box-shadow:
        0 0 0 3px
        rgba(189,164,178,.10);
}


.update-btn {

    width: 31px;
    height: 31px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border: none;

    border-radius: 9px;

    background:
        linear-gradient(
            135deg,
            #806174,
            #a17d90
        );

    color: white;

    cursor: pointer;

    transition: .25s ease;
}

.update-btn:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(100,75,90,.18);
}


/* =========================================================
   VIEW BUTTON
========================================================= */

.view-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    padding: 8px 13px;

    border-radius: 30px;

    color: #6e5261;

    background:
        #faf3f7;

    border:
        1px solid #eadde5;

    text-decoration: none;

    font-size: 8px;

    font-weight: 600;

    transition: all .25s ease;
}

.view-btn:hover {

    color: #30262d;

    background:
        #f4e7ee;

    transform:
        translateY(-2px);
}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 80px 20px;

    color: #988891;
}

.empty i {

    width: 65px;
    height: 65px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin:
        0 auto 18px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #f8eaf1,
            #f1edf7
        );

    color: #987789;

    font-size: 23px;
}

.empty h3 {

    margin: 0 0 6px;

    font-family:
        "Cormorant Garamond",
        serif;

    color: #3b3036;

    font-size: 27px;

    font-weight: 500;
}

.empty p {

    margin: 0;

    font-size: 9px;

    color: #a0939a;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .page {

        padding:
            35px 25px 60px;
    }

    .stats {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 650px) {

    .page {

        padding:
            25px 15px 50px;
    }

    .page-header {

        flex-direction: column;

        align-items: flex-start;
    }

    .page-header h1 {

        font-size: 38px;
    }

    .stats {

        grid-template-columns: 1fr;
    }

    .orders-box {

        border-radius: 16px;
    }
}



</style>

</head>


<body>


<?php include "includes/sidebar.php"; ?>

<div class="page">

    <!-- =================================================
         HEADER
    ================================================== -->
    <div class="page-header">

        <div>

            <h1>

                <i class="fa-solid fa-box-open"></i>

                Orders

            </h1>

            <p>
                Manage and track all ÉLORIA customer orders.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="dashboard-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Dashboard

        </a>

    </div>


    <!-- =================================================
         STATISTICS
    ================================================== -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-box"></i>

            </div>

            <div class="stat-number">
                <?= $total_orders ?>
            </div>

            <div class="stat-label">
                Total Orders
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-regular fa-clock"></i>

            </div>

            <div class="stat-number">
                <?= $pending ?>
            </div>

            <div class="stat-label">
                Pending Orders
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-spinner"></i>

            </div>

            <div class="stat-number">
                <?= $processing ?>
            </div>

            <div class="stat-label">
                Processing
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">

                <i class="fa-solid fa-check"></i>

            </div>

            <div class="stat-number">
                <?= $delivered ?>
            </div>

            <div class="stat-label">
                Delivered
            </div>

        </div>


    </div>


    <!-- =================================================
         ORDERS
    ================================================== -->

    <div class="orders-box">

        <div class="table-wrapper">


            <?php if ($result->num_rows > 0): ?>


                <table>

                    <thead>

                        <tr>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Phone</th>

                            <th>City</th>

                            <th>Total</th>

                            <th>Payment</th>

                            <th>Status</th>

                            <th>Update</th>

                            <th>Details</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $order =
                        $result->fetch_assoc()
                    ): ?>


                        <tr>


                            <!-- ORDER -->

                            <td>

                                <div class="order-number">

                                    <?= htmlspecialchars(
                                        $order['order_number']
                                    ) ?>

                                </div>

                                <small>

                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $order['created_at']
                                        )
                                    ) ?>

                                </small>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer">

                                    <?= htmlspecialchars(
                                        $order['customer_name']
                                    ) ?>

                                </div>

                                <div class="email">

                                    <?= htmlspecialchars(
                                        $order['email']
                                    ) ?>

                                </div>

                            </td>


                            <!-- PHONE -->

                            <td>

                                <?= htmlspecialchars(
                                    $order['phone']
                                ) ?>

                            </td>


                            <!-- CITY -->

                            <td>

                                <?= htmlspecialchars(
                                    $order['city']
                                ) ?>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <div class="amount">

                                    Rs.
                                    <?= number_format(
                                        $order['total'],
                                        2
                                    ) ?>

                                </div>

                            </td>


                            <!-- PAYMENT -->

                            <td>

                                <?= htmlspecialchars(
                                    $order['payment_method']
                                ) ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status
                                    <?= statusClass(
                                        $order['order_status']
                                    ) ?>"
                                >

                                    <?= htmlspecialchars(
                                        $order['order_status']
                                    ) ?>

                                </span>

                            </td>


                            <!-- UPDATE -->

                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= (int)
                                            $order['id'] ?>"
                                    >


                                    <select
                                        name="order_status"
                                        class="status-select"
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
                                                value="<?= $status ?>"
                                                <?= $order[
                                                    'order_status'
                                                ] === $status
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                <?= $status ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>


                                    <button
                                        type="submit"
                                        name="update_status"
                                        class="update-btn"
                                        title="Update Status"
                                    >

                                        <i
                                            class="fa-solid fa-check"
                                        ></i>

                                    </button>

                                </form>

                            </td>


                            <!-- DETAILS -->

                            <td>

                                <a
                                    href="order-details.php?id=<?= (int)$order['id'] ?>"
                                    class="view-btn"
                                >

                                    <i
                                        class="fa-regular fa-eye"
                                    ></i>

                                    View

                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    <i
                        class="fa-solid fa-box-open"
                    ></i>

                    <h3>
                        No Orders Yet
                    </h3>

                    <p>
                        Customer orders will appear here.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


</body>

</html>
