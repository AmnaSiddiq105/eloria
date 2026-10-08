
<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, name, email, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$user_name = $user['name'];
$user_email = $user['email'];
$member_since = date("F Y", strtotime($user['created_at']));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ÉLORIA | My Account</title>


    <!-- ÉLORIA FONTS -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">


    <!-- MAIN ÉLORIA CSS -->

    <link rel="stylesheet"
          href="assets/css/home.css">


    <!-- ACCOUNT PAGE CSS -->

    <link rel="stylesheet"
          href="assets/css/account.css">

</head>


<body>


<!-- PAGE LOADER -->

<div class="page-loader">

    <div class="loader-logo">
        ÉLORIA
    </div>

    <div class="loader-line">
        <span></span>
    </div>

</div>


<!-- NAVBAR -->

<?php include "includes/navbar.php"; ?>


<!-- ACCOUNT HERO -->

<section class="account-hero">

    <div class="account-hero-content">

        <span class="account-label">
            WELCOME BACK
        </span>

        <h1>
            Hello,
            <em><?= htmlspecialchars($user_name) ?></em>
        </h1>

        <p>
            Your ÉLORIA space, all in one place.
        </p>

    </div>

</section>


<!-- ACCOUNT SECTION -->

<section class="account-section">

    <div class="account-container">


        <!-- PROFILE CARD -->

        <div class="profile-card">

            <div class="profile-top">

                <div class="profile-avatar">

                    <?= strtoupper(
                        htmlspecialchars(
                            substr($user_name, 0, 1)
                        )
                    ) ?>

                </div>

                <div class="profile-heading">

                    <span>
                        MY PROFILE
                    </span>

                    <h2>
                        <?= htmlspecialchars($user_name) ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars($user_email) ?>
                    </p>

                </div>

            </div>


            <div class="profile-details">

                <div class="profile-detail">

                    <span>
                        NAME
                    </span>

                    <strong>
                        <?= htmlspecialchars($user_name) ?>
                    </strong>

                </div>


                <div class="profile-detail">

                    <span>
                        EMAIL
                    </span>

                    <strong>
                        <?= htmlspecialchars($user_email) ?>
                    </strong>

                </div>


                <div class="profile-detail">

                    <span>
                        MEMBER SINCE
                    </span>

                    <strong>
                        <?= htmlspecialchars($member_since) ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- ACCOUNT ACTIONS -->

        <div class="account-actions">

            <div class="account-actions-heading">

                <span>
                    YOUR ÉLORIA
                </span>

                <h2>
                    Everything you need,
                    <em>right here.</em>
                </h2>

            </div>


            <div class="account-cards">


                <!-- ORDERS -->

                <a href="my-orders.php"
                   class="account-card">

                    <div class="account-card-icon">
                        ♡
                    </div>

                    <div class="account-card-content">

                        <span>
                            01
                        </span>

                        <h3>
                            My Orders
                        </h3>

                        <p>
                            View your previous and
                            current orders.
                        </p>

                    </div>

                    <div class="account-card-arrow">
                        →
                    </div>

                </a>


                <!-- SHOP -->

                <a href="shop.php"
                   class="account-card">

                    <div class="account-card-icon">
                        ✦
                    </div>

                    <div class="account-card-content">

                        <span>
                            02
                        </span>

                        <h3>
                            Explore Collection
                        </h3>

                        <p>
                            Discover your next
                            ÉLORIA favorite.
                        </p>

                    </div>

                    <div class="account-card-arrow">
                        →
                    </div>

                </a>


                <!-- WISHLIST -->

                <a href="wishlist.php"
                   class="account-card">

                    <div class="account-card-icon">
                        ♡
                    </div>

                    <div class="account-card-content">

                        <span>
                            03
                        </span>

                        <h3>
                            My Wishlist
                        </h3>

                        <p>
                            Keep your favorite
                            pieces close.
                        </p>

                    </div>

                    <div class="account-card-arrow">
                        →
                    </div>

                </a>


                <!-- LOGOUT -->

                <a href="logout.php"
                   class="account-card logout-card">

                    <div class="account-card-icon">
                        ↗
                    </div>

                    <div class="account-card-content">

                        <span>
                            04
                        </span>

                        <h3>
                            Sign Out
                        </h3>

                        <p>
                            Sign out safely from
                            your ÉLORIA account.
                        </p>

                    </div>

                    <div class="account-card-arrow">
                        →
                    </div>

                </a>

            </div>

        </div>

    </div>

</section>


<!-- ACCOUNT QUOTE -->

<section class="account-quote">

    <div class="account-quote-inner">

        <span>
            A LITTLE ÉLORIA REMINDER
        </span>

        <h2>
            Beauty is in the details.
        </h2>

        <p>
            Thank you for being part of the ÉLORIA world.
        </p>

    </div>

</section>


<!-- FOOTER -->

<?php include "includes/footer.php"; ?>


<!-- HOME JS FOR LOADER / NAVBAR -->

<script src="assets/js/home.js"></script>

</body>

</html>
