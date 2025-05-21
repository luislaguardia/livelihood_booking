<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pag-handle ng approve, decline o trash action
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = '';

    if ($action == 'approve') {
        $status = 'Approved';
        $update = "UPDATE bookings SET status='$status', approved_at=NOW() WHERE id=$id"; // Update approved_at
    } elseif ($action == 'decline') {
        $status = 'Cancelled';
    } elseif ($action == 'trash') {
        $status = 'Trash';
    } elseif ($action == 'restore') {
        $status = 'Pending'; // Restore trashed booking to Pending
    } elseif ($action == 'delete') {
        // Delete the booking permanently from trash_history and bookings
        $delete_history = "DELETE FROM trash_history WHERE booking_id=$id";
        $delete_booking = "DELETE FROM bookings WHERE id=$id";
        // First delete from trash_history, then from bookings
        if (mysqli_query($conn, $delete_history) && mysqli_query($conn, $delete_booking)) {
            header("Location: update_booking_status.php");
            exit();
        } else {
            echo "Error deleting booking: " . mysqli_error($conn) . "<br>";
        }
    }

    if (!empty($status)) {
        // Update the booking status
        if ($status == 'Trash' || $status == 'Cancelled') {
            $update = "UPDATE bookings SET status='$status' WHERE id=$id";
        } elseif ($status == 'Pending') {
            $update = "UPDATE bookings SET status='$status' WHERE id=$id";
        }

        if (mysqli_query($conn, $update)) {
            echo "Booking status updated successfully.<br>";
        } else {
            echo "Error updating booking: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Invalid action or update query missing.<br>";
        // If booking is trashed, move to trash_history
        if ($status == 'Trash') {
            $select_booking = "SELECT id, customer_name, service, worker_name, booking_date FROM bookings WHERE id=$id";
            $result = mysqli_query($conn, $select_booking);
            $row = mysqli_fetch_assoc($result);

            $history_insert = "INSERT INTO trash_history (booking_id, customer_name, service, worker_name, booking_date, status)
                               VALUES ({$row['id']}, '{$row['customer_name']}', '{$row['service']}', '{$row['worker_name']}', '{$row['booking_date']}', 'Trash')";
            if (mysqli_query($conn, $history_insert)) {
                echo "Booking moved to trash history.<br>";
            } else {
                echo "Error inserting into trash history: " . mysqli_error($conn) . "<br>";
            }
        }

        header("Location: update_booking_status.php");
        exit();
    }
}

// Handle the "done" action
if (isset($_GET['id']) && $_GET['action'] == 'done') {
    $id = $_GET['id'];
    $status = 'Done';
    $done_at = date('Y-m-d H:i:s'); // Get current date and time
    $update = "UPDATE bookings SET status='$status', done_at='$done_at' WHERE id=$id";
    
    if (mysqli_query($conn, $update)) {
        header("Location: update_booking_status.php");
        exit();
    } else {
        echo "Error updating booking: " . mysqli_error($conn) . "<br>";
    }
}

// Retrieve all active bookings from database
$sql = "SELECT id, customer_name, service, worker_name, booking_date, status, done_at FROM bookings WHERE status != 'Trash' ORDER BY booking_date DESC";
$trash_sql = "SELECT id, customer_name, service, worker_name, booking_date, status FROM bookings WHERE status = 'Trash' ORDER BY booking_date DESC";
$result = mysqli_query($conn, $sql);
$trash_result = mysqli_query($conn, $trash_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .table-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ccc;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-decline {
            background-color: #dc3545;
        }

        .btn-trash {
            background-color: #6c757d;
        }

        .btn-back {
            background-color: #6c757d;
            margin-top: 20px;
            display: inline-block;
        }

        .center {
            text-align: center;
        }

        .status-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="table-container">
        <h2>Booking Management</h2>

        <!-- Active Bookings -->
        <h3>Active Bookings</h3>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <p class="center">No active bookings found.</p>
        <?php else: ?>
        <table>
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
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                <td><?= htmlspecialchars($row['service']); ?></td>
                <td><?= htmlspecialchars($row['worker_name']); ?></td>
                <td><?= $row['booking_date']; ?></td>
                <td class="status-label"><?= $row['status']; ?></td>
                <td>
                    <?php if ($row['status'] == 'Pending') { ?>
                        <a class="btn btn-approve" href="update_booking_status.php?id=<?= $row['id']; ?>&action=approve">Approve</a>
                        <a class="btn btn-decline" href="update_booking_status.php?id=<?= $row['id']; ?>&action=decline">Decline</a>
                    <?php } elseif ($row['status'] == 'In Progress') { ?>
                        <a class="btn btn-approve" href="update_booking_status.php?id=<?= $row['id']; ?>&action=done">Mark as Done</a>
                    <?php } elseif ($row['status'] == 'Done') { ?>
                        <span class="status-label">Done on <?= date('Y-m-d H:i:s', strtotime($row['done_at'])); ?></span>
                    
                        <a class="btn btn-trash" href="update_booking_status.php?id=<?= $row['id']; ?>&action=trash">Move to Trash</a>
                    <?php } else { ?>
                        <span class="status-label"><?= $row['status']; ?></span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php endif; ?>

        <!-- Trashed Bookings -->
        <h3>Trashed Bookings</h3>
        <?php if (mysqli_num_rows($trash_result) == 0): ?>
            <p class="center">No trashed bookings found.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Worker</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($trash_result)) { ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['customer_name']); ?></td>
                <td><?= htmlspecialchars($row['service']); ?></td>
                <td><?= htmlspecialchars($row['worker_name']); ?></td>
                <td><?= $row['booking_date']; ?></td>
                <td class="status-label"><?= $row['status']; ?></td>
                <td>
                    <a class="btn btn-trash" href="update_booking_status.php?id=<?= $row['id']; ?>&action=restore">Restore</a> |
                    <a class="btn btn-decline" href="update_booking_status.php?id=<?= $row['id']; ?>&action=delete">Delete Permanently</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php endif; ?>

        <div class="center">
            <a href="admin_dashboard.php" class="btn btn-back">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
