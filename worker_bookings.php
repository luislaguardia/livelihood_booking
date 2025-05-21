<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$worker_name = $_SESSION["name"];  // Assuming skilled workers login and store their name in session

$sql = "SELECT * FROM bookings WHERE worker_name = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $worker_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Pending Bookings for <?php echo $worker_name; ?></h2>
<table border="1">
    <tr>
        <th>User ID</th>
        <th>Skill</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['user_id'] ?></td>
        <td><?= $row['skill'] ?></td>
        <td><?= $row['status'] ?></td>
        <td>
            <form method="post" action="update_booking_status.php">
                <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                <button name="action" value="accept">Accept</button>
                <button name="action" value="decline">Decline</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
