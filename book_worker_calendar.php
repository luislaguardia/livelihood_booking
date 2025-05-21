<?php
include 'db.php';
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$worker_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($worker_id === 0) {
    echo "⚠️ Invalid worker ID.";
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch booked dates
$bookedDates = [];
$result = $conn->query("SELECT booking_date FROM bookings WHERE worker_id = $worker_id AND status IN ('Approved', 'Pending')");
while ($row = $result->fetch_assoc()) {
    $bookedDates[] = $row['booking_date'];
}

// If user submitted the form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['selected_date'])) {
    $selected_date = $_POST['selected_date'];
    $description = $_POST['description'];

    $check = $conn->prepare("SELECT id FROM bookings WHERE worker_id = ? AND booking_date = ?");
    $check->bind_param("is", $worker_id, $selected_date);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO bookings (user_id, service, booking_date, status, worker_name, skill, price, customer_name, worker_id)
                                  VALUES (?, 'N/A', ?, 'Pending', '', '', 0, '', ?)");
        $insert->bind_param("isi", $user_id, $selected_date, $worker_id);
        if ($insert->execute()) {
            $message = "✅ Booking submitted for " . $selected_date;
        } else {
            $error = "❌ Failed to book.";
        }
    } else {
        $error = "⚠️ This date is already booked.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Book Worker</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <style>
    body { font-family: Arial; padding: 20px; }
    #calendar { max-width: 800px; margin: auto; }
    .msg { text-align:center; font-weight:bold; margin: 10px; }
    form { max-width: 500px; margin: 20px auto; text-align: center; }
    textarea { width: 100%; height: 100px; margin-top: 10px; }
    button { padding: 10px 20px; }
  </style>
</head>
<body>
<h2 style="text-align:center;">Book This Worker</h2>

<?php if (isset($message)) echo '<p class="msg" style="color:green;">' . $message . '</p>'; ?>
<?php if (isset($error)) echo '<p class="msg" style="color:red;">' . $error . '</p>'; ?>

<div id="calendar"></div>

<form method="POST" id="booking-form" style="display:none;">
  <input type="hidden" name="selected_date" id="selected_date">
  <textarea name="description" placeholder="Describe your request..."></textarea><br>
  <button type="submit">Confirm Booking</button>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const bookedDates = <?= json_encode($bookedDates) ?>;

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      selectAllow: function(selectInfo) {
        const dateStr = selectInfo.startStr;
        return !bookedDates.includes(dateStr);
      },
      events: bookedDates.map(date => ({ title: 'Booked', start: date, allDay: true, color: '#ccc' })),
      select: function(info) {
        document.getElementById('selected_date').value = info.startStr;
        document.getElementById('booking-form').style.display = 'block';
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
      }
    });

    calendar.render();
  });
</script>
</body>
</html>
