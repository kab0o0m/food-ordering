<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Helper: send JSON and stop
function send_json($ok, $msg) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => $ok,
        "message" => $msg
    ]);
    exit;
}

// ---------- HANDLE POST (AJAX password reset) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // detect JSON body (fetch)
    $rawBody = file_get_contents("php://input");
    $data = json_decode($rawBody, true);

    // fallback: if not JSON (e.g. normal form POST), use $_POST
    if (!$data && !empty($_POST)) {
        $data = $_POST;
    }

    // basic validation
    $email       = isset($data['email']) ? trim($data['email']) : '';
    $newPassword = isset($data['newPassword']) ? $data['newPassword'] : '';

    if ($email === '' || $newPassword === '') {
        send_json(false, "Missing required fields.");
    }

    // Validate password length
    if (strlen($newPassword) < 8) {
        send_json(false, "Password must be at least 8 characters.");
    }

    // Check if email exists
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $checkSql = "SELECT user_id FROM Users WHERE email='$emailEsc' LIMIT 1";
    $checkRes = mysqli_query($conn, $checkSql);
    
    if (!$checkRes || mysqli_num_rows($checkRes) === 0) {
        send_json(false, "No account found with this email address.");
    }

    // Hash new password
    $pwdHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password using prepared statement
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Users SET password_hash = ? WHERE email = ?"
    );

    if (!$stmt) {
        send_json(false, "Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ss", $pwdHash, $email);

    if (mysqli_stmt_execute($stmt)) {
        send_json(true, "Password reset successfully.");
    } else {
        send_json(false, "Update failed: " . mysqli_error($conn));
    }
}

mysqli_close($conn);
?>