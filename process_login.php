<?php
session_start();
include 'db.php';

// Check if POST data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, password, role, is_approved FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_name, $db_password, $user_role, $is_approved);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            if ($user_role === 'admin' && $is_approved != 1) {
                header("Location: login.php?error=Your admin account is pending approval.");
                exit();
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
            header("Location: login.php?error=Invalid credentials!");
            exit();
        }
    } else {
        header("Location: login.php?error=User not found!");
        exit();
    }
} else {
    // If accessed directly, redirect to login page
    header("Location: login.php");
    exit();
}
