<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Select the booking from trash history
    $select_booking = "SELECT * FROM trash_history WHERE booking_id=$id";
    $result = mysqli_query($conn, $select_booking);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Check if the booking already exists in the active bookings table
        $check_existing = "SELECT * FROM bookings WHERE id = {$row['booking_id']}";
        $existing = mysqli_query($conn, $check_existing);

        if (mysqli_num_rows($existing) == 0) {
            // Insert the booking back to active bookings table
            $insert_booking = "INSERT INTO bookings (id, customer_name, service, worker_name, booking_date)
                               VALUES ({$row['booking_id']}, '{$row['customer_name']}', '{$row['service']}', '{$row['worker_name']}', '{$row['booking_date']}')";
            if (mysqli_query($conn, $insert_booking)) {
                // Delete from trash
                $delete_trash = "DELETE FROM trash_history WHERE booking_id=$id";
                mysqli_query($conn, $delete_trash);

                header("Location: trash_history.php?status=success");
                exit();
            } else {
                echo "Error restoring booking: " . mysqli_error($conn);
            }
        } else {
            echo "Booking already exists in active bookings.";
        }
    } else {
        echo "Booking not found in trash history.";
    }
}
?>
