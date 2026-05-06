<?php
/**
 * Customer Homepage
 * Restaurant Order System
 * 
 * Displays all available food items from the database
 * Allows customers to add items to their cart
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize cart in session if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Fetch all food items from the database
$query = "SELECT food_id, food_name, price, image_url FROM foods ORDER BY food_name ASC";
$result = $conn->query($query);

// Check if query executed successfully
if (!$result) {
    die("Query Error: " . $conn->error);
}

// Store all food items in an array
$foods = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }
}

?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h1 class="display-5 text-dark">
            <i class="fas fa-utensils text-warning"></i> Our Menu
        </h1>
        <p class="text-muted">Browse and select your favorite dishes</p>
    </div>
</div>

<!-- Food Items Grid -->
<div class="row g-4">
    <?php
        // Check if there are any food items
        if (count($foods) > 0) {
            foreach ($foods as $food) {
                $food_id = htmlspecialchars($food['food_id']);
                $food_name = htmlspecialchars($food['food_name']);
                $price = htmlspecialchars($food['price']);
                $image_url = htmlspecialchars($food['image_url']);
    ?>
        <!-- Food Card -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 food-card">
                <!-- Food Image -->
                <div class="card-img-top position-relative overflow-hidden" style="height: 200px; background-color: #f5f5f5;">
                    <img src="<?php echo $image_url; ?>" 
                         alt="<?php echo $food_name; ?>" 
                         class="w-100 h-100 object-fit-cover"
                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                    
                    <!-- Price Badge -->
                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2" style="font-size: 1rem;">
                        $<?php echo number_format($price, 2); ?>
                    </span>
                </div>
                
                <!-- Card Body -->
                <div class="card-body d-flex flex-column">
                    <!-- Food Name -->
                    <h5 class="card-title text-dark mb-3">
                        <?php echo $food_name; ?>
                    </h5>
                    
                    <!-- Description (Optional) -->
                    <p class="card-text text-muted small flex-grow-1">
                        Delicious and fresh meal prepared with premium ingredients
                    </p>
                </div>
                
                <!-- Card Footer with Add to Cart Form -->
                <div class="card-footer bg-white border-top-0 pt-3">
                    <form action="cart.php" method="POST" class="w-100">
                        <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                        
                        <!-- Quantity Selector -->
                        <div class="input-group mb-2">
                            <button class="btn btn-outline-secondary" type="button" id="btn-minus-<?php echo $food_id; ?>">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control text-center" 
                                   name="quantity" 
                                   id="qty-<?php echo $food_id; ?>" 
                                   value="1" 
                                   min="1" 
                                   max="10"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="btn-plus-<?php echo $food_id; ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <!-- Add to Cart Button -->
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- JavaScript for Quantity Controls -->
        <script>
            document.getElementById('btn-minus-<?php echo $food_id; ?>').addEventListener('click', function() {
                const qtyInput = document.getElementById('qty-<?php echo $food_id; ?>');
                if (qtyInput.value > 1) {
                    qtyInput.value = parseInt(qtyInput.value) - 1;
                }
            });
            
            document.getElementById('btn-plus-<?php echo $food_id; ?>').addEventListener('click', function() {
                const qtyInput = document.getElementById('qty-<?php echo $food_id; ?>');
                if (qtyInput.value < 10) {
                    qtyInput.value = parseInt(qtyInput.value) + 1;
                }
            });
        </script>

    <?php
            }
        } else {
            // No foods available message
            echo '<div class="col-12">';
            echo '<div class="alert alert-info text-center" role="alert">';
            echo '<h4><i class="fas fa-info-circle"></i> No Items Available</h4>';
            echo '<p>We will be adding items soon. Please check back later!</p>';
            echo '</div>';
            echo '</div>';
        }
    ?>
</div>

<!-- Custom Styles -->
<style>
    .food-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .food-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }
    
    .card-img-top img {
        transition: transform 0.3s ease;
    }
    
    .food-card:hover .card-img-top img {
        transform: scale(1.05);
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    .btn-warning:hover {
        background-color: #ff9500;
        border-color: #ff9500;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
