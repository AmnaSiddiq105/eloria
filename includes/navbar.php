
<?php

$current_page = basename($_SERVER['PHP_SELF']);

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| User Information
|--------------------------------------------------------------------------
*/
$user_name = $_SESSION['user_name'] ?? 'User';
$user_id   = $_SESSION['user_id'] ?? 0;


/*
|--------------------------------------------------------------------------
| Cart Count
|--------------------------------------------------------------------------
*/

$cart_count = 0;

if ($user_id && isset($conn) && $conn instanceof mysqli) {

    $cartStmt = $conn->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS total_items
         FROM cart
         WHERE user_id = ?"
    );

    if ($cartStmt) {

        $cartStmt->bind_param("i", $user_id);
        $cartStmt->execute();

        $cartResult = $cartStmt->get_result();

        if ($cartResult) {

            $cartData = $cartResult->fetch_assoc();

            $cart_count = (int) ($cartData['total_items'] ?? 0);
        }

        $cartStmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Wishlist Count
|--------------------------------------------------------------------------
*/

$wishlist_count = 0;

if ($user_id && isset($conn) && $conn instanceof mysqli) {

    $wishlistStmt = $conn->prepare(
        "SELECT COUNT(*) AS total_items
         FROM wishlist
         WHERE user_id = ?"
    );

    if ($wishlistStmt) {

        $wishlistStmt->bind_param("i", $user_id);
        $wishlistStmt->execute();

        $wishlistResult = $wishlistStmt->get_result();

        if ($wishlistResult) {

            $wishlistData = $wishlistResult->fetch_assoc();

            $wishlist_count = (int) ($wishlistData['total_items'] ?? 0);
        }

        $wishlistStmt->close();
    }
}


?>

<header class="navbar">

    <div class="nav-inner">

        <!-- LOGO -->
        <a href="home.php" class="nav-logo">
            <span>É</span>LORIA
        </a>


        <!-- DESKTOP NAVIGATION -->
        <nav class="desktop-nav">

            <a href="home.php"
               class="<?= $current_page == 'home.php' ? 'active' : '' ?>">
                Home
            </a>

            <a href="shop.php"
               class="<?= $current_page == 'shop.php' ? 'active' : '' ?>">
                Shop
            </a>

            <a href="about.php"
               class="<?= $current_page == 'about.php' ? 'active' : '' ?>">
                Our Story
            </a>

            <a href="wishlist.php"
               class="<?= $current_page == 'wishlist.php' ? 'active' : '' ?>">
                Wishlist
            </a>

        </nav>


        <!-- RIGHT SIDE -->
        <div class="nav-actions">


            <!-- WISHLIST -->
      
<a href="wishlist.php"
   class="nav-icon wishlist-nav"
   title="Wishlist">

    <span class="wishlist-heart">♡</span>

<span class="wishlist-count" id="wishlistCount">
    <?= $wishlist_count ?>
</span>

</a>


            <!-- CART -->
            <a href="cart.php"
               class="nav-icon cart-icon"
               title="Cart">

                🛍

                <?php if ($cart_count > 0): ?>

                    <span class="cart-count">
                        <?= $cart_count ?>
                    </span>

                <?php endif; ?>

            </a>


            <!-- USER MENU -->
            <div class="user-menu" id="userMenu">

                <button
                    type="button"
                    class="user-button"
                    id="userButton"
                    aria-expanded="false"
                >

                    <span class="user-circle">

                        <?= htmlspecialchars(
                            strtoupper(
                                substr($user_name, 0, 1)
                            )
                        ) ?>

                    </span>

                    <span class="user-name">

                        <?= htmlspecialchars($user_name) ?>

                    </span>

                    <span class="user-arrow" id="userArrow">
                        ⌄
                    </span>

                </button>


                <!-- DROPDOWN -->
                <div
                    class="user-dropdown"
                    id="userDropdown"
                >

                    <!-- MY ACCOUNT -->
                    <a href="account.php">

                        <span>👤</span>

                        My Account

                    </a>


                    <!-- MY ORDERS -->
                    <a href="my-orders.php">

                        <span>🛍</span>

                        My Orders

                    </a>


                    <!-- SIGN OUT -->
                    <a
                        href="logout.php"
                        class="logout-link"
                    >

                        <span>↪</span>

                        Sign Out

                    </a>

                </div>

            </div>


            <!-- MOBILE BUTTON -->
            <button
                type="button"
                class="menu-toggle"
                id="menuToggle"
            >

                ☰

            </button>

        </div>

    </div>


    <!-- MOBILE MENU -->

    <div
        class="mobile-menu"
        id="mobileMenu"
    >

        <a href="home.php">
            Home
        </a>

        <a href="shop.php">
            Shop
        </a>

        <a href="about.php">
            Our Story
        </a>

        <a href="wishlist.php">
            Wishlist
        </a>

        <a href="cart.php">

            Cart

            <?php if ($cart_count > 0): ?>

                (<?= $cart_count ?>)

            <?php endif; ?>

        </a>

        <a href="account.php">
            My Account
        </a>

        <a href="my-orders.php">
            My Orders
        </a>

        <a
            href="logout.php"
            class="mobile-logout"
        >
            Sign Out
        </a>

    </div>

</header>


<script>

/*
|--------------------------------------------------------------------------
| USER DROPDOWN
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {

    const userButton =
        document.getElementById("userButton");

    const userDropdown =
        document.getElementById("userDropdown");

    const userMenu =
        document.getElementById("userMenu");


    if (userButton && userDropdown && userMenu) {

        /*
        | Toggle dropdown
        */

        userButton.addEventListener("click", function (event) {

            event.stopPropagation();

            userDropdown.classList.toggle("show");

            const isOpen =
                userDropdown.classList.contains("show");

            userButton.setAttribute(
                "aria-expanded",
                isOpen ? "true" : "false"
            );

        });


        /*
        | Prevent click inside dropdown
        | from closing immediately
        */

        userDropdown.addEventListener(
            "click",
            function (event) {
                event.stopPropagation();
            }
        );


        /*
        | Close when clicking outside
        */

        document.addEventListener(
            "click",
            function () {

                userDropdown.classList.remove("show");

                userButton.setAttribute(
                    "aria-expanded",
                    "false"
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE MENU
    |--------------------------------------------------------------------------
    */

    const menuToggle =
        document.getElementById("menuToggle");

    const mobileMenu =
        document.getElementById("mobileMenu");


    if (menuToggle && mobileMenu) {

        menuToggle.addEventListener(
            "click",
            function () {

                mobileMenu.classList.toggle("show");

            }
        );

    }

});

</script>
