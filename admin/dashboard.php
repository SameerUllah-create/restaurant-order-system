<?php
/**
 * Admin Dashboard
 * Restaurant Order System
 * 
 * Main dashboard for authenticated admin users
 */

// Include config file
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../admin_login.php");
    exit();
}

// Get admin information from session
$admin_name = isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin';
$admin_username = isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Unknown';

// Fetch some basic statistics from the database
$total_orders_query = "SELECT COUNT(*) as total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'] ?? 0;

$pending_orders_query = "SELECT COUNT(*) as pending_orders FROM orders WHERE STATUS = 'Pending'";
$pending_orders_result = $conn->query($pending_orders_query);
$pending_orders = $pending_orders_result->fetch_assoc()['pending_orders'] ?? 0;

$completed_orders_query = "SELECT COUNT(*) as completed_orders FROM orders WHERE STATUS = 'Delivered'";
$completed_orders_result = $conn->query($completed_orders_query);
$completed_orders = $completed_orders_result->fetch_assoc()['completed_orders'] ?? 0;

$total_revenue_query = "SELECT SUM(total) as total_revenue FROM orders WHERE STATUS = 'Delivered'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Restaurant Order System</title>
    
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
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-top: 4px solid var(--accent-color);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #000;
        }
        
        .stat-label {
            color: #999;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .logout-btn {
            background-color: #dc3545;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                        <a class="nav-link" href="order-management.php">
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
                        <h1 class="display-6">Dashboard</h1>
                        <p class="text-muted">Welcome back, <?php echo $admin_name; ?>!</p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <!-- Total Orders Card -->
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="stat-value"><?php echo $total_orders; ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>

                        <!-- Pending Orders Card -->
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="stat-value"><?php echo $pending_orders; ?></div>
                                <div class="stat-label">Pending Orders</div>
                            </div>
                        </div>

                        <!-- Completed Orders Card -->
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-value"><?php echo $completed_orders; ?></div>
                                <div class="stat-label">Completed Orders</div>
                            </div>
                        </div>

                        <!-- Total Revenue Card -->
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                        </div>
                    </div>

                    <!-- Welcome Section -->
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Quick Start</h5>
                        </div>
                        <div class="card-body">
                            <p>You're now logged into the Restaurant Order System Admin Panel. Use the sidebar navigation to manage orders, food items, and customers.</p>
                            <p class="mb-0"><strong>Admin Username:</strong> <?php echo $admin_username; ?></p>
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
