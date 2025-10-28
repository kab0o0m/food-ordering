<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- Database connection ----
$host = "127.0.0.1";
$port = 3307;           // SSH tunnel port
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

// Always return JSON
header('Content-Type: application/json');

// Small helper
function respond($ok, $msg, $extra = []) {
    echo json_encode(array_merge([
        "success" => $ok,
        "message" => $msg
    ], $extra));
    exit;
}

// figure out method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Expect ?email=something
    if (!isset($_GET['email']) || trim($_GET['email']) === '') {
        respond(false, "Missing email");
    }

    $email = mysqli_real_escape_string($conn, $_GET['email']);

    $sql = "SELECT user_id, name, email, phone FROM Users WHERE email = '$email' LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        respond(true, "User fetched", [
            "user" => $row
        ]);
    } else {
        respond(false, "User not found");
    }

} else if ($method === 'POST') {
    // We'll accept JSON body
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    // Fallback to form-encoded if not JSON
    if (!$data && !empty($_POST)) {
        $data = $_POST;
    }

    if (!$data) {
        respond(false, "No data received");
    }

    // required fields
    $email    = isset($data['email']) ? trim($data['email']) : '';
    $name     = isset($data['name']) ? trim($data['name']) : '';
    $phone    = isset($data['phone']) ? trim($data['phone']) : '';
    $password = isset($data['password']) ? $data['password'] : ''; // may be blank

    if ($email === '' || $name === '' || $phone === '') {
        respond(false, "Missing required fields (email, name, phone)");
    }

    // sanitize
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $nameEsc  = mysqli_real_escape_string($conn, $name);
    $phoneEsc = mysqli_real_escape_string($conn, $phone);

    // Check that user exists first
    $checkSql = "SELECT user_id FROM Users WHERE email = '$emailEsc' LIMIT 1";
    $checkRes = mysqli_query($conn, $checkSql);
    if (!$checkRes || mysqli_num_rows($checkRes) === 0) {
        respond(false, "User not found, cannot update");
    }
    $userRow = mysqli_fetch_assoc($checkRes);
    $userId  = (int)$userRow['user_id'];

    // Build update query
    if ($password !== '') {
        // user wants to change password
        if (strlen($password) < 8) {
            respond(false, "Password too short (min 8 chars)");
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $hashEsc = mysqli_real_escape_string($conn, $hash);

        $updateSql = "
            UPDATE Users
            SET name = '$nameEsc',
                phone = '$phoneEsc',
                password_hash = '$hashEsc'
            WHERE user_id = $userId
            LIMIT 1
        ";
    } else {
        // no password change
        $updateSql = "
            UPDATE Users
            SET name = '$nameEsc',
                phone = '$phoneEsc'
            WHERE user_id = $userId
            LIMIT 1
        ";
    }

    if (!mysqli_query($conn, $updateSql)) {
        respond(false, "Update failed: " . mysqli_error($conn));
    }

    // send back clean data (not the password)
    respond(true, "Profile updated", [
        "user" => [
            "user_id" => $userId,
            "name"    => $name,
            "email"   => $email,
            "phone"   => $phone
        ]
    ]);

} else {
    // method not allowed
    http_response_code(405);
    respond(false, "Method not allowed");
}

mysqli_close($conn);
