<?php
require_once 'includes/config.php';

echo "<h2>Orders Table Structure:</h2>";
$result = $conn->query("DESCRIBE orders");

if ($result) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><br><h2>Sample Orders:</h2>";
    $sample = $conn->query("SELECT * FROM orders LIMIT 5");
    if ($sample && $sample->num_rows > 0) {
        while ($row = $sample->fetch_assoc()) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    } else {
        echo "No orders found.";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
