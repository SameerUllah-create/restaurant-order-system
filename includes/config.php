<?php
/**
 * Database Configuration File
 * Restaurant Order System
 * 
 * This file handles database connection initialization and session management
 * with comprehensive error handling and security best practices.
 */

// Start the session at the beginning
session_start();

// Database connection parameters
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'restaurant_db';

// Create MySQLi connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check if connection failed
if ($conn->connect_error) {
    // Log error details for debugging
    error_log("Database Connection Failed: " . $conn->connect_error, 0);
    
    // Display user-friendly error message (avoid exposing sensitive details)
    die("Connection Error: Unable to connect to the database. Please try again later.");
}

// Set the character set to UTF-8 for proper data encoding
$conn->set_charset("utf8mb4");

// Set error reporting mode to throw exceptions for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Verify session is initialized properly
if (!isset($_SESSION['initialized'])) {
    $_SESSION['initialized'] = true;
}

?>
