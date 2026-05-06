<?php
session_start();
require_once 'includes/config.php';

// Get the admin account
$query = "SELECT id, username, password FROM admins WHERE username = 'admin'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    echo "<h2>Debug Information:</h2>";
    echo "Username: " . htmlspecialchars($admin['username']) . "<br>";
    echo "Password Hash: " . htmlspecialchars($admin['password']) . "<br><br>";
    
    $test_password = 'admin123';
    
    echo "<h3>Password Verification Tests:</h3>";
    
    // Test 1: password_verify
    $verify_result = password_verify($test_password, $admin['password']);
    echo "1. password_verify('admin123', hash): " . ($verify_result ? "✓ TRUE" : "✗ FALSE") . "<br>";
    
    // Test 2: Plain text comparison
    $plain_result = ($test_password === $admin['password']);
    echo "2. Plain text comparison ('admin123' === hash): " . ($plain_result ? "✓ TRUE" : "✗ FALSE") . "<br>";
    
    // Test 3: Check if it looks like a hash
    $is_hash = (strlen($admin['password']) > 50);
    echo "3. Looks like a hash (length > 50): " . ($is_hash ? "✓ YES" : "✗ NO") . "<br>";
    
    echo "<br><h3>Issue Found:</h3>";
    
    // If password_verify fails AND it's not plain text AND it looks like a hash
    if (!$verify_result && !$plain_result && $is_hash) {
        echo "The password appears to be hashed but verification fails. Rehashing the password...";
        
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = "UPDATE admins SET password = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("s", $new_hash);
        
        if ($stmt->execute()) {
            echo "<br><br>✓ Password has been reset!<br>";
            echo "<strong>Username: admin</strong><br>";
            echo "<strong>Password: admin123</strong><br><br>";
            echo "Please try logging in again: <a href='admin_login.php'>Go to Login</a>";
        } else {
            echo "<br>Error updating password: " . $conn->error;
        }
    } else if (!$verify_result && !$plain_result) {
        echo "Password verification failed for an unknown reason.<br>";
        echo "Resetting password to 'admin123'...";
        
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = "UPDATE admins SET password = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("s", $new_hash);
        
        if ($stmt->execute()) {
            echo "<br><br>✓ Password has been reset!<br>";
            echo "<strong>Username: admin</strong><br>";
            echo "<strong>Password: admin123</strong><br><br>";
            echo "Please try logging in again: <a href='admin_login.php'>Go to Login</a>";
        } else {
            echo "<br>Error: " . $conn->error;
        }
    }
} else {
    echo "Admin account not found!";
}

$conn->close();
?>
