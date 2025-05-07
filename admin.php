<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

// Handle new registration
if (isset($_POST['register_vehicle'])) {
    $plate = trim($_POST['plate_number']);
    $email = trim($_POST['owner_email']);

    $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, owner_email) VALUES (?, ?)");
    $stmt->bind_param("ss", $plate, $email);
    $stmt->execute();
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Handle edit
if (isset($_POST['update_vehicle'])) {
    $id = intval($_POST['vehicle_id']);
    $plate = trim($_POST['plate_number']);
    $email = trim($_POST['owner_email']);

    $stmt = $conn->prepare("UPDATE vehicles SET plate_number = ?, owner_email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $plate, $email, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}

// Fetch vehicles
$vehicles = $conn->query("SELECT * FROM vehicles");

$editMode = false;
$editVehicle = null;
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editVehicle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Vehicle Registration</title>
</head>
<body>
    <h2>Welcome, Admin!</h2>

    <h3><?= $editMode ? 'Edit Vehicle' : 'Register a Vehicle' ?></h3>
    <form action="admin.php" method="POST">
        <?php if ($editMode): ?>
            <input type="hidden" name="vehicle_id" value="<?= $editVehicle['id'] ?>">
        <?php endif; ?>

        <label>Plate Number:</label><br>
        <input type="text" name="plate_number" required value="<?= $editMode ? $editVehicle['plate_number'] : '' ?>"><br><br>

        <label>Owner Email:</label><br>
        <input type="email" name="owner_email" required value="<?= $editMode ? $editVehicle['owner_email'] : '' ?>"><br><br>

        <button type="submit" name="<?= $editMode ? 'update_vehicle' : 'register_vehicle' ?>">
            <?= $editMode ? 'Update Vehicle' : 'Register Vehicle' ?>
        </button>
    </form>

    <h3>Registered Vehicles</h3>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Plate Number</th>
            <th>Owner Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['plate_number']) ?></td>
                <td><?= htmlspecialchars($row['owner_email']) ?></td>
                <td>
                    <a href="admin.php?edit=<?= $row['id'] ?>">Edit</a> |
                    <a href="admin.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this vehicle?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
