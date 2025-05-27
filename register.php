<?php
session_start();
require 'includes/db.php';
require_once 'includes/config.php';

// If user is already logged in, redirect them
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .message.success {
            background-color: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .message.error {
            background-color: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .g-recaptcha {
            margin-bottom: 15px;
        }

        .recaptcha-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>

    <div id="message" class="message"></div>

    <form id="registerForm" onsubmit="handleRegister(event)">
        <label>Email:</label>
        <input type="email" 
               name="email" 
               required 
               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
               title="Please enter a valid email address">

        <div class="password-container">
            <label>Password:</label>
            <input type="password" 
                   name="password" 
                   required 
                   minlength="8"
                   title="Password must be at least 8 characters long">
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>

        <label>Role:</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="security_guard">Security Guard</option>
            <option value="admin">Admin</option>
        </select>

        <div class="recaptcha-container">
            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
        </div>

        <button type="submit">
            <span>Register</span>
            <div class="spinner" id="registerSpinner"></div>
        </button>
    </form>

    <div class="login-link">
        Already have an account? <a href="index.php">Login here</a>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.querySelector('input[name="password"]');
    const toggleButton = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = '👁️‍🗨️';
    } else {
        passwordInput.type = 'password';
        toggleButton.textContent = '👁️';
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('span');
    const spinner = document.getElementById('registerSpinner');
    const messageDiv = document.getElementById('message');
    
    // Check if reCAPTCHA is completed
    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
        messageDiv.textContent = 'Please complete the reCAPTCHA verification.';
        messageDiv.className = 'message error';
        messageDiv.style.display = 'block';
        return;
    }
    
    try {
        // Disable form submission
        submitButton.disabled = true;
        buttonText.style.display = 'none';
        spinner.style.display = 'block';
        
        // Create FormData and append reCAPTCHA response
        const formData = new FormData(form);
        formData.append('g-recaptcha-response', recaptchaResponse);
        
        // Send form data
        const response = await fetch('includes/register_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        // Display message
        messageDiv.textContent = data.message;
        messageDiv.className = 'message ' + (data.success ? 'success' : 'error');
        messageDiv.style.display = 'block';
        
        // Reset reCAPTCHA
        grecaptcha.reset();
        
        // If registration was successful, clear the form
        if (data.success) {
            form.reset();
            // Redirect to login page after 3 seconds
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 3000);
        }
        
    } catch (error) {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.className = 'message error';
        messageDiv.style.display = 'block';
        grecaptcha.reset();
    } finally {
        // Re-enable form submission
        submitButton.disabled = false;
        buttonText.style.display = 'block';
        spinner.style.display = 'none';
    }
}
</script>

</body>
</html>
