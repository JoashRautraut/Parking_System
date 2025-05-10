<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
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
    <h2>Settings</h2>
<form method="POST">
    <label for="theme">Choose Theme:</label>
    <select id="theme">
        <option>Light</option>
        <option>Dark</option>
    </select>
    <button type="submit">Save</button>
</form>
    </div>
</div>
</body>
</html>
