<?php
// ------------------- PHP: Handle POST requests -------------------
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Only run POST logic if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Database connection
    $host = "127.0.0.1";
    $port = 3307;
    $username = "kab0o0m";
    $password = "phantoka123";
    $database = "kab0o0m\$ie4727";

    $conn = mysqli_connect($host, $username, $password, $database, $port);

    if (!$conn) {
        echo json_encode(["success" => false, "message" => "DB connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // Get JSON body
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) $data = $_POST;

    $email = trim($data['email'] ?? '');
    $newPassword = $data['newPassword'] ?? '';

    // Validation
    if ($email === '' || $newPassword === '') {
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        exit;
    }

    if (strlen($newPassword) < 8) {
        echo json_encode(["success" => false, "message" => "Password must be at least 8 characters."]);
        exit;
    }

    // Check if email exists
    $emailEsc = mysqli_real_escape_string($conn, $email);
    $res = mysqli_query($conn, "SELECT user_id FROM Users WHERE email='$emailEsc' LIMIT 1");

    if (!$res || mysqli_num_rows($res) === 0) {
        echo json_encode(["success" => false, "message" => "No account found with this email address."]);
        exit;
    }

    // Hash new password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password securely
    $stmt = mysqli_prepare($conn, "UPDATE Users SET password_hash=? WHERE email=?");
    mysqli_stmt_bind_param($stmt, "ss", $hash, $email);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Password reset successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed: " . mysqli_error($conn)]);
    }

    mysqli_close($conn);
    exit;
}
?>

<!-- ------------------- HTML Form ------------------- -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoodHub - Reset Password</title>
    <link rel="stylesheet" href="../style.css" />
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <h1>Reset Password</h1>
        <p>Enter your email and create a new password</p>

        <form id="forgotPasswordForm">
            <!-- Email -->
            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" id="email" required />
                <span class="error-message" id="emailError"></span>
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label>New Password <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" id="newPassword" required />
                    <button type="button" class="toggle-password" data-target="newPassword">👁️</button>
                </div>
                <span class="error-message" id="newPasswordError"></span>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirmPassword" required />
                    <button type="button" class="toggle-password" data-target="confirmPassword">👁️</button>
                </div>
                <span class="error-message" id="confirmPasswordError"></span>
            </div>

            <button type="submit" class="auth-submit-btn">Reset Password</button>
        </form>

        <p>Remember your password? <a href="../login/login.html">Login here</a></p>
    </div>
</div>

<script>
// ------------------- JS: Form Validation + Fetch -------------------
const form = document.getElementById("forgotPasswordForm");

form.addEventListener("submit", function(e) {
    e.preventDefault();
    clearErrors();

    const email = document.getElementById("email").value.trim();
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    let valid = true;
    if (!isValidEmail(email)) {
        showError("emailError", "Please enter a valid email address");
        valid = false;
    }
    if (newPassword.length < 8) {
        showError("newPasswordError", "Password must be at least 8 characters");
        valid = false;
    }
    if (newPassword !== confirmPassword) {
        showError("confirmPasswordError", "Passwords do not match");
        valid = false;
    }

    if (!valid) return;

    fetch("forget-password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, newPassword })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) window.location.href = "../login/login.html";
    })
    .catch(err => {
        console.error(err);
        alert("An unexpected error occurred.");
    });
});

function showError(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
}
function clearErrors() {
    document.querySelectorAll(".error-message").forEach(el => el.textContent = "");
}
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Toggle password visibility
document.querySelectorAll(".toggle-password").forEach(btn => {
    btn.addEventListener("click", function() {
        const target = document.getElementById(this.dataset.target);
        if (target.type === "password") {
            target.type = "text";
            this.textContent = "🙈";
        } else {
            target.type = "password";
            this.textContent = "👁️";
        }
    });
});
</script>
</body>
</html>
