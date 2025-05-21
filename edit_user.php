<?php
session_start();
include 'db.php'; // I-connect ang database

// Check kung admin ang logged-in user
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Kunin ang user ID mula sa URL
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $query = "SELECT id, name, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Kung walang user, i-redirect
if (!$user) {
    header("Location: user_management.php");
    exit();
}

// Pag-save ng mga binagong data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update the user
    $update_query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $name, $email, $role, $user_id);

    if ($stmt->execute()) {
        header("Location: user_management.php");
        exit();
    } else {
        $message = "Error updating user!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Edit User</h1>

    <form method="POST">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>

        <label for="role">Role</label>
        <select name="role" id="role">
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>

   <!-- Back Button -->
<a href="user_management.php" class="back-button">Back to User Management</a>

</div>

</body>
</html>

