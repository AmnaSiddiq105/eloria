<?php

session_start();

require_once "../db.php";


// =====================================================
// ALREADY LOGGED IN
// =====================================================

if (isset($_SESSION['admin_id'])) {

    header("Location: dashboard.php");
    exit;
}


$error = "";


// =====================================================
// LOGIN
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');


    // =================================================
    // VALIDATION
    // =================================================

    if ($email === '' || $password === '') {

        $error = "Please enter email and password.";

    } else {


        // =============================================
        // FIND ADMIN
        // =============================================

        $sql = "
            SELECT
                id,
                name,
                email,
                password
            FROM admin
            WHERE email = ?
            LIMIT 1
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $error = "Unable to process login.";

        } else {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();

            $admin = $result->fetch_assoc();

            $stmt->close();


            // =========================================
            // CHECK ADMIN LOGIN
            // =========================================

            if (
                $admin &&
                $password === $admin['password']
            ) {


                // =====================================
                // ADMIN SESSION
                // =====================================

                $_SESSION['admin_id'] =
                    $admin['id'];

                $_SESSION['admin_name'] =
                    $admin['name'];

                $_SESSION['admin_email'] =
                    $admin['email'];


                // =====================================
                // GO TO ADMIN DASHBOARD
                // =====================================

                header(
                    "Location: dashboard.php"
                );

                exit;


            } else {

                $error =
                    "Invalid email or password.";
            }
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

<title>Admin Login | ÉLORIA</title>


<!-- GOOGLE FONT -->

<link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@400;500;600&display=swap"
    rel="stylesheet"
>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    min-height: 100vh;

    font-family: "Montserrat", Arial, sans-serif;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        linear-gradient(
            135deg,
            #fdf6f8,
            #f4e9ed,
            #eee4ea
        );

}


/* =====================================================
   LOGIN CARD
===================================================== */

.login-card {

    width: 100%;

    max-width: 450px;

    background:
        rgba(255,255,255,.88);

    border:
        1px solid
        rgba(255,255,255,.9);

    border-radius: 28px;

    padding: 42px 38px;

    box-shadow:
        0 25px 70px
        rgba(77,53,71,.14);

    backdrop-filter: blur(15px);

    animation:
        cardEnter .6s ease;

}


@keyframes cardEnter {

    from {

        opacity: 0;

        transform:
            translateY(20px);

    }

    to {

        opacity: 1;

        transform:
            translateY(0);

    }

}


/* =====================================================
   LOGO
===================================================== */

.logo {

    width: 78px;

    height: 78px;

    margin:
        0 auto 18px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            #76515f,
            #4c3540
        );

    color: #fff;

    font-size: 27px;

    box-shadow:
        0 12px 30px
        rgba(76,53,64,.22);

}


/* =====================================================
   BRAND
===================================================== */

.brand {

    text-align: center;

    font-family:
        "Cormorant Garamond",
        Georgia,
        serif;

    font-size: 38px;

    letter-spacing: 4px;

    color: #49343c;

    margin-bottom: 3px;

}


.subtitle {

    text-align: center;

    color: #8a747d;

    font-size: 11px;

    letter-spacing: 3px;

    text-transform: uppercase;

    margin-bottom: 32px;

}


/* =====================================================
   ERROR
===================================================== */

.error {

    background: #fff2f3;

    border:
        1px solid #efd1d5;

    color: #9a4d57;

    border-radius: 12px;

    padding: 12px 14px;

    font-size: 13px;

    margin-bottom: 20px;

    text-align: center;

}


/* =====================================================
   FORM
===================================================== */

.form-group {

    margin-bottom: 20px;

}


.form-label {

    display: block;

    color: #604758;

    font-size: 12px;

    font-weight: 600;

    margin-bottom: 8px;

    letter-spacing: .3px;

}


.input-wrapper {

    position: relative;

}


.input-wrapper i {

    position: absolute;

    left: 15px;

    top: 50%;

    transform:
        translateY(-50%);

    color: #9b7c89;

    font-size: 14px;

}


.form-control {

    width: 100%;

    border:
        1px solid #dfd0d8;

    border-radius: 13px;

    padding:
        13px
        14px
        13px
        43px;

    background:
        rgba(255,255,255,.75);

    color: #50394a;

    font-family:
        "Montserrat",
        Arial,
        sans-serif;

    font-size: 13px;

    outline: none;

    transition: .3s;

}


.form-control::placeholder {

    color: #b19da6;

}


.form-control:focus {

    border-color: #9b7182;

    background: #fff;

    box-shadow:
        0 0 0 4px
        rgba(155,113,130,.09);

}


/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn {

    width: 100%;

    border: none;

    padding: 14px;

    border-radius: 30px;

    background:
        linear-gradient(
            135deg,
            #76515f,
            #4c3540
        );

    color: #fff;

    font-family:
        "Montserrat",
        Arial,
        sans-serif;

    font-weight: 600;

    font-size: 13px;

    letter-spacing: .4px;

    cursor: pointer;

    transition: .3s;

}


.login-btn:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 12px 28px
        rgba(76,53,64,.24);

}


.login-btn i {

    margin-right: 7px;

}


/* =====================================================
   BACK LINK
===================================================== */

.back-link {

    display: block;

    text-align: center;

    margin-top: 23px;

    color: #806075;

    text-decoration: none;

    font-size: 12px;

    transition: .3s;

}


.back-link:hover {

    color: #4c3540;

}


/* =====================================================
   SECURITY NOTE
===================================================== */

.security-note {

    margin-top: 24px;

    padding: 11px 14px;

    border-radius: 12px;

    background:
        rgba(248,241,243,.8);

    color: #8a747d;

    font-size: 10px;

    line-height: 1.6;

    text-align: center;

}


.security-note i {

    color: #76515f;

    margin-right: 4px;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 480px) {

    .login-card {

        padding:
            35px 23px;

        border-radius: 23px;

    }

    .brand {

        font-size: 34px;

    }

}

</style>

</head>


<body>


<div class="login-card">


    <!-- LOGO -->

    <div class="logo">

        <i class="fa-solid fa-crown"></i>

    </div>


    <!-- BRAND -->

    <div class="brand">

        ÉLORIA

    </div>


    <div class="subtitle">

        Admin Panel Login

    </div>


    <!-- ERROR -->

    <?php if ($error !== ''): ?>

        <div class="error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <form
        method="POST"
        action=""
    >


        <!-- EMAIL -->

        <div class="form-group">

            <label class="form-label">

                Email Address

            </label>


            <div class="input-wrapper">

                <i class="fa-regular fa-envelope"></i>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="admin@eloria.com"
                    autocomplete="email"
                    required
                >

            </div>

        </div>


        <!-- PASSWORD -->

        <div class="form-group">

            <label class="form-label">

                Password

            </label>


            <div class="input-wrapper">

                <i class="fa-solid fa-lock"></i>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter admin password"
                    autocomplete="current-password"
                    required
                >

            </div>

        </div>


        <!-- LOGIN -->

        <button
            type="submit"
            class="login-btn"
        >

            <i class="fa-solid fa-right-to-bracket"></i>

            Login to Admin Panel

        </button>


    </form>


    <!-- BACK TO MAIN SITE -->

    <a
        href="../welcome.php"
        class="back-link"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to ÉLORIA

    </a>


    <!-- SECURITY -->

    <div class="security-note">

        <i class="fa-solid fa-shield-halved"></i>

        This area is restricted to authorized
        ÉLORIA administrators.

    </div>


</div>


</body>

</html>