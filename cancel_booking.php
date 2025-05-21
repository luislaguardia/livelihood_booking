<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($booking_user_id);
    $stmt->fetch();
    $stmt->close();

    if ($booking_user_id == $user_id) {
        // Update status to 'Cancelled'
        $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Booking cancelled successfully.";
    } else {
        $_SESSION['message'] = "Unauthorized cancellation attempt.";
    }
}

header("Location: my_bookings.php");
exit();
