<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';
require 'send_email.php'; // Ensure this file contains your PHPMailer configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_violation'])) {
    $plate = trim($_POST['plate_number']);
    $email = trim($_POST['owner_email']);
    $remarks = trim($_POST['remarks']);

    // Insert violation into database
    $stmt = $conn->prepare("INSERT INTO violations (plate_number, remarks) VALUES (?, ?)");
    $stmt->bind_param("ss", $plate, $remarks);

    if ($stmt->execute()) {
        // Send email notification
        if (sendViolationEmail($email, $plate, $remarks)) {
            echo "<script>alert('Violation logged and email sent successfully.'); window.location.href='faculty.php';</script>";
        } else {
            echo "<script>alert('Violation logged but failed to send email.'); window.location.href='faculty.php';</script>";
        }
    } else {
        echo "<script>alert('Error logging violation.'); window.location.href='faculty.php';</script>";
    }

    $stmt->close();
}
?>
