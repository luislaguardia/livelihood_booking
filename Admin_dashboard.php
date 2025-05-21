<?php
session_start();
include 'db.php'; // Database connection

// Check if the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not an admin
    exit();
}

// Get total earnings from completed ('Done') bookings
$stmt = $conn->prepare("
    SELECT SUM(s.price) AS total_earnings
    FROM bookings b
    JOIN skilled_workers s ON b.worker_id = s.id
    WHERE b.status = 'Done'
");
$stmt->execute();
$stmt->bind_result($total_earnings);
$stmt->fetch();
$stmt->close();


// Get total users count (from users table)
$result = $conn->query("SELECT COUNT(*) FROM users WHERE id != 1");
$stmt = $conn->prepare("SELECT * FROM users WHERE id != 1");
$row = $result->fetch_row();
$total_users = $row[0];

// Get active bookings count
$stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
$stmt->execute();
$stmt->bind_result($active_bookings);
$stmt->fetch();
$stmt->close();

// Get total earnings from completed bookings
$stmt = $conn->prepare("SELECT SUM(earnings) FROM bookings WHERE status = 'Completed'");
$stmt->execute();
$stmt->bind_result($total_earnings);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <a href="logout.php" style="
    display: inline-block;
    background-color:rgb(184, 192, 235);
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    margin-left: 20px;
    transition: background-color 0.3s ease;
" onmouseover="this.style.backgroundColor='#c82333'" onmouseout="this.style.backgroundColor='#dc3545'">
    🔒 Logout
</a>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="user_management.php">Users</a></li>
                <li><a href="update_booking_status.php">Bookings</a></li>
                <li><a href="manage_workers.php">Skilled Workers</a></li> <!-- Link updated to manage_workers.php -->
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="admin_settings.php">Settings</a></li>
                <li><a href="pending_admins.php">Pending Admins</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Welcome, Admin</h1>
            <p>Here’s a quick overview of your platform:</p>

            <div class="stats">
                <div class="stat-box">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Active Bookings</h3>
                    <p><?php echo $active_bookings; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Earnings</h3>
                    <p>₱<?php echo number_format($total_earnings, 2); ?></p>
                </div>
            </div>

            <h2>Pending Admins</h2>
            <table>
                <tr>
                    <th>Admin ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
                <!-- Assuming we already fetched the pending admin data in the previous code -->
            </table>

        </div>
    </div>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 20px;
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 10px 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            background-color: #444;
        }
        .sidebar ul li a:hover {
            background-color: #555;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
        }
        .stat-box {
            background-color: #f4f4f4;
            padding: 20px;
            width: 30%;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</body>
</html>
