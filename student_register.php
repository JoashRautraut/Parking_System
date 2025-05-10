<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];

// Check if the student has already registered a vehicle
$query = "SELECT id FROM vehicles WHERE owner_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: congratulations.php");
    exit();
}

$stmt->close();

// Handle registration
if (isset($_POST['register_vehicle'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_type = trim($_POST['vehicle_type']);

    $stmt = $conn->prepare("INSERT INTO vehicles (first_name, last_name, plate_number, vehicle_type, owner_email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $plate_number, $vehicle_type, $email);

    if ($stmt->execute()) {
        header("Location: congratulations.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Vehicle</title>
</head>
<body>
    <h2>Register Your Vehicle</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="student_register.php" method="POST">
        

        <label>Plate Number:</label><br>
        <input type="text" name="plate_number" required><br><br>

        <label>Vehicle Type:</label><br>
        <select name="vehicle_type" required>
            <option value="car">Car</option>
            <option value="motorcycle">Motorcycle</option>
        </select><br><br>

        <button type="submit" name="register_vehicle">Register Vehicle</button>
    </form>
</body>
</html>
