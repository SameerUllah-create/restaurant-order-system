<?php
require_once 'includes/config.php';

// Get all admins from the database
$query = "SELECT id, username, email, admin_name FROM admins";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h2>Existing Admin Accounts:</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Admin Name</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['admin_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h2>No Admin Accounts Found</h2>";
    echo "<p>Would you like to create an admin account?</p>";
}
?>
