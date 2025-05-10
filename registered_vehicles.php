<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle Delete Vehicle
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: registered_vehicles.php");
    exit();
}

// Handle Update Vehicle
if (isset($_POST['update_vehicle'])) {
    $id = intval($_POST['vehicle_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $plate_number = trim($_POST['plate_number']);
    $vehicle_type = trim($_POST['vehicle_type']);

    $stmt = $conn->prepare("UPDATE vehicles SET first_name = ?, last_name = ?, plate_number = ?, vehicle_type = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $plate_number, $vehicle_type, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: registered_vehicles.php");
    exit();
}

// Fetch Vehicles
$vehicles = $conn->query("SELECT * FROM vehicles");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Vehicles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="registered_vehicles.php">Registered Vehicles</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</div>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Registered Vehicles</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Plate Number</th>
                    <th>Vehicle Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <tr>
                        <td><?= $vehicle['id'] ?></td>
                        <td><?= htmlspecialchars($vehicle['first_name']) ?></td>
                        <td><?= htmlspecialchars($vehicle['last_name']) ?></td>
                        <td><?= htmlspecialchars($vehicle['plate_number']) ?></td>
                        <td><?= htmlspecialchars($vehicle['vehicle_type']) ?></td>
                        <td>
                            <a href="registered_vehicles.php?edit=<?= $vehicle['id'] ?>">Edit</a> |
                            <a href="registered_vehicles.php?delete=<?= $vehicle['id'] ?>" onclick="return confirm('Are you sure you want to delete this vehicle?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if (isset($_GET['edit'])): ?>
            <?php
            $editId = intval($_GET['edit']);
            $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->bind_param("i", $editId);
            $stmt->execute();
            $result = $stmt->get_result();
            $vehicle = $result->fetch_assoc();
            $stmt->close();
            ?>
            <h2>Edit Vehicle</h2>
            <form method="POST">
                <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($vehicle['first_name']) ?>" required><br><br>
                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($vehicle['last_name']) ?>" required><br><br>
                <label>Plate Number:</label>
                <input type="text" name="plate_number" value="<?= htmlspecialchars($vehicle['plate_number']) ?>" required><br><br>
                <label>Vehicle Type:</label>
                <select name="vehicle_type" required>
                    <option value="car" <?= $vehicle['vehicle_type'] === 'car' ? 'selected' : '' ?>>Car</option>
                    <option value="motorcycle" <?= $vehicle['vehicle_type'] === 'motorcycle' ? 'selected' : '' ?>>Motorcycle</option>
                </select><br><br>
                <button type="submit" name="update_vehicle">Update Vehicle</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
