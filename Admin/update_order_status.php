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

// --- Read and Decode JSON Payload ---
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data || !isset($data['order_id'], $data['status'])) {
    echo json_encode(["success" => false, "message" => "Missing or invalid input data"]);
    exit;
}

// --- Prepare the status update ---
$order_id = intval($data['order_id']);
$status = "COMPLETED";  // We directly set the status to 'READY' for simplicity.

// --- Update Order Status with Prepared Statement ---
$stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Order status updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update order status"]);
}

// --- Close the prepared statement and connection ---
$stmt->close();
mysqli_close($conn);
?>
