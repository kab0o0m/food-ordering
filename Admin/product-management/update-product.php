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
// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';

    // Validate required fields except image
    if ($id <= 0 || $name === '' || $description === '' || $price <= 0 || $category === '') {
        send_json(false, "All fields except image are required.");
    }

    // Fetch current image_url from DB
    $get_img_sql = "SELECT image_url FROM products WHERE id = ? LIMIT 1";
    $get_img_stmt = mysqli_prepare($conn, $get_img_sql);
    mysqli_stmt_bind_param($get_img_stmt, "i", $id);
    mysqli_stmt_execute($get_img_stmt);
    $get_img_result = mysqli_stmt_get_result($get_img_stmt);

    if (mysqli_num_rows($get_img_result) === 0) {
        send_json(false, "Product not found.");
    }

    $row = mysqli_fetch_assoc($get_img_result);
    $existing_image_url = $row['image_url'];

    // Keep the old image URL if not provided
    if ($image_url === '') {
        $image_url = $existing_image_url;
    }

    // Update product
    $update_sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, image_url = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);

    if (!$stmt) {
        send_json(false, "Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $category, $image_url, $id);

    if (mysqli_stmt_execute($stmt)) {
        send_json(true, "Product updated successfully!");
    } else {
        send_json(false, "Update failed: " . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);
}


mysqli_close($conn);
?>