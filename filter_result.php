<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Function to calculate age from birthday if needed
function calculate_age($birthdate) {
    $dob = new DateTime($birthdate);
    $today = new DateTime('today');
    return $dob->diff($today)->y;
}

$bookingMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_worker_id'])) {
    // Handle booking submission
    $worker_id = intval($_POST['book_worker_id']);
    $user_id = $_SESSION['user_id'];
    $booking_date = date("Y-m-d H:i:s");

    // Verify worker exists and approved
    $stmtCheck = $conn->prepare("SELECT id FROM skilled_workers WHERE id = ? AND status = 'approved'");
    $stmtCheck->bind_param("i", $worker_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        $bookingMessage = "Worker not found or not approved.";
    } else {
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, booking_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $worker_id, $booking_date);
        if ($stmt->execute()) {
            // Redirect to my_bookings.php after successful booking
            header("Location: my_bookings.php?msg=Booking successful!");
            exit();
        } else {
            $bookingMessage = "Booking failed. Please try again.";
        }
    }
    $stmtCheck->close();
}

// Get filter parameters from GET
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$age_range = isset($_GET['age_range']) ? trim($_GET['age_range']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_exp_months = isset($_GET['min_exp_months']) ? (int)$_GET['min_exp_months'] : 0; // Capture min_exp_months

// Build SQL filters
$sql = "SELECT * FROM skilled_workers WHERE status = 'approved'"; // Base query
$params = [];
$types = "";

// Search filter
if (!empty($search)) {
    $sql .= " AND (fullname LIKE ? OR skills LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Location filter
if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

// Age filter (using age column; adjust if your schema differs)
if (!empty($age_range) && preg_match('/^(\d+)-(\d+)$/', $age_range, $matches)) {
    $minAge = (int)$matches[1];
    $maxAge = (int)$matches[2];
    $sql .= " AND age BETWEEN ? AND ?";
    $params[] = $minAge;
    $params[] = $maxAge;
    $types .= "ii";
}

// Filter work experience in months
if ($min_exp_months > 0) {
    $sql .= " AND work_experience_in_months >= ?"; // Assuming you have a column for months
    $params[] = $min_exp_months;
    $types .= "i"; // Add 'i' for integer binding
}

$sql .= " ORDER BY fullname ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . htmlspecialchars($conn->error));
}


if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Filtered Workers</title>
<button onclick="window.history.back();" style="background-color: #3498db; color: white; border: none; padding: 7px 14px; border-radius: 5px; font-weight: bold; cursor: pointer;">&larr; Back to Dashboard</a>
</button>
<!-- OR, if you want to link to home.php -->
<!-- <a href="home.php" style="background-color: #3498db; color: white; padding: 7px 14px; border-radius: 5px; font-weight: bold; text-decoration: none;">Back</a> -->
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    padding: 20px;
    margin: 0;
  }
  .worker-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin: 10px auto;
    max-width: 600px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
  }
  .worker-photo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #3498db;
    flex-shrink: 0;
  }
  .worker-info {
    flex-grow: 1;
  }
  .worker-info h3 {
    margin: 0 0 10px 0;
    color: #2980b9;
    font-size: 1.5rem;
  }
  .worker-info p {
    margin: 5px 0;
    color: #555;
  }
  .book-btn {
    background-color: #27ae60;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
  }
  .book-btn:hover {
    background-color: #1e8449;
  }
  .message {
    max-width: 600px;
    margin: 10px auto;
    padding: 12px;
    border-radius: 5px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    font-weight: bold;
  }
  .no-results {
    text-align: center;
    color: #e74c3c;
    font-weight: bold;
    margin-top: 40px;
  }
</style>
</head>
<body>

<h2 style="text-align:center; color:#2c3e50; margin-bottom:30px;">Filtered Skilled Workers</h2>

<?php if (!empty($bookingMessage)): ?>
  <div class="message"><?php echo htmlspecialchars($bookingMessage); ?></div>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
  <?php while($worker = $result->fetch_assoc()): ?>
    <?php
      $photo = !empty($worker['photo']) ? $worker['photo'] : 'default_worker.jpg';
    ?>
    <div class="worker-card">
      <img src="uploads/<?php echo htmlspecialchars($photo); ?>" alt="Worker Photo" class="worker-photo" />
      <div class="worker-info">
        <h3><?php echo htmlspecialchars($worker['fullname']); ?></h3>
        <p><strong>Skills:</strong> <?php echo htmlspecialchars($worker['skills']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($worker['age'] ?? 'N/A'); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($worker['location'] ?? 'N/A'); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($worker['contact_info']); ?></p>

        <form method="post" action="filter_result.php">
          <input type="hidden" name="book_worker_id" value="<?php echo intval($worker['id']); ?>">
          <button type="submit" class="book-btn">Book Now</button>
        </form>

    

      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p class="no-results">No skilled workers found matching your criteria.</p>
<?php endif; 
?>

</body>
</html>

