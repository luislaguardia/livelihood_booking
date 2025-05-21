<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: home.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Fetch current user info
$stmt = $conn->prepare("SELECT name, email, password, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $hashed_password, $profile_picture);
$stmt->fetch();
$stmt->close();

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST["name"];
    $new_email = $_POST["email"];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check === false) {
            $message = "❌ File is not an image.";
        } elseif (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Update profile picture path in database
            $update_picture = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $update_picture->bind_param("si", $target_file, $user_id);
            $update_picture->execute();
            $update_picture->close();
        } else {
            $message = "❌ Error uploading image.";
        }
    }

    // Validate current password
    if (!password_verify($current_password, $hashed_password)) {
        $message = "❌ Current password is incorrect.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $message = "❌ New passwords do not match.";
    } else {
        // Update profile
        if (!empty($new_password)) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $update->bind_param("sssi", $new_name, $new_email, $new_hashed, $user_id);
        } else {
            $update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update->bind_param("ssi", $new_name, $new_email, $user_id);
        }

        // Execute update and check for success
        if ($update->execute()) {
            $message = "✅ Profile updated successfully!";
            $_SESSION["name"] = $new_name; // Update session name
        } else {
            $message = "❌ Error updating profile.";
        }
        $update->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef0f4;
            margin: 0;
            padding: 0;
        }

        .settings-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        h3 {
            margin-top: 30px;
            color: #444;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            color: #e74c3c;
        }

        .message.success {
            color: green;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
</head>
<body>
    <div class="settings-container">
        <h2>Admin Settings</h
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef0f4;
            margin: 0;
            padding: 0;
        }

        .settings-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        h3 {
            margin-top: 30px;
            color: #444;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            color: #e74c3c;
        }

        .message.success {
            color: green;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <h2>Admin Settings</h2>

        <form method="POST">
            <h3>Profile Info</h3>
            <input type="text" name="name" value="<?= htmlspecialchars($name); ?>" required>
            <input type="email" name="email" value="<?= htmlspecialchars($email); ?>" required>

            <h3>Change Password (optional)</h3>
            <input type="password" name="new_password" placeholder="New Password">
            <input type="password" name="confirm_password" placeholder="Confirm New Password">

            <h3>Confirm Changes</h3>
            <input type="password" name="current_password" placeholder="Current Password (required)" required>

            <button type="submit">Save Changes</button>
        </form>
          

        <form method="POST" enctype="multipart/form-data">
  <!-- Existing inputs -->
  <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
  <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

  <!-- Para sa profile picture upload -->
  <label for="profile_picture">Profile Picture:</label>
  <input type="file" name="profile_picture" id="profile_picture" accept="image/*">




  

  <!-- Password change inputs etc. here -->

  <button type="submit">Save Changes</button>
</form>


        <p class="message <?= strpos($message, '✅') !== false ? 'success' : '' ?>"><?= $message; ?></p>

        <a class="back-link" href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>
