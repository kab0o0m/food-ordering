<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ---- Database connection ----
$host = "127.0.0.1";
$port = 3307; // SSH tunnel port
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

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit;
}

// Read request body
$rawBody = file_get_contents("php://input");
$data = json_decode($rawBody, true);

// Fallback to POST form if not JSON
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$pwdPlain = isset($data['password']) ? $data['password'] : '';

if ($email === '' || $pwdPlain === '') {
    echo json_encode([
        "success" => false,
        "message" => "Missing email or password."
    ]);
    exit;
}

// Find user by email
$emailEsc = mysqli_real_escape_string($conn, $email);
$sql = "SELECT user_id, name, email, password_hash, phone, role 
        FROM Users 
        WHERE email='$emailEsc'
        LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
    exit;
}

// Check password
$userRow = mysqli_fetch_assoc($result);
$storedHash = $userRow['password_hash'];

if (!password_verify($pwdPlain, $storedHash)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
    exit;
}

// Success
echo json_encode([
    "success" => true,
    "message" => "Login OK",
    "user" => [
        "user_id" => $userRow['user_id'],
        "name"    => $userRow['name'],
        "email"   => $userRow['email'],
        "phone"   => $userRow['phone'],
        "role"    => $userRow['role'],
    ]
]);
exit;
?>
