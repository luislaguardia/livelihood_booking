<?php
session_start();
include 'db.php';

// I-check kung may booking_id sa URL
if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php?message=Invalid request.");
    exit();
}

$booking_id = (int)$_GET['booking_id'];

// Kunin ang worker_id mula sa booking
$booking_query = $conn->prepare("SELECT worker_id FROM bookings WHERE id = ?");
$booking_query->bind_param("i", $booking_id);
$booking_query->execute();
$booking_result = $booking_query->get_result();

if ($booking_result->num_rows === 0) {
    header("Location: my_bookings.php?message=Booking not found.");
    exit();
}

$booking = $booking_result->fetch_assoc();
$worker_id = $booking['worker_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // I-insert ang rating sa database
    $stmt = $conn->prepare("INSERT INTO ratings (worker_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $worker_id, $customer_id, $rating, $comment);
    
    if ($stmt->execute()) {
        // Redirect back to my bookings or show success message
        header("Location: my_bookings.php?message=Rating submitted successfully.");
        exit();
    } else {
        // Handle error
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Worker</title>
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

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Rate Worker</h2>
        <form method="POST" action="">
            <label for="rating">Rating:</label>
            <select name="rating" required>
                <option value="">Select a rating</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
            <label for="comment">Comment:</label>
            <textarea name="comment" placeholder="Leave a comment (optional)"></textarea>
            <button type="submit">Submit Rating</button>
        </form>
    </div>
</body>
</html>
