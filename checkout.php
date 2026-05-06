<?php
/**
 * Checkout Page
 * Restaurant Order System
 * 
 * Allows customers to enter delivery information and finalize their order
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Redirect to cart if cart is empty
if (count($_SESSION['cart']) === 0) {
    header("Location: cart.php");
    exit();
}

// Calculate order total
$order_total = 0;
$cart_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $order_total += $item['price'] * $item['quantity'];
    $cart_items += $item['quantity'];
}

// Add tax calculation (example: 10%)
$tax_rate = 0.10;
$tax_amount = $order_total * $tax_rate;
$delivery_charge = 2.50;
$final_total = $order_total + $tax_amount + $delivery_charge;

?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h1 class="display-5 text-dark">
            <i class="fas fa-clipboard-check text-warning"></i> Checkout
        </h1>
        <p class="text-muted">Enter your delivery information to complete your order</p>
    </div>
</div>

<!-- Main Checkout Content -->
<div class="row">
    <!-- Checkout Form Column -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-location-dot"></i> Delivery Information
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Checkout Form -->
                <form action="place-order.php" method="POST" id="checkoutForm" novalidate>
                    
                    <!-- Full Name Field -->
                    <div class="mb-3">
                        <label for="fullName" class="form-label fw-bold">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="fullName" 
                               name="full_name" 
                               placeholder="Enter your full name"
                               pattern="[A-Za-z\s]+"
                               required>
                        <small class="form-text text-muted">
                            Please enter your full name for delivery
                        </small>
                        <div class="invalid-feedback">
                            Please provide a valid full name.
                        </div>
                    </div>

                    <!-- Phone Number Field -->
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label fw-bold">
                            Phone Number <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control form-control-lg" 
                               id="phoneNumber" 
                               name="phone_number" 
                               placeholder="Enter your phone number"
                               pattern="[0-9\-\+\(\)\s]+"
                               minlength="10"
                               maxlength="20"
                               required>
                        <small class="form-text text-muted">
                            We'll use this to contact you about your order
                        </small>
                        <div class="invalid-feedback">
                            Please provide a valid phone number.
                        </div>
                    </div>

                    <!-- Address Field -->
                    <div class="mb-3">
                        <label for="address" class="form-label fw-bold">
                            Delivery Address <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control form-control-lg" 
                                  id="address" 
                                  name="address" 
                                  rows="4" 
                                  placeholder="Enter your complete delivery address"
                                  minlength="10"
                                  maxlength="500"
                                  required></textarea>
                        <small class="form-text text-muted">
                            Include street address, apartment number, city, and postal code
                        </small>
                        <div class="invalid-feedback">
                            Please provide a complete delivery address (minimum 10 characters).
                        </div>
                    </div>

                    <!-- Special Instructions (Optional) -->
                    <div class="mb-4">
                        <label for="specialInstructions" class="form-label fw-bold">
                            Special Instructions <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control" 
                                  id="specialInstructions" 
                                  name="special_instructions" 
                                  rows="3" 
                                  placeholder="e.g., No onions, extra spicy, allergies, etc."></textarea>
                        <small class="form-text text-muted">
                            Add any special requests or dietary preferences
                        </small>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="agreeTerms" 
                                   name="agree_terms" 
                                   required>
                            <label class="form-check-label" for="agreeTerms">
                                I agree to the <a href="#" class="text-warning text-decoration-none">Terms & Conditions</a> 
                                and <a href="#" class="text-warning text-decoration-none">Privacy Policy</a>
                            </label>
                            <div class="invalid-feedback">
                                You must agree to the terms and conditions.
                            </div>
                        </div>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="cart.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Back to Cart
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg fw-bold">
                            <i class="fas fa-credit-card"></i> Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Summary Column -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="fas fa-receipt"></i> Order Summary
                </h5>
            </div>
            <div class="card-body">
                <!-- Order Items -->
                <div class="mb-4">
                    <h6 class="text-dark mb-3">Items (<?php echo $cart_items; ?>)</h6>
                    <div class="border-bottom pb-3">
                        <?php
                            foreach ($_SESSION['cart'] as $item) {
                                $subtotal = $item['price'] * $item['quantity'];
                        ?>
                        <div class="d-flex justify-content-between mb-2 small">
                            <div>
                                <strong><?php echo htmlspecialchars($item['food_name']); ?></strong>
                                <br>
                                <span class="text-muted">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <div class="text-end">
                                $<?php echo number_format($subtotal, 2); ?>
                            </div>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                </div>

                <!-- Cost Breakdown -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span>$<?php echo number_format($order_total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax (10%):</span>
                        <span>$<?php echo number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Delivery Charge:</span>
                        <span>$<?php echo number_format($delivery_charge, 2); ?></span>
                    </div>
                    <hr>
                </div>

                <!-- Final Total -->
                <div class="d-flex justify-content-between mb-4">
                    <strong class="h5">Total Amount:</strong>
                    <strong class="h5 text-warning">$<?php echo number_format($final_total, 2); ?></strong>
                </div>

                <!-- Delivery Information -->
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <small>
                        <strong>Estimated Delivery:</strong> 30-45 minutes
                    </small>
                </div>

                <!-- Security Badge -->
                <div class="text-center py-3 border-top">
                    <small class="text-muted">
                        <i class="fas fa-lock"></i> Secure Checkout
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .form-control:focus,
    .form-control-lg:focus {
        border-color: #ff9500;
        box-shadow: 0 0 0 0.2rem rgba(255, 149, 0, 0.25);
    }
    
    .btn-warning:hover {
        background-color: #ff9500;
        border-color: #ff9500;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .form-check-input:checked {
        background-color: #ff9500;
        border-color: #ff9500;
    }
    
    .sticky-top {
        z-index: 100;
    }
    
    .text-danger {
        color: #dc3545;
    }
</style>

<!-- Form Validation Script -->
<script>
    // Bootstrap 5 Form Validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('#checkoutForm');
        
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                // Validate phone number format
                const phoneInput = document.getElementById('phoneNumber');
                const phoneRegex = /^[0-9\-\+\(\)\s]{10,20}$/;
                
                if (!phoneRegex.test(phoneInput.value)) {
                    event.preventDefault();
                    event.stopPropagation();
                    phoneInput.classList.add('is-invalid');
                    alert('Please enter a valid phone number');
                    return;
                }
                
                // Validate name (letters and spaces only)
                const nameInput = document.getElementById('fullName');
                const nameRegex = /^[A-Za-z\s]{2,}$/;
                
                if (!nameRegex.test(nameInput.value)) {
                    event.preventDefault();
                    event.stopPropagation();
                    nameInput.classList.add('is-invalid');
                    alert('Please enter a valid full name (letters and spaces only)');
                    return;
                }
                
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>
