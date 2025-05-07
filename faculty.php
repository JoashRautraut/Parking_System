<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php'; // Corrected path to db.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard - Log Violations</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        .form-popup {
            display: none;
            position: fixed;
            top: 25%;
            left: 50%;
            transform: translate(-50%, -25%);
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #333;
            z-index: 1000;
        }

        .form-popup input, .form-popup textarea {
            width: 100%;
            margin-top: 10px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
</head>
<body>

<h2>Welcome, Faculty</h2>

<table>
    <thead>
        <tr>
            <th>Plate Number</th>
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
                <td>{$row['owner_email']}</td>
                <td>
                    <button onclick=\"openForm('{$row['plate_number']}', '{$row['owner_email']}')\">Log Violation</button>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>

<!-- Violation Form -->
<div class="overlay" id="overlay"></div>
<div class="form-popup" id="violationForm">
    <form action="log_violation.php" method="POST">
        <input type="hidden" name="plate_number" id="form_plate_number">
        <input type="hidden" name="owner_email" id="form_owner_email">

        <label for="remarks">Violation Remarks:</label>
        <textarea name="remarks" id="remarks" rows="4" required></textarea><br><br>

        <button type="submit" name="submit_violation">Submit</button>
        <button type="button" onclick="closeForm()">Cancel</button>
    </form>
</div>

<script>
function openForm(plate, email) {
    document.getElementById('form_plate_number').value = plate;
    document.getElementById('form_owner_email').value = email;
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('violationForm').style.display = 'block';
}

function closeForm() {
    document.getElementById('violationForm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}
</script>

</body>
</html>
