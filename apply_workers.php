<?php
session_start();
include 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION["name"] ?? "";
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skills = $_POST["skills"];
    $contact_info = $_POST["contact_info"];
    $price = $_POST["price"];
    $category = $_POST["category"];

    $stmt = $conn->prepare("INSERT INTO skilled_workers (fullname, skills, contact_info, price, category, status, is_featured)
                            VALUES (?, ?, ?, ?, ?, 'Pending', 0)");
    $stmt->bind_param("sssss", $fullname, $skills, $contact_info, $price, $category);

    if ($stmt->execute()) {
        $success = "✅ Application submitted! Please wait for admin approval.";
    } else {
        $error = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Apply as Skilled Worker</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f4f4f4;
    }
    h2 {
      text-align: center;
    }
    form {
      max-width: 500px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    label {
      font-weight: bold;
      margin-bottom: 5px;
      display: block;
    }
    input, select, textarea {
      width: 100%;
      margin-bottom: 15px;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      padding: 10px 20px;
      background: #27ae60;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background: #219150;
    }
    .message {
      max-width: 500px;
      margin: 10px auto;
      padding: 10px;
      text-align: center;
      font-weight: bold;
      border-radius: 6px;
    }
    .success {
      background-color: #d4edda;
      color: #155724;
    }
    .error {
      background-color: #f8d7da;
      color: #721c24;
    }
    .back-btn {
      display: block;
      text-align: center;
      margin: 20px auto;
      background-color: #3498db;
      color: white;
      padding: 10px 20px;
      width: 200px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }
    .back-btn:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>

  <h2>Apply as Skilled Worker</h2>

  <?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Full Name:</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($fullname) ?>" readonly>

    <label>Skills (comma separated):</label>
    <input type="text" name="skills" placeholder="e.g., Plumbing, Welding" required>

    <label>Contact Info:</label>
    <input type="text" name="contact_info" placeholder="e.g., 09123456789" required>

    <label>Price (₱):</label>
    <input type="number" step="0.01" name="price" placeholder="e.g., 500.00" required>

    <label>Category:</label>
    <select name="category" required>
      <option value="">Select Category</option>
      <option value="Plumbing">Plumbing</option>
      <option value="Electrical">Electrical</option>
      <option value="Metal Works">Metal Works</option>
      <option value="Construction">Construction</option>
    </select>

    <button type="submit">Submit Application</button>
  </form>

  <!-- Back to Dashboard Button -->
  <a href="home.php" class="back-btn">⬅ Back to Dashboard</a>

</body>
</html>