<?php
session_start();
include 'db.php';

// Check if admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Get skilled workers
$result = $conn->query("SELECT * FROM skilled_workers"); // Make sure this table exists
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Skilled Workers</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        a.button { background: #333; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; }
        a.button:hover { background: #555; }
    </style>
</head>
<body>
    <h1>Skilled Workers Management</h1>
    <a class="button" href="admin_dashboard.php">← Back to Dashboard</a>
    
    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fullname</th>
            <th>Skills</th>
            <th>Contact Info</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($worker = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $worker['id']; ?></td>
            <td><?= htmlspecialchars($worker['fullname'] ?? '') ?></td>
            <td><?= htmlspecialchars($worker['skills'] ?? '') ?></td>
            <td><?= htmlspecialchars($worker['contact_info'] ?? '') ?></td>
            <td><?= htmlspecialchars($worker['status'] ?? '') ?></td>
            <td>
                <a href="edit_worker.php?id=<?php echo $worker['id']; ?>">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
