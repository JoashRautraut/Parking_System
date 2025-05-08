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
    <title>Congratulations</title>
</head>
<body>
    <h2>Congratulations!</h2>
    <p>You have successfully registered your vehicle. Thank you!</p>
    <a href="index.php">Return to Home</a>
</body>
</html>
