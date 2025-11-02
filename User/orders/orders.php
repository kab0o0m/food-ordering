<?php
// Fetch orders for the logged-in user (use $_SESSION or JWT token for authentication)
if (!isset($_SESSION['user'])) {
  header('Location: ../login/login.html');
  exit;
}

$user_id = $_SESSION['user']['id'];

// Query orders based on the logged-in user
$sql = "SELECT * FROM orders WHERE customer_email = '$user_id' ORDER BY order_date DESC";
$result = mysqli_query($conn, $sql);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoodHub - My Orders</title>
    <link rel="stylesheet" href="../style.css" />
  </head>

  <body>
    <nav class="navbar">
      <div class="logo">FoodHub</div>
      <ul class="nav-links">
        <li><a href="../homepage/menu.php">HOME</a></li>
        <li><a href="cart.html">MY ORDER</a></li>
      </ul>
      <button class="account-btn" id="accountBtn">MY ACCOUNT</button>
    </nav>

    <div class="order-list-container">
      <h1>Your Orders</h1>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <h3>Order #<?php echo $order['id']; ?></h3>
          <p>Order Date: <?php echo $order['order_date']; ?></p>
          <p>Status: <?php echo $order['order_status']; ?></p>
          <p>Total: $<?php echo $order['total']; ?></p>
          <button>View Order Details</button>
        </div>
      <?php endforeach; ?>
    </div>
  </body>
</html>
