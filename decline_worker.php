<?php
include 'db.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE skilled_workers SET status='declined' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: user_management.php");
exit();
?>
