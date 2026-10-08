<?php

session_start();

require_once "db.php";
require_once "includes/send_email.php";


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];


// =====================================================
// ONLY POST REQUEST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: checkout.php");
    exit;
}


// =====================================================
// GET FORM DATA
// =====================================================

$customer_name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$payment_method = trim(
    $_POST['payment_method'] ?? 'Cash on Delivery'
);


// =====================================================
// VALIDATION
// =====================================================

if (
    $customer_name === '' ||
    $email === '' ||
    $phone === '' ||
    $address === '' ||
    $city === ''
) {

    die("Please fill all required fields.");
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die("Please enter a valid email address.");
}


// =====================================================
// GET CART
// =====================================================

$cart_sql = "
    SELECT
        c.id AS cart_id,
        c.product_id,
        c.quantity,
        c.size,
        p.name,
        p.price
    FROM cart c
    INNER JOIN products p
        ON c.product_id = p.id
    WHERE c.user_id = ?
";

$cart_stmt = $conn->prepare($cart_sql);

if (!$cart_stmt) {

    die("Unable to load cart.");
}

$cart_stmt->bind_param(
    "i",
    $user_id
);

$cart_stmt->execute();

$cart_result = $cart_stmt->get_result();


// =====================================================
// EMPTY CART CHECK
// =====================================================

if ($cart_result->num_rows === 0) {

    die("Your cart is empty.");
}


// =====================================================
// CALCULATE TOTAL
// =====================================================

$cart_items = [];

$subtotal = 0;

while ($item = $cart_result->fetch_assoc()) {

    $quantity = (int) $item['quantity'];
    $price = (float) $item['price'];

    $item_total = $quantity * $price;

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


// =====================================================
// GENERATE ORDER NUMBER
// =====================================================

$order_number =
    'ELORIA-' .
    date('YmdHis') .
    '-' .
    strtoupper(
        substr(
            uniqid(),
            -5
        )
    );


// =====================================================
// START TRANSACTION
// =====================================================

$conn->begin_transaction();


try {


    // =================================================
    // INSERT ORDER
    // =================================================

    $order_sql = "
        INSERT INTO orders
        (
            user_id,
            order_number,
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
            order_status
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?
        )
    ";

    $order_stmt = $conn->prepare($order_sql);

    if (!$order_stmt) {

        throw new Exception(
            "Unable to prepare order."
        );
    }


    $payment_status = 'Pending';
    $order_status = 'Pending';


    $order_stmt->bind_param(
        "issssssddssss",
        $user_id,
        $order_number,
        $customer_name,
        $email,
        $phone,
        $address,
        $city,
        $subtotal,
        $delivery_charges,
        $total,
        $payment_method,
        $payment_status,
        $order_status
    );


    if (!$order_stmt->execute()) {

        throw new Exception(
            "Unable to create order."
        );
    }


    // =================================================
    // GET NEW ORDER ID
    // =================================================

    $order_id = $conn->insert_id;


    // =================================================
    // INSERT ORDER ITEMS
    // =================================================

    $item_sql = "
        INSERT INTO order_items
        (
            order_id,
            product_id,
            quantity,
            price
        )
        VALUES (?, ?, ?, ?)
    ";

    $item_stmt = $conn->prepare($item_sql);

    if (!$item_stmt) {

        throw new Exception(
            "Unable to prepare order items."
        );
    }


    foreach ($cart_items as $item) {

        $product_id = (int) $item['product_id'];
        $quantity = (int) $item['quantity'];
        $price = (float) $item['price'];


        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $product_id,
            $quantity,
            $price
        );


        if (!$item_stmt->execute()) {

            throw new Exception(
                "Unable to save order item."
            );
        }
    }


    // =================================================
    // CLEAR CART
    // =================================================

    $delete_cart_sql = "
        DELETE FROM cart
        WHERE user_id = ?
    ";

    $delete_cart_stmt = $conn->prepare(
        $delete_cart_sql
    );


    if (!$delete_cart_stmt) {

        throw new Exception(
            "Unable to clear cart."
        );
    }


    $delete_cart_stmt->bind_param(
        "i",
        $user_id
    );


    if (!$delete_cart_stmt->execute()) {

        throw new Exception(
            "Unable to clear cart."
        );
    }


    // =================================================
    // COMMIT ORDER
    // =================================================

    $conn->commit();


    // =================================================
    // SEND ORDER CONFIRMATION EMAIL
    // =================================================

    $subject =
        "ÉLORIA Order Confirmation - " .
        $order_number;


    // Create product rows for email
    $items_html = '';

    foreach ($cart_items as $item) {

        $product_name =
            htmlspecialchars(
                $item['name'],
                ENT_QUOTES,
                'UTF-8'
            );

        $quantity =
            (int) $item['quantity'];

        $price =
            number_format(
                (float) $item['price'],
                2
            );

        $item_total =
            number_format(
                (float) $item['item_total'],
                2
            );


        $items_html .= "
            <tr>
                <td style='
                    padding:14px 10px;
                    border-bottom:1px solid #eee;
                    color:#4b3a3a;
                '>
                    {$product_name}
                </td>

                <td style='
                    padding:14px 10px;
                    border-bottom:1px solid #eee;
                    text-align:center;
                    color:#4b3a3a;
                '>
                    {$quantity}
                </td>

                <td style='
                    padding:14px 10px;
                    border-bottom:1px solid #eee;
                    text-align:right;
                    color:#4b3a3a;
                '>
                    Rs. {$item_total}
                </td>
            </tr>
        ";
    }


    $safe_customer_name =
        htmlspecialchars(
            $customer_name,
            ENT_QUOTES,
            'UTF-8'
        );

    $safe_order_number =
        htmlspecialchars(
            $order_number,
            ENT_QUOTES,
            'UTF-8'
        );

    $safe_payment_method =
        htmlspecialchars(
            $payment_method,
            ENT_QUOTES,
            'UTF-8'
        );

    $safe_address =
        htmlspecialchars(
            $address,
            ENT_QUOTES,
            'UTF-8'
        );

    $safe_city =
        htmlspecialchars(
            $city,
            ENT_QUOTES,
            'UTF-8'
        );


    $htmlBody = "
<!DOCTYPE html>

<html>

<head>

<meta charset='UTF-8'>

<title>ÉLORIA Order Confirmation</title>

</head>

<body style='
    margin:0;
    padding:0;
    background:#f8f2f3;
    font-family:Arial, Helvetica, sans-serif;
'>

<table width='100%' cellpadding='0' cellspacing='0'>

<tr>

<td align='center' style='padding:40px 15px;'>

<table
    width='650'
    cellpadding='0'
    cellspacing='0'
    style='
        max-width:650px;
        width:100%;
        background:#ffffff;
        border-radius:20px;
        overflow:hidden;
        box-shadow:0 10px 35px rgba(80,50,60,0.10);
    '
>

<!-- HEADER -->

<tr>

<td style='
    background:linear-gradient(
        135deg,
        #ead5dc,
        #f7e9ed,
        #eee0e8
    );
    padding:35px 30px;
    text-align:center;
'>

<div style='
    font-family:Georgia, serif;
    font-size:34px;
    letter-spacing:4px;
    color:#49343c;
'>
    ÉLORIA
</div>

<div style='
    margin-top:8px;
    font-size:12px;
    letter-spacing:3px;
    color:#80636d;
'>
    A LITTLE LUXURY
</div>

</td>

</tr>


<!-- CONTENT -->

<tr>

<td style='padding:35px 35px 20px;'>

<h2 style='
    margin:0 0 12px;
    color:#49343c;
    font-family:Georgia, serif;
    font-weight:normal;
'>
    Thank you, {$safe_customer_name} ✦
</h2>

<p style='
    color:#6d5a61;
    line-height:1.7;
    font-size:15px;
'>
    Your order has been successfully placed with ÉLORIA.
    We're delighted to have you shop with us.
</p>


<!-- ORDER NUMBER -->

<div style='
    margin:25px 0;
    padding:18px;
    background:#faf3f5;
    border-radius:12px;
    text-align:center;
'>

<div style='
    font-size:11px;
    letter-spacing:2px;
    color:#967783;
    margin-bottom:8px;
'>
    ORDER NUMBER
</div>

<div style='
    font-size:17px;
    font-weight:bold;
    color:#49343c;
'>
    {$safe_order_number}
</div>

</div>


<!-- ITEMS -->

<h3 style='
    color:#49343c;
    font-family:Georgia, serif;
    font-weight:normal;
'>
    Your Items
</h3>

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    style='
        border-collapse:collapse;
        font-size:14px;
    '
>

<tr>

<th style='
    padding:12px 10px;
    background:#f8f1f3;
    text-align:left;
    color:#6d555f;
'>
    Product
</th>

<th style='
    padding:12px 10px;
    background:#f8f1f3;
    text-align:center;
    color:#6d555f;
'>
    Qty
</th>

<th style='
    padding:12px 10px;
    background:#f8f1f3;
    text-align:right;
    color:#6d555f;
'>
    Total
</th>

</tr>

{$items_html}

</table>


<!-- TOTALS -->

<table
    width='100%'
    cellpadding='0'
    cellspacing='0'
    style='margin-top:25px;'
>

<tr>

<td style='
    padding:7px 0;
    color:#777;
'>
    Subtotal
</td>

<td align='right' style='
    padding:7px 0;
    color:#49343c;
'>
    Rs. " . number_format($subtotal, 2) . "
</td>

</tr>

<tr>

<td style='
    padding:7px 0;
    color:#777;
'>
    Delivery Charges
</td>

<td align='right' style='
    padding:7px 0;
    color:#49343c;
'>
    Rs. " . number_format($delivery_charges, 2) . "
</td>

</tr>

<tr>

<td style='
    padding:15px 0;
    border-top:1px solid #eee;
    font-weight:bold;
    color:#49343c;
'>
    Total
</td>

<td align='right' style='
    padding:15px 0;
    border-top:1px solid #eee;
    font-size:18px;
    font-weight:bold;
    color:#49343c;
'>
    Rs. " . number_format($total, 2) . "
</td>

</tr>

</table>


<!-- PAYMENT -->

<div style='
    margin-top:20px;
    padding:18px;
    background:#faf7f8;
    border-radius:12px;
'>

<p style='
    margin:0 0 7px;
    color:#777;
    font-size:13px;
'>
    Payment Method
</p>

<p style='
    margin:0;
    color:#49343c;
    font-weight:bold;
'>
    {$safe_payment_method}
</p>

</div>


<!-- ADDRESS -->

<div style='
    margin-top:15px;
    padding:18px;
    background:#faf7f8;
    border-radius:12px;
'>

<p style='
    margin:0 0 7px;
    color:#777;
    font-size:13px;
'>
    Delivery Address
</p>

<p style='
    margin:0;
    color:#49343c;
    line-height:1.6;
'>
    {$safe_address}<br>
    {$safe_city}
</p>

</div>


<p style='
    margin-top:30px;
    color:#6d5a61;
    line-height:1.7;
'>
    We will contact you regarding your order and delivery.
    Thank you for choosing ÉLORIA.
</p>

</td>

</tr>


<!-- FOOTER -->

<tr>

<td style='
    padding:25px 30px;
    background:#f8f1f3;
    text-align:center;
    color:#806b73;
    font-size:12px;
    line-height:1.6;
'>

<div style='
    font-family:Georgia, serif;
    font-size:18px;
    color:#49343c;
    letter-spacing:2px;
    margin-bottom:8px;
'>
    ÉLORIA
</div>

Thank you for being part of our little luxury world ✦

</td>

</tr>

</table>

</td>

</tr>

</table>

</body>

</html>
";


    // =================================================
    // SEND EMAIL USING PHPMailer
    // =================================================

    $email_sent = sendEloriaEmail(
        $email,
        $customer_name,
        $subject,
        $htmlBody
    );


    // =================================================
    // IF EMAIL FAILED, LOG IT
    // ORDER WILL STILL REMAIN SUCCESSFUL
    // =================================================

    if (!$email_sent) {

        error_log(
            "ÉLORIA order email failed for order: " .
            $order_number .
            " | Email: " .
            $email
        );
    }


    // =================================================
    // REDIRECT TO SUCCESS PAGE
    // =================================================

    header(
        "Location: order_success.php?order=" .
        urlencode($order_number)
    );

    exit;


} catch (Throwable $e) {


    // =================================================
    // ROLLBACK
    // =================================================

    $conn->rollback();


    echo "<div style='
        font-family:Arial;
        max-width:700px;
        margin:80px auto;
        padding:30px;
        border-radius:15px;
        background:#fff5f5;
        border:1px solid #e5caca;
        color:#4a2525;
    '>";

    echo "<h2>Unable to place your order</h2>";

    echo "<p>";

    echo htmlspecialchars(
        $e->getMessage()
    );

    echo "</p>";

    echo "<a href='checkout.php'
        style='
        display:inline-block;
        margin-top:15px;
        padding:12px 20px;
        background:#5b3a3a;
        color:white;
        text-decoration:none;
        border-radius:8px;
    '>
        Back to Checkout
    </a>";

    echo "</div>";

    exit;
}

?>