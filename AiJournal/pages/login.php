<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set page title
$pageTitle = "Login";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Handle form submission
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt to log in
    if (empty($errors)) {
        // Get user from database
        $query = "SELECT id, name, email, password_hash FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to dashboard
                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}

// Set dark mode
$darkMode = isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $darkMode ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Journal</title>
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-journal-whills"></i>
                    </div>
                    <h1 class="logo-text">AI Journal</h1>
                </div>
                <div class="theme-toggle" id="themeToggle"></div>
            </div>
            
            <div class="auth-body">
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Please log in to continue your journaling journey</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <div class="alert-icon">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Login Failed</div>
                            <ul class="alert-message">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control" 
                                value="<?php echo htmlspecialchars($email); ?>" 
                                placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="d-flex justify-between align-center">
                            <label for="password" class="form-label">Password</label>
                            <a href="reset-password.php" class="form-link">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" 
                                placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt btn-icon"></i> Login
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="auth-link">Sign up</a></p>
            </div>
        </div>
        
        <div class="auth-artwork">
            <div class="artwork-container">
                <div class="artwork-content">
                    <h2>Track Your Journey</h2>
                    <p>AI Journal helps you document your thoughts and gain insights into your emotional patterns over time.</p>
                    <div class="artwork-features">
                        <div class="feature">
                            <div class="feature-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="feature-text">AI-Powered Sentiment Analysis</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="feature-text">Visual Mood Tracking</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="feature-text">Private & Secure</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark-mode');
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            document.cookie = `darkMode=${isDarkMode}; path=/; max-age=31536000`;
        });
        
        // Password toggle visibility
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = toggle.closest('.input-group').querySelector('input');
                const icon = toggle.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>
</html> 