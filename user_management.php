<?php
session_start();
include 'db.php'; // I-connect ang database

// Check kung admin ang logged-in user
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Kunin ang listahan ng lahat ng users mula sa database
$stmt = $conn->prepare("SELECT * FROM users");
// Kunin ang listahan ng skilled workers
$skilledStmt = $conn->prepare("SELECT * FROM skilled_workers WHERE status IS NULL OR status != 'approved'");
$skilledStmt->execute();
$skilledWorkers = $skilledStmt->get_result();
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        /* General Reset */
        body, h1, h2, table, th, td {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        body {
            background-color: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        h1 {
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
            color: #2c3e50;
        }

        /* Back Button */
        .back-button {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #2980b9;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:hover {
            background-color: #ecf0f1;
        }

        /* Action Links */
        .actions a {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            background-color: #2ecc71;
            color: white;
            margin-right: 8px;
            transition: background-color 0.3s ease;
        }

        .actions a:hover {
            background-color: #27ae60;
        }

        .actions a.delete {
            background-color: #e74c3c;
        }

        .actions a.delete:hover {
            background-color: #c0392b;
        }

        /* Responsive Table for smaller screens */
        @media (max-width: 768px) {
            table {
                width: 100%;
            }
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Back Button -->
    <a href="admin_dashboard.php" class="back-button">Back to Admin Dashboard</a>

    <h1>User Management</h1>

    <!-- Users table -->
    <h1>Skilled Worker Approval</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Skills</th>
            <th>Contact</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($worker = $skilledWorkers->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $worker['id']; ?></td>
                <td><?php echo $worker['fullname']; ?></td>
                <td><?php echo $worker['skills']; ?></td>
                <td><?php echo $worker['contact_info']; ?></td>
                <td><?php echo $worker['price']; ?></td>
                <td><?php echo $worker['status'] ?? 'Pending'; ?></td>
                <td class="actions">
                    <a href="approve_worker.php?id=<?php echo $worker['id']; ?>">Approve</a>
                    <a href="decline_worker.php?id=<?php echo $worker['id']; ?>" class="delete">Decline</a>
                    <a href="userM_edit.php?id=<?php echo $user['id']; ?>">Edit</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['status']; ?></td>
                    <td class="actions">
                        <!-- Actions for the user, like Edit/Delete -->
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
