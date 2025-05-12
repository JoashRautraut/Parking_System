<?php
session_start();
require 'includes/db.php';



// Check if the email is already registered
$message = "";
if (isset($_POST['check_email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url('images/register-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 40px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .container form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .container form input,
        .container form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
        }

        .container form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .container form button:hover {
            background-color: #0056b3;
        }

        .container .login-link {
            text-align: center;
            margin-top: 10px;
        }

        .container .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .container .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <?= $message ?>

    

    <form action="includes/register_handler.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Role:</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="security_guard">Security Guard</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="index.php">Login here</a>.
    </div>
</div>

</body>
</html>
