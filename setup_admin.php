<?php
require_once 'includes/config.php';

// Check if admins table exists and what's in it
$result = $conn->query("SELECT * FROM admins");

if (!$result) {
    echo "Error: " . $conn->error;
    echo "<br><br>The admins table might not exist. Let me create it for you...";
    
    // Create the admins table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        admin_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<br>✓ Table created successfully!";
        
        // Now insert a default admin account
        $username = 'admin';
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = 'admin@restaurant.com';
        $admin_name = 'Administrator';
        
        $insert = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
        
        if ($stmt->execute()) {
            echo "<br>✓ Default admin account created!";
            echo "<br><br><strong>Login Credentials:</strong>";
            echo "<br>Username: <strong>admin</strong>";
            echo "<br>Password: <strong>admin123</strong>";
            echo "<br><br><a href='admin_login.php'>Go to Login Page</a>";
        } else {
            echo "<br>Error inserting admin: " . $conn->error;
        }
    } else {
        echo "<br>Error creating table: " . $conn->error;
    }
} else {
    // Table exists, show what's in it
    if ($result->num_rows === 0) {
        echo "<h2>No Admin Accounts Found!</h2>";
        echo "<p>The admins table exists but is empty. Let me create a default admin account...</p>";
        
        $username = 'admin';
        $password = 'admin123';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email = 'admin@restaurant.com';
        $admin_name = 'Administrator';
        
        $insert = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
        
        if ($stmt->execute()) {
            echo "<br>✓ Default admin account created!";
            echo "<br><br><strong>Login Credentials:</strong>";
            echo "<br>Username: <strong>admin</strong>";
            echo "<br>Password: <strong>admin123</strong>";
            echo "<br><br><a href='admin_login.php'>Go to Login Page</a>";
        } else {
            echo "<br>Error: " . $conn->error;
        }
    } else {
        echo "<h2>Existing Admin Accounts:</h2>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Admin Name</th><th>Password Hash</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['admin_name']) . "</td>";
            echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><br><strong>⚠️ If you forgot your password, use this to create a new admin account:</strong><br>";
        
        // Create new account form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if (!empty($username) && !empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $email = $username . '@admin.com';
                $admin_name = 'Admin';
                $insert = "INSERT INTO admins (username, password, email, admin_name) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert);
                $stmt->bind_param("ssss", $username, $hashed_password, $email, $admin_name);
                
                if ($stmt->execute()) {
                    echo "<div style='color: green;'><strong>✓ Admin account created!</strong></div>";
                    echo "<strong>Username:</strong> " . htmlspecialchars($username) . "<br>";
                    echo "<strong>Password:</strong> " . htmlspecialchars($password);
                }
            }
        }
        
        echo "<form method='POST'>";
        echo "Username: <input type='text' name='username' required><br>";
        echo "Password: <input type='password' name='password' required><br>";
        echo "<button type='submit'>Create New Admin</button>";
        echo "</form>";
    }
}
?>
