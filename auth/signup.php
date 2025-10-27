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
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $terms = isset($_POST['terms']);
    
    // Rate limiting
    if (!checkRateLimit('signup_' . $_SERVER['REMOTE_ADDR'], 3, 300)) {
        $error = 'Too many signup attempts. Please try again in 5 minutes.';
    } else {
        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields.';
        } elseif (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters long.';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!$terms) {
            $error = 'Please accept the terms and conditions.';
        } else {
            // Check if email already exists
            $existingUser = getUserByEmail($email);
            if ($existingUser) {
                $error = 'An account with this email already exists.';
            } else {
                // Create user
                if (createUser($name, $email, $password)) {
                    $success = 'Account created successfully! You can now sign in.';
                    
                    // Send welcome email (optional)
                    $subject = 'Welcome to YBT Digital!';
                    $message = "
                        <h2>Welcome to YBT Digital, {$name}!</h2>
                        <p>Thank you for joining our community. You can now explore and purchase premium digital products.</p>
                        <p><a href='" . $_SERVER['HTTP_HOST'] . "/digital nest/auth/login.php'>Sign in to your account</a></p>
                        <p>Best regards,<br>The YBT Digital Team</p>
                    ";
                    sendEmail($email, $subject, $message);
                    
                    // Clear form data
                    $_POST = [];
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
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
    <title>Sign Up - YBT Digital</title>
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
            max-width: 450px;
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
        
        .password-strength {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: #ef4444; }
        .strength-medium { background: #f59e0b; }
        .strength-strong { background: #10b981; }
        
        .password-requirements {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
            color: #64748b;
        }
        
        .requirement.met {
            color: #10b981;
        }
        
        .requirement i {
            width: 16px;
            margin-right: 0.5rem;
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
                <h3>Create Account</h3>
                <p class="text-muted mb-0">Join YBT Digital today</p>
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
            
            <form method="POST" class="needs-validation" novalidate id="signupForm">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required minlength="2" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    <label for="name">Full Name</label>
                    <div class="invalid-feedback">
                        Please enter your full name (at least 2 characters).
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <label for="email">Email Address</label>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
                    <label for="password">Password</label>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <div class="requirement" id="req-length">
                            <i class="fas fa-times"></i>
                            <span>At least 6 characters</span>
                        </div>
                        <div class="requirement" id="req-uppercase">
                            <i class="fas fa-times"></i>
                            <span>One uppercase letter</span>
                        </div>
                        <div class="requirement" id="req-lowercase">
                            <i class="fas fa-times"></i>
                            <span>One lowercase letter</span>
                        </div>
                        <div class="requirement" id="req-number">
                            <i class="fas fa-times"></i>
                            <span>One number</span>
                        </div>
                    </div>
                    <div class="invalid-feedback">
                        Password must be at least 6 characters long.
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="invalid-feedback" id="confirmPasswordFeedback">
                        Please confirm your password.
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="../terms.php" target="_blank">Terms of Service</a> and <a href="../privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                    <div class="invalid-feedback">
                        You must accept the terms and conditions.
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                    <label class="form-check-label" for="newsletter">
                        Subscribe to our newsletter for updates and special offers
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-auth" id="submitBtn">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <div class="divider">
                <span>or sign up with</span>
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
                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Sign in</a></p>
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
        
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password)
            };
            
            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById(`req-${req}`);
                const icon = element.querySelector('i');
                
                if (requirements[req]) {
                    element.classList.add('met');
                    icon.className = 'fas fa-check';
                } else {
                    element.classList.remove('met');
                    icon.className = 'fas fa-times';
                }
            });
            
            // Calculate strength
            const metRequirements = Object.values(requirements).filter(Boolean).length;
            let strength = 0;
            let strengthClass = '';
            
            if (metRequirements >= 4) {
                strength = 100;
                strengthClass = 'strength-strong';
            } else if (metRequirements >= 2) {
                strength = 60;
                strengthClass = 'strength-medium';
            } else if (metRequirements >= 1) {
                strength = 30;
                strengthClass = 'strength-weak';
            }
            
            strengthBar.style.width = strength + '%';
            strengthBar.className = 'password-strength-bar ' + strengthClass;
        });
        
        // Confirm password validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const feedback = document.getElementById('confirmPasswordFeedback');
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                feedback.textContent = 'Passwords do not match.';
            } else {
                this.setCustomValidity('');
                feedback.textContent = 'Please confirm your password.';
            }
        });
        
        // Social login (placeholder)
        function socialLogin(provider) {
            alert('Social login with ' + provider + ' will be implemented soon!');
        }
        
        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input[type="text"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Submit button loading state
        document.getElementById('signupForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
