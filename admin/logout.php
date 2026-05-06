<?php
/**
 * Admin Logout
 * Restaurant Order System
 * 
 * Destroys admin session and redirects to login page
 */

// Start session if not already started
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../admin_login.php");
exit();
?>
