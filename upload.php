<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $skill = $_POST['skill'];
    $contact = $_POST['contact'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $image_name = $_FILES['photo']['name'];
        $image_tmp = $_FILES['photo']['tmp_name'];
        $image_path = 'uploads/' . basename($image_name);

        if (move_uploaded_file($image_tmp, $image_path)) {
            // Tamang query at binding
            $query = "INSERT INTO skilled_workers (fullname, skill, contact_info, photo) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $fullname, $skill, $contact, $image_path);

            if ($stmt->execute()) {
                echo "Worker added successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error uploading image.";
        }
    } else {
        echo "No image uploaded or there was an error.";
    }
}
?>
