<?php
session_start(); // Kung gusto mong may session na user

// Kailangan mong i-connect sa database
include 'db.php';

// Kunin ang mga messages mula sa database
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);



// Mag-check kung nag-submit ng form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kunin ang mga field mula sa form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // I-save ang message sa database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        echo "Message sent successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}


// Kung hindi naka-login, ibalik sa login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["name"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Contact Us</h2>

        <!-- Your contact form here -->
        <form action="contact.php" method="POST">
    <label for="name">Name:</label><br>
    <input type="text" id="name" name="name" required><br><br>
    
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>
    
    <label for="message">Message:</label><br>
    <textarea id="message" name="message" required></textarea><br><br>
    
    <button type="submit">Send Message</button>
</form>


        <!-- Back Button -->
        <br>
        <a href="home.php"><button>Back to Home</button></a>

    </div>
</body>
</html>
