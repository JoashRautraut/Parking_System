<?php
session_start();
require 'includes/db.php';


// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Dashboard Page - admin.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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

<div class="main-content">
    <h2>Dashboard</h2>
    <div id="vehicleChart" style="width: 100%; height: 400px;"></div>
    <div id="typeChart" style="width: 100%; height: 400px;"></div>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
    google.charts.load('current', {packages: ['corechart']});
    google.charts.setOnLoadCallback(drawCharts);

    function drawCharts() {
        drawVehicleChart();
        drawTypeChart();
    }

    function drawVehicleChart() {
        var data = google.visualization.arrayToDataTable([
            ['Status', 'Count'],
            ['Inside Campus', 10],
            ['Outside Campus', 5]
        ]);

        var options = {
            title: 'Vehicle Parking Status',
            pieHole: 0.4,
            colors: ['#007bff', '#dc3545']
        };

        var chart = new google.visualization.PieChart(document.getElementById('vehicleChart'));
        chart.draw(data, options);
    }

    function drawTypeChart() {
        var data = google.visualization.arrayToDataTable([
            ['Type', 'Count', { role: 'style' }],
            ['Car', 7, '#007bff'],
            ['Motorcycle', 8, '#dc3545']
        ]);

        var options = {
            title: 'Vehicle Types',
            bar: { groupWidth: '75%' },
            legend: { position: 'none' }
        };

        var chart = new google.visualization.BarChart(document.getElementById('typeChart'));
        chart.draw(data, options);
    }
</script>

</body>
</html>
