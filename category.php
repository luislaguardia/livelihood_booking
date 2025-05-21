<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Get category
if (!isset($_GET['category']) || empty(trim($_GET['category']))) {
    echo "No category selected.";
    exit();
}
$category = strtolower(trim($_GET['category']));
$category = mysqli_real_escape_string($conn, $category);

// Handle booking submission from modal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['selected_date'], $_POST['worker_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $worker_id = intval($_POST['worker_id']);
    $description = trim($_POST['description'] ?? '');
    $selected_date = date('Y-m-d', strtotime($_POST['selected_date']));
    $service = $category;

    $check_sql = "SELECT * FROM bookings WHERE worker_id = ? AND booking_date = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $worker_id, $selected_date);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows == 0) {
        $query_worker_sql = "SELECT fullname, skills, price FROM skilled_workers WHERE id = ?";
        $stmt_worker = $conn->prepare($query_worker_sql);
        $stmt_worker->bind_param("i", $worker_id);
        $stmt_worker->execute();
        $result_worker = $stmt_worker->get_result();

        if ($worker_row = $result_worker->fetch_assoc()) {
            $worker_name = $worker_row['fullname'];
            $skill = $worker_row['skills'];
            $price = floatval($worker_row['price']);
            $customer_name = '';

            $insert_sql = "INSERT INTO bookings (user_id, service, booking_date, status, worker_name, skill, price, customer_name, worker_id)
                           VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("isssdssi", $user_id, $service, $selected_date, $worker_name, $skill, $price, $customer_name, $worker_id);
            $stmt_insert->execute();
        }
    }

    header("Location: category.php?category=" . urlencode($category));
    exit();
}

// Fetch workers
$sql = "SELECT * FROM skilled_workers 
        WHERE (
            LOWER(skills) LIKE CONCAT('%', ?, '%') 
            OR LOWER(category) LIKE CONCAT('%', ?, '%')
        ) AND status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $category, $category);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Workers - <?= htmlspecialchars($category) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
  <style>
    body { font-family: Arial; background: #f8f9fa; padding: 20px; }
    .worker-list { max-width: 900px; margin: auto; display: flex; flex-wrap: wrap; gap: 25px; justify-content: center; }
    .worker-card { background: white; border-radius: 12px; box-shadow: 0 0 8px rgba(0,0,0,0.1); width: 250px; padding: 20px; text-align: center; }
    .worker-card img { max-width: 100%; height: 160px; object-fit: cover; border-radius: 10px; margin-bottom: 15px; }
    .worker-name { font-weight: bold; font-size: 1.2rem; margin-bottom: 8px; }
    .worker-contact { font-size: 0.9rem; color: #333; margin-bottom: 12px; }
    .book-btn { background: #27ae60; color: white; padding: 10px 18px; border: none; border-radius: 30px; cursor: pointer; font-weight: 600; }
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; }
    .modal-content { background:#fff; max-width:600px; margin:50px auto; padding:20px; border-radius:8px; position:relative; }
    .modal-content span { position:absolute; right:15px; top:10px; cursor:pointer; }
  </style>
</head>
<body>

<h1 style="text-align:center;">Workers - <?= ucfirst(htmlspecialchars($category)) ?></h1>

<div class="worker-list">
<?php while ($row = $result->fetch_assoc()):
  $worker_id = $row['id'];
  $photo = !empty($row['photo']) ? $row['photo'] : 'default_worker.jpg';

  $booked = [];
  $bq = $conn->query("SELECT booking_date FROM bookings WHERE worker_id = $worker_id AND status IN ('Approved', 'Pending')");
  while ($d = $bq->fetch_assoc()) $booked[] = $d['booking_date'];
?>
  <div class="worker-card">
    <img src="uploads/<?= htmlspecialchars($photo) ?>" alt="Worker">
    <div class="worker-name"><?= htmlspecialchars($row['fullname']) ?></div>
    <div class="worker-contact"><strong>Skills:</strong> <?= htmlspecialchars($row['skills']) ?></div>
    <div class="worker-contact"><strong>Contact:</strong> <?= htmlspecialchars($row['contact_info']) ?></div>
    <div class="worker-contact"><strong>Price:</strong> ₱<?= number_format((float)$row['price'], 2) ?></div>
    <button class="book-btn" onclick='openModal(<?= $worker_id ?>, <?= json_encode($booked) ?>)'>Book</button>
  </div>
<?php endwhile; ?>
</div>

<!-- Modal -->
<div id="calendarModal" class="modal">
  <div class="modal-content">
    <span onclick="document.getElementById('calendarModal').style.display='none'">&times;</span>
    <h3>Pick a Booking Date</h3>
    <div id="calendar" style="margin-bottom:15px;"></div>
    <form method="POST">
      <input type="hidden" name="selected_date" id="selected_date" required>
      <input type="hidden" name="worker_id" id="modal_worker_id">
      <textarea name="description" placeholder="Describe your request..." required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; margin-top:10px;"></textarea>
      <button type="submit" class="book-btn" style="margin-top:10px;">Confirm Booking</button>
    </form>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let calendarInstance;

function openModal(workerId, bookedDates) {
  document.getElementById('calendarModal').style.display = 'block';
  document.getElementById('modal_worker_id').value = workerId;
  document.getElementById('selected_date').value = '';

  // Reset and render calendar
  const calendarEl = document.getElementById('calendar');
  calendarEl.innerHTML = ''; // clear previous
  calendarInstance = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    height: 400,
    selectAllow: function(info) {
      return !bookedDates.includes(info.startStr);
    },
    select: function(info) {
      document.getElementById('selected_date').value = info.startStr;
    },
    events: bookedDates.map(date => ({
      title: 'Booked',
      start: date,
      allDay: true,
      color: '#ccc'
    }))
  });
  calendarInstance.render();
}
</script>

</body>
</html>