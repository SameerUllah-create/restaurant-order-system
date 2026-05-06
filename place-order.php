<?php
/**
 * Order Processing Page
 * Restaurant Order System
 * 
 * Processes checkout form submission and creates order records
 * Inserts into orders and order_items tables using prepared statements
 */

// Include necessary files
require_once 'includes/config.php';

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

// Don't call session_start() again if already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validate that cart is not empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header("Location: cart.php");
    exit();
}

// Retrieve and sanitize form data
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$special_instructions = isset($_POST['special_instructions']) ? trim($_POST['special_instructions']) : '';
$agree_terms = isset($_POST['agree_terms']) ? 1 : 0;

// Validate required fields
if (empty($full_name) || empty($phone_number) || empty($address) || !$agree_terms) {
    $_SESSION['error'] = "All required fields must be filled and terms must be accepted.";
    header("Location: checkout.php");
    exit();
}

// Validate input formats
if (!preg_match("/^[A-Za-z\s]{2,}$/", $full_name)) {
    $_SESSION['error'] = "Invalid full name format. Only letters and spaces allowed.";
    header("Location: checkout.php");
    exit();
}

if (!preg_match("/^[0-9\-\+\(\)\s]{10,20}$/", $phone_number)) {
    $_SESSION['error'] = "Invalid phone number format.";
    header("Location: checkout.php");
    exit();
}

if (strlen($address) < 10 || strlen($address) > 500) {
    $_SESSION['error'] = "Address must be between 10 and 500 characters.";
    header("Location: checkout.php");
    exit();
}

// Calculate order totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax_rate = 0.10;
$tax_amount = $subtotal * $tax_rate;
$delivery_charge = 2.50;
$total_amount = $subtotal + $tax_amount + $delivery_charge;

// Prepare and execute INSERT into orders table with correct column names
$order_query = "INSERT INTO orders (customer_name, phone, address, total, STATUS, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($order_query);

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: checkout.php");
    exit();
}

// Bind parameters
$order_status = "Pending";
$stmt->bind_param(
    "sssds",
    $full_name,
    $phone_number,
    $address,
    $total_amount,
    $order_status
);

// Execute the statement
if (!$stmt->execute()) {
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header("Location: checkout.php");
    exit();
}

// Get the last inserted order ID
$order_id = $stmt->insert_id;
$stmt->close();

// Check if order was successfully created
if ($order_id === 0) {
    $_SESSION['error'] = "Failed to create order record.";
    header("Location: checkout.php");
    exit();
}

// Loop through cart items and insert into order_items table
$order_item_query = "INSERT INTO order_items (order_id, food_id, quantity, price) 
                     VALUES (?, ?, ?, ?)";

$item_stmt = $conn->prepare($order_item_query);

if (!$item_stmt) {
    // Rollback: Delete the order if item insertion fails
    $conn->query("DELETE FROM orders WHERE id = $order_id");
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: checkout.php");
    exit();
}

// Track successfully inserted items
$items_inserted = 0;
$insertion_errors = array();

foreach ($_SESSION['cart'] as $item) {
    $food_id = intval($item['food_id']);
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);
    
    // Bind parameters for each item
    $item_stmt->bind_param(
        "iidi",
        $order_id,
        $food_id,
        $quantity,
        $price
    );
    
    // Execute item insertion
    if (!$item_stmt->execute()) {
        $insertion_errors[] = "Failed to insert item";
    } else {
        $items_inserted++;
    }
}

$item_stmt->close();

// Store order details in session for success page
$_SESSION['last_order_id'] = $order_id;
$_SESSION['last_order_total'] = $total_amount;
$_SESSION['last_order_name'] = $full_name;

// Clear the cart session
$_SESSION['cart'] = array();

// Close database connection
$conn->close();

// Redirect to success page
header("Location: success.php");
exit();
?>
