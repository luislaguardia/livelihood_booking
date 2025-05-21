<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$cancel_msg = "";

// Cancel logic
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);  // sanitize input

    // Check if booking exists and is not cancelled
    $check_sql = "SELECT * FROM bookings WHERE user_id = ? AND id = ? AND status != 'Cancelled'";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $cancel_id);
    $stmt->execute();
    $check_result = $stmt->get_result();

    if ($check_result && $check_result->num_rows > 0) {
        // Booking found and can be cancelled
        $cancel_stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
        $cancel_stmt->bind_param("i", $cancel_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
        $cancel_msg = "Booking cancelled successfully.";
    } else {
        $cancel_msg = "Booking not found or already cancelled.";
    }

    $stmt->close();
}

// Mark as Done logic
if (isset($_GET['done_id'])) {
    $done_id = intval($_GET['done_id']);  // sanitize input

    // Check if booking belongs to user and is not already done or cancelled
    $check_done = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'Approved'");
    $check_done->bind_param("ii", $done_id, $user_id);
    $check_done->execute();
    $check_result_done = $check_done->get_result();

    if ($check_result_done && $check_result_done->num_rows > 0) {
        $done_stmt = $conn->prepare("UPDATE bookings SET status = 'Done' WHERE id = ?");
        $done_stmt->bind_param("i", $done_id);
        $done_stmt->execute();
        $done_stmt->close();
        $cancel_msg = "Booking marked as done.";
    } else {
        $cancel_msg = "Booking not found or already completed.";
    }

    $check_done->close();
}

// Fetch active bookings
$stmt = $conn->prepare("SELECT b.*, s.fullname AS worker_name, s.skills AS skill
                        FROM bookings b
                        JOIN skilled_workers s ON b.worker_id = s.id
                        WHERE b.user_id = ? AND b.status NOT IN ('Cancelled', 'Done')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch history of bookings
$history_stmt = $conn->prepare("SELECT b.*, s.fullname AS worker_name, s.skills AS skill
                                 FROM bookings b
                                 JOIN skilled_workers s ON b.worker_id = s.id
                                 WHERE b.user_id = ? AND b.status IN ('Cancelled', 'Done')");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <style>
        body { font-family: Arial; background: #f0f8ff; }
        table { border-collapse: collapse; width: 80%; margin: auto; background: white; }
        th, td { padding: 10px; text-align: center; border: 1px solid #ccc; }
        th { background-color: #add8e6; }
    </style>
</head>
<body>

<h2 style="text-align:center;">My Bookings</h2>

<?php if (!empty($cancel_msg)): ?>
    <p class="message"><?= htmlspecialchars($cancel_msg) ?></p>
<?php endif; ?>

<table>
   
    <tr>
        <th>Worker Name</th>
        <th>Skill</th>
        <th>Status</th>
        <th>Booking Date</th>
        <th>Action</th>
    </tr>
   <?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['worker_name']) ?></td>
    <td><?= htmlspecialchars($row['skill']) ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>
    <td><?= $row['booking_date'] ?></td>
    <td>
        <?php if ($row['status'] == 'Pending'): ?>
            <a href="my_bookings.php?cancel_id=<?= $row['id'] ?>" onclick="return confirm('Cancel this booking?');">Cancel</a>
        <?php elseif ($row['status'] == 'Approved'): ?>
            <a href="my_bookings.php?done_id=<?= $row['id'] ?>" onclick="return confirm('Mark this booking as done?');">Done</a>
        <?php else: ?>
            —
        <?php endif; ?>
    </td>


    <td>
    <?php if ($row['status'] == 'In Progress') { ?>
        <a class="btn btn-approve" href="update_booking_status.php?id=<?= $row['id']; ?>&action=done">Mark as Done</a>
    <?php } elseif ($row['status'] == 'Done') { ?>
        <span class="status-label">Done on <?= date('Y-m-d H:i:s', strtotime($row['done_at'])); ?></span>
        <a class="btn btn-approve" href="worker_rating.php?booking_id=<?= $row['id']; ?>">Rate Worker</a>
    <?php } else { ?>
        <span class="status-label"><?= $row['status']; ?></span>
    <?php } ?>
</td>


</tr>
<?php endwhile; ?>
</table>

<div style="text-align:center; margin-top: 30px;">
    <a href="home.php" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px;
    ">← Back to Home</a>

    <a href="booking_history.php" style="
        display: inline-block;
        padding: 10px 20px;
        background-color: #2ecc71;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px;
    ">📜 View Booking History</a>
</div>

</body>
</html>
