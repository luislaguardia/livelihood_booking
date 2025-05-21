<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$home_link = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'home.php';
?>
