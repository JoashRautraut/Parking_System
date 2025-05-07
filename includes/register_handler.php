<?php
require_once 'db.php'; // Make sure this connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($email) || empty($password) || empty($role)) {
        die('All fields are required.');
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        die('Email already registered.');
    }

    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "Registration successful. <a href='../index.php'>Login here</a>";
    } else {
        echo "Something went wrong. Please try again.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
