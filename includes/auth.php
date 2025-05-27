<?php
// Prevent any unwanted output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
require_once 'db.php';

// Initialize response array
$response = ['success' => false, 'error' => null];

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception('Email and password are required');
    }

    // Sanitize inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT id, password, role, active, email_verified FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Database error');
    }
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid email or password');
    }

    $user = $result->fetch_assoc();

    // Verify if account is active
    if ($user['active'] != 1) {
        throw new Exception('Account is inactive. Please contact administrator.');
    }

    // Check if email is verified
    if ($user['email_verified'] != 1) {
        // Create new verification token and send email
        $verifier = new EmailVerification($conn);
        $verifier->resendVerificationEmail($user['id'], $email);
        throw new Exception('Please verify your email address. A new verification link has been sent to your email.');
    }

    // Verify password
    if (!password_verify($_POST['password'], $user['password'])) {
        // Log failed attempt
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $conn->prepare("INSERT INTO login_attempts (user_id, ip_address, attempt_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user['id'], $ip);
        $stmt->execute();
        
        throw new Exception('Invalid email or password');
    }

    // Check for too many failed attempts
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $attempts = $stmt->get_result()->fetch_assoc()['attempts'];

    if ($attempts >= 5) {
        throw new Exception('Too many failed attempts. Please try again later.');
    }

    // Clear failed attempts for this IP
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Set session variables
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();

    // Update last login timestamp
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();

    $response['success'] = true;
    $response['role'] = $user['role'];

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} finally {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Ensure no output has been sent yet
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    echo json_encode($response);
    exit;
}
