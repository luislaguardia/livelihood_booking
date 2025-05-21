<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete booking permanently from trash history
    $delete_trash = "DELETE FROM trash_history WHERE booking_id=$id";
    mysqli_query($conn, $delete_trash);

    header("Location: trash_history.php?status=success");
    exit();
}
?>
