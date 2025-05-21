<?php
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = "customer";  // ✅ always sets role to "customer"

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email already registered!";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $message = "User successfully registered!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="register.php">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" id="reg_password" name="password" placeholder="Password" required>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

            <!-- Show Password checkbox beside label -->
            <label style="display: flex; align-items: center; gap: 5px; margin: 5px 0;">
                <input type="checkbox" onclick="toggleRegisterPassword()"> Show Password
            </label>

            <!-- Hidden Field for Admin Registration -->

            <button type="submit">Register</button>
        </form>

        <p style="color:red;"><?php echo $message; ?></p>
        <p style="margin-top: 20px;">Already have an account? <a href="login.php">Login here</a></p>
        <p>Are you an admin? <a href="admin_register.php">Register as Admin</a></p>
    </div>

    <script>
        function toggleRegisterPassword() {
            var p1 = document.getElementById("reg_password");
            var p2 = document.getElementById("confirm_password");
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
        