<?php
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);


$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $message = "Passwords do not match!";
    } else {

// Check how many admins are already in the table
$check_admins = $conn->prepare("SELECT id FROM admins");
$check_admins->execute();
$check_admins->store_result();

$is_approved = ($check_admins->num_rows == 0) ? 1 : 0; // First admin is auto-approved

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

// Insert into admins table instead of users table
$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    if ($is_approved) {
        header("Location: login.php?registered=admin");
        exit(); // ok kasi diretso sa login
    } else {
// For subsequent admins, save them in users table with pending status
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'pending_admin')");
$stmt->bind_param("sss", $name, $email, $hashed_password);

if ($stmt->execute()) {
    $message = "Successfully sent request. Waiting for admin approval.";
} else {
    $message = "Error: " . $stmt->error;
}
        // Do not exit here — kailangan niya bumalik sa registration form with message
    }
} else {
    $message = "Error: " . $stmt->error;
}

        }
    }

// Assume $user_id is the ID of the user who is being approved as admin
// Step 1: Fetch user data from users table
$stmt = $conn->prepare("SELECT name, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $hashed_password);
$stmt->fetch();
$stmt->close();

// Step 2: Insert user data into admins table
$insert_stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$insert_stmt->bind_param("sss", $name, $email, $hashed_password);
$insert_stmt->execute();
$insert_stmt->close();

// Step 3: Delete the user from users table
$delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete_stmt->bind_param("i", $user_id);
$delete_stmt->execute();
$delete_stmt->close();



?>

<!-- Admin Registration Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Admin Registration</h2>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" id="admin_password" placeholder="Password" required>
        <input type="password" name="confirm_password" id="admin_confirm_password" placeholder="Confirm Password" required>

        <!-- Show Password checkbox beside label -->
        <label style="display: flex; align-items: center; gap: 5px; margin: 5px 0;">
            <input type="checkbox" onclick="toggleAdminPassword()"> Show Password
        </label>

        <button type="submit">Register as Admin</button>
    </form>
    <p style="color:red;"><?php echo $message; ?></p>
    <p style="margin-top: 5px;">Already have an account? <a href="login.php">Login here</a></p>
</div>

<script>
    // Function to toggle password visibility for admin registration
    function toggleAdminPassword() {
        var p1 = document.getElementById("admin_password");
        var p2 = document.getElementById("admin_confirm_password");
        if (p1.type === "password" || p2.type === "password") {
            p1.type = "text";
            p2.type = "text";
        } else {
            p1.type = "password";
            p2.type = "password";
        }
    }
</script>
</body>
</html>

