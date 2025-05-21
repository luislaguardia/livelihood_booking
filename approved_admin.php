<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $admin_to_approve = intval($_POST['id']);
    $approved_by = $_SESSION['user_id']; // current logged-in admin

    $stmt = $conn->prepare("UPDATE users SET is_approved = 1, approved_by = ? WHERE id = ?");
    $stmt->bind_param("ii", $approved_by, $admin_to_approve);

    if ($stmt->execute()) {
        header("Location: pending_admins.php?approved=1");
    } else {
        echo "Error approving admin.";
    }
}
?>
