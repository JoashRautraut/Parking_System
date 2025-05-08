<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Already Registered</title>
</head>
<body>
    <h2>Congratulations!</h2>
    <p>You have already registered your vehicle successfully.</p>
</body>
</html>
