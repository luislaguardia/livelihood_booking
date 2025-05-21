<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch all bookings including cancelled
$stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking History</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; }
        table { border-collapse: collapse; width: 80%; margin: auto; background: white; }
        th, td { padding: 10px; text-align: center; border: 1px solid #ccc; }
        th { background-color: #d3d3d3; }
        h2 { text-align: center; }
        .status-cancelled { color: red; }
        .status-pending { color: orange; }
        .status-done { color: green; }
    </style>
</head>
<body>

<h2>Booking History</h2>

<table>
    <tr>
        <th>Worker Name</th>
        <th>Skill</th>
        <th>Status</th>
        <th>Booking Date</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['worker_name']) ?></td>
        <td><?= htmlspecialchars($row['skill']) ?></td>
        <td class="status-<?= strtolower($row['status']) ?>">
            <?= htmlspecialchars($row['status']) ?>
        </td>
        <td><?= htmlspecialchars($row['booking_date']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<br><center><a href="home.php">← Back to Home</a></center>

</body>
</html>
