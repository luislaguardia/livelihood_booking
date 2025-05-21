<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, name, password, role, is_approved FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_name, $db_password, $user_role, $is_approved);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            // Check if the admin account is approved
            if ($user_role === 'admin' && $is_approved != 1) {
                $message = "Your admin account is pending approval.";
            } else {
                $_SESSION["user_id"] = $user_id;
                $_SESSION["name"] = $db_name;
                $_SESSION["role"] = $user_role;

                // Redirect based on role
                if ($user_role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            }
        } else {
            $message = "Invalid credentials!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
<div class="login-container">
    <h2>Login</h2>
    <form method="POST" action="loadingscreen.php">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" id="login_password" name="password" placeholder="Password" required>
        
        <div class="checkbox-container">
            <label style="display: flex; align-items: center; gap: 5px; margin: 5px 0;">
                <input type="checkbox" onclick="toggleLoginPassword()" id="showPassword"> Show Password
            </label>
        </div>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($_GET['error'])): ?>
        <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<style>
    body {
        margin: 0;
        padding: 0;
        background: url('uploads/LM.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: Arial, sans-serif;
    }

    .login-container {
        width: 400px;
        margin: 100px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    input, button {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        font-size: 16px;
    }

   .checkbox-container {
  display: flex;
  align-items: center;
  gap: 8px; /* space between checkbox and label */
  margin-top: -5px;
  margin-bottom: 10px;
}
.checkbox-container input[type="checkbox"] {
  margin: 0;
  cursor: pointer;
}
.checkbox-container label {
  cursor: pointer;
  font-size: 14px;
  margin: 0;
  user-select: none;
}


    label {
        font-size: 14px;
        margin: 0; /* No extra margin */
    }
</style>

<script>
function toggleLoginPassword() {
  var x = document.getElementById("login_password");
  x.type = (x.type === "password") ? "text" : "password";
}
</script>
</body>
</html>
