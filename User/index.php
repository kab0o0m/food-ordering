<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- Database connection ----
$host = "127.0.0.1";
$port = 3307;           // port from SSH tunnel
$username = "kab0o0m";  
$password = "phantoka123";  // MySQL password
$database = "kab0o0m\$ie4727";

$conn = mysqli_connect($host, $username, $password, $database, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ---- Handle form submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password_plain = $_POST['password'];

    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Users (name, email, password_hash, phone) 
            VALUES ('$name', '$email', '$password_hashed', '$phone')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;text-align:center;'>✅ New user registered successfully!</p>";
    } else {
        echo "<p style='color:red;text-align:center;'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pizza Shop Registration</title>
    <style>
        form { width: 300px; margin: 20px auto; }
        input, button { width: 100%; padding: 8px; margin: 5px 0; }
        table { border-collapse: collapse; width: 60%; margin: 20px auto; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Register New User</h2>
    <form method="POST" action="">
        Name:<br>
        <input type="text" name="name" required><br>
        Email:<br>
        <input type="email" name="email" required><br>
        Phone:<br>
        <input type="text" name="phone" required><br>
        Password:<br>
        <input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>

    <h2 style="text-align:center;">Registered Users</h2>
    <?php
    // ---- Fetch all users ----
    $result = mysqli_query($conn, "SELECT user_id, name, email, phone FROM Users");

    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>{$row['user_id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['phone']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='text-align:center;'>No users registered yet.</p>";
    }

    mysqli_close($conn);
    ?>
</body>
</html>