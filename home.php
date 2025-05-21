<?php
session_start();
// Session timeout duration: 30 minutes (1800 seconds)
$timeout_duration = 1800;
// Check if last activity timestamp is set
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Session expired
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}
// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();
// Your existing session checks
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Kunin ang pangalan ng customer
$user_id = $_SESSION["user_id"];
$query = mysqli_query($conn, "SELECT name FROM users WHERE id='$user_id'");
$row = mysqli_fetch_assoc($query);
$customer_name = $row ? $row['name'] : "Customer";

// Categories list with corresponding images
$categories = [
    "Transportation" => "Transportation.jpg",
    "Construction" => "Construction.jpg",
    "Metal Works" => "Metal Works.jpg",
    "Electrical" => "electrician.jpg"
];


// Initialize filter parameters
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$age_range = isset($_GET['age_range']) ? trim($_GET['age_range']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$min_exp = isset($_GET['min_exp']) ? (int)$_GET['min_exp'] : 0; // Initialize min_exp

// Prepare SQL for filtered workers display
$sql = "SELECT * FROM skilled_workers WHERE 1=1";
$params = [];
$types = "";

// Search fullname, name, skills
if (!empty($search)) {
    $sql .= " AND (fullname LIKE ? OR name LIKE ? OR skills LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Filter location
if (!empty($location)) {
    $sql .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

// Age range filter
if (!empty($age_range) && preg_match('/^(\d+)-(\d+)$/', $age_range, $matches)) {
    $minAge = (int)$matches[1];
    $maxAge = (int)$matches[2];
    $sql .= " AND age BETWEEN ? AND ?";
    $params[] = $minAge;
    $params[] = $maxAge;
    $types .= "ii";
}

// Filter work experience (assuming work_experience is number of years)
if ($min_exp > 0) {
    $sql .= " AND work_experience >= ?";
    $params[] = $min_exp;
    $types .= "i";
}

$sql .= " ORDER BY fullname ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch featured skilled workers with ratings
$featured_workers = [];
$featured_query = $conn->query("SELECT skilled_workers.*, COALESCE(AVG(ratings.rating), 0) as average_rating FROM skilled_workers LEFT JOIN ratings ON skilled_workers.id = ratings.worker_id WHERE is_featured = 1 GROUP BY skilled_workers.id ORDER BY fullname ASC");
if ($featured_query) {
    while ($row = $featured_query->fetch_assoc()) {
        $featured_workers[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $worker_id = (int)$_POST['worker_id'];
    $customer_id = $_SESSION['user_id']; // Assuming user is logged in
    $rating = (int)$_POST['rating'];
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // I-check kung ang customer ay nagbigay na ng rating sa worker na ito
    $check_query = $conn->prepare("SELECT * FROM ratings WHERE worker_id = ? AND customer_id = ?");
    $check_query->bind_param("ii", $worker_id, $customer_id);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        // Update existing rating
        $stmt = $conn->prepare("UPDATE ratings SET rating = ?, comment = ? WHERE worker_id = ? AND customer_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $worker_id, $customer_id);
    } else {
        // Insert new rating
        $stmt = $conn->prepare("INSERT INTO ratings (worker_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $worker_id, $customer_id, $rating, $comment);
    }
    
    if ($stmt->execute()) {
        // Redirect back to the previous page or show success message
        header("Location: home.php?message=Rating submitted successfully.");
        exit();
    } else {
        // Handle error
        echo "Error: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .navbar {
      background: #2c3e50;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
    }

    .navbar .left, .navbar .right {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .navbar a {
      color: white;
      text-decoration: none;
      font-weight: bold;
      position: relative;
    }

    .navbar a:hover {
      text-decoration: underline;
    }

    .welcome {
      font-size: 1.5rem;
    }

    .dropdown {
      position: relative;
      display: inline-block;
    }
    
    .dropdown > a {
      cursor: pointer;
      padding: 10px 15px;
      background-color: #2c3e50;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      min-width: 180px;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.15);
      z-index: 1000;
      border-radius: 6px;
      top: 110%;
      left: 0;
    }

    .dropdown-content a {
      color: #333;
      padding: 10px 15px;
      display: block;
      text-decoration: none;
      transition: 0.3s ease;
    }

    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
    
    h2 {
      text-align: center;
      margin: 30px 0 10px;
      color: #333;
    }

    .category-container {
      max-width: 1000px;
      margin: auto;
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      padding: 20px;
    }

    .search-container {
      text-align: center;
      margin-bottom: 30px;
    }

    .search-container input[type="text"] {
      padding: 10px;
      width: 300px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .search-container button {
      padding: 10px 20px;
      font-size: 1rem;
      background-color: #2c3e50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 10px;
      transition: background 0.3s ease;
    }

    .search-container button:hover {
      background-color: #34495e;
    }

    .category-card {
    background: rgba(0, 0, 0, 0.5); 
    color: yellow; 
    width: 200px;
    height: 130px;
    border-radius: 10px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.2rem;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s ease;
    background-size: cover; /* Cover the entire card */
    background-position: center; /* Center the image */
}

.category-card:hover {
    transform: translateY(-5px);
    opacity: 0.9; /* Slightly change opacity on hover */
}


    .featured-container {
      max-width: 1000px;
      margin: 20px auto;
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      padding: 0 20px 20px;
    }

    .featured-card {
      background: white;
      width: 220px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 15px;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .featured-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0,0,0,0.15);
    }

    .featured-card h3 {
      margin: 0 0 10px;
      font-size: 1.3rem;
      color: #2c3e50;
    }

    .featured-card p {
      margin: 5px 0;
      color: #555;
      font-size: 0.95rem;
    }

    .featured-card img {
      width: 100%;
      height: 140px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="left">
    <span class="welcome">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</span>
  </div>
  <div class="right">
    <a href="home.php">Dashboard</a>
    <form method="GET" action="search_worker.php" style="max-width: 700px; margin: 20px auto; display: flex; gap: 8px; align-items: center;">
      <input type="text" name="query" placeholder="Search by name or skills" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" style="flex-grow: 1; padding: 8px; font-size: 1rem;">
      <button type="submit" style="padding: 9px 16px; background-color: #3498db; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Search</button>
      <button type="button" onclick="toggleFilters()" style="padding: 9px 16px; background-color: #95a5a6; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin-left: 6px;">Filter ▾</button>
    </form>

      <!-- Apply  Worker  -->
  <a href="apply_workers.php" style="background-color: #f39c12; padding: 8px 15px; border-radius: 5px; font-weight: bold;">Apply as Worker</a>

<div class="dropdown">
  <a href="#" onclick="toggleDropdown()">Menu ▾</a>
  <div id="dropdownContent" class="dropdown-content">
    <a href="my_bookings.php">My Bookings</a>
    <a href="feedback.php">Feedback</a>
    <a href="contact.php">Contact</a>
  </div>
</div>

    <div id="filters" style="max-width: 700px; margin: 20px auto 20px; display: none; gap: 10px; background: #fafafa; padding: 10px 15px; border-radius: 6px; box-shadow: 0 0 6px rgba(0,0,0,0.1); font-size: 0.9rem;">
      <form method="GET" action="filter_result.php" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
  
        <input type="hidden" name="query" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">

        <input type="text" name="age_range" placeholder="Age range (e.g. 19-22)" value="<?php echo isset($_GET['age_range']) ? htmlspecialchars($_GET['age_range']) : ''; ?>" style="width: 140px; padding: 6px; border: 1px solid #ccc; border-radius: 4px;">

        <input type="text" name="location" placeholder="Location" value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>" style="padding: 6px; width: 150px; border: 1px solid #ccc; border-radius: 4px;">

        <input type="number" name="min_exp" placeholder="Min Work Years" min="0" value="<?php echo isset($_GET['min_exp']) ? (int)$_GET['min_exp'] : ''; ?>" style="width: 140px; padding: 6px; border: 1px solid #ccc; border-radius: 4px;">

        <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 7px 14px; border-radius: 5px; font-weight: bold; cursor: pointer;">Apply</button>
      </form>
    </div>


    <div class="dropdown">
      <a href="#" onclick="toggleDropdown()">Menu ▾</a>
      <div id="dropdownContent" class="dropdown-content">
        <a href="my_bookings.php">My Bookings</a>
        <a href="feedback.php">Feedback</a>
        <a href="contact.php">Contact</a>
      </div>
    </div>

    <a href="logout.php">Logout</a>
  </div>
</div>

<h2>Available Categories</h2>

<div class="category-container">
  <?php foreach ($categories as $category => $image): ?>
    <a href="category.php?category=<?php echo urlencode($category); ?>" class="category-card" style="background-image: url('uploads/<?php echo htmlspecialchars($image); ?>');">
      <?php echo htmlspecialchars($category); ?>
    </a>
  <?php endforeach; ?>
</div>


<!-- <h2>All Available Skilled Workers</h2>

<div class="featured-container">
  <?php
  $available_workers_query = $conn->query("SELECT * FROM skilled_workers WHERE status = 'approved' ORDER BY fullname ASC");
  if ($available_workers_query && $available_workers_query->num_rows > 0):
    while ($worker = $available_workers_query->fetch_assoc()):
  ?>
      <div class="featured-card">
        <img src="uploads/<?php echo htmlspecialchars($worker['photo'] ?: 'default_worker.jpg'); ?>" alt="Photo of <?php echo htmlspecialchars($worker['fullname']); ?>">
        <h3><?php echo htmlspecialchars($worker['fullname']); ?></h3>
        <p><strong>Skills:</strong> <?php echo htmlspecialchars($worker['skills']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($worker['location'] ?? 'N/A'); ?></p>
        <p><strong>Work Experience:</strong> <?php echo htmlspecialchars($worker['work_experience'] ?? 0); ?> years</p>
      </div>
  <?php
    endwhile;
  else:
    echo "<p style='text-align:center;'>No available skilled workers at the moment.</p>";
  endif;
  ?>
</div> -->
<!-- pwede to ilagay, instead of recommened skilled workers -->

<h2>Featured / Recommended Skilled Workers</h2>

<div class="featured-container">
  <?php if (!empty($featured_workers)): ?>
    <?php foreach ($featured_workers as $worker): ?>
      <div class="featured-card">
        <img src="<?php echo htmlspecialchars('uploads/' . $worker['photo'] ?: 'default_worker.jpg'); ?>" alt="Photo of <?php echo htmlspecialchars($worker['fullname']); ?>">
        <h3><?php echo htmlspecialchars($worker['fullname']); ?></h3>
        <p><strong>Skills:</strong> <?php echo htmlspecialchars($worker['skills']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($worker['location']) ?: 'N/A'; ?></p>
        <p><strong>Work Experience:</strong> <?php echo htmlspecialchars($worker['work_experience']); ?> years</p>
        
        <div class="rating">
            <div style="display: flex; align-items: center;">
                <img src="uploads/hammerxsaw.jpg" alt="Hammer" style="width: 20px; height: 20px; margin-right: 5px;">
                <span>
                    <?php 
                      $average_rating = $worker['average_rating'] > 0 ? $worker['average_rating'] : 1.0;
                      echo htmlspecialchars(number_format($average_rating, 1)); 
                    ?> / 5
                </span>
            </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center;">No featured skilled workers available at the moment.</p>
  <?php endif; ?>
</div>



<script>
function toggleFilters() {
  const filters = document.getElementById('filters');
  filters.style.display = (filters.style.display === 'flex' ? 'none' : 'flex');
}

function toggleDropdown() {
  const dropdown = document.getElementById("dropdownContent");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

window.addEventListener("click", function(e) {
  if (!e.target.closest(".dropdown")) {
    const dropdown = document.getElementById("dropdownContent");
    dropdown.style.display = "none";
  }
});

window.onload = function() {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('age_range') || urlParams.has('location') || urlParams.has('min_exp')) {
    document.getElementById('filters').style.display = 'flex';
  }
}
</script>

</body>
</html>

