
<?php
session_start();
require_once "../db.php";

/* =========================
   ADMIN LOGIN CHECK
========================= */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_id = (int)$_SESSION['admin_id'];

$success = "";
$error = "";

/* =========================
   GET ADMIN
========================= */
$sql = "
    SELECT id, name, email, password, created_at
    FROM admin
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$admin = $result->fetch_assoc();

$stmt->close();


/* =========================
   UPDATE PROFILE
========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['change_password'])) {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $email === '') {

        $error = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $check_sql = "
            SELECT id
            FROM admin
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "si",
            $email,
            $admin_id
        );

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $error = "This email is already being used by another admin.";

        } else {

            $update_sql = "
                UPDATE admin
                SET name = ?, email = ?
                WHERE id = ?
            ";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "ssi",
                $name,
                $email,
                $admin_id
            );

            if ($update_stmt->execute()) {

                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_email'] = $email;

                $admin['name'] = $name;
                $admin['email'] = $email;

                $success = "Profile updated successfully.";

            } else {

                $error = "Unable to update profile. Please try again.";
            }

            $update_stmt->close();
        }

        $check_stmt->close();
    }
}


/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (
        $current_password === '' ||
        $new_password === '' ||
        $confirm_password === ''
    ) {

        $error = "Please fill all password fields.";

    } elseif (
        !password_verify(
            $current_password,
            $admin['password']
        )
    ) {

        /*
         * Supports old plain-text admin password
         * as well as hashed passwords.
         */
        if ($current_password !== $admin['password']) {

            $error = "Current password is incorrect.";
        }
    }

    if ($error === "" && strlen($new_password) < 6) {

        $error = "New password must be at least 6 characters.";

    } elseif (
        $error === "" &&
        $new_password !== $confirm_password
    ) {

        $error = "New password and confirm password do not match.";
    }

    if ($error === "") {

        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $password_sql = "
            UPDATE admin
            SET password = ?
            WHERE id = ?
        ";

        $password_stmt = $conn->prepare($password_sql);

        $password_stmt->bind_param(
            "si",
            $hashed_password,
            $admin_id
        );

        if ($password_stmt->execute()) {

            $admin['password'] = $hashed_password;

            $success = "Password changed successfully.";

        } else {

            $error = "Unable to change password. Please try again.";
        }

        $password_stmt->close();
    }
}


/* =========================
   ADMIN INITIAL
========================= */
$admin_name = trim($admin['name']);

$initial = strtoupper(
    substr($admin_name, 0, 1)
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Profile | ÉLORIA</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
<link rel="stylesheet" href="assets/admin-sidebar.css">
    <style>

        /* =========================
           RESET
        ========================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            min-height: 100vh;

            font-family: "Montserrat", sans-serif;

            color: #2d2730;

            background:
                radial-gradient(
                    circle at 10% 10%,
                    rgba(244, 205, 221, .42),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 20%,
                    rgba(218, 208, 244, .45),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #fffafc 0%,
                    #f8f3fb 50%,
                    #f3f6ff 100%
                );

            overflow-x: hidden;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            top: 22px;
            left: 22px;

            width: 245px;
            height: calc(100vh - 44px);

            padding: 30px 18px;

            background:
                rgba(255,255,255,.72);

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            border:
                1px solid
                rgba(255,255,255,.85);

            border-radius: 28px;

            box-shadow:
                0 18px 50px
                rgba(91, 65, 91, .09);

            z-index: 1000;
        }


        .brand {

            text-align: center;

            margin-bottom: 38px;

            padding-bottom: 25px;

            border-bottom:
                1px solid
                rgba(91,65,91,.08);
        }


        .brand h2 {

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 38px;

            line-height: 1;

            font-weight: 600;

            letter-spacing: 2px;

            color: #332b35;
        }


        .brand span {

            display: block;

            margin-top: 8px;

            color: #9b8796;

            font-size: 9px;

            letter-spacing: 3px;

            text-transform: uppercase;
        }


        .menu {

            display: flex;

            flex-direction: column;

            gap: 7px;
        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            border-radius: 14px;

            color: #766875;

            text-decoration: none;

            font-size: 13px;

            font-weight: 500;

            transition:
                transform .3s ease,
                background .3s ease,
                color .3s ease,
                box-shadow .3s ease;
        }


        .menu a i {

            width: 20px;

            text-align: center;

            font-size: 17px;

            color: #a58b9f;

            transition: .3s;
        }


        .menu a:hover {

            color: #493d47;

            background:
                linear-gradient(
                    135deg,
                    rgba(249,222,234,.65),
                    rgba(231,222,247,.65)
                );

            transform: translateX(3px);
        }


        .menu a:hover i {

            color: #9b7192;
        }


        .menu a.active {

            color: #493d47;

            background:
                linear-gradient(
                    135deg,
                    #f8dce8,
                    #e8def7
                );

            box-shadow:
                0 7px 18px
                rgba(157,119,155,.10);
        }


        .menu a.active i {

            color: #8e6686;
        }


        .logout {

            margin-top: 20px;

            padding-top: 18px;

            border-top:
                1px solid
                rgba(91,65,91,.08);
        }


        .logout a {

            color: #987d89;
        }


        .logout a:hover {

            color: #a34f6d;

            background:
                rgba(250,226,235,.65);
        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 291px;

            min-height: 100vh;

            padding: 45px 42px 55px;
        }


        /* =========================
           HEADER
        ========================= */

        .page-header {

            margin-bottom: 25px;
        }


        .page-label {

            display: inline-block;

            margin-bottom: 5px;

            color: #a07892;

            font-size: 10px;

            font-weight: 600;

            letter-spacing: 2.5px;

            text-transform: uppercase;
        }


        .page-header h1 {

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 43px;

            line-height: 1;

            font-weight: 600;

            color: #302a31;
        }


        .page-header p {

            margin-top: 9px;

            color: #948693;

            font-size: 13px;
        }


        /* =========================
           ALERTS
        ========================= */

        .alert {

            display: flex;

            align-items: center;

            gap: 10px;

            padding: 14px 17px;

            margin-bottom: 20px;

            border-radius: 15px;

            font-size: 12px;

            font-weight: 500;

            box-shadow:
                0 7px 20px
                rgba(70,50,70,.04);
        }


        .success {

            color: #42715a;

            background:
                rgba(231,248,238,.85);

            border:
                1px solid
                rgba(188,225,202,.8);
        }


        .success i {

            color: #5d9b76;
        }


        .error {

            color: #a35a6e;

            background:
                rgba(255,239,244,.9);

            border:
                1px solid
                rgba(242,204,215,.85);
        }


        .error i {

            color: #c4778e;
        }


        /* =========================
           PROFILE HERO CARD
        ========================= */

        .profile-card {

            position: relative;

            display: flex;

            align-items: center;

            gap: 22px;

            padding: 30px 32px;

            margin-bottom: 25px;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,.90),
                    rgba(255,247,251,.86)
                );

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid
                rgba(255,255,255,.9);

            border-radius: 25px;

            box-shadow:
                0 15px 42px
                rgba(85,65,85,.08);
        }


        .profile-card::after {

            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            right: -70px;
            top: -90px;

            border-radius: 50%;

            background:
                rgba(225,210,244,.30);

            pointer-events: none;
        }


        .avatar {

            position: relative;

            width: 84px;
            height: 84px;

            flex-shrink: 0;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            color: #fff;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 38px;

            font-weight: 600;

            background:
                linear-gradient(
                    135deg,
                    #e5b8cc,
                    #b5a0d5
                );

            box-shadow:
                0 10px 25px
                rgba(167,127,161,.22);

            z-index: 1;
        }


        .profile-info {

            position: relative;

            z-index: 1;
        }


        .profile-info h2 {

            color: #393039;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 29px;

            line-height: 1;

            font-weight: 600;

            margin-bottom: 7px;
        }


        .profile-info p {

            color: #8e7d8c;

            font-size: 12px;
        }


        .admin-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            margin-top: 10px;

            padding: 6px 11px;

            border-radius: 30px;

            color: #87627d;

            background:
                linear-gradient(
                    135deg,
                    #f9e5ee,
                    #eee7fa
                );

            font-size: 10px;

            font-weight: 600;

            letter-spacing: .4px;
        }


        /* =========================
           CONTENT GRID
        ========================= */

        .content-grid {

            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);

            gap: 25px;

            align-items: start;
        }


        /* =========================
           CARDS
        ========================= */

        .card {

            background:
                rgba(255,255,255,.82);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border:
                1px solid
                rgba(255,255,255,.9);

            border-radius: 23px;

            padding: 28px;

            box-shadow:
                0 14px 38px
                rgba(80,60,80,.07);

            transition:
                transform .3s ease,
                box-shadow .3s ease;
        }


        .card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 18px 42px
                rgba(80,60,80,.10);
        }


        .card h3 {

            display: flex;

            align-items: center;

            gap: 9px;

            color: #393039;

            font-family:
                "Cormorant Garamond",
                serif;

            font-size: 23px;

            font-weight: 600;

            margin-bottom: 4px;
        }


        .card h3 i {

            color: #a87898;

            font-size: 17px;
        }


        .card-description {

            color: #998a97;

            font-size: 11px;

            line-height: 1.6;

            margin-bottom: 22px;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 17px;
        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            color: #665764;

            font-size: 11px;

            font-weight: 600;

            letter-spacing: .2px;
        }


        .input-wrap {

            position: relative;
        }


        .input-wrap i {

            position: absolute;

            left: 14px;
            top: 50%;

            transform:
                translateY(-50%);

            color: #b091a8;

            font-size: 15px;

            pointer-events: none;
        }


        .input-wrap input {

            width: 100%;

            padding: 12px 14px 12px 41px;

            border:
                1px solid
                #eadfe8;

            border-radius: 13px;

            outline: none;

            color: #493f48;

            background:
                rgba(255,255,255,.88);

            font-family:
                "Montserrat",
                sans-serif;

            font-size: 12px;

            transition:
                border .3s,
                box-shadow .3s,
                background .3s;
        }


        .input-wrap input::placeholder {

            color: #b4a6b1;
        }


        .input-wrap input:focus {

            background: #fff;

            border-color: #d5afc8;

            box-shadow:
                0 0 0 4px
                rgba(218,180,204,.13);
        }


        /* =========================
           BUTTON
        ========================= */

        .save-btn {

            display: inline-flex;

            align-items: center;
            justify-content: center;

            gap: 7px;

            border: none;

            padding: 12px 19px;

            border-radius: 13px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #c68eaa,
                    #9c8ac6
                );

            font-family:
                "Montserrat",
                sans-serif;

            font-size: 11px;

            font-weight: 600;

            cursor: pointer;

            box-shadow:
                0 8px 20px
                rgba(161,126,164,.18);

            transition:
                transform .3s ease,
                box-shadow .3s ease;
        }


        .save-btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 11px 25px
                rgba(161,126,164,.25);
        }


        .save-btn i {

            font-size: 14px;
        }


        /* =========================
           INFO BOX
        ========================= */

        .info-box {

            display: flex;

            align-items: flex-start;

            gap: 8px;

            margin-top: 19px;

            padding: 13px 14px;

            border-radius: 13px;

            color: #8d7b89;

            background:
                linear-gradient(
                    135deg,
                    rgba(250,242,247,.9),
                    rgba(244,240,251,.85)
                );

            border:
                1px solid
                rgba(229,216,229,.7);

            font-size: 10.5px;

            line-height: 1.65;
        }


        .info-box i {

            flex-shrink: 0;

            color: #ad829f;

            margin-top: 1px;
        }


        /* =========================
           MOBILE MENU BUTTON
        ========================= */

        .mobile-menu-btn {

            display: none;

            position: fixed;

            right: 18px;
            top: 18px;

            width: 43px;
            height: 43px;

            border: none;

            border-radius: 13px;

            color: #5c4c59;

            background:
                rgba(255,255,255,.9);

            box-shadow:
                0 8px 25px
                rgba(70,50,70,.10);

            cursor: pointer;

            z-index: 1200;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .sidebar {

                width: 220px;
            }

            .main {

                margin-left: 260px;

                padding: 38px 28px;
            }

            .content-grid {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 760px) {

            .mobile-menu-btn {

                display: flex;

                align-items: center;
                justify-content: center;
            }


            .sidebar {

                position: fixed;

                left: -270px;

                top: 15px;

                width: 245px;

                height: calc(100vh - 30px);

                transition: left .3s ease;
            }


            .sidebar.show {

                left: 15px;
            }


            .main {

                margin-left: 0;

                padding:
                    75px 18px 35px;
            }


            .page-header h1 {

                font-size: 36px;
            }


            .profile-card {

                padding: 24px;

                flex-direction: column;

                text-align: center;
            }


            .profile-info {

                width: 100%;
            }


            .content-grid {

                grid-template-columns: 1fr;
            }
        }


        @media (max-width: 480px) {

            .main {

                padding-left: 14px;
                padding-right: 14px;
            }


            .page-header h1 {

                font-size: 32px;
            }


            .card {

                padding: 21px;
            }


            .profile-card {

                border-radius: 21px;
            }


            .avatar {

                width: 75px;
                height: 75px;

                font-size: 34px;
            }
        }

    </style>

</head>

<body>


<!-- MOBILE MENU BUTTON -->

<button
    class="mobile-menu-btn"
    id="mobileMenuBtn"
    type="button"
    aria-label="Open menu"
>
    <i class="bi bi-list"></i>
</button>


<!-- =========================
     SIDEBAR
========================= -->
<?php include "includes/sidebar.php"; ?> 



<!-- =========================
     MAIN
========================= -->

<main class="main">


    <div class="page-header">

        <span class="page-label">
            ÉLORIA Administration
        </span>

        <h1>Admin Profile</h1>

        <p>
            Manage your ÉLORIA administrator account.
        </p>

    </div>


    <!-- ALERTS -->

    <?php if ($success !== ""): ?>

        <div class="alert success">

            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="alert error">

            <i class="bi bi-exclamation-circle-fill"></i>

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =========================
         PROFILE HEADER
    ========================= -->

    <section class="profile-card">

        <div class="avatar">

            <?= htmlspecialchars($initial) ?>

        </div>


        <div class="profile-info">

            <h2>
                <?= htmlspecialchars($admin['name']) ?>
            </h2>

            <p>
                <?= htmlspecialchars($admin['email']) ?>
            </p>

            <span class="admin-badge">

                <i class="bi bi-shield-check"></i>

                Administrator

            </span>

        </div>

    </section>


    <!-- =========================
         CONTENT
    ========================= -->

    <div class="content-grid">


        <!-- UPDATE PROFILE -->

        <section class="card">

            <h3>

                <i class="bi bi-person-gear"></i>

                Profile Information

            </h3>

            <p class="card-description">

                Update your administrator name and email address.

            </p>


            <form method="POST">

                <div class="form-group">

                    <label>
                        Admin Name
                    </label>

                    <div class="input-wrap">

                        <i class="bi bi-person"></i>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($admin['name']) ?>"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <div class="input-wrap">

                        <i class="bi bi-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($admin['email']) ?>"
                            required
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="bi bi-check2"></i>

                    Save Changes

                </button>

            </form>


            <div class="info-box">

                <i class="bi bi-info-circle"></i>

                <span>
                    Your updated name and email will also be
                    reflected in the admin session.
                </span>

            </div>

        </section>


        <!-- CHANGE PASSWORD -->

        <section class="card">

            <h3>

                <i class="bi bi-shield-lock"></i>

                Change Password

            </h3>

            <p class="card-description">

                Keep your administrator account secure.

            </p>


            <form method="POST">

                <div class="form-group">

                    <label>
                        Current Password
                    </label>

                    <div class="input-wrap">

                        <i class="bi bi-lock"></i>

                        <input
                            type="password"
                            name="current_password"
                            placeholder="Enter current password"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        New Password
                    </label>

                    <div class="input-wrap">

                        <i class="bi bi-key"></i>

                        <input
                            type="password"
                            name="new_password"
                            placeholder="Minimum 6 characters"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Confirm New Password
                    </label>

                    <div class="input-wrap">

                        <i class="bi bi-key-fill"></i>

                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Repeat new password"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    name="change_password"
                    class="save-btn"
                >

                    <i class="bi bi-shield-check"></i>

                    Change Password

                </button>

            </form>


            <div class="info-box">

                <i class="bi bi-lock-fill"></i>

                <span>
                    Your new password will be securely stored
                    using password hashing.
                </span>

            </div>

        </section>


    </div>

</main>


<!-- =========================
     MOBILE SIDEBAR SCRIPT
========================= -->

<script>

const mobileMenuBtn =
    document.getElementById("mobileMenuBtn");

const sidebar =
    document.getElementById("sidebar");

mobileMenuBtn.addEventListener("click", function () {

    sidebar.classList.toggle("show");

    const icon =
        mobileMenuBtn.querySelector("i");

    if (sidebar.classList.contains("show")) {

        icon.className = "bi bi-x-lg";

    } else {

        icon.className = "bi bi-list";
    }

});


document.addEventListener("click", function (event) {

    if (
        window.innerWidth <= 760 &&
        sidebar.classList.contains("show") &&
        !sidebar.contains(event.target) &&
        !mobileMenuBtn.contains(event.target)
    ) {

        sidebar.classList.remove("show");

        mobileMenuBtn
            .querySelector("i")
            .className = "bi bi-list";
    }

});

</script>

</body>

</html>

<?php
$conn->close();
?>
