<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Vehicle Registration
if (isset($_POST['register_vehicle'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['owner_email']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_type = trim($_POST['vehicle_type']);

    // Check if email is already registered
    $stmt = $conn->prepare("SELECT id FROM vehicles WHERE owner_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "<p style='color: red;'>Email is already associated with a registered vehicle.</p>";
    } else {
        // Insert Vehicle
        $stmt = $conn->prepare("INSERT INTO vehicles (student_id, first_name, last_name, owner_email, plate_number, vehicle_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $first_name, $last_name, $email, $plate_number, $vehicle_type);

        if ($stmt->execute()) {
            header("Location: congratulations.php");
            exit();
        } else {
            $message = "<p style='color: red;'>Error: " . htmlspecialchars($stmt->error) . "</p>";
        }
    }
    $stmt->close();
}

// Fetch Registered Vehicles by Student
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE student_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$vehicles = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Panel - Register Vehicle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0px 0px 10px #ccc;
        }

        .logout-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            float: right;
        }

        .logout-btn:hover {
            background-color: #0056b3;
        }

        h2 {
            color: #333;
        }

        .form-section, .table-section {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <form action="logout.php" method="POST" style="float: right;">
        <button type="submit" class="logout-btn">Logout</button>
    </form>

    <h2>Register a Vehicle</h2>
    <?= $message ?>

    <div class="form-section">
        <form action="student.php" method="POST">
            <label>First Name:</label><br>
            <input type="text" name="first_name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="owner_email" required><br><br>

            <label>Plate Number:</label><br>
            <input type="text" name="plate_number" required><br><br>

            <label>Vehicle Type:</label><br>
            <select name="vehicle_type" required>
                <option value="Car">Car</option>
                <option value="Motorcycle">Motorcycle</option>
            </select><br><br>

            <button type="submit" name="register_vehicle">Register Vehicle</button>
        </form>
    </div>

    <h2>Your Registered Vehicles</h2>
    <div class="table-section">
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Plate Number</th>
                <th>Vehicle Type</th>
            </tr>
            <?php while ($row = $vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['owner_email']) ?></td>
                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                    <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
