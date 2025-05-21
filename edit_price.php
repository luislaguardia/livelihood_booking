<?php
include 'db.php';

if (isset($_POST['worker_id'], $_POST['price'])) {
    $id = $_POST['worker_id'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("UPDATE skilled_workers SET price = ? WHERE id = ?");
    $stmt->bind_param("di", $price, $id);

    if ($stmt->execute()) {
        echo "Price updated!";
    } else {
        echo "Error updating price.";
    }
} else {
    echo "Invalid request.";
}
?>
<a href="manage_workers.php">Go back</a>
