<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin' AND is_approved = 0");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: pending_admins.php?declined=1");
    } else {
        echo "Error declining admin.";
    }
}
?>
