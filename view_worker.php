<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if (!isset($_GET['category']) || empty(trim($_GET['category']))) {
    echo "No category selected.";
    exit();
}

$category = strtolower(trim($_GET['category']));
$escapedCategory = mysqli_real_escape_string($conn, $category);

$sql = "SELECT * FROM skilled_workers 
        WHERE LOWER(skills) LIKE '%$escapedCategory%' 
        AND status = 'approved'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Worker - <?php echo htmlspecialchars($category); ?></title>
  <style>
    body { font-family: Arial; background: #f8f9fa; margin: 0; padding: 20px; }
    .top-bar { max-width: 900px; margin: auto; }
    .worker-list { max-width: 900px; margin: auto; display: flex; flex-wrap: wrap; gap: 25px; justify-content: center; }
    .worker-card { background: white; border-radius: 12px; box-shadow: 0 0 8px rgba(0,0,0,0.1); width: 250px; padding: 20px; text-align: center; }
    .worker-card img { max-width: 100%; height: 160px; object-fit: cover; border-radius: 10px; margin-bottom: 15px; }
    .worker-name { font-weight: bold; font-size: 1.2rem; margin-bottom: 8px; }
    .worker-skills { color: #666; font-size: 0.9rem; margin-bottom: 12px; }
    .worker-contact { font-size: 0.9rem; color: #333; margin-bottom: 12px; }
    .book-btn { background: #27ae60; color: white; padding: 10px 18px; border: none; border-radius: 30px; text-decoration: none; font-weight: 600; }
    .book-btn:hover { background: #1e8449; }
  </style>
</head>
<body>

<div class="top-bar">
  <p><a href="home.php" class="back-btn">&larr; Back to Dashboard</a></p>
</div>

<h1 style="text-align:center;">Worker - <?php echo htmlspecialchars(ucwords($category)); ?></h1>

<div class="worker-list">
<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $photo = !empty($row['photo']) ? $row['photo'] : 'default_worker.jpg';
        echo '<div class="worker-card">';
        echo '<img src="uploads/' . htmlspecialchars($photo) . '" alt="Worker Photo">';
        echo '<div class="worker-name">' . htmlspecialchars($row['fullname']) . '</div>';
        echo '<div class="worker-skills">' . htmlspecialchars($row['skills']) . '</div>';
        echo '<div class="worker-contact">Contact: ' . htmlspecialchars($row['contact_info']) . '</div>';
        echo '<a href="book_worker.php?id=' . $row['id'] . '" class="book-btn">Book Now</a>';
        echo '</div>';
    }
} else {
    echo "<p style='text-align:center; color:#555;'>No skilled workers found in this category.</p>";
}
?>
</div>

</body>
</html>
