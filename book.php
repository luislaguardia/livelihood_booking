<?php
session_start();
include 'db.php';

// 1) Ensure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$user_id  = $_SESSION["user_id"];
$customer = $_SESSION["name"] ?? 'Guest';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2) Grab & validate POST data
    $worker_id = intval($_POST["worker_id"] ?? 0);
    $skill     = trim($_POST["skill"]     ?? '');
    $photo     = trim($_POST["photo"]     ?? '');
    $price     = floatval($_POST["price"] ?? 0);

    if ($worker_id <= 0 || $skill === '' || $price <= 0) {
        die("<p style='color:red;'>Incomplete booking information. Please make sure all fields are filled.</p>");
    }

    // 3) Verify that worker exists and has photo & price
    $chk = $conn->prepare("
        SELECT fullname 
          FROM skilled_workers 
         WHERE id = ? 
           AND photo <> '' 
           AND price > 0
    ");
    $chk->bind_param("i", $worker_id);
    $chk->execute();
    $chk->bind_result($fullname);

    if (!$chk->fetch()) {
        die("<p style='color:red;'>Error: This worker cannot be booked (missing photo or price).</p>");
    }
    $chk->close();
// 4) Prevent duplicate booking for same user & worker, ignoring cancelled bookings
$dup = $conn->prepare("
    SELECT COUNT(*) 
      FROM bookings 
     WHERE user_id = ? 
       AND worker_id = ? 
       AND status <> 'Cancelled'
");
    $dup->bind_param("ii", $user_id, $worker_id);
    $dup->execute();
    $dup->bind_result($count);
    $dup->fetch();
    $dup->close();

    if ($count > 0) {
        die("<p style='color:orange;'>You have already booked <strong>{$fullname}</strong>.</p>
             <p><a href='home.php'>Back to Home</a></p>");
    }

    // 5) Insert booking
    $status       = "Pending";
    $booking_date = date("Y-m-d");
    $service      = $skill;

    $ins = $conn->prepare("
        INSERT INTO bookings 
        (user_id, service, booking_date, status, worker_name, skill, price, worker_id, customer_name) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ins->bind_param(
        "isssssdis",
        $user_id,
        $service,
        $booking_date,
        $status,
        $fullname,
        $skill,
        $price,
        $worker_id,
        $customer
    );

    if ($ins->execute()) {
        echo "<div style='padding:20px; background:#e0ffe0; border:1px solid #2ecc71;'>
                <h2>Booking Successfully!</h2>
                <p>You booked <strong>{$fullname}</strong> for <strong>{$skill}</strong> at ₱" 
                . number_format($price,2) . ".</p>
                <a href='home.php' style='padding:10px 20px;
                   background:#3498db;color:#fff;text-decoration:none;
                   border-radius:4px;'>Go Back to Home</a>
              </div>";
    } else {
        echo "<p style='color:red;'>Booking failed: " . htmlspecialchars($ins->error) . "</p>";
    }
    $ins->close();
} else {
    header("Location: home.php");
    exit();
}
?>
