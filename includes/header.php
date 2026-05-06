<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Order System</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #000;
            --accent-color: #ff9500;
            --accent-light: #ffa500;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 8px rgba(255, 149, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-color) !important;
            letter-spacing: 1px;
        }
        
        .navbar-brand:hover {
            color: var(--accent-light) !important;
            transition: color 0.3s ease;
        }
        
        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin-left: 20px;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: var(--accent-color) !important;
        }
        
        .btn-cart {
            background-color: var(--accent-color);
            color: #fff;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 15px;
            padding: 0.5rem 1rem;
        }
        
        .btn-cart:hover {
            background-color: var(--accent-light);
            color: #fff;
            transform: scale(1.05);
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .cart-icon-container {
            position: relative;
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils"></i> Restaurant
            </a>
            
            <!-- Toggler for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-cart position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php
                                // Display cart counter
                                $cart_count = 0;
                                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                                    $cart_count = count($_SESSION['cart']);
                                }
                                
                                if ($cart_count > 0) {
                                    echo '<span class="cart-badge">' . $cart_count . '</span>';
                                }
                            ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <main class="container-fluid mt-4">
