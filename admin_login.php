<?php
/**
 * Admin Login Page
 * Restaurant Order System
 * 
 * Authenticates admin users against the admins table
 * Sets session variables for authenticated admins
 */

// Include config file
require_once 'includes/config.php';

// Initialize error message
$login_error = '';
$login_success = false;

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate inputs are not empty
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password.";
    } else {
        // Prepare SQL statement to fetch admin by username
        $query = "SELECT id, username, password, email, admin_name FROM admins WHERE username = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            $login_error = "Database error. Please try again later.";
        } else {
            // Bind parameter
            $stmt->bind_param("s", $username);
            
            // Execute query
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                // Check if admin user exists
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify password - check both hashed and plain text passwords
                    $password_valid = false;
                    
                    // First try password_verify (for bcrypt hashed passwords)
                    if (password_verify($password, $admin['password'])) {
                        $password_valid = true;
                    } 
                    // Fallback to plain text comparison (for plain text passwords in database)
                    else if ($password === $admin['password']) {
                        $password_valid = true;
                    }
                    
                    if ($password_valid) {
                        // Password is correct - set session variables
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_name'] = $admin['admin_name'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['admin_logged_in'] = true;
                        
                        // Log successful login
                        error_log("Admin login successful: " . $admin['username']);
                        
                        // Redirect to dashboard
                        header("Location: admin/dashboard.php");
                        exit();
                    } else {
                        // Password is incorrect
                        $login_error = "Invalid username or password.";
                        error_log("Failed login attempt for username: " . $username);
                    }
                } else {
                    // Username not found
                    $login_error = "Invalid username or password.";
                    error_log("Login attempt with non-existent username: " . $username);
                }
            } else {
                $login_error = "Database query failed. Please try again.";
            }
            
            $stmt->close();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Restaurant Order System</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: none;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #ff9500 0%, #ffa500 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.95;
            margin: 0;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-control {
            border: 1.5px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        .form-control:focus {
            border-color: #ff9500;
            box-shadow: 0 0 0 0.2rem rgba(255, 149, 0, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #ff9500 0%, #ffa500 100%);
            border: none;
            color: white;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #e68a00 0%, #ff9500 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 149, 0, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #721c24;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-danger .alert-icon {
            margin-right: 10px;
        }
        
        .input-group-icon {
            position: relative;
        }
        
        .input-group-icon i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            pointer-events: none;
        }
        
        .form-check {
            margin-top: 16px;
            margin-bottom: 20px;
        }
        
        .form-check-input {
            border-color: #ddd;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .form-check-input:checked {
            background-color: #ff9500;
            border-color: #ff9500;
        }
        
        .form-check-label {
            margin-left: 8px;
            font-size: 0.9rem;
            color: #555;
            cursor: pointer;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #999;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: #ff9500;
        }
    </style>
</head>
<body>
    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h1>Admin Portal</h1>
                <p>Restaurant Order System</p>
            </div>
            
            <!-- Login Form -->
            <div class="login-body">
                <!-- Error Message -->
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle alert-icon"></i>
                        <strong>Login Failed!</strong><br>
                        <?php echo htmlspecialchars($login_error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form action="admin_login.php" method="POST" id="loginForm" novalidate>
                    
                    <!-- Username Field -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username"
                               autocomplete="username"
                               required
                               autofocus>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="input-group-icon">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   autocomplete="current-password"
                                   required>
                            <span class="password-toggle" onclick="togglePasswordVisibility()" title="Show/Hide Password">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Remember Me Checkbox -->
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="rememberMe" 
                               name="remember_me">
                        <label class="form-check-label" for="rememberMe">
                            Remember me
                        </label>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <i class="fas fa-shield-alt"></i> Secure Admin Access
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password Visibility Toggle -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
        
        // Auto-focus on username field
        window.addEventListener('load', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>
