<?php
/**
 * Order Management Page
 * Restaurant Order System
 * 
 * Allows admins to view and manage orders
 */

// Include config file
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../admin_login.php");
    exit();
}

$admin_name = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin';
$success_message = '';
$error_message = '';

// Handle POST request for updating order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_status' && isset($_POST['order_id']) && isset($_POST['order_status'])) {
        $order_id = intval($_POST['order_id']);
        $order_status = trim($_POST['order_status']);
        
        // Validate status is one of the allowed values
        $allowed_statuses = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];
        
        if (!in_array($order_status, $allowed_statuses)) {
            $error_message = "Invalid status value.";
        } else {
            // Prepare and execute UPDATE query
            $update_query = "UPDATE orders SET STATUS = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            
            if ($stmt) {
                $stmt->bind_param("si", $order_status, $order_id);
                
                if ($stmt->execute()) {
                    $success_message = "Order status updated successfully!";
                } else {
                    $error_message = "Failed to update order status. Please try again.";
                }
                $stmt->close();
            } else {
                $error_message = "Database error: " . $conn->error;
            }
        }
    }
}

// Fetch all orders with optional filtering
$search = '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$orders_query = "SELECT id, customer_name, phone, address, total, STATUS, created_at 
                 FROM orders 
                 WHERE 1=1";

// Add filters if provided
if (!empty($filter_status)) {
    $orders_query .= " AND STATUS = ?";
}

$orders_query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($orders_query);

if (!$stmt) {
    die("Query Error: " . $conn->error);
}

// Bind filter parameter if exists
if (!empty($filter_status)) {
    $stmt->bind_param("s", $filter_status);
}

$stmt->execute();
$orders_result = $stmt->get_result();
$orders = array();

while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Restaurant Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #000;
            --accent-color: #ff9500;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color) !important;
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 70px);
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 149, 0, 0.1);
            border-left-color: var(--accent-color);
            color: var(--accent-color);
        }
        
        .main-content {
            padding: 30px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-preparing {
            background-color: #0d6efd;
            color: white;
        }
        
        .status-delivered {
            background-color: #198754;
            color: white;
        }
        
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        .status-dropdown {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .status-dropdown:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 149, 0, 0.25);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(255, 149, 0, 0.05);
        }
        
        .filter-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .filter-btn:hover {
            background-color: #ff8c00;
            color: white;
        }
        
        .filter-btn.active {
            background-color: #e68a00;
        }
        
        .logout-btn {
            background-color: #dc3545;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .order-detail-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .order-detail-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-utensils"></i> Restaurant Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <strong><?php echo $admin_name; ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-btn btn btn-danger text-white ms-3" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row" style="margin-top: -30px;">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="sidebar">
                    <nav class="nav flex-column mt-3">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="order-management.php">
                            <i class="fas fa-list"></i> Orders
                        </a>
                        <a class="nav-link" href="food-management.php">
                            <i class="fas fa-utensils"></i> Food Items
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-users"></i> Customers
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
                    <!-- Page Header -->
                    <div class="mb-4">
                        <h1 class="display-6">Order Management</h1>
                        <p class="text-muted">View and manage all customer orders</p>
                    </div>

                    <!-- Success Message -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Error Message -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Bar -->
                    <div class="mb-4 d-flex gap-2 flex-wrap">
                        <a href="order-management.php" class="btn filter-btn <?php echo empty($filter_status) ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> All Orders
                        </a>
                        <a href="order-management.php?status=Pending" class="btn filter-btn <?php echo $filter_status === 'Pending' ? 'active' : ''; ?>">
                            <i class="fas fa-hourglass-half"></i> Pending
                        </a>
                        <a href="order-management.php?status=Preparing" class="btn filter-btn <?php echo $filter_status === 'Preparing' ? 'active' : ''; ?>">
                            <i class="fas fa-fire"></i> Preparing
                        </a>
                        <a href="order-management.php?status=Delivered" class="btn filter-btn <?php echo $filter_status === 'Delivered' ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i> Delivered
                        </a>
                    </div>

                    <!-- Orders Table -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">All Orders (<?php echo count($orders); ?>)</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer Name</th>
                                        <th>Phone Number</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Ordered At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if (count($orders) > 0) {
                                            foreach ($orders as $order) {
                                                $order_id = htmlspecialchars($order['id']);
                                                $customer_name = htmlspecialchars($order['customer_name']);
                                                $phone = htmlspecialchars($order['phone']);
                                                $total = htmlspecialchars($order['total']);
                                                $order_status = htmlspecialchars($order['STATUS']);
                                                $created_at = htmlspecialchars($order['created_at']);
                                                
                                                // Determine status badge class
                                                $status_class = 'status-' . strtolower($order_status);
                                    ?>
                                    <tr>
                                        <td class="align-middle">
                                            <strong>#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <strong><?php echo $customer_name; ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <i class="fas fa-phone"></i> <?php echo $phone; ?>
                                        </td>
                                        <td class="align-middle">
                                            <strong class="text-warning">PKR <?php echo number_format($total, 2); ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <!-- Status Update Form -->
                                            <form action="order-management.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                
                                                <select name="order_status" class="status-dropdown" onchange="this.form.submit()">
                                                    <option value="Pending" <?php echo $order_status === 'Pending' ? 'selected' : ''; ?>>
                                                        Pending
                                                    </option>
                                                    <option value="Preparing" <?php echo $order_status === 'Preparing' ? 'selected' : ''; ?>>
                                                        Preparing
                                                    </option>
                                                    <option value="Delivered" <?php echo $order_status === 'Delivered' ? 'selected' : ''; ?>>
                                                        Delivered
                                                    </option>
                                                    <option value="Cancelled" <?php echo $order_status === 'Cancelled' ? 'selected' : ''; ?>>
                                                        Cancelled
                                                    </option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted">
                                                <?php echo date('M d, Y h:i A', strtotime($created_at)); ?>
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderDetailModal<?php echo $order_id; ?>" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Order Detail Modal -->
                                    <div class="modal fade" id="orderDetailModal<?php echo $order_id; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title">
                                                        Order Details #<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">Customer Name</p>
                                                            <h6><?php echo $customer_name; ?></h6>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">Phone Number</p>
                                                            <h6><?php echo $phone; ?></h6>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="text-muted mb-1">Delivery Address</p>
                                                        <h6><?php echo htmlspecialchars($order['address']); ?></h6>
                                                    </div>
                                                    <hr>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">Total Amount</p>
                                                            <h6 class="text-warning">PKR <?php echo number_format($total, 2); ?></h6>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="text-muted mb-1">Order Status</p>
                                                            <span class="status-badge <?php echo $status_class; ?>">
                                                                <?php echo ucfirst($order_status); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div>
                                                        <p class="text-muted mb-1">Order Date</p>
                                                        <h6><?php echo date('M d, Y h:i A', strtotime($created_at)); ?></h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center py-4"><p class="text-muted">No orders found.</p></td></tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
