<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "db.php";


/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);

    exit;
}


$user_id = (int) $_SESSION['user_id'];

$product_id = isset($_POST['product_id'])
    ? (int) $_POST['product_id']
    : 0;


/* =========================================
   PRODUCT ID CHECK
========================================= */

if ($product_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid product."
    ]);

    exit;
}


/* =========================================
   CHECK PRODUCT EXISTS
========================================= */

$product_stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM products WHERE id = ? LIMIT 1"
);

if (!$product_stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Product query failed."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $product_stmt,
    "i",
    $product_id
);

mysqli_stmt_execute($product_stmt);

$product_result =
    mysqli_stmt_get_result($product_stmt);

$product_exists =
    $product_result &&
    mysqli_num_rows($product_result) > 0;

mysqli_stmt_close($product_stmt);


if (!$product_exists) {

    echo json_encode([
        "success" => false,
        "message" => "Product not found."
    ]);

    exit;
}


/* =========================================
   CHECK WISHLIST
========================================= */

$check_stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM wishlist
     WHERE user_id = ?
     AND product_id = ?
     LIMIT 1"
);

if (!$check_stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Wishlist query failed."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $user_id,
    $product_id
);

mysqli_stmt_execute($check_stmt);

$check_result =
    mysqli_stmt_get_result($check_stmt);


$wishlist_exists =
    $check_result &&
    mysqli_num_rows($check_result) > 0;


$wishlist_row = null;

if ($wishlist_exists) {
    $wishlist_row =
        mysqli_fetch_assoc($check_result);
}

mysqli_stmt_close($check_stmt);


/* =========================================
   REMOVE FROM WISHLIST
========================================= */

if ($wishlist_exists) {

    $delete_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );

    if (!$delete_stmt) {

        echo json_encode([
            "success" => false,
            "message" => "Unable to remove from wishlist."
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $delete_stmt,
        "ii",
        $user_id,
        $product_id
    );

    $deleted =
        mysqli_stmt_execute($delete_stmt);

    mysqli_stmt_close($delete_stmt);


    if (!$deleted) {

        echo json_encode([
            "success" => false,
            "message" => "Unable to remove product."
        ]);

        exit;
    }


    /* COUNT WISHLIST */

    $count_result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM wishlist
         WHERE user_id = " . $user_id
    );

    $wishlist_count = 0;

    if ($count_result) {

        $count_row =
            mysqli_fetch_assoc($count_result);

        $wishlist_count =
            (int) $count_row['total'];
    }


    echo json_encode([
        "success" => true,
        "action" => "removed",
        "wishlist_count" => $wishlist_count,
        "message" => "Removed from wishlist."
    ]);

    exit;
}


/* =========================================
   ADD TO WISHLIST
========================================= */

$insert_stmt = mysqli_prepare(
    $conn,
    "INSERT INTO wishlist
        (user_id, product_id, created_at)
     VALUES
        (?, ?, NOW())"
);

if (!$insert_stmt) {

    echo json_encode([
        "success" => false,
        "message" => "Unable to add to wishlist."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $insert_stmt,
    "ii",
    $user_id,
    $product_id
);

$inserted =
    mysqli_stmt_execute($insert_stmt);

mysqli_stmt_close($insert_stmt);


if (!$inserted) {

    echo json_encode([
        "success" => false,
        "message" => "Unable to add product to wishlist."
    ]);

    exit;
}


/* =========================================
   COUNT WISHLIST
========================================= */

$count_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM wishlist
     WHERE user_id = " . $user_id
);

$wishlist_count = 0;

if ($count_result) {

    $count_row =
        mysqli_fetch_assoc($count_result);

    $wishlist_count =
        (int) $count_row['total'];
}


/* =========================================
   SUCCESS
========================================= */

echo json_encode([
    "success" => true,
    "action" => "added",
    "wishlist_count" => $wishlist_count,
    "message" => "Added to wishlist."
]);

exit;
?>