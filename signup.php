<?php

session_start();

require_once "db.php";
require_once "includes/send_email.php";


// =====================================================
// CHECK IF ALREADY LOGGED IN
// =====================================================

if (isset($_SESSION['user_id'])) {
    header("Location: welcome.php");
    exit;
}


$errors = [];


// =====================================================
// FORM SUBMISSION
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // =================================================
    // NAME VALIDATION
    // =================================================

    if ($name === "") {

        $errors[] = "Please enter your full name.";

    } elseif (strlen($name) < 3) {

        $errors[] = "Name must contain at least 3 characters.";

    }


    // =================================================
    // EMAIL VALIDATION
    // =================================================

    if ($email === "") {

        $errors[] = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";

    }


    // =================================================
    // PASSWORD VALIDATION
    // =================================================

    if ($password === "") {

        $errors[] = "Please enter a password.";

    } elseif (strlen($password) < 6) {

        $errors[] = "Password must contain at least 6 characters.";

    }


    // =================================================
    // CONFIRM PASSWORD
    // =================================================

    if ($confirm_password === "") {

        $errors[] = "Please confirm your password.";

    } elseif ($password !== $confirm_password) {

        $errors[] = "Passwords do not match.";

    }


    // =================================================
    // CHECK EXISTING EMAIL
    // =================================================

    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );


        if (!$check) {

            $errors[] = "Unable to check your email address.";

        } else {

            $check->bind_param(
                "s",
                $email
            );

            $check->execute();

            $result = $check->get_result();


            if ($result->num_rows > 0) {

                $errors[] =
                    "An account with this email already exists.";

            }


            $check->close();
        }
    }


    // =================================================
    // CREATE ACCOUNT
    // =================================================

    if (empty($errors)) {

        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $stmt = $conn->prepare(
            "INSERT INTO users
                (name, email, password)
             VALUES
                (?, ?, ?)"
        );


        if (!$stmt) {

            $errors[] =
                "Unable to create your account.";

        } else {

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );


            if ($stmt->execute()) {

                // =========================================
                // GET NEW USER ID
                // =========================================

                $user_id = $stmt->insert_id;


                // =========================================
                // CREATE SESSION
                // =========================================

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user_id;

                $_SESSION["user_name"] = $name;

                $_SESSION["user_email"] = $email;


                // =========================================
                // WELCOME EMAIL
                // =========================================

                $subject =
                    "Welcome to ÉLORIA, " . $name . " ✦";


                $safe_name =
                    htmlspecialchars(
                        $name,
                        ENT_QUOTES,
                        'UTF-8'
                    );


                $htmlBody = '

                <!DOCTYPE html>

                <html>

                <head>

                    <meta charset="UTF-8">

                    <meta name="viewport"
                          content="width=device-width, initial-scale=1.0">

                    <title>Welcome to ÉLORIA</title>

                </head>


                <body style="
                    margin:0;
                    padding:0;
                    background:#f8f0f5;
                    font-family:Arial, Helvetica, sans-serif;
                    color:#493448;
                ">


                    <table
                        width="100%"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        style="
                            background:#f8f0f5;
                            padding:40px 15px;
                        "
                    >

                        <tr>

                            <td align="center">


                                <table
                                    width="600"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    style="
                                        max-width:600px;
                                        width:100%;
                                        background:#ffffff;
                                        border-radius:20px;
                                        overflow:hidden;
                                        box-shadow:0 15px 45px rgba(80,50,75,0.12);
                                    "
                                >


                                    <!-- HEADER -->

                                    <tr>

                                        <td
                                            align="center"
                                            style="
                                                padding:42px 25px;
                                                background:linear-gradient(
                                                    135deg,
                                                    #ead5e1,
                                                    #e5dced
                                                );
                                            "
                                        >

                                            <div style="
                                                font-family:Georgia, serif;
                                                font-size:38px;
                                                letter-spacing:6px;
                                                color:#493448;
                                            ">
                                                ÉLORIA
                                            </div>


                                            <div style="
                                                margin-top:8px;
                                                font-size:11px;
                                                letter-spacing:2px;
                                                color:#735d70;
                                                text-transform:uppercase;
                                            ">
                                                A Little Luxury
                                            </div>

                                        </td>

                                    </tr>


                                    <!-- CONTENT -->

                                    <tr>

                                        <td
                                            style="
                                                padding:45px 40px;
                                            "
                                        >

                                            <div style="
                                                font-size:11px;
                                                letter-spacing:3px;
                                                color:#9b6f89;
                                                text-transform:uppercase;
                                                margin-bottom:15px;
                                            ">
                                                WELCOME TO ÉLORIA
                                            </div>


                                            <h1 style="
                                                margin:0;
                                                font-family:Georgia, serif;
                                                font-size:34px;
                                                font-weight:normal;
                                                line-height:1.2;
                                                color:#443143;
                                            ">
                                                Hello ' . $safe_name . ',
                                            </h1>


                                            <p style="
                                                margin:22px 0 0;
                                                font-size:14px;
                                                line-height:1.8;
                                                color:#766474;
                                            ">
                                                Welcome to ÉLORIA.
                                                Your account has been created
                                                successfully, and we are so happy
                                                to have you with us.
                                            </p>


                                            <p style="
                                                margin:15px 0 0;
                                                font-size:14px;
                                                line-height:1.8;
                                                color:#766474;
                                            ">
                                                Discover beautiful pieces,
                                                thoughtful details and a little
                                                luxury made especially for you.
                                            </p>


                                            <!-- ACCOUNT BOX -->

                                            <table
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                                style="
                                                    margin-top:30px;
                                                    background:#faf4f8;
                                                    border:1px solid #eadce5;
                                                    border-radius:12px;
                                                "
                                            >

                                                <tr>

                                                    <td
                                                        style="
                                                            padding:20px;
                                                            font-size:13px;
                                                            line-height:1.8;
                                                            color:#695568;
                                                        "
                                                    >

                                                        <strong
                                                            style="color:#493448;"
                                                        >
                                                            Your account email
                                                        </strong>

                                                        <br>

                                                        ' . htmlspecialchars(
                                                            $email,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) . '

                                                    </td>

                                                </tr>

                                            </table>


                                            <!-- BUTTON -->

                                            <div
                                                style="
                                                    text-align:center;
                                                    margin-top:32px;
                                                "
                                            >

                                                <a
                                                    href="http://localhost:82/eloria/shop.php"
                                                    style="
                                                        display:inline-block;
                                                        padding:15px 30px;
                                                        background:#65485f;
                                                        color:#ffffff;
                                                        text-decoration:none;
                                                        border-radius:10px;
                                                        font-size:11px;
                                                        letter-spacing:2px;
                                                        text-transform:uppercase;
                                                    "
                                                >
                                                    Explore ÉLORIA
                                                </a>

                                            </div>


                                            <p style="
                                                margin:35px 0 0;
                                                font-family:Georgia, serif;
                                                font-size:18px;
                                                font-style:italic;
                                                color:#9b718a;
                                                text-align:center;
                                            ">
                                                ✦ Elegance in every detail ✦
                                            </p>


                                        </td>

                                    </tr>


                                    <!-- FOOTER -->

                                    <tr>

                                        <td
                                            align="center"
                                            style="
                                                padding:25px;
                                                background:#faf6f9;
                                                border-top:1px solid #eee3ea;
                                            "
                                        >

                                            <div style="
                                                font-size:10px;
                                                letter-spacing:1px;
                                                color:#a493a0;
                                            ">
                                                © ' . date("Y") . ' ÉLORIA
                                            </div>


                                            <div style="
                                                margin-top:7px;
                                                font-size:10px;
                                                color:#b0a1ad;
                                            ">
                                                A little luxury, made for you.
                                            </div>

                                        </td>

                                    </tr>


                                </table>


                            </td>

                        </tr>

                    </table>


                </body>

                </html>
                ';


                // =========================================
                // SEND EMAIL
                // =========================================
                //
                // Email failure will NOT cancel account
                // creation because the database account
                // has already been created successfully.
                //

                sendEloriaEmail(
                    $email,
                    $name,
                    $subject,
                    $htmlBody
                );


                // =========================================
                // REDIRECT
                // =========================================

                header("Location: welcome.php");

                exit;


            } else {

                $errors[] =
                    "Something went wrong. Please try again.";

            }


            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Account | ÉLORIA</title>


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
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="assets/css/auth.css?v=2"
    >
<style>

    /* =========================================
       ADMIN LOGIN BUTTON
    ========================================== */

    .admin-login-button {
        width: 100%;
        box-sizing: border-box;

        margin-top: 14px;

        padding: 13px 18px;

        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;

        text-decoration: none;

        border: 1px solid #d9c5d2;

        background: transparent;

        color: #65485f;

        font-family: 'Montserrat', sans-serif;

        font-size: 11px;

        font-weight: 500;

        letter-spacing: 1.5px;

        border-radius: 8px;

        transition:
            all 0.3s ease;
    }


    .admin-login-button span:first-child {
        font-size: 16px;
    }


    .admin-login-button span:last-child {
        font-size: 15px;
        transition: transform 0.3s ease;
    }


    .admin-login-button:hover {

        background: #65485f;

        color: #ffffff;

        border-color: #65485f;

        transform: translateY(-2px);

        box-shadow:
            0 8px 20px rgba(101, 72, 95, 0.15);
    }


    .admin-login-button:hover span:last-child {

        transform: translateX(4px);
    }

</style>
</head>


<body>


<div class="auth-page">


    <div class="auth-decoration decoration-one"></div>

    <div class="auth-decoration decoration-two"></div>


    <div class="auth-container">


        <!-- =========================================
             LEFT SIDE
        ========================================== -->

        <div class="auth-showcase">


            <div class="brand">

                <span class="brand-symbol">
                    E
                </span>


                <div>

                    <h1>
                        ÉLORIA
                    </h1>

                    <p>
                        A little luxury, made for you.
                    </p>

                </div>

            </div>


            <div class="showcase-content">


                <span class="small-heading">
                    WELCOME TO ÉLORIA
                </span>


                <h2>

                    Your style.
                    <br>

                    Your story.
                    <br>

                    <em>Your ÉLORIA.</em>

                </h2>


                <p>

                    Discover a beautiful collection created
                    for those who love elegance, confidence
                    and little moments of luxury.

                </p>


            </div>


            <div class="showcase-footer">

                ✦ Curated with love

            </div>


        </div>



        <!-- =========================================
             RIGHT SIDE
        ========================================== -->

        <div class="auth-card">


            <div class="mobile-logo">

                ÉLORIA

            </div>


            <span class="form-label">

                CREATE YOUR ACCOUNT

            </span>


            <h2>

                Begin your journey

            </h2>


            <p class="form-intro">

                Join ÉLORIA and make every moment feel special.

            </p>



            <!-- ERRORS -->

            <?php if (!empty($errors)): ?>

                <div class="error-box">

                    <?php foreach ($errors as $error): ?>

                        <p>
                            ✦
                            <?= htmlspecialchars($error) ?>
                        </p>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>



            <!-- SIGNUP FORM -->

            <form
                method="POST"
                action=""
                id="signupForm"
            >


                <!-- NAME -->

                <div class="input-group">

                    <label>
                        Full Name
                    </label>


                    <div class="input-wrapper">

                        <span>
                            ♡
                        </span>


                        <input
                            type="text"
                            name="name"
                            placeholder="Enter your full name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            autocomplete="name"
                            required
                        >

                    </div>

                </div>



                <!-- EMAIL -->

                <div class="input-group">

                    <label>
                        Email Address
                    </label>


                    <div class="input-wrapper">

                        <span>
                            ✉
                        </span>


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



                <!-- PASSWORD -->

                <div class="input-group">

                    <label>
                        Password
                    </label>


                    <div class="input-wrapper">

                        <span>
                            ♢
                        </span>


                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword('password', this)"
                        >
                            Show
                        </button>

                    </div>

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="input-group">

                    <label>
                        Confirm Password
                    </label>


                    <div class="input-wrapper">

                        <span>
                            ♢
                        </span>


                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Repeat your password"
                            autocomplete="new-password"
                            required
                        >


                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword('confirm_password', this)"
                        >
                            Show
                        </button>

                    </div>

                </div>



                <!-- BUTTON -->

                <button
                    type="submit"
                    class="auth-button"
                >

                    <span>
                        Create Account
                    </span>

                    <span>
                        →
                    </span>

                </button>


            </form>

            <!-- =========================================
     ADMIN LOGIN
========================================== -->

<a
    href="admin/login.php"
    class="admin-login-button"
>
    <span>
        ♕
    </span>

    <span>
        Admin Login
    </span>

    <span>
        →
    </span>
</a>



            <!-- DIVIDER -->

            <div class="divider">

                <span>
                    already a member?
                </span>

            </div>



            <!-- LOGIN -->

            <a
                href="login.php"
                class="secondary-button"
            >

                Sign In

            </a>



            <p class="terms">

                By creating an account, you agree to
                ÉLORIA's terms and privacy policy.

            </p>


        </div>


    </div>


</div>


<script src="assets/js/auth.js"></script>


</body>

</html>