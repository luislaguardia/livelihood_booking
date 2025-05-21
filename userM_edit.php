<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_management.php");
    exit();
}

$user_id = intval($_GET['id']);
$error = '';
$success = '';

// Fetch user data for the form
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // User not found
    header("Location: user_management.php");
    exit();
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $status = trim($_POST['status']);

    if (empty($name) || empty($email) || empty($status)) {
        $error = "Please fill in all fields.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email is already used by another user
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email is already taken by another user.";
        } else {
            // Update user data
            $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ?, status = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $name, $email, $status, $user_id);

            if ($updateStmt->execute()) {
                $success = "User updated successfully.";
                // Refresh user data
                $user['name'] = $name;
                $user['email'] = $email;
                $user['status'] = $status;
            } else {
                $error = "Error updating user: " . $conn->error;
            }
        }
        $checkStmt->close();
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit User</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f4f9;
        padding: 20px;
        max-width: 600px;
        margin: auto;
    }
    h1 {
        text-align: center;
        color: #2c3e50;
    }
    form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }
    input[type="text"], input[type="email"], select {
        width: 100%;
        padding: 8px;
        margin-top: 4px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .btn {
        margin-top: 15px;
        padding: 10px 15px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn:hover {
        background-color: #2980b9;
    }
    .message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
    }
    .error {
        background-color: #e74c3c;
        color: white;
    }
    .success {
        background-color: #2ecc71;
        color: white;
    }
    .back-link {
        display: inline-block;
        margin-bottom: 15px;
        text-decoration: none;
        color: #3498db;
    }
    .back-link:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<a href="user_management.php" class="back-link">&larr; Back to User Management</a>

<h1>Edit User</h1>

<?php if ($error): ?>
    <div class="message error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="message success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" action="">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required />

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />

    <label for="status">Status:</label>
    <select name="status" id="status" required>
        <option value="">-- Select Status --</option>
        <option value="active" <?php if($user['status'] == 'active') echo 'selected'; ?>>Active</option>
        <option value="inactive" <?php if($user['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
        <option value="pending" <?php if($user['status'] == 'pending') echo 'selected'; ?>>Pending</option>
    </select>

    <button type="submit" class="btn">Update User</button>
</form>

</body>
</html>
