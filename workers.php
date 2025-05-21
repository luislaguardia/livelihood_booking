<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Display welcome message
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";
?>

<h3>Booking Form</h3>
<form action="process_booking.php" method="POST">
    <label for="service">Service:</label>
    <input type="text" id="service" name="service" required><br><br>

    <label for="date">Preferred Date:</label>
    <input type="date" id="date" name="date" required><br><br>

    <label for="time">Preferred Time:</label>
    <input type="time" id="time" name="time" required><br><br>

    <button type="submit">Submit Booking</button>
</form>

<a href="logout.php">Logout</a>
