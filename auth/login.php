<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Rate limiting
    if (!checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
        $error = 'Too many login attempts. Please try again in 5 minutes.';
    } else {
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            $user = getUserByEmail($email);
            
            if ($user && verifyPassword($password, $user['password'])) {
                if ($user['status'] == 'blocked') {
                    $error = 'Your account has been blocked. Please contact support.';
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Remember me functionality
                    if ($remember) {
                        $token = generateToken();
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                        // Store token in database (implement remember_tokens table if needed)
                    }
                    
                    // Redirect to intended page or dashboard
                    $redirect = $_GET['redirect'] ?? '../index.php';
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YBT Digital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo h3 {
            color: #2563eb;
            font-weight: 700;
            margin: 0;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .btn-auth {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .social-login {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-social {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-social:hover {
            background: #f8fafc;
            color: #2563eb;
        }
        
        @media (max-width: 576px) {
            .auth-card {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fas fa-digital-tachograph fa-3x text-primary mb-3"></i>
                <h3>Welcome Back</h3>
                <p class="text-muted mb-0">Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email Address</label>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="divider">
                <span>or continue with</span>
            </div>
            
            <div class="social-login">
                <button type="button" class="btn btn-social" onclick="socialLogin('google')">
                    <i class="fab fa-google"></i>
                </button>
                <button type="button" class="btn btn-social" onclick="socialLogin('facebook')">
                    <i class="fab fa-facebook-f"></i>
                </button>
                <button type="button" class="btn btn-social" onclick="socialLogin('twitter')">
                    <i class="fab fa-twitter"></i>
                </button>
            </div>
            
            <div class="text-center">
                <p class="mb-0">Don't have an account? <a href="signup.php" class="text-decoration-none fw-bold">Sign up</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Social login (placeholder)
        function socialLogin(provider) {
            alert('Social login with ' + provider + ' will be implemented soon!');
        }
        
        // Show/hide password
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input[type="email"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
    </script>
</body>
</html>
