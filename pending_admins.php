<?php
session_start();
include 'db.php';

// Check if user is logged in and if they are an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Debugging: Check if session variables are set
// var_dump($_SESSION);  // Uncomment this line to check the session variables

// Query to get pending admins (admins with is_approved = 0)
$result = $conn->query("SELECT id, name, email FROM users WHERE role = 'admin' AND is_approved = 0");

// Check if query failed
if (!$result) {
    die('Error executing query: ' . $conn->error);  // If there's an error with the query
}

// Fetch all pending admins
$admins = $result->fetch_all(MYSQLI_ASSOC);

// Debugging: Check what data is fetched from the database
// var_dump($admins);  // Uncomment this line to check what’s returned from the query

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Admin Approvals</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #2c3e50;
        }

        .admin-card {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .admin-card:last-child {
            border-bottom: none;
        }

        .admin-info {
            font-size: 16px;
            color: #34495e;
        }

        .buttons form {
            display: inline-block;
        }

        .buttons button {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .approve-btn {
            background-color: #27ae60; /* Green */
        }

        .approve-btn:hover {
            background-color: #1e8449;
            transform: scale(1.05);
        }

        .decline-btn {
            background-color: #e74c3c; /* Red */
        }

        .decline-btn:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }

        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #1a252f;
        }

        @media (max-width: 600px) {
            .buttons {
                display: block;
                text-align: center;
            }

            .buttons button {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Pending Admin Approvals</h2>

        <?php if (empty($admins)): ?>
            <p>No pending admin requests.</p>
        <?php else: ?>
            <?php foreach ($admins as $admin): ?>
                <div class="admin-card">
                    <div class="admin-info">
                        <strong><?= htmlspecialchars($admin['name']) ?></strong> (<?= htmlspecialchars($admin['email']) ?>)
                    </div>
                    <div class="buttons">
                        <form method="post" action="approve_admin.php">
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                        <form method="post" action="decline_admin.php">
                            <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                            <button type="submit" class="decline-btn">Decline</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

</body>
</html>
