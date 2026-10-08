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
// GET USER ORDERS
// =====================================================

$sql = "
    SELECT
        id,
        order_number,
        customer_name,
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

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Unable to load your orders.");
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>My Orders | ÉLORIA</title>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
      rel="stylesheet">

<link rel="stylesheet" href="assets/css/home.css">
<style>

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Poppins", Arial, sans-serif;

        background:
            linear-gradient(
                135deg,
                #fff8fb,
                #f7eef5,
                #eee7f1
            );

        color: #3d2c39;
    }

    /* =========================================
       PAGE
    ========================================= */

    .orders-wrapper {
        max-width: 1150px;
        margin: auto;
        padding: 60px 20px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 45px;
    }

    .page-header h1 {
        font-size: 40px;
        font-weight: 700;
        color: #4d3547;
        margin-bottom: 10px;
    }

    .page-header p {
        color: #8a7482;
        margin: 0;
    }

    /* =========================================
       ORDER CARD
    ========================================= */

    .order-card {
        background: rgba(255,255,255,0.94);
        border-radius: 22px;
        padding: 25px;
        margin-bottom: 20px;

        border: 1px solid #eee2e9;

        box-shadow:
            0 12px 35px rgba(77,53,71,0.08);

        transition: 0.3s ease;
    }

    .order-card:hover {
        transform: translateY(-3px);

        box-shadow:
            0 18px 40px rgba(77,53,71,0.12);
    }

    /* =========================================
       TOP
    ========================================= */

    .order-top {
        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 20px;

        padding-bottom: 18px;
        border-bottom: 1px solid #eee4ea;
    }

    .order-number {
        font-size: 18px;
        font-weight: 700;
        color: #583f51;
    }

    .order-date {
        font-size: 13px;
        color: #988492;
        margin-top: 5px;
    }

    /* =========================================
       STATUS
    ========================================= */

    .status {
        display: inline-block;
        padding: 7px 14px;
        border-radius: 30px;

        font-size: 12px;
        font-weight: 600;
    }

    .status-pending {
        background: #fff4d8;
        color: #916f1f;
    }

    .status-processing {
        background: #e8f0ff;
        color: #4568a5;
    }

    .status-shipped {
        background: #e9f5ff;
        color: #28709c;
    }

    .status-delivered {
        background: #e4f7ed;
        color: #337653;
    }

    .status-cancelled {
        background: #fde8e8;
        color: #a14b4b;
    }

    /* =========================================
       DETAILS
    ========================================= */

    .order-details {
        display: grid;

        grid-template-columns:
            repeat(4, 1fr);

        gap: 15px;

        padding: 22px 0;
    }

    .detail-box {
        background: #fbf7f9;
        border-radius: 14px;
        padding: 16px;
    }

    .detail-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;

        color: #9b8492;

        margin-bottom: 7px;
    }

    .detail-value {
        font-size: 15px;
        font-weight: 600;
        color: #50394a;
    }

    /* =========================================
       BOTTOM
    ========================================= */

    .order-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 20px;

        padding-top: 18px;
        border-top: 1px solid #eee4ea;
    }

    .total-label {
        font-size: 13px;
        color: #907c88;
    }

    .total-price {
        font-size: 22px;
        font-weight: 700;
        color: #573d50;
    }

    /* =========================================
       BUTTON
    ========================================= */

    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        text-decoration: none;

        padding: 11px 20px;

        border-radius: 30px;

        background:
            linear-gradient(
                135deg,
                #8d5c79,
                #604159
            );

        color: white;

        font-size: 13px;
        font-weight: 600;

        transition: 0.3s ease;
    }

    .view-btn:hover {
        color: white;
        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(96,65,89,0.25);
    }

    /* =========================================
       EMPTY ORDERS
    ========================================= */

    .empty-orders {
        text-align: center;

        background: rgba(255,255,255,0.9);

        border-radius: 25px;

        padding: 70px 25px;

        box-shadow:
            0 15px 40px rgba(77,53,71,0.08);
    }

    .empty-icon {
        width: 80px;
        height: 80px;

        margin: 0 auto 20px;

        border-radius: 50%;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #f4e8ef;

        color: #78556d;

        font-size: 30px;
    }

    .empty-orders h2 {
        font-size: 25px;
        color: #543b4d;
        margin-bottom: 10px;
    }

    .empty-orders p {
        color: #95818d;
        margin-bottom: 25px;
    }

    .shop-btn {
        display: inline-block;

        padding: 12px 25px;

        border-radius: 30px;

        text-decoration: none;

        background:
            linear-gradient(
                135deg,
                #8d5c79,
                #604159
            );

        color: white;

        font-weight: 600;
    }

    .shop-btn:hover {
        color: white;
    }

    /* =========================================
       RESPONSIVE
    ========================================= */

    @media (max-width: 850px) {

        .order-details {
            grid-template-columns:
                repeat(2, 1fr);
        }

    }

    @media (max-width: 600px) {

        .orders-wrapper {
            padding: 40px 15px;
        }

        .page-header h1 {
            font-size: 31px;
        }

        .order-top {
            align-items: flex-start;
            flex-direction: column;
        }

        .order-details {
            grid-template-columns: 1fr;
        }

        .order-bottom {
            align-items: flex-start;
            flex-direction: column;
        }

        .view-btn {
            width: 100%;
            justify-content: center;
        }

    }

</style>


</head>

<body>

<?php include "includes/navbar.php"; ?>

<div class="orders-wrapper">


<!-- =========================================
     HEADER
========================================== -->

<div class="page-header">

    <h1>
        <i class="fa-solid fa-box-open"></i>
        My Orders
    </h1>

    <p>
        Track and view all your ÉLORIA orders.
    </p>

</div>


<?php if ($result->num_rows > 0): ?>


    <!-- =====================================
         ORDERS
    ====================================== -->

    <?php while ($order = $result->fetch_assoc()): ?>


        <?php

        $status =
            strtolower(
                trim($order['order_status'])
            );

        $status_class =
            'status-' . $status;

        ?>


        <div class="order-card">


            <!-- TOP -->

            <div class="order-top">

                <div>

                    <div class="order-number">

                        <i class="fa-solid fa-receipt"></i>

                        <?= htmlspecialchars(
                            $order['order_number']
                        ) ?>

                    </div>

                    <div class="order-date">

                        <i class="fa-regular fa-calendar"></i>

                        <?= date(
                            "d M Y, h:i A",
                            strtotime(
                                $order['created_at']
                            )
                        ) ?>

                    </div>

                </div>


                <div>

                    <span class="status
                        <?= htmlspecialchars(
                            $status_class
                        ) ?>">

                        <?= htmlspecialchars(
                            $order['order_status']
                        ) ?>

                    </span>

                </div>

            </div>


            <!-- DETAILS -->

            <div class="order-details">


                <div class="detail-box">

                    <div class="detail-label">
                        Payment
                    </div>

                    <div class="detail-value">

                        <?= htmlspecialchars(
                            $order['payment_method']
                        ) ?>

                    </div>

                </div>


                <div class="detail-box">

                    <div class="detail-label">
                        Payment Status
                    </div>

                    <div class="detail-value">

                        <?= htmlspecialchars(
                            $order['payment_status']
                        ) ?>

                    </div>

                </div>


                <div class="detail-box">

                    <div class="detail-label">
                        Subtotal
                    </div>

                    <div class="detail-value">

                        Rs.
                        <?= number_format(
                            $order['subtotal'],
                            2
                        ) ?>

                    </div>

                </div>


                <div class="detail-box">

                    <div class="detail-label">
                        Delivery
                    </div>

                    <div class="detail-value">

                        <?php if (
                            (float)
                            $order['delivery_charges']
                            == 0
                        ): ?>

                            FREE

                        <?php else: ?>

                            Rs.
                            <?= number_format(
                                $order['delivery_charges'],
                                2
                            ) ?>

                        <?php endif; ?>

                    </div>

                </div>


            </div>


            <!-- BOTTOM -->

            <div class="order-bottom">


                <div>

                    <div class="total-label">
                        Order Total
                    </div>

                    <div class="total-price">

                        Rs.
                        <?= number_format(
                            $order['total'],
                            2
                        ) ?>

                    </div>

                </div>


                <a
                    href="order_success.php?order=<?= urlencode(
                        $order['order_number']
                    ) ?>"
                    class="view-btn"
                >

                    View Details

                    <i class="fa-solid fa-arrow-right"></i>

                </a>


            </div>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <!-- =====================================
         EMPTY
    ====================================== -->

    <div class="empty-orders">

        <div class="empty-icon">

            <i class="fa-solid fa-bag-shopping"></i>

        </div>

        <h2>
            No Orders Yet
        </h2>

        <p>
            You haven't placed any orders with ÉLORIA yet.
        </p>

        <a
            href="shop.php"
            class="shop-btn"
        >

            <i class="fa-solid fa-bag-shopping"></i>
            Explore Collection

        </a>

    </div>


<?php endif; ?>


</div>
<?php include "includes/footer.php"; ?>

<script src="assets/js/home.js"></script>

</body>

</html>

<?php

$stmt->close();

?>
