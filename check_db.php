<?php
require_once 'includes/config.php';

// Get the admins table structure
$result = $conn->query("DESCRIBE admins");

if ($result) {
    echo "<h2>Admins Table Structure:</h2>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Error: " . $conn->error;
}
?>
