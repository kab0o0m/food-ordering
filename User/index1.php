<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodHub - Order Food Online</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">FoodHub</div>
        <ul class="nav-links">
            <li><a href="index.html">HOME</a></li>
            <li><a href="menu.html">MENU</a></li>
            <li><a href="cart.html">MY ORDER</a></li>
            <li><a href="checkout.html">CHECKOUT</a></li>
        </ul>
        <div class="nav-right">
            <button class="account-btn">MY ACCOUNT</button>
        </div>
    </nav>

    <!-- Delivery/Pickup Toggle -->
    <div class="service-toggle">
        <button class="toggle-btn delivery" onclick="selectService('delivery')">🚚 DELIVERY</button>
        <button class="toggle-btn pickup" onclick="selectService('pickup')">🏪 PICK UP</button>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-large">
            <img src="" alt="cheese volcano promo">
            <div class="hero-content">
                <h1 class="hero-title">CHEESE VOLCANO</h1>
                <p class="hero-subtitle">NOW WITH</p>
                <div class="hero-discount">50% OFF</div>
                <button class="order-btn">ORDER NOW</button>
            </div>
        </div>

        <div class="side-promos">
            <div class="promo-card">
                <img src="" alt="free delivery promo">
                <div class="promo-overlay">
                    <h3>FREE DELIVERY*</h3>
                    <h2>EVERYDAY</h2>
                    <p>*WITH MINIMUM $30 PURCHASE</p>
                    <button class="order-btn">ORDER NOW</button>
                </div>
            </div>

            <div class="promo-card">
                <img src="" alt="everyday value promo">
                <div class="promo-overlay">
                    <h3>EVERYDAY VALUE</h3>
                    <h2>1 MEDIUM PIZZA</h2>
                    <h1>$5.99</h1>
                    <button class="order-btn">ORDER NOW</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Deals Section -->
    <section class="deals-section">
        <div class="deals-grid">
            <div class="deal-card">
                <img src="" alt="50% off deal">
                <div class="deal-info">
                    <span class="deal-badge">50% OFF ALL PIZZAS</span>
                    <div class="deal-price">$12.95</div>
                    <p class="deal-description">Get 50% OFF on All Pizzas - Now from just $12.99!</p>
                    <button class="deal-btn">ORDER NOW →</button>
                </div>
            </div>

            <div class="deal-card">
                <img src="" alt="2 pizzas 2 sides deal">
                <div class="deal-info">
                    <span class="deal-badge">2 PIZZAS + 2 SIDES</span>
                    <div class="deal-price">$36.95</div>
                    <p class="deal-description">Get 2 Pizzas + 2 Sides - Treat yourself to your favorite pizzas and
                        delicious sides at great value!</p>
                    <button class="deal-btn">ORDER NOW →</button>
                </div>
            </div>

            <div class="deal-card">
                <img src="" alt="new dominos 3 wbox deal">
                <div class="deal-info">
                    <span class="deal-badge">NEW DOMINO'S 3 W/BOX</span>
                    <div class="deal-price">$25</div>
                    <p class="deal-description">For just $5 SD, hit the rewatch of your meal. Give it away with 2 TWO
                        SIDES or your choice for just $5 each, snack, munch, Get 2 snacks for $10 and 3 mains for $25!
                    </p>
                    <button class="deal-btn">ORDER NOW →</button>
                </div>
            </div>
        </div>
    </section>

    <!-- <script>
        function selectService(service) {
            const deliveryBtn = document.querySelector('.toggle-btn.delivery');
            const pickupBtn = document.querySelector('.toggle-btn.pickup');

            if (service === 'delivery') {
                alert('Delivery service selected! Enter your address to start ordering.');
            } else {
                alert('Pickup service selected! Choose your nearest location.');
            }
        }

        // Add click handlers for order buttons
        document.querySelectorAll('.order-btn, .deal-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                if (!e.target.closest('.hero-large') && !e.target.closest('.promo-card')) {
                    alert('Redirecting to menu page...');
                }
            });
        });
    </script> -->
    <script src="index.js"></script>
</body>

</html>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- Database connection ----
$host = "127.0.0.1";
$port = 3307;           // port from SSH tunnel
$username = "kab0o0m";  
$password = "phantoka123";  // MySQL password
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>