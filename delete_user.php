<?php
session_start();
include 'db.php'; // I-connect ang database

// Check kung admin ang logged-in user
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Kunin ang user ID mula sa URL at i-delete ang user
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: user_management.php");
        exit();
    } else {
        echo "Error deleting user!";
    }
}
?>
