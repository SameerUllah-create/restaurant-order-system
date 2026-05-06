<?php
/**
 * Shopping Cart Management Page
 * Restaurant Order System
 * 
 * Handles cart operations: add items, update quantities, remove items
 * Displays cart summary with checkout functionality
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize cart in session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle POST requests for adding/updating items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Add item to cart
        if ($action === 'add' && isset($_POST['food_id']) && isset($_POST['quantity'])) {
            $food_id = intval($_POST['food_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($food_id > 0 && $quantity > 0) {
                // Check if item already exists in cart
                if (isset($_SESSION['cart'][$food_id])) {
                    // Update quantity
                    $_SESSION['cart'][$food_id]['quantity'] += $quantity;
                } else {
                    // Fetch food details from database
                    $query = "SELECT food_id, food_name, price, image_url FROM foods WHERE food_id = ?";
                    $stmt = $conn->prepare($query);
                    
                    if ($stmt) {
                        $stmt->bind_param("i", $food_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $food = $result->fetch_assoc();
                            // Add new item to cart
                            $_SESSION['cart'][$food_id] = array(
                                'food_id' => $food['food_id'],
                                'food_name' => $food['food_name'],
                                'price' => $food['price'],
                                'image_url' => $food['image_url'],
                                'quantity' => $quantity
                            );
                        }
                        $stmt->close();
                    }
                }
            }
        }
        
        // Remove item from cart
        elseif ($action === 'remove' && isset($_POST['food_id'])) {
            $food_id = intval($_POST['food_id']);
            if (isset($_SESSION['cart'][$food_id])) {
                unset($_SESSION['cart'][$food_id]);
            }
        }
        
        // Update quantity
        elseif ($action === 'update_quantity' && isset($_POST['food_id']) && isset($_POST['quantity'])) {
            $food_id = intval($_POST['food_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($quantity > 0 && isset($_SESSION['cart'][$food_id])) {
                $_SESSION['cart'][$food_id]['quantity'] = $quantity;
            } elseif ($quantity <= 0 && isset($_SESSION['cart'][$food_id])) {
                unset($_SESSION['cart'][$food_id]);
            }
        }
    }
}

// If food_id and quantity are passed directly from index.php form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_id']) && !isset($_POST['action'])) {
    $_POST['action'] = 'add';
    
    $food_id = intval($_POST['food_id']);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($food_id > 0 && $quantity > 0) {
        if (isset($_SESSION['cart'][$food_id])) {
            $_SESSION['cart'][$food_id]['quantity'] += $quantity;
        } else {
            $query = "SELECT food_id, food_name, price, image_url FROM foods WHERE food_id = ?";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("i", $food_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $food = $result->fetch_assoc();
                    $_SESSION['cart'][$food_id] = array(
                        'food_id' => $food['food_id'],
                        'food_name' => $food['food_name'],
                        'price' => $food['price'],
                        'image_url' => $food['image_url'],
                        'quantity' => $quantity
                    );
                }
                $stmt->close();
            }
        }
    }
}

// Calculate cart totals
$cart_subtotal = 0;
$cart_total_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $cart_subtotal += $item['price'] * $item['quantity'];
    $cart_total_items += $item['quantity'];
}

?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h1 class="display-5 text-dark">
            <i class="fas fa-shopping-cart text-warning"></i> Shopping Cart
        </h1>
        <p class="text-muted">Review and manage your items</p>
    </div>
</div>

<!-- Cart Content -->
<div class="row">
    <div class="col-lg-8">
        <?php
            // Check if cart has items
            if (count($_SESSION['cart']) > 0) {
        ?>
        <!-- Cart Table -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Food Name</th>
                        <th scope="col" class="text-center">Price</th>
                        <th scope="col" class="text-center">Quantity</th>
                        <th scope="col" class="text-center">Subtotal</th>
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach ($_SESSION['cart'] as $item) {
                            $subtotal = $item['price'] * $item['quantity'];
                            $food_id = htmlspecialchars($item['food_id']);
                            $food_name = htmlspecialchars($item['food_name']);
                            $price = htmlspecialchars($item['price']);
                            $quantity = htmlspecialchars($item['quantity']);
                    ?>
                    <tr>
                        <!-- Food Name -->
                        <td class="align-middle">
                            <strong><?php echo $food_name; ?></strong>
                        </td>
                        
                        <!-- Price -->
                        <td class="text-center align-middle">
                            <span class="badge bg-info">$<?php echo number_format($price, 2); ?></span>
                        </td>
                        
                        <!-- Quantity with +/- Buttons -->
                        <td class="text-center align-middle">
                            <form action="cart.php" method="POST" class="d-inline-flex quantity-form" id="qty-form-<?php echo $food_id; ?>">
                                <input type="hidden" name="action" value="update_quantity">
                                <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="decreaseQuantity(<?php echo $food_id; ?>)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                
                                <input type="number" class="form-control form-control-sm text-center mx-2" 
                                       style="width: 70px;" 
                                       name="quantity" 
                                       id="qty-input-<?php echo $food_id; ?>" 
                                       value="<?php echo $quantity; ?>" 
                                       min="1" 
                                       max="50"
                                       onchange="updateQuantity(<?php echo $food_id; ?>)">
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="increaseQuantity(<?php echo $food_id; ?>)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </form>
                        </td>
                        
                        <!-- Subtotal -->
                        <td class="text-center align-middle">
                            <strong class="text-warning">$<?php echo number_format($subtotal, 2); ?></strong>
                        </td>
                        
                        <!-- Remove Button -->
                        <td class="text-center align-middle">
                            <form action="cart.php" method="POST" class="d-inline" onsubmit="return confirm('Remove this item from cart?');">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Continue Shopping Button -->
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <?php
            } else {
                // Empty cart message
                echo '<div class="alert alert-warning text-center py-5" role="alert">';
                echo '<i class="fas fa-inbox" style="font-size: 3rem;"></i>';
                echo '<h4 class="mt-3">Your Cart is Empty</h4>';
                echo '<p class="mb-3">Add some delicious items to get started!</p>';
                echo '<a href="index.php" class="btn btn-warning">Start Shopping</a>';
                echo '</div>';
            }
        ?>
    </div>

    <!-- Cart Summary Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-receipt"></i> Order Summary
                </h5>
            </div>
            <div class="card-body">
                <!-- Cart Stats -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items:</span>
                        <strong><?php echo $cart_total_items; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal:</span>
                        <strong class="text-warning">$<?php echo number_format($cart_subtotal, 2); ?></strong>
                    </div>
                    <hr>
                </div>

                <!-- Delivery Estimate (Optional) -->
                <div class="mb-4 p-3 bg-light rounded">
                    <small class="text-muted">
                        <i class="fas fa-truck"></i> Estimated Delivery: 30-45 minutes
                    </small>
                </div>

                <!-- Checkout Button -->
                <?php
                    if (count($_SESSION['cart']) > 0) {
                        echo '<button class="btn btn-warning btn-lg w-100 fw-bold" onclick="proceedToCheckout()">';
                        echo '<i class="fas fa-credit-card"></i> Proceed to Checkout';
                        echo '</button>';
                    } else {
                        echo '<button class="btn btn-warning btn-lg w-100 fw-bold" disabled>';
                        echo '<i class="fas fa-credit-card"></i> Proceed to Checkout';
                        echo '</button>';
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .quantity-form {
        gap: 5px;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(255, 149, 0, 0.05);
    }
    
    .sticky-top {
        z-index: 100;
    }
    
    .btn-outline-secondary:hover {
        background-color: #ff9500;
        border-color: #ff9500;
        color: white;
    }
    
    .btn-warning:hover {
        background-color: #ff9500;
        border-color: #ff9500;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
</style>

<!-- JavaScript Functions -->
<script>
    function increaseQuantity(foodId) {
        const input = document.getElementById('qty-input-' + foodId);
        if (parseInt(input.value) < 50) {
            input.value = parseInt(input.value) + 1;
            updateQuantity(foodId);
        }
    }
    
    function decreaseQuantity(foodId) {
        const input = document.getElementById('qty-input-' + foodId);
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateQuantity(foodId);
        }
    }
    
    function updateQuantity(foodId) {
        const form = document.getElementById('qty-form-' + foodId);
        form.submit();
    }
    
    function proceedToCheckout() {
        window.location.href = 'checkout.php';
    }
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>
