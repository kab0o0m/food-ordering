<?php
// --- Error Reporting for Debugging ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// --- Database Connection ---
$host = "127.0.0.1";
$port = 3307;
$username = "kab0o0m";
$password = "phantoka123";
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);

// --- Check Connection ---
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// --- Get User Email from Request ---
$email = isset($_GET['email']) ? mysqli_real_escape_string($conn, $_GET['email']) : '';

// --- Fetch Orders for the User ---
$sql = "SELECT o.id, o.customer_name, o.customer_phone, o.customer_email, o.order_date, o.total, o.order_status
        FROM orders o
        WHERE o.customer_email = '$email'
        ORDER BY o.order_date DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Failed to fetch orders"]);
    exit;
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $order_id = $row['id'];
    
    // Fetch the order items for each order
    $item_sql = "SELECT oi.product_name, oi.unit_price, oi.quantity, oi.line_total
                 FROM order_items oi
                 WHERE oi.order_id = '$order_id'";

    $item_result = mysqli_query($conn, $item_sql);
    $items = [];
    
    while ($item = mysqli_fetch_assoc($item_result)) {
        $items[] = $item;
    }

    // Add items to the order
    $row['items'] = $items;
    $orders[] = $row;
}

// Return orders as JSON
echo json_encode(["success" => true, "orders" => $orders]);

mysqli_close($conn);
?>
