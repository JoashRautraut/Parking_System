<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'security_guard') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';
require 'send_email.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Guard Dashboard - Log Violations</title>
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

        h2 {
            color: #333;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        .log-btn {
            padding: 5px 10px;
            background-color:rgb(224, 16, 16);
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .log-btn:hover {
            background-color:rgb(223, 1, 1);
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 25%;
            left: 50%;
            transform: translate(-50%, -25%);
            background: #fff;
            padding: 20px;
            border: 1px solid #333;
            z-index: 1000;
            box-shadow: 0px 0px 10px #ccc;
        }

        .form-popup input, .form-popup textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.logout-btn {
    padding: 10px 15px;
    background-color: #dc3545;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.logout-btn:hover {
    background-color: #c82333;
}

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Welcome, Security Guard</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by Plate Number..." onkeyup="filterTable()">
    </div>


    <table id="vehicleTable">
    <thead>
        <tr>
            <th>Plate Number</th>
            <th>Vehicle Type</th>
            <th>Owner Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $vehicles = $conn->query("SELECT * FROM vehicles");
        while ($row = $vehicles->fetch_assoc()) {
            echo "<tr>
                <td>{$row['plate_number']}</td>
                <td>{$row['vehicle_type']}</td>
                
                <td>{$row['owner_email']}</td>
                <td>
                    <button class='log-btn' onclick=\"openForm('{$row['plate_number']}', '{$row['vehicle_type']}',  '{$row['owner_email']}')\">Log Violation</button>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>

</div>

<!-- Violation Form -->
<div class="overlay" id="overlay"></div>
<div class="form-popup" id="violationForm">
    <form action="log_violation.php" method="POST">
        <input type="hidden" name="plate_number" id="form_plate_number">
        <input type="hidden" name="vehicle_type" id="form_vehicle_type">
        <input type="hidden" name="owner_email" id="form_owner_email">

        <label for="remarks">Violation Remarks:</label>
        <textarea name="remarks" id="remarks" rows="4" required></textarea><br><br>

        <button type="submit" name="submit_violation">Submit</button>
        <button type="button" onclick="closeForm()">Cancel</button>
    </form>
</div>


<script>
    function openForm(plate, vehicleType,  email) {
    document.getElementById('form_plate_number').value = plate;
    document.getElementById('form_vehicle_type').value = vehicleType;
    document.getElementById('form_owner_email').value = email;
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('violationForm').style.display = 'block';
}

    

    function closeForm() {
        document.getElementById('violationForm').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }

    function filterTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll("#vehicleTable tbody tr");

        rows.forEach(row => {
            const plateNumber = row.querySelector("td:first-child").textContent.toLowerCase();
            row.style.display = plateNumber.includes(input) ? "" : "none";
        });
    }
</script>

</body>
</html>
