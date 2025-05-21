<?php
session_start();
include 'db.php';

// Siguraduhing may id sa URL
if (!isset($_GET['id'])) {
    echo "Worker ID not specified.";
    exit;
}

$id = $_GET['id'];

// Kunin ang worker data
$stmt = $conn->prepare("SELECT * FROM skilled_workers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Worker not found.";
    exit;
}

$worker = $result->fetch_assoc();

// Kapag na-submit ang form, i-update ang worker
if (isset($_POST['update'])) {
    $fullname = $_POST['fullname'];
    $skills = $_POST['skills'];
    $contact_info = $_POST['contact_info'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $category = $_POST['category'];

    $stmt_update = $conn->prepare("UPDATE skilled_workers SET fullname=?, skills=?, contact_info=?, price=?, status=?, category=? WHERE id=?");
    $stmt_update->bind_param("ssssssi", $fullname, $skills, $contact_info, $price, $status, $category, $id);
    $stmt_update->execute();

    $success = "Worker updated successfully!";

    // Update din ang $worker para makita agad sa form yung changes
    $worker['fullname'] = $fullname;
    $worker['skills'] = $skills;
    $worker['contact_info'] = $contact_info;
    $worker['price'] = $price;
    $worker['status'] = $status;
    $worker['category'] = $category;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Worker</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9fafb;
        padding: 20px;
        color: #333;
    }

    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
    }

    form {
        max-width: 600px;
        margin: 0 auto;
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        border-color: #3498db;
        outline: none;
    }

    button[type="submit"] {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: block;
        margin: 0 auto;
    }

    button[type="submit"]:hover {
        background-color: #2980b9;
    }

    p {
        text-align: center;
        font-weight: bold;
    }

    a {
        display: block;
        text-align: center;
        margin-top: 25px;
        text-decoration: none;
        color: #3498db;
        font-weight: 600;
    }

    a:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>

<h2>Edit Worker</h2>

<?php if (isset($success)): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Full Name:</label><br />
    <input type="text" name="fullname" value="<?php echo htmlspecialchars($worker['fullname'] ?? ''); ?>" required /><br /><br />

    <label>Skills (comma separated):</label><br />
    <input type="text" name="skills" value="<?php echo htmlspecialchars($worker['skills'] ?? ''); ?>" /><br /><br />

    <label>Contact Info:</label><br />
    <input type="text" name="contact_info" value="<?php echo htmlspecialchars($worker['contact_info'] ?? ''); ?>" /><br /><br />

    <label>Price:</label><br />
    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($worker['price'] ?? ''); ?>" /><br /><br />

    <label>Status:</label><br />
    <select name="status">
        <option value="pending" <?php if ($worker['status'] == 'pending') echo 'selected'; ?>>Pending</option>
        <option value="approved" <?php if ($worker['status'] == 'approved') echo 'selected'; ?>>Approved</option>
        <option value="declined" <?php if ($worker['status'] == 'declined') echo 'selected'; ?>>Declined</option>
    </select><br /><br />

    <label>Category:</label><br />
    <input type="text" name="category" value="<?php echo htmlspecialchars($worker['category'] ?? ''); ?>" /><br /><br />

    <button type="submit" name="update">Update Worker</button>
</form>

<br />
<a href="manage_workers.php">Back to Workers List</a>

</body>
</html>
