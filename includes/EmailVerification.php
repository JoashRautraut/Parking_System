<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

class EmailVerification {
    private $conn;
    private $mailer;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        
        try {
            $this->mailer = new PHPMailer(true);
            
            // Configure PHPMailer
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->isHTML(true);
            
            // Enable debug output for troubleshooting
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            
        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
            throw new Exception("Email system initialization failed");
        }
    }
    
    public function generateToken() {
        return bin2hex(random_bytes(32)); // 64 characters long
    }
    
    public function createVerificationToken($userId) {
        $token = $this->generateToken();
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $this->conn->prepare("INSERT INTO email_verification (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $token, $expires);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }
    
    public function sendVerificationEmail($email, $token) {
        try {
            $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/parking_system/verify.php?token=" . $token;
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            $this->mailer->Subject = "Verify Your Email Address";
            
            $message = "<!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .button { 
                        background: #007bff; 
                        color: white; 
                        padding: 12px 24px; 
                        text-decoration: none; 
                        border-radius: 4px; 
                        display: inline-block;
                    }
                    .footer { margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Welcome to the Parking System!</h2>
                    <p>Thank you for registering. Please verify your email address by clicking the button below:</p>
                    <p><a href='{$verificationLink}' class='button' style='color: white;'>Verify Email Address</a></p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p>{$verificationLink}</p>
                    <p>This link will expire in 24 hours.</p>
                    <div class='footer'>
                        <p>If you didn't create an account, you can safely ignore this email.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            $this->mailer->Body = $message;
            $this->mailer->AltBody = "Please verify your email address by clicking this link: {$verificationLink}";
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyToken($token) {
        $stmt = $this->conn->prepare("
            SELECT user_id, expires_at 
            FROM email_verification 
            WHERE token = ? 
            AND expires_at > NOW() 
            LIMIT 1
        ");
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Update user as verified
            $updateStmt = $this->conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $row['user_id']);
            
            if ($updateStmt->execute()) {
                // Delete the verification token
                $deleteStmt = $this->conn->prepare("DELETE FROM email_verification WHERE token = ?");
                $deleteStmt->bind_param("s", $token);
                $deleteStmt->execute();
                
                return true;
            }
        }
        
        return false;
    }
    
    public function resendVerificationEmail($userId, $email) {
        // Delete any existing tokens for this user
        $stmt = $this->conn->prepare("DELETE FROM email_verification WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Create new token and send email
        $token = $this->createVerificationToken($userId);
        if ($token) {
            return $this->sendVerificationEmail($email, $token);
        }
        return false;
    }
} 