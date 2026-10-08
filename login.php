<?php
session_start();
require_once "db.php";

if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "") {
        $errors[] = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password === "") {
        $errors[] = "Please enter your password.";
    }

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: welcome.php");
                exit;

            } else {

                $errors[] = "Incorrect email or password.";
            }

        } else {

            $errors[] = "Incorrect email or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Sign In | ÉLORIA</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
          rel="stylesheet">

    <!-- <link rel="stylesheet"
          href="assets/css/auth.css"> -->

          <link rel="stylesheet"
      href="assets/css/auth.css?v=2">

</head>

<body>

<div class="auth-page">

    <div class="auth-decoration decoration-one"></div>
    <div class="auth-decoration decoration-two"></div>

    <div class="auth-container">

        <!-- SHOWCASE -->

        <div class="auth-showcase">

            <div class="brand">

                <span class="brand-symbol">E</span>

                <div>

                    <h1>ÉLORIA</h1>

                    <p>
                        A little luxury, made for you.
                    </p>

                </div>

            </div>


            <div class="showcase-content">

                <span class="small-heading">
                    WELCOME BACK
                </span>

                <h2>
                    Good things
                    <br>
                    are waiting
                    <br>
                    <em>for you.</em>
                </h2>

                <p>
                    Step back into your ÉLORIA world,
                    where every detail is designed
                    to make you feel beautiful.
                </p>

            </div>


            <div class="showcase-footer">
                ✦ Elegance in every detail
            </div>

        </div>


        <!-- LOGIN CARD -->

        <div class="auth-card">

            <div class="mobile-logo">
                ÉLORIA
            </div>

            <span class="form-label">
                WELCOME BACK
            </span>

            <h2>
                Sign in to ÉLORIA
            </h2>

            <p class="form-intro">
                Enter your details to continue your journey.
            </p>


            <?php if (!empty($errors)): ?>

                <div class="error-box">

                    <?php foreach ($errors as $error): ?>

                        <p>✦ <?= htmlspecialchars($error) ?></p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <form method="POST"
                  action=""
                  id="loginForm">

                <div class="input-group">

                    <label>Email Address</label>

                    <div class="input-wrapper">

                        <span>✉</span>

                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <div class="input-group">

                    <label>Password</label>

                    <div class="input-wrapper">

                        <span>♢</span>

                        <input
                            type="password"
                            name="password"
                            id="loginPassword"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword('loginPassword', this)">
                            Show
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="auth-button">

                    <span>Sign In</span>
                    <span>→</span>

                </button>

            </form>


            <div class="divider">
                <span>new to ÉLORIA?</span>
            </div>


            <a href="signup.php"
               class="secondary-button">

                Create Account

            </a>


            <p class="terms">
                Your information is protected and securely stored.
            </p>

        </div>

    </div>

</div>

<script src="assets/js/auth.js"></script>

</body>
</html>