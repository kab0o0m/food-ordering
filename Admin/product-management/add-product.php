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
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';

    // Validate required fields
    if ($name === '' || $description === '' || $price <= 0 || $category === '' || $image_url === '') {
        send_json(false, "All fields are required.");
    }

    // Check if product name already exists
    $check_sql = "SELECT id FROM products WHERE name = ? LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $name);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        send_json(false, "A product with this name already exists.");
    }

    // Insert new product using prepared statement
    $insert_sql = "INSERT INTO products (name, description, price, category, image_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);

    if (!$stmt) {
        send_json(false, "Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssdss", $name, $description, $price, $category, $image_url);

    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        send_json(true, "Product added successfully! Product ID: " . $new_id);
    } else {
        send_json(false, "Insert failed: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>