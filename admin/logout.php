
<?php

session_start();

/* =========================
   REMOVE ADMIN SESSION
========================= */

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

/* =========================
   DESTROY SESSION
========================= */

session_unset();
session_destroy();

/* =========================
   REDIRECT TO LOGIN
========================= */

header("Location: login.php");
exit;

?>

