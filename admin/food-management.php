<?php
/**
 * Food Management Page
 * Restaurant Order System
 * 
 * Allows admins to manage food items: add, edit, delete, and view
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

// Handle POST request for adding new food
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_food') {
        // Get form data
        $food_name = isset($_POST['food_name']) ? trim($_POST['food_name']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        
        // Validate inputs
        if (empty($food_name) || $price <= 0) {
            $error_message = "Please provide a valid food name and price.";
        } else {
            // Handle image upload
            $image_filename = '';
            $upload_dir = '../assets/images/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Check if file was uploaded
            if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['food_image']['tmp_name'];
                $file_name = $_FILES['food_image']['name'];
                $file_size = $_FILES['food_image']['size'];
                $file_type = $_FILES['food_image']['type'];
                
                // Validate file
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($file_type, $allowed_types)) {
                    $error_message = "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.";
                } elseif ($file_size > $max_size) {
                    $error_message = "File size too large. Maximum size is 5MB.";
                } else {
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $image_filename = 'food_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $image_filename;
                    
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // File uploaded successfully
                        // Prepare to insert into database
                    } else {
                        $error_message = "Failed to upload image. Please try again.";
                    }
                }
            } else {
                $error_message = "Please select an image file to upload.";
            }
            
            // Insert into database if no errors
            if (empty($error_message) && !empty($image_filename)) {
                $image_url = 'assets/images/' . $image_filename;
                
                $insert_query = "INSERT INTO foods (food_name, price, image_url) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                
                if ($stmt) {
                    $stmt->bind_param("sds", $food_name, $price, $image_url);
                    
                    if ($stmt->execute()) {
                        $success_message = "Food item added successfully!";
                    } else {
                        $error_message = "Database error: " . $conn->error;
                        // Delete uploaded file if database insertion failed
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                    $stmt->close();
                } else {
                    $error_message = "Database error: " . $conn->error;
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            }
        }
    }
    
    // Handle delete food
    elseif ($action === 'delete_food' && isset($_POST['food_id'])) {
        $food_id = intval($_POST['food_id']);
        
        // First, fetch the image URL to delete the file
        $fetch_query = "SELECT image_url FROM foods WHERE food_id = ?";
        $fetch_stmt = $conn->prepare($fetch_query);
        
        if ($fetch_stmt) {
            $fetch_stmt->bind_param("i", $food_id);
            $fetch_stmt->execute();
            $result = $fetch_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $food = $result->fetch_assoc();
                $image_path = '../' . $food['image_url'];
                
                // Delete from database
                $delete_query = "DELETE FROM foods WHERE food_id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                
                if ($delete_stmt) {
                    $delete_stmt->bind_param("i", $food_id);
                    
                    if ($delete_stmt->execute()) {
                        // Delete image file if it exists
                        if (file_exists($image_path)) {
                            unlink($image_path);
                        }
                        $success_message = "Food item deleted successfully!";
                    } else {
                        $error_message = "Failed to delete food item.";
                    }
                    $delete_stmt->close();
                }
            }
            $fetch_stmt->close();
        }
    }
}

// Fetch all food items
$foods_query = "SELECT food_id, food_name, price, image_url FROM foods ORDER BY food_name ASC";
$foods_result = $conn->query($foods_query);
$foods = array();

if ($foods_result) {
    while ($row = $foods_result->fetch_assoc()) {
        $foods[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Management - Restaurant Admin</title>
    
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
        
        .btn-add-food {
            background-color: var(--accent-color);
            color: white;
            font-weight: 600;
        }
        
        .btn-add-food:hover {
            background-color: #ff8c00;
            color: white;
        }
        
        .table-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .btn-sm-edit {
            background-color: #0d6efd;
            color: white;
            border: none;
        }
        
        .btn-sm-delete {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--accent-color) 0%, #ffa500 100%);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 149, 0, 0.25);
        }
        
        .btn-primary-submit {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            font-weight: 600;
        }
        
        .btn-primary-submit:hover {
            background-color: #ff8c00;
            border-color: #ff8c00;
            color: white;
        }
        
        .logout-btn {
            background-color: #dc3545;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .food-card-preview {
            max-width: 200px;
            max-height: 200px;
        }
        
        .upload-preview {
            display: none;
            margin-top: 15px;
            text-align: center;
        }
        
        .upload-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 6px;
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
                        <a class="nav-link" href="#">
                            <i class="fas fa-list"></i> Orders
                        </a>
                        <a class="nav-link active" href="food-management.php">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="display-6">Food Management</h1>
                            <p class="text-muted">Manage restaurant food items</p>
                        </div>
                        <button class="btn btn-add-food btn-lg" data-bs-toggle="modal" data-bs-target="#addFoodModal">
                            <i class="fas fa-plus"></i> Add New Food
                        </button>
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

                    <!-- Food Items Table -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">All Food Items (<?php echo count($foods); ?>)</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Food Name</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        if (count($foods) > 0) {
                                            foreach ($foods as $food) {
                                                $food_id = htmlspecialchars($food['food_id']);
                                                $food_name = htmlspecialchars($food['food_name']);
                                                $price = htmlspecialchars($food['price']);
                                                $image_url = htmlspecialchars($food['image_url']);
                                    ?>
                                    <tr>
                                        <td class="align-middle">
                                            <img src="../<?php echo $image_url; ?>" alt="<?php echo $food_name; ?>" class="table-img" onerror="this.src='https://via.placeholder.com/50?text=No+Image'">
                                        </td>
                                        <td class="align-middle">
                                            <strong><?php echo $food_name; ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-info">PKR <?php echo number_format($price, 2); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <button class="btn btn-sm btn-sm-edit" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form action="food-management.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                <input type="hidden" name="action" value="delete_food">
                                                <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                                <button type="submit" class="btn btn-sm btn-sm-delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center py-4"><p class="text-muted">No food items found. <a href="#" data-bs-toggle="modal" data-bs-target="#addFoodModal">Add one now</a></p></td></tr>';
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

    <!-- Add Food Modal -->
    <div class="modal fade" id="addFoodModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add New Food Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form action="food-management.php" method="POST" enctype="multipart/form-data" id="addFoodForm">
                        <input type="hidden" name="action" value="add_food">

                        <!-- Food Name -->
                        <div class="mb-3">
                            <label for="foodName" class="form-label fw-bold">
                                Food Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="foodName" 
                                   name="food_name" 
                                   placeholder="e.g., Margherita Pizza"
                                   required>
                            <small class="form-text text-muted">Enter the name of the food item</small>
                        </div>

                        <!-- Price -->
                        <div class="mb-3">
                            <label for="foodPrice" class="form-label fw-bold">
                                Price <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">PKR</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="foodPrice" 
                                       name="price" 
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       required>
                            </div>
                            <small class="form-text text-muted">Enter the price in PKR</small>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="foodImage" class="form-label fw-bold">
                                Food Image <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control form-control-lg" 
                                   id="foodImage" 
                                   name="food_image" 
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   required>
                            <small class="form-text text-muted">Accepted formats: JPEG, PNG, GIF, WebP (Max 5MB)</small>
                        </div>

                        <!-- Image Preview -->
                        <div class="upload-preview" id="uploadPreview">
                            <p class="text-muted small">Image Preview:</p>
                            <img id="previewImage" alt="Preview">
                        </div>

                        <!-- Submit and Cancel Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary-submit btn-lg">
                                <i class="fas fa-save"></i> Add Food Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Image preview functionality
        document.getElementById('foodImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previewImage').src = event.target.result;
                    document.getElementById('uploadPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('uploadPreview').style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
