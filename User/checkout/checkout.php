<?php
// --- Error Reporting for Debugging ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// --- Database Connection ---
$host = "127.0.0.1";
$port = 3307;           // SSH tunnel port
$username = "kab0o0m";
$password = "phantoka123";
$database = "kab0o0m\$ie4727"; // e.g. yourusername$yourdbname

$conn = mysqli_connect($host, $username, $password, $database, $port);

// --- Check Connection ---
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// --- Read and Decode JSON Payload ---
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
    exit;
}

// --- Extract Data ---
$name  = mysqli_real_escape_string($conn, $data['name'] ?? '');
$phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
$email = mysqli_real_escape_string($conn, $data['email'] ?? '');
$total = floatval($data['total'] ?? 0);
$cart  = $data['cart'] ?? [];

// --- Validate Required Fields ---
if (empty($name) || empty($phone) || empty($email) || empty($cart)) {
    echo json_encode(["success" => false, "message" => "Missing required order details"]);
    exit;
}

// --- Insert into Orders Table ---
$order_sql = "INSERT INTO orders (customer_name, customer_phone, customer_email, order_date, total, order_status)
              VALUES (?, ?, ?, NOW(), ?, 'Pending')"; // Order status default to 'Pending'
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("sssd", $name, $phone, $email, $total);

if (!$order_stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to insert order"]);
    exit;
}

$order_id = $order_stmt->insert_id;

// --- Insert Each Cart Item into Order Items ---
$item_sql = "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
             VALUES (?, ?, ?, ?, ?, ?)";
$item_stmt = $conn->prepare($item_sql);

foreach ($cart as $item) {
    $product_id   = intval($item['id']);
    $product_name = mysqli_real_escape_string($conn, $item['name']);
    $unit_price   = floatval($item['price']);
    $quantity     = intval($item['qty']);
    $line_total   = $unit_price * $quantity;

    $item_stmt->bind_param("iisddd", $order_id, $product_id, $product_name, $unit_price, $quantity, $line_total);
    if (!$item_stmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to insert item into order items"]);
        exit;
    }
}

// --- Return Success Response ---
echo json_encode([
    "success" => true,
    "order_id" => $order_id,
    "message" => "Order placed successfully"
]);

// --- Close Connections ---
$item_stmt->close();
$order_stmt->close();
$conn->close();
?>
