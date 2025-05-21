<?php
include 'db.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$is_admin = $_SESSION["role"] === "admin"; // Make sure 'role' is set in session

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback</title>
  <link rel="stylesheet" href="style.css">
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 20px;
    }
    table, th, td {
      border: 1px solid #ccc;
      padding: 8px;
    }
  </style>
</head>
<body>
<div class="feedback-container">

<?php if ($is_admin): ?>
  <h2>User Feedback</h2>
  <?php
    $result = $conn->query("SELECT name, message, email, created_at FROM feedback ORDER BY created_at DESC");
    if ($result->num_rows > 0):
  ?>
  <table>
    <tr><th>Name</th><th>Message</th><th>Email</th><th>Submitted At</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['message']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= $row['created_at'] ?? '-' ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
  <?php else: ?>
    <p>No feedback yet.</p>
  <?php endif; ?>

<?php else: ?>
  <h2>Send us your Feedback</h2>

  <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        $stmt = $conn->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Thank you for your feedback!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
  ?>

  <form action="feedback.php" method="POST">
    <input type="text" name="name" placeholder="Your Name" required>
    <textarea name="message" placeholder="Write your feedback here..." required></textarea>
    <input type="email" name="email" placeholder="Your Email (optional)">
    <button type="submit">Submit</button>
  </form>
<?php endif; ?>

  <div class="back-btn">
    <?php include 'header.php'; ?>
    <a href="<?php echo $home_link; ?>">Back to Home</a>
  </div>
</div>
</body>
</html>