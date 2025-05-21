<!-- <?php
// $host = "localhost";
// $user = "root";
// $password = "";
// $dbname = "livelihood_db";

// $conn = new mysqli($host, $user, $password, $dbname);

// if ($conn->connect_error) {
//   die("Connection failed: " . $conn->connect_error);
// }
?> -->

<?php
$host = "localhost";
$user = "root";
$password = "root"; 
$dbname = "livelihood_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
<!-- palitan mo to pre, adjust mo na la -->