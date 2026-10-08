<?php
session_start();
require_once "db.php";

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

/* =========================
   REMOVE FROM WISHLIST
========================= */
if (isset($_GET['remove'])) {

    $product_id = (int) $_GET['remove'];

    if ($product_id > 0) {

        $remove_sql = "
            DELETE FROM wishlist
            WHERE user_id = ? AND product_id = ?
        ";

        $remove_stmt = $conn->prepare($remove_sql);

        if ($remove_stmt) {
            $remove_stmt->bind_param("ii", $user_id, $product_id);
            $remove_stmt->execute();
            $remove_stmt->close();
        }
    }

    header("Location: wishlist.php");
    exit;
}

/* =========================
   ADD TO CART
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $product_id = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if ($product_id > 0) {

        if ($quantity < 1) {
            $quantity = 1;
        }

        /* Check whether product already exists in cart */
        $check_cart_sql = "
            SELECT id, quantity
            FROM cart
            WHERE user_id = ? AND product_id = ?
            LIMIT 1
        ";

        $check_cart_stmt = $conn->prepare($check_cart_sql);

        if ($check_cart_stmt) {

            $check_cart_stmt->bind_param(
                "ii",
                $user_id,
                $product_id
            );

            $check_cart_stmt->execute();

            $cart_result = $check_cart_stmt->get_result();

            if ($cart_result->num_rows > 0) {

                $cart_row = $cart_result->fetch_assoc();

                $new_quantity =
                    (int) $cart_row['quantity'] + $quantity;

                $update_cart_sql = "
                    UPDATE cart
                    SET quantity = ?
                    WHERE id = ?
                ";

                $update_cart_stmt =
                    $conn->prepare($update_cart_sql);

                if ($update_cart_stmt) {

                    $update_cart_stmt->bind_param(
                        "ii",
                        $new_quantity,
                        $cart_row['id']
                    );

                    $update_cart_stmt->execute();
                    $update_cart_stmt->close();
                }

            } else {

                /*
                 * Size is kept NULL here because
                 * wishlist does not select a size.
                 */
                $insert_cart_sql = "
                    INSERT INTO cart
                    (user_id, product_id, quantity)
                    VALUES (?, ?, ?)
                ";

                $insert_cart_stmt =
                    $conn->prepare($insert_cart_sql);

                if ($insert_cart_stmt) {

                    $insert_cart_stmt->bind_param(
                        "iii",
                        $user_id,
                        $product_id,
                        $quantity
                    );

                    $insert_cart_stmt->execute();
                    $insert_cart_stmt->close();
                }
            }

            $check_cart_stmt->close();
        }
    }

    /*
     * Optional remove after adding to cart.
     * This keeps the wishlist clean.
     */
    if ($product_id > 0) {

        $remove_after_cart_sql = "
            DELETE FROM wishlist
            WHERE user_id = ? AND product_id = ?
        ";

        $remove_after_cart_stmt =
            $conn->prepare($remove_after_cart_sql);

        if ($remove_after_cart_stmt) {

            $remove_after_cart_stmt->bind_param(
                "ii",
                $user_id,
                $product_id
            );

            $remove_after_cart_stmt->execute();
            $remove_after_cart_stmt->close();
        }
    }

    header("Location: wishlist.php?added=1");
    exit;
}

/* =========================
   GET WISHLIST PRODUCTS
========================= */

$wishlist_sql = "
    SELECT
        w.id AS wishlist_id,
        w.product_id,
        p.name,
        p.slug,
        p.description,
        p.price,
        p.old_price,
        p.image,
        p.stock,
        p.status,
        c.name AS category_name
    FROM wishlist w
    INNER JOIN products p
        ON w.product_id = p.id
    LEFT JOIN categories c
        ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";

$wishlist_stmt = $conn->prepare($wishlist_sql);

if (!$wishlist_stmt) {
    die("Wishlist query error: " . $conn->error);
}

$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();

$wishlist_result = $wishlist_stmt->get_result();

$wishlist_items = [];

while ($row = $wishlist_result->fetch_assoc()) {
    $wishlist_items[] = $row;
}

$wishlist_stmt->close();


/* =========================
   IMAGE HELPER
========================= */

function getWishlistImage($product)
{
    $image = trim($product['image'] ?? '');
    $slug = trim($product['slug'] ?? '');
    $name = trim($product['name'] ?? '');

    /*
     * 1. Database image exists
     */
    if (!empty($image)) {

        /*
         * Online image
         */
        if (
            filter_var(
                $image,
                FILTER_VALIDATE_URL
            )
        ) {
            return $image;
        }

        /*
         * Already contains product image path
         */
        if (
            str_starts_with(
                $image,
                'assets/images/products/'
            )
        ) {
            return $image;
        }

        /*
         * Local filename
         */
        return 'assets/images/products/' . $image;
    }

    /*
     * 2. Try local slug image
     */
    if (!empty($slug)) {

        $extensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];

        foreach ($extensions as $extension) {

            $local_file =
                __DIR__ .
                '/assets/images/products/' .
                $slug .
                '.' .
                $extension;

            if (file_exists($local_file)) {

                return
                    'assets/images/products/' .
                    $slug .
                    '.' .
                    $extension;
            }
        }
    }

    /*
     * 3. Online fallback
     *
     * Product name is used so the image
     * remains related to the product.
     */
    $search_text = strtolower($name);

    if (
        str_contains($search_text, 'pearl') ||
        str_contains($search_text, 'jewelry') ||
        str_contains($search_text, 'charm')
    ) {

        $keyword = 'luxury jewelry';

    } elseif (
        str_contains($search_text, 'blush') ||
        str_contains($search_text, 'rose') ||
        str_contains($search_text, 'bloom') ||
        str_contains($search_text, 'petal')
    ) {

        $keyword = 'pink beauty';

    } elseif (
        str_contains($search_text, 'noir') ||
        str_contains($search_text, 'black') ||
        str_contains($search_text, 'midnight')
    ) {

        $keyword = 'black luxury perfume';

    } else {

        $keyword = 'luxury beauty products';
    }

    return
        'https://loremflickr.com/700/850/' .
        urlencode($keyword) .
        '?lock=' .
        abs(crc32($slug . $name));
}


/* =========================
   SUCCESS MESSAGE
========================= */

$added_message = isset($_GET['added']) && $_GET['added'] == '1';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ÉLORIA | My Wishlist</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    <!-- Home CSS for SAME navbar -->
    <link
        rel="stylesheet"
        href="assets/css/home.css"
    >

    <style>

        /* =========================
           WISHLIST PAGE
        ========================= */

        .wishlist-page {
            background:
                linear-gradient(
                    135deg,
                    #fffafa 0%,
                    #fdf5f7 45%,
                    #f7eff4 100%
                );

            min-height: 100vh;

            padding: 70px 6% 100px;

            font-family: 'Montserrat', sans-serif;

            color: #3b2933;
        }


        .wishlist-container {
            max-width: 1250px;

            margin: 0 auto;
        }


        /* =========================
           TITLE
        ========================= */

        .wishlist-heading {
            text-align: center;
            padding:30px;

            margin-bottom: 55px;
        }


        .wishlist-heading .small-title {
            display: block;
 padding:10px;
            font-size: 12px;

            letter-spacing: 4px;

            text-transform: uppercase;

            color: #a47b89;

            margin-bottom: 40px;
        }


        .wishlist-heading h1 {
            font-family: 'Cormorant Garamond', serif;

            font-size: 52px;

            font-weight: 600;

            margin: 0;

            color: #4b303c;
        }


        .wishlist-heading p {
            margin-top: 12px;

            color: #8b707a;

            font-size: 14px;
        }


        /* =========================
           SUCCESS MESSAGE
        ========================= */

        .wishlist-success {
            max-width: 600px;

            margin: 0 auto 30px;

            padding: 14px 20px;

            border-radius: 14px;

            background: #f5e6ec;

            color: #754d5c;

            text-align: center;

            font-size: 13px;

            animation: wishlistFade 0.5s ease;
        }


        @keyframes wishlistFade {

            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }


        /* =========================
           GRID
        ========================= */

        .wishlist-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 30px;
        }


        /* =========================
           CARD
        ========================= */

        .wishlist-card {

            position: relative;

            background: rgba(255,255,255,0.78);

            border: 1px solid rgba(185,145,160,0.18);

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 15px 40px rgba(94,54,70,0.08);

            transition:
                transform 0.35s ease,
                box-shadow 0.35s ease;

            backdrop-filter: blur(10px);
        }


        .wishlist-card:hover {

            transform: translateY(-8px);

            box-shadow:
                0 22px 50px rgba(94,54,70,0.14);
        }


        /* =========================
           IMAGE
        ========================= */

        .wishlist-image {

            width: 100%;

            height: 330px;

            background: #f7eef1;

            overflow: hidden;

            position: relative;
        }


        .wishlist-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

            transition:
                transform 0.5s ease;
        }


        .wishlist-card:hover
        .wishlist-image img {

            transform: scale(1.05);
        }


        .wishlist-image-fallback {

            display: none;

            width: 100%;

            height: 100%;

            align-items: center;

            justify-content: center;

            flex-direction: column;

            text-align: center;

            background:
                linear-gradient(
                    135deg,
                    #f7e8ee,
                    #efe0e8
                );

            color: #9c7584;
        }


        .wishlist-image-fallback span {

            font-family:
                'Cormorant Garamond',
                serif;

            font-size: 32px;

            font-weight: 600;
        }


        .wishlist-image-fallback small {

            margin-top: 5px;

            font-size: 10px;

            letter-spacing: 2px;

            text-transform: uppercase;
        }


        .wishlist-image.image-error
        .wishlist-image-fallback {

            display: flex;
        }


        .wishlist-image.image-error img {

            display: none;
        }


        /* =========================
           REMOVE BUTTON
        ========================= */

        .wishlist-remove {

            position: absolute;

            top: 15px;

            right: 15px;

            width: 38px;

            height: 38px;

            border-radius: 50%;

            background: rgba(255,255,255,0.92);

            border: 1px solid rgba(120,80,95,0.12);

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            color: #8d5368;

            font-size: 18px;

            box-shadow:
                0 5px 15px rgba(0,0,0,0.08);

            transition: all 0.3s ease;

            z-index: 2;
        }


        .wishlist-remove:hover {

            background: #8d5368;

            color: white;

            transform: rotate(10deg);
        }


        /* =========================
           CARD CONTENT
        ========================= */

        .wishlist-content {

            padding: 24px 23px 25px;
        }


        .wishlist-category {

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: 2px;

            color: #b08795;

            margin-bottom: 8px;
        }


        .wishlist-name {

            font-family:
                'Cormorant Garamond',
                serif;

            font-size: 28px;

            font-weight: 600;

            color: #4b303c;

            margin: 0 0 8px;
        }


        .wishlist-description {

            font-size: 12px;

            line-height: 1.7;

            color: #8b737c;

            min-height: 40px;

            margin-bottom: 15px;

            display: -webkit-box;

            -webkit-line-clamp: 2;

            -webkit-box-orient: vertical;

            overflow: hidden;
        }


        /* =========================
           PRICE
        ========================= */

        .wishlist-price {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 18px;
        }


        .wishlist-current-price {

            font-size: 18px;

            font-weight: 600;

            color: #704556;
        }


        .wishlist-old-price {

            font-size: 13px;

            color: #b6a0a8;

            text-decoration: line-through;
        }


        /* =========================
           ACTIONS
        ========================= */

        .wishlist-actions {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 10px;
        }


        .wishlist-view {

            display: flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            border: 1px solid #b991a1;

            color: #704556;

            border-radius: 12px;

            min-height: 44px;

            font-size: 11px;

            letter-spacing: 0.5px;

            transition: all 0.3s ease;
        }


        .wishlist-view:hover {

            background: #f5e6ec;

            color: #563443;
        }


        .wishlist-cart {

            border: none;

            background:
                linear-gradient(
                    135deg,
                    #765063,
                    #a56f82
                );

            color: white;

            border-radius: 12px;

            min-height: 44px;

            font-family: 'Montserrat', sans-serif;

            font-size: 11px;

            cursor: pointer;

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }


        .wishlist-cart:hover {

            transform: translateY(-2px);

            box-shadow:
                0 8px 20px rgba(118,80,99,0.25);
        }


        /* =========================
           OUT OF STOCK
        ========================= */

        .wishlist-out-stock {

            width: 100%;

            padding: 13px;

            text-align: center;

            border-radius: 12px;

            background: #f1ebed;

            color: #9a7d87;

            font-size: 11px;

            letter-spacing: 0.5px;
        }


        /* =========================
           EMPTY WISHLIST
        ========================= */

        .wishlist-empty {

            max-width: 650px;

            margin: 40px auto 20px;

            text-align: center;

            padding: 70px 30px;

            background:
                rgba(255,255,255,0.72);

            border:
                1px solid rgba(185,145,160,0.18);

            border-radius: 28px;

            box-shadow:
                0 15px 40px rgba(94,54,70,0.07);
        }


        .wishlist-empty-icon {

            font-size: 55px;

            margin-bottom: 15px;

            opacity: 0.75;
        }


        .wishlist-empty h2 {

            font-family:
                'Cormorant Garamond',
                serif;

            font-size: 38px;

            color: #4b303c;

            margin: 0 0 10px;
        }


        .wishlist-empty p {

            color: #8b707a;

            font-size: 13px;

            margin-bottom: 25px;
        }


        .wishlist-shop-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-decoration: none;

            background:
                linear-gradient(
                    135deg,
                    #765063,
                    #a56f82
                );

            color: white;

            padding: 13px 28px;

            border-radius: 12px;

            font-size: 11px;

            letter-spacing: 1px;

            transition: all 0.3s ease;
        }


        .wishlist-shop-btn:hover {

            color: white;

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(118,80,99,0.25);
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .wishlist-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 650px) {

            .wishlist-page {

                padding:
                    45px 18px 70px;
            }


            .wishlist-heading h1 {

                font-size: 42px;
            }


            .wishlist-grid {

                grid-template-columns: 1fr;

                gap: 22px;
            }


            .wishlist-image {

                height: 360px;
            }


            .wishlist-content {

                padding: 22px;
            }

        }

    </style>

</head>


<body>

<?php include "includes/navbar.php"; ?>


<main class="wishlist-page">

    <div class="wishlist-container">


        <!-- =========================
             HEADING
        ========================= -->

        <div class="wishlist-heading">

            <span class="small-title">
                ÉLORIA COLLECTION
            </span>

            <h1>
                My Wishlist
            </h1>

            <p>
                Pieces you've saved for a little later.
            </p>

        </div>


        <!-- =========================
             SUCCESS MESSAGE
        ========================= -->

        <?php if ($added_message): ?>

            <div class="wishlist-success">

                ✨ Product added to your cart successfully.

            </div>

        <?php endif; ?>


        <?php if (count($wishlist_items) > 0): ?>


            <!-- =========================
                 WISHLIST GRID
            ========================= -->

            <div class="wishlist-grid">


                <?php foreach ($wishlist_items as $product): ?>

                    <?php

                    $product_image =
                        getWishlistImage($product);

                    $is_stock_available =
                        ((int) $product['stock'] > 0)
                        &&
                        ((int) $product['status'] === 1);

                    ?>


                    <article class="wishlist-card">


                        <!-- IMAGE -->

                        <div
                            class="wishlist-image"
                            id="wishlist-image-<?=
                                (int) $product['product_id'];
                            ?>"
                        >

                            <img
                                src="<?= htmlspecialchars($product_image); ?>"
                                alt="<?= htmlspecialchars($product['name']); ?>"
                                loading="lazy"
                                onerror="
                                    this.style.display='none';
                                    this.parentElement.classList.add('image-error');
                                "
                            >

                            <div class="wishlist-image-fallback">

                                <span>
                                    ÉLORIA
                                </span>

                                <small>
                                    Image unavailable
                                </small>

                            </div>


                            <!-- REMOVE -->

                            <a
                                href="wishlist.php?remove=<?= (int) $product['product_id']; ?>"
                                class="wishlist-remove"
                                title="Remove from wishlist"
                                onclick="
                                    return confirm(
                                        'Remove this product from your wishlist?'
                                    );
                                "
                            >
                                ♡
                            </a>

                        </div>


                        <!-- CONTENT -->

                        <div class="wishlist-content">


                            <?php if (!empty($product['category_name'])): ?>

                                <div class="wishlist-category">

                                    <?= htmlspecialchars(
                                        $product['category_name']
                                    ); ?>

                                </div>

                            <?php endif; ?>


                            <h2 class="wishlist-name">

                                <?= htmlspecialchars(
                                    $product['name']
                                ); ?>

                            </h2>


                            <?php if (!empty($product['description'])): ?>

                                <p class="wishlist-description">

                                    <?= htmlspecialchars(
                                        $product['description']
                                    ); ?>

                                </p>

                            <?php else: ?>

                                <p class="wishlist-description">

                                    A beautiful ÉLORIA piece
                                    selected just for you.

                                </p>

                            <?php endif; ?>


                            <!-- PRICE -->

                            <div class="wishlist-price">

                                <span
                                    class="wishlist-current-price"
                                >

                                    Rs.
                                    <?= number_format(
                                        (float) $product['price']
                                    ); ?>

                                </span>


                                <?php
                                if (
                                    !empty($product['old_price'])
                                    &&
                                    (float)$product['old_price']
                                    > (float)$product['price']
                                ):
                                ?>

                                    <span
                                        class="wishlist-old-price"
                                    >

                                        Rs.
                                        <?= number_format(
                                            (float)
                                            $product['old_price']
                                        ); ?>

                                    </span>

                                <?php endif; ?>

                            </div>


                            <!-- ACTIONS -->

                            <?php if ($is_stock_available): ?>

                                <div class="wishlist-actions">


                                    <a
                                        href="product.php?id=<?= (int) $product['product_id']; ?>"
                                        class="wishlist-view"
                                    >
                                        View Product
                                    </a>


                                    <form
                                        method="POST"
                                        style="margin:0;"
                                    >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $product['product_id']; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="1"
                                        >

                                        <button
                                            type="submit"
                                            name="add_to_cart"
                                            class="wishlist-cart"
                                        >
                                            Add to Cart
                                        </button>

                                    </form>


                                </div>

                            <?php else: ?>

                                <div class="wishlist-out-stock">

                                    Currently unavailable

                                </div>

                            <?php endif; ?>


                        </div>

                    </article>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- =========================
                 EMPTY WISHLIST
            ========================= -->

            <div class="wishlist-empty">

                <div class="wishlist-empty-icon">
                    ♡
                </div>

                <h2>
                    Your wishlist is waiting
                </h2>

                <p>
                    You haven't saved anything yet.
                    Discover something beautiful and
                    add it to your wishlist.
                </p>

                <a
                    href="shop.php"
                    class="wishlist-shop-btn"
                >
                    Explore ÉLORIA
                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


<?php include "includes/footer.php"; ?>


<script>

    /*
     * Automatically remove success message
     * after a few seconds.
     */

    const successMessage =
        document.querySelector('.wishlist-success');

    if (successMessage) {

        setTimeout(() => {

            successMessage.style.opacity = '0';

            successMessage.style.transform =
                'translateY(-8px)';

            setTimeout(() => {

                successMessage.remove();

            }, 400);

        }, 3000);

    }

</script>


</body>

</html>