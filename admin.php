<?php
session_start();
require 'includes/db.php';

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch Students with Search/Filter safely
$searchQuery = "";
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $searchQuery = " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
}

$students = $conn->query("SELECT * FROM users WHERE role = 'student' $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Students</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            position: fixed;
            height: 100%;
            padding-top: 20px;
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            text-align: center;
        }
        .sidebar a:hover {
            background: #007bff;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 8px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            padding: 8px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
        <ul>
            <li><a href="#" onclick="loadPage('dashboard.php')"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#" onclick="loadPage('students.php')"><i class="fas fa-users"></i> Student Records</a></li>
            <li><a href="#" onclick="loadPage('settings.php')"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</div>

<div class="main-content" id="content">
    <h2>Welcome to the Admin Panel</h2>
    <p>Select a section from the sidebar.</p>
</div>

<script src="script.js"></script>

<div class="main-content">
    <h2>Dashboard</h2>
    <div id="vehicleChart" style="width: 100%; height: 400px;"></div>
    <div id="typeChart" style="width: 100%; height: 400px;"></div>

    <h2>Manage Students</h2>
    <form method="GET" action="admin.php">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit">Search</button>
    </form>

    
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= $student['id'] ?></td>
                    <td><?= htmlspecialchars($student['first_name']) ?></td>
                    <td><?= htmlspecialchars($student['last_name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td class="actions">
                        <a href="admin.php?edit=<?= $student['id'] ?>">Edit</a> |
                        <a href="admin.php?delete=<?= $student['id'] ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
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