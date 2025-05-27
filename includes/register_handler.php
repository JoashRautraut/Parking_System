<?php
require_once 'db.php'; // Make sure this connects to your database
require_once 'EmailVerification.php';

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    
    // Validate inputs
    if (empty($email) || empty($password) || empty($role)) {
        throw new Exception('All fields are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format.');
    }

    // Validate password length
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long.');
    }

    // Validate role
    $allowed_roles = ['student', 'security_guard', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        throw new Exception('Invalid role selected.');
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database preparation failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Database execution failed: ' . $stmt->error);
    }

    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception('Email already registered.');
    }
    $stmt->close();

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Could not begin transaction');
    }

    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        $stmt = $conn->prepare("INSERT INTO users (email, password, role, email_verified) VALUES (?, ?, ?, 0)");
        if (!$stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        $stmt->bind_param("sss", $email, $hashedPassword, $role);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user account: ' . $stmt->error);
        }

        $userId = $conn->insert_id;
        $stmt->close();

        // Create and send verification email
        $verifier = new EmailVerification($conn);
        $token = $verifier->createVerificationToken($userId);

        if (!$token || !$verifier->sendVerificationEmail($email, $token)) {
            throw new Exception('Failed to send verification email. Please try again.');
        }

        // Commit transaction
        if (!$conn->commit()) {
            throw new Exception('Could not commit transaction');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Please check your email to verify your account.'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
