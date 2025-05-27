<?php
require_once 'includes/db.php';
require_once 'includes/EmailVerification.php';

$message = '';
$messageType = '';

if (isset($_GET['token'])) {
    $verifier = new EmailVerification($conn);
    
    if ($verifier->verifyToken($_GET['token'])) {
        $message = "Email verified successfully! You can now login.";
        $messageType = "success";
    } else {
        $message = "Invalid or expired verification link.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(to right, #ff8a00, #e52e71);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: opacity 0.3s;
        }

        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Verification</h1>
        
        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <a href="index.php" class="button">Go to Login</a>
    </div>
</body>
</html> 