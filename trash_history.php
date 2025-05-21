<?php
session_start();
include('db.php');

// Check kung naka-login at admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Kunin lahat ng na-cancelled bookings sa trash_history
$sql = "SELECT * FROM trash_history ORDER BY booking_date DESC";
$result = mysqli_query($conn, $sql);

// Check kung may records sa trash history
if (mysqli_num_rows($result) == 0) {
    echo "No cancelled bookings found in trash.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trash History</title>
</head>
<body>
    <h2>Trash History</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Worker</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['booking_id']; ?></td>
            <td><?= htmlspecialchars($row['customer_name']); ?></td>
            <td><?= htmlspecialchars($row['service']); ?></td>
            <td><?= htmlspecialchars($row['worker_name']); ?></td>
            <td><?= $row['booking_date']; ?></td>
            <td><?= $row['status']; ?></td>
            <td>
                <a href="restore_booking.php?id=<?= $row['booking_id']; ?>">Restore</a> |
                <a href="delete_permanently.php?id=<?= $row['booking_id']; ?>">Delete Permanently</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
