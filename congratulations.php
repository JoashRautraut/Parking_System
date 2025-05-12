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
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('https://img.freepik.com/premium-photo/congratulations-road-sign-sky-background_140916-37089.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
        }

        .container h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .container p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .container a {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    
    <p>You have successfully registered your vehicle. Thank you!</p>
    <a href="index.php">Return to Home</a>
</div>

</body>
</html>
