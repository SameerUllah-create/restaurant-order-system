<?php
require_once 'includes/config.php';

// Check if form was submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $admin_name = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '';
    
    if (empty($username) || empty($password) || empty($email) || empty($admin_name)) {
        $message = "<div style='color: red;'>All fields are required!</div>";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if username already exists
        $check_query = "SELECT id FROM admins WHERE username = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "<div style='color: red;'>Username already exists!</div>";
        } else {
            // Insert new admin
            $insert_query = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
            
            if ($insert_stmt->execute()) {
                $message = "<div style='color: green;'><strong>Admin account created successfully!</strong><br>Username: <strong>" . htmlspecialchars($username) . "</strong><br>You can now login with this account.</div>";
            } else {
                $message = "<div style='color: red;'>Error creating admin account: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin Account</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 300px; padding: 8px; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .message { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Create Admin Account</h1>
    
    <?php if (!empty($message)) echo $message; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="admin_name">Admin Name:</label>
            <input type="text" id="admin_name" name="admin_name" required>
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Create Admin Account</button>
    </form>
    
    <hr>
    
    <h2>Quick Start (Use Default Credentials):</h2>
    <p>If you want to create a test admin account quickly, use these credentials:</p>
    <ul>
        <li><strong>Admin Name:</strong> Administrator</li>
        <li><strong>Username:</strong> admin</li>
        <li><strong>Email:</strong> admin@restaurant.com</li>
        <li><strong>Password:</strong> admin123</li>
    </ul>
    <p>Fill in the form above with these values and click "Create Admin Account".</p>
</body>
</html>
