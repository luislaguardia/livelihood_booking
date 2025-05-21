<?php
include 'db.php';

$worker_id = $_GET['id'] ?? 0;
$worker_id = intval($worker_id);

$bookings = [];
$result = $conn->query("SELECT booking_date FROM bookings WHERE worker_id = $worker_id AND status = 'Approved'");
while ($row = $result->fetch_assoc()) {
    $bookings[] = [
        'title' => 'Booked',
        'start' => $row['booking_date'],
        'allDay' => true
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8' />
  <title>Worker Availability Calendar</title>
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js'></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    #calendar {
      max-width: 800px;
      margin: auto;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?= json_encode($bookings) ?>
      });
      calendar.render();
    });
  </script>
</head>
<body>
  <h2 style="text-align:center;">Worker Availability</h2>
  <div id='calendar'></div>
</body>
</html>
