<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

function calculate_age($birthdate) {
    $dob = new DateTime($birthdate);
    $today = new DateTime('today');
    return $dob->diff($today)->y;
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$bookingMessage = ""; // to display later
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_worker_id'])) {
    $worker_id = $_POST['book_worker_id'];
    $query = $_POST['query']; // get back the query string
    $user_id = $_SESSION['user_id'];
    $booking_date = date("Y-m-d H:i:s");

    // I-search ang skilled workers
    $result = $conn->query("SELECT id FROM skilled_workers WHERE id = $worker_id");
    if ($result->num_rows == 0) {
        $bookingMessage = "Worker not found.";
    } else {
        // Dito ay dapat gamitin ang tamang variable name
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, worker_id, booking_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $worker_id, $booking_date); // Gamitin ang tamang variable

        if ($stmt->execute()) {
            $bookingMessage = "Booking successful!";
        } else {
            $bookingMessage = "Booking failed. Please try again.";
        }
        $stmt->close(); // Isara ang statement pagkatapos gamitin
    }
}

// Pag-update ng SQL query
if (!empty($query)) {
$sql = "SELECT id, fullname, skills, description, birthday, location, contact_info, price, photo FROM skilled_workers WHERE fullname LIKE ? OR skills LIKE ? ORDER BY fullname ASC";
    $stmt = $conn->prepare($sql);
    $likeQuery = "%$query%";
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null; // No results if no query
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Search Skilled Workers</title>
  <style>
   /* Reset & base styles */
    * {
      box-sizing: border-box;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: rgb(255, 255, 255);
      margin: 0;
      padding: 40px 20px;
      color: #333;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-weight: 700;
    }
    /* Form styles */
    form {
      max-width: 600px;
      margin: 0 auto 40px;
      display: flex;
      gap: 12px;
      justify-content: center;
    }
    form input[type="text"] {
      flex: 1;
      padding: 12px 15px;
      font-size: 16px;
      border: 2px solid #ddd;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }
    form input[type="text"]:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 8px rgba(52, 152, 219, 0.4);
    }
    form button {
      padding: 12px 25px;
      background-color: #3498db;
      border: none;
      border-radius: 6px;
      color: white;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    form button:hover {
      background-color: #2980b9;
    }
    /* Worker cards container */
    .worker-list {
      max-width: 900px;
      margin: auto;
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      justify-content: center;
    }

    .worker-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        width: 250px;
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .worker-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .worker-photo {
        max-width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 10px;
    }

    .worker-info h3 {
        margin: 0 0 10px;
        color: #2980b9;
        font-weight: 700;
        font-size: 1.3rem;
    }

    .worker-info p {
        font-size: 0.95rem;
        color: #555;
        margin: 5px 0;
    }

    .book-btn {
        margin-top: auto;
        padding: 10px 20px;
        background: #27ae60;
        color: white;
        border-radius: 30px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: background 0.3s ease;
    }

    .book-btn:hover {
        background-color: #1e8449;
    }

    .no-results {
        text-align: center;
        color: #777;
        font-size: 1.1rem;
        margin-top: 50px;
    }

    /* Back link style */
    a.back-link {
      display: block;
      text-align: center;
      margin: 40px auto 0;
      color: #2980b9;
      font-weight: 600;
      text-decoration: none;
      font-size: 1rem;
      max-width: 600px;
    }
    a.back-link:hover {
      text-decoration: underline;
    }

    .back-btn {
        color: white;
        font-weight: bold;
        text-decoration: none;
        padding: 8px 12px;
        background-color: #2c80b4;
        border-radius: 6px;
        transition: background-color 0.3s, color 0.3s;
        max-width: 200px; 
        box-shadow: 0 0 8px rgba(0,0,0,0.1); 
        display: flex; 
        align-items: center; 
        gap: 20px; 
    }
    .back-btn:hover {
      background-color: #246397;
    }
  </style>
</head>
<body>

<div style="text-align: center; margin-bottom: 20px;">
    <a href="home.php" class="back-btn">← Back to Dashboard</a>
</div>

<h2>Search Skilled Workers</h2>
<?php if (!empty($bookingMessage)): ?>
  <div style="max-width: 600px; margin: 10px auto; padding: 12px; border-radius: 5px;
              background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
    <?php echo $bookingMessage; ?>
  </div>
<?php endif; ?>

<form method="GET" action="search_worker.php">
  <input
    type="text"
    name="query"
    placeholder="Search by name or skills"
    value="<?php echo htmlspecialchars($query); ?>"
    autocomplete="off"
  />
  <button type="submit">Search</button>
</form>

<div class="worker-list">
<?php
if ($result && $result->num_rows > 0) {
    while ($worker = $result->fetch_assoc()) {
        $age = isset($worker['birthdate']) ? calculate_age($worker['birthdate']) : 'N/A';
        $photo = !empty($worker['photo']) ? $worker['photo'] : 'default_worker.jpg';
        ?>
        <div class="worker-card">
            <img src="uploads/<?php echo htmlspecialchars($photo); ?>" alt="Worker Photo" class="worker-photo" />
            <div class="worker-info">
                <h3><?php echo htmlspecialchars($worker['fullname']); ?></h3>
                <p><strong>Skills:</strong> <?php echo htmlspecialchars($worker['skills']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($worker['description']); ?></p> <!-- Add this line -->
                <p><strong>Age:</strong> <?php echo $age; ?></p>
                <p><strong>Location:</strong> <?php echo isset($worker['location']) ? htmlspecialchars($worker['location']) : 'N/A'; ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($worker['contact_info']); ?></p>
                <p><strong>Price:</strong> ₱<?php echo number_format($worker['price'], 2); ?></p>
                <form method="post" action="search_worker.php">
                    <input type="hidden" name="book_worker_id" value="<?php echo intval($worker['id']); ?>">
                    <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">
                    <button type="submit" class="book-btn">Book Now</button>
                </form>
            </div>
        </div>
        <?php
    }
} else {
    echo '<p class="no-results">No skilled workers found matching your search.</p>';
}
?>
</div>


</body>
</html>
