<?php
session_start();

// Database connection parameters
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'restaurant_db';

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✓ Database connected successfully!<br><br>";

// Check if admins table exists
$result = $conn->query("SELECT COUNT(*) as count FROM admins");

if ($result) {
    $row = $result->fetch_assoc();
    $admin_count = $row['count'];
    echo "✓ Admins table exists with " . $admin_count . " record(s)<br><br>";
    
    if ($admin_count === 0) {
        echo "<strong>Creating default admin account...</strong><br>";
        
        $username = 'admin';
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = 'admin@restaurant.com';
        $admin_name = 'Administrator';
        
        $insert = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        
        if (!$stmt) {
            echo "Error: " . $conn->error . "<br>";
        } else {
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
            
            if ($stmt->execute()) {
                echo "✓ Default admin account created!<br><br>";
                echo "<strong>LOGIN CREDENTIALS:</strong><br>";
                echo "Username: <strong>admin</strong><br>";
                echo "Password: <strong>admin123</strong><br><br>";
                echo "<a href='admin_login.php' style='padding: 10px; background: orange; color: white; text-decoration: none;'>Go to Login</a>";
            } else {
                echo "Error: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "<strong>Existing admin accounts:</strong><br>";
        $admins = $conn->query("SELECT id, username, email FROM admins");
        while ($admin = $admins->fetch_assoc()) {
            echo "ID: " . $admin['id'] . " | Username: " . $admin['username'] . " | Email: " . $admin['email'] . "<br>";
        }
        echo "<br><a href='admin_login.php' style='padding: 10px; background: orange; color: white; text-decoration: none;'>Go to Login</a>";
    }
} else {
    echo "✗ Admins table doesn't exist. Creating it...<br>";
    
    $create_table = "CREATE TABLE admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        admin_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "✓ Admins table created!<br><br>";
        
        $username = 'admin';
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = 'admin@restaurant.com';
        $admin_name = 'Administrator';
        
        $insert = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
        
        if ($stmt->execute()) {
            echo "✓ Default admin account created!<br><br>";
            echo "<strong>LOGIN CREDENTIALS:</strong><br>";
            echo "Username: <strong>admin</strong><br>";
            echo "Password: <strong>admin123</strong><br><br>";
            echo "<a href='admin_login.php' style='padding: 10px; background: orange; color: white; text-decoration: none;'>Go to Login</a>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "✗ Error creating table: " . $conn->error;
    }
}

$conn->close();
?>
