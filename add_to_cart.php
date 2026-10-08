<?php

session_start();
require_once "db.php";

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first.'
    ]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);
$size = trim($_POST['size'] ?? '');

// Validate
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product.'
    ]);
    exit;
}

if ($quantity <= 0) {
    $quantity = 1;
}

// Check product exists
$product_stmt = $conn->prepare("
    SELECT id, name
    FROM products
    WHERE id = ?
    LIMIT 1
");

$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();

$product_result = $product_stmt->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found.'
    ]);
    exit;
}

$product = $product_result->fetch_assoc();


// Check if same product + same size already exists
$check_stmt = $conn->prepare("
    SELECT id, quantity
    FROM cart
    WHERE user_id = ?
      AND product_id = ?
      AND (
          size = ?
          OR (size IS NULL AND ? = '')
      )
    LIMIT 1
");

$check_stmt->bind_param(
    "iiss",
    $user_id,
    $product_id,
    $size,
    $size
);

$check_stmt->execute();

$check_result = $check_stmt->get_result();


// If already exists → increase quantity
if ($check_result->num_rows > 0) {

    $cart_item = $check_result->fetch_assoc();

    $new_quantity = (int) $cart_item['quantity'] + $quantity;

    $update_stmt = $conn->prepare("
        UPDATE cart
        SET quantity = ?
        WHERE id = ?
    ");

    $update_stmt->bind_param(
        "ii",
        $new_quantity,
        $cart_item['id']
    );

    if (!$update_stmt->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to update cart.'
        ]);
        exit;
    }

} else {

    // New product/size combination
    $insert_stmt = $conn->prepare("
        INSERT INTO cart
        (user_id, product_id, quantity, size)
        VALUES (?, ?, ?, ?)
    ");

    $insert_stmt->bind_param(
        "iiis",
        $user_id,
        $product_id,
        $quantity,
        $size
    );

    if (!$insert_stmt->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to add product to cart.'
        ]);
        exit;
    }
}


// Get updated cart count
$count_stmt = $conn->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total_items
    FROM cart
    WHERE user_id = ?
");

$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();

$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();

$cart_count = (int) $count_data['total_items'];


// Success response
echo json_encode([
    'success' => true,
    'message' => $product['name'] . ' added to your cart.',
    'cart_count' => $cart_count
]);

?>