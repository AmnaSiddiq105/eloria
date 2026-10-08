<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Welcome | ÉLORIA</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="assets/css/auth.css">

</head>

<body>

<div class="welcome-page">

    <div class="welcome-glow glow-one"></div>
    <div class="welcome-glow glow-two"></div>

    <div class="welcome-card">

        <div class="welcome-symbol">
            E
        </div>

        <span class="welcome-small">
            ÉLORIA
        </span>

        <h1>
            Welcome,
            <br>
            <em><?= htmlspecialchars($user_name) ?></em>
        </h1>

        <p>
            Your beautiful journey starts here.
        </p>

        <div class="welcome-line"></div>

        <p class="welcome-message">

            Take a moment for yourself.
            Discover pieces, stories and little
            luxuries made especially for you.

        </p>

        <a href="home.php"
           class="enter-button">

            Enter ÉLORIA
            <span>→</span>

        </a>

    </div>

</div>

</body>
</html>