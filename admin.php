<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

// Handle new vehicle registration
if (isset($_POST['register_vehicle'])) {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $plate = trim($_POST['plate_number']);
    $email = trim($_POST['owner_email']);
    $vehicleType = trim($_POST['vehicle_type']);

    $stmt = $conn->prepare("INSERT INTO vehicles (first_name, last_name, plate_number, owner_email, vehicle_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $lastName, $plate, $email, $vehicleType);
    $stmt->execute();
    $stmt->close();
}

// Fetch vehicle data
$vehicles = $conn->query("SELECT * FROM vehicles");

// Fetch data for charts
$vehicleTypeData = $conn->query("SELECT vehicle_type, COUNT(*) as count FROM vehicles GROUP BY vehicle_type");
$typeData = [];
while ($row = $vehicleTypeData->fetch_assoc()) {
    $typeData[] = [$row['vehicle_type'], (int)$row['count']];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var typeData = google.visualization.arrayToDataTable([
                ['Vehicle Type', 'Count'],
                <?php foreach ($typeData as $data) { echo "['{$data[0]}', {$data[1]}],"; } ?>
            ]);

            var typeOptions = {
                title: 'Registered Vehicles by Type',
                pieHole: 0.4,
                is3D: true
            };

            var typeChart = new google.visualization.PieChart(document.getElementById('typeChart'));
            typeChart.draw(typeData, typeOptions);
        }
    </script>
</head>
<body>
    <h2>Admin Dashboard</h2>

    <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
            <h3>Register a Vehicle</h3>
            <form action="admin.php" method="POST">
                <label>First Name:</label><br>
                <input type="text" name="first_name" required><br><br>
                <label>Last Name:</label><br>
                <input type="text" name="last_name" required><br><br>
                <label>Plate Number:</label><br>
                <input type="text" name="plate_number" required><br><br>
                <label>Owner Email:</label><br>
                <input type="email" name="owner_email" required><br><br>
                <label>Vehicle Type:</label><br>
                <select name="vehicle_type" required>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                </select><br><br>
                <button type="submit" name="register_vehicle">Register Vehicle</button>
            </form>
        </div>

        <div style="flex: 1;">
            <h3>Registered Vehicles by Type</h3>
            <div id="typeChart" style="width: 100%; height: 300px;"></div>
        </div>
    </div>

    <h3>All Registered Vehicles</h3>
    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Plate Number</th>
            <th>Owner Email</th>
            <th>Vehicle Type</th>
        </tr>
        <?php while ($row = $vehicles->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['plate_number']) ?></td>
                <td><?= htmlspecialchars($row['owner_email']) ?></td>
                <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
