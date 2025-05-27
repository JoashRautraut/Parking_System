<?php
// Start session with default secure settings
session_start();
require_once 'includes/db.php';

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Verify if the user still exists in database
    $stmt = $conn->prepare("SELECT role, active FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['active'] == 1) {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            switch($row['role']) {
                case 'admin':
                    header('Location: admin.php');
                    break;
                case 'security_guard':
                    header('Location: security_guard.php');
                    break;
                case 'student':
                    header('Location: student.php');
                    break;
                default:
                    // Invalid role, destroy session
                    session_destroy();
                    header('Location: index.php?error=invalid_role');
                    break;
            }
            exit();
        } else {
            // User is inactive
            session_destroy();
            header('Location: index.php?error=account_inactive');
            exit();
        }
    } else {
        // User no longer exists
        session_destroy();
        header('Location: index.php?error=invalid_session');
        exit();
    }
}

// Check if this is a logout or session expiry
$message = '';
$messageType = '';
if (isset($_GET['session']) && $_GET['session'] === 'expired') {
    $message = 'Your session has expired. Please log in again.';
    $messageType = 'error';
} else if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = 'You have been successfully logged out.';
    $messageType = 'success';
} else if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'invalid_role':
            $message = 'Invalid user role. Please contact administrator.';
            break;
        case 'account_inactive':
            $message = 'Your account is inactive. Please contact administrator.';
            break;
        case 'invalid_session':
            $message = 'Invalid session. Please login again.';
            break;
        default:
            $message = 'An error occurred. Please try again.';
    }
    $messageType = 'error';
}
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ff8a00, #e52e71);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #e52e71;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #ff8a00, #e52e71);
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        button:hover {
            opacity: 0.9;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .register-link a {
            color: #e52e71;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        #error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }

        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .message.show {
            display: block;
            animation: fadeIn 0.5s ease;
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

        .login-attempts-message {
            color: #e52e71;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        /* Add loading spinner styles */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #e52e71;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back!</h1>
            <p>Please login to continue</p>
        </div>

        <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?> show">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <div id="error-message" class="message error"></div>
        
        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <input type="email" 
                       name="email" 
                       id="email" 
                       required 
                       placeholder="Email"
                       autocomplete="email"
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                       title="Please enter a valid email address">
            </div>
            <div class="form-group password-container">
                <input type="password" 
                       name="password" 
                       id="password" 
                       required 
                       placeholder="Password"
                       autocomplete="current-password"
                       minlength="8">
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>
            <div id="login-attempts-message" class="login-attempts-message"></div>
            <button type="submit">
                <span>Login</span>
                <div class="spinner" id="loginSpinner"></div>
            </button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
    let loginAttempts = 0;
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 15 * 60 * 1000; // 15 minutes in milliseconds
    let lockoutEndTime = localStorage.getItem('lockoutEndTime');

    function checkLockout() {
        if (lockoutEndTime && new Date().getTime() < parseInt(lockoutEndTime)) {
            const remainingTime = Math.ceil((parseInt(lockoutEndTime) - new Date().getTime()) / 1000 / 60);
            document.getElementById('login-attempts-message').textContent = 
                `Too many failed attempts. Please try again in ${remainingTime} minutes.`;
            document.querySelector('button[type="submit"]').disabled = true;
            return true;
        }
        if (lockoutEndTime && new Date().getTime() >= parseInt(lockoutEndTime)) {
            localStorage.removeItem('lockoutEndTime');
            loginAttempts = 0;
        }
        return false;
    }

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.querySelector('.toggle-password');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.textContent = '👁️‍🗨️';
        } else {
            passwordInput.type = 'password';
            toggleButton.textContent = '👁️';
        }
    }

    window.onload = function() {
        checkLockout();
        const urlParams = new URLSearchParams(window.location.search);
        const message = document.querySelector('.message');
        if (message && message.classList.contains('show')) {
            setTimeout(() => {
                message.classList.remove('show');
            }, 5000);
        }
    }

    async function handleLogin(e) {
        e.preventDefault();
        
        if (checkLockout()) return;

        const form = e.target;
        const formData = new FormData(form);
        const errorMessage = document.getElementById('error-message');
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = document.getElementById('loginSpinner');
        const buttonText = submitButton.querySelector('span');

        try {
            errorMessage.style.display = 'none';
            submitButton.disabled = true;
            buttonText.style.display = 'none';
            spinner.style.display = 'block';

            const response = await fetch('includes/auth.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                loginAttempts = 0;
                buttonText.textContent = 'Redirecting...';
                buttonText.style.display = 'block';
                
                switch (data.role) {
                    case 'admin':
                        window.location.href = 'admin.php';
                        break;
                    case 'security_guard':
                        window.location.href = 'security_guard.php';
                        break;
                    case 'student':
                        window.location.href = 'student.php';
                        break;
                    default:
                        throw new Error('Unknown user role');
                }
            } else {
                throw new Error(data.error || 'Invalid email or password');
            }
        } catch (error) {
            loginAttempts++;
            console.error('Login error:', error);
            
            if (loginAttempts >= MAX_LOGIN_ATTEMPTS) {
                const lockoutEndTime = new Date().getTime() + LOCKOUT_DURATION;
                localStorage.setItem('lockoutEndTime', lockoutEndTime);
                document.getElementById('login-attempts-message').textContent = 
                    'Too many failed attempts. Please try again in 15 minutes.';
                submitButton.disabled = true;
            } else {
                const remainingAttempts = MAX_LOGIN_ATTEMPTS - loginAttempts;
                document.getElementById('login-attempts-message').textContent = 
                    `Login failed. ${remainingAttempts} attempts remaining.`;
            }
            
            errorMessage.textContent = error.message || 'An error occurred during login';
            errorMessage.style.display = 'block';
        } finally {
            submitButton.disabled = false;
            spinner.style.display = 'none';
            buttonText.style.display = 'block';
            buttonText.textContent = 'Login';
        }
    }
    </script>
</body>
</html>
  </rewritten_file> 