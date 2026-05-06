<?php
/**
 * Order Success Page
 * Restaurant Order System
 * 
 * Displays confirmation after successful order placement
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if order was successfully created
if (!isset($_SESSION['last_order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_SESSION['last_order_id']);
$order_total = isset($_SESSION['last_order_total']) ? floatval($_SESSION['last_order_total']) : 0;
$customer_name = isset($_SESSION['last_order_name']) ? htmlspecialchars($_SESSION['last_order_name']) : 'Valued Customer';

// Fetch order details from database
$query = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query Error: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items with food names
$items_query = "SELECT oi.id, oi.order_id, oi.food_id, oi.quantity, oi.price, f.food_name 
                FROM order_items oi
                LEFT JOIN foods f ON oi.food_id = f.food_id
                WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_query);

if (!$items_stmt) {
    die("Query Error: " . $conn->error);
}

$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = array();

while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}

$items_stmt->close();

?>

<!-- Success Message Container -->
<div class="row mb-5">
    <div class="col-12">
        <!-- Success Alert -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="text-center">
                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                <h2 class="mt-3">Order Placed Successfully!</h2>
                <p class="lead mb-0">Thank you for your order, <strong><?php echo $customer_name; ?></strong></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Order Details Column -->
    <div class="col-lg-8">
        <!-- Order Information Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-box"></i> Order Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Order ID</p>
                        <h5 class="text-dark">
                            <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Order Status</p>
                        <h5>
                            <span class="badge bg-warning text-dark">
                                <?php echo ucfirst($order['STATUS']); ?>
                            </span>
                        </h5>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Delivery Address</p>
                        <p class="text-dark">
                            <strong><?php echo htmlspecialchars($order['address']); ?></strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Phone Number</p>
                        <p class="text-dark">
                            <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Order Date & Time</p>
                        <p class="text-dark">
                            <strong><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></strong>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Estimated Delivery</p>
                        <p class="text-dark">
                            <strong>30-45 minutes</strong>
                        </p>
                    </div>
                </div>


            </div>
        </div>

        <!-- Order Items Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Order Items
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Food Name</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($order_items as $item) {
                        ?>
                        <tr>
                            <td class="align-middle">
                                <strong><?php echo htmlspecialchars($item['food_name']); ?></strong>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge bg-info">$<?php echo number_format($item['price'], 2); ?></span>
                            </td>
                            <td class="text-center align-middle">
                                <strong><?php echo $item['quantity']; ?></strong>
                            </td>
                            <td class="text-end align-middle">
                                <strong class="text-warning">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                            </td>
                        </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>What's Next?</strong>
            <ul class="mb-0 mt-2">
                <li>Your order is being prepared by our kitchen team</li>
                <li>You'll receive a call at <strong><?php echo htmlspecialchars($order['phone']); ?></strong> when your order is ready for delivery</li>
                <li>Our delivery partner will arrive within 30-45 minutes</li>
                <li>You can track your order using Order ID: <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></li>
            </ul>
        </div>
    </div>

    <!-- Order Summary Column -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-receipt"></i> Price Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-4">
                    <strong class="h5">Total Amount:</strong>
                    <strong class="h5 text-success">$<?php echo number_format($order['total'], 2); ?></strong>
                </div>

                <!-- Confirmation Number -->
                <div class="alert alert-success text-center" role="alert">
                    <small class="text-muted">Confirmation Number</small>
                    <h6 class="mb-0">
                        <strong><?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </h6>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-warning btn-lg fw-bold mb-2">
                        <i class="fas fa-home"></i> Continue Shopping
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print();">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .sticky-top {
        z-index: 100;
    }
    
    .alert-success {
        border: none;
        background-color: rgba(25, 135, 84, 0.1);
        border-left: 4px solid #198754;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(255, 149, 0, 0.05);
    }
    
    .btn-warning:hover {
        background-color: #ff9500;
        border-color: #ff9500;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
    
    @media print {
        .btn, .alert-dismissible {
            display: none;
        }
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
