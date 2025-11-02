<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "127.0.0.1";
$port = 3307;
$username = "kab0o0m";
$password = "phantoka123";
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);
if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed: " . mysqli_connect_error()
    ]);
    exit;
}

// Helper function
function send_json($ok, $msg) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => $ok,
        "message" => $msg
    ]);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON body
    $rawBody = file_get_contents("php://input");
    $data = json_decode($rawBody, true);

    $id = isset($data['id']) ? intval($data['id']) : 0;

    if ($id <= 0) {
        send_json(false, "Invalid product ID.");
    }

    // Check if product exists
    $check_sql = "SELECT id FROM products WHERE id = ? LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) === 0) {
        send_json(false, "Product not found.");
    }

    // Check if product is in any orders (optional - prevents deleting products with order history)
    $order_check_sql = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
    $order_stmt = mysqli_prepare($conn, $order_check_sql);
    mysqli_stmt_bind_param($order_stmt, "i", $id);
    mysqli_stmt_execute($order_stmt);
    $order_result = mysqli_stmt_get_result($order_stmt);
    $order_data = mysqli_fetch_assoc($order_result);

    if ($order_data['count'] > 0) {
        send_json(false, "Cannot delete product. It has been ordered " . $order_data['count'] . " times. Consider marking it as unavailable instead.");
    }

    // Delete product using prepared statement
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);

    if (!$stmt) {
        send_json(false, "Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        send_json(true, "Product deleted successfully!");
    } else {
        send_json(false, "Delete failed: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>