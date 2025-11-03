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

// --- Normalize status to ENUM: Pending | Completed ---
$requested = strtolower(trim($data['status']));
$status = ($requested === 'completed') ? 'Completed' : 'Pending';

// --- Update Order Status with Prepared Statement ---
$order_id = (int)$data['order_id'];
$upd = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
$upd->bind_param("si", $status, $order_id);

if (!$upd->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to update order status"]);
    $upd->close();
    $conn->close();
    exit;
}
$upd->close();

// --- If status is Completed, fetch order + items and email the customer ---
if ($status === 'Completed') {
    // Fetch order
    $ordStmt = $conn->prepare("SELECT id, customer_name, customer_email, total, order_date FROM orders WHERE id = ?");
    $ordStmt->bind_param("i", $order_id);
    $ordStmt->execute();
    $orderRes = $ordStmt->get_result();
    $order = $orderRes->fetch_assoc();
    $ordStmt->close();

    if ($order && !empty($order['customer_email'])) {
        // Fetch items
        $itStmt = $conn->prepare("SELECT product_name, unit_price, quantity, line_total FROM order_items WHERE order_id = ?");
        $itStmt->bind_param("i", $order_id);
        $itStmt->execute();
        $itRes = $itStmt->get_result();
        $items = [];
        while ($row = $itRes->fetch_assoc()) { $items[] = $row; }
        $itStmt->close();

        // --- Compose HTML table for email ---
        $rows = "";
        foreach ($items as $it) {
            $rows .= "<tr>
                <td>".htmlspecialchars($it['product_name'])."</td>
                <td style='text-align:center;'>".(int)$it['quantity']."</td>
                <td style='text-align:right;'>$".number_format((float)$it['unit_price'], 2)."</td>
                <td style='text-align:right;'>$".number_format((float)$it['line_total'], 2)."</td>
            </tr>";
        }
        $table = "<table border='1' cellspacing='0' cellpadding='8' style='border-collapse:collapse;width:100%;max-width:600px;'>
            <thead>
              <tr>
                <th align='left'>Item</th>
                <th align='center'>Qty</th>
                <th align='right'>Price</th>
                <th align='right'>Total</th>
              </tr>
            </thead>
            <tbody>$rows
              <tr>
                <td colspan='3' align='right'><strong>Total</strong></td>
                <td align='right'><strong>$".number_format((float)$order['total'], 2)."</strong></td>
              </tr>
            </tbody>
        </table>";

        // --- Send email (PHPMailer) ---
        try {
            // Adjust these paths to where PHPMailer is installed
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';


            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'johnang009@gmail.com';      // your Gmail
            $mail->Password   = 'mlgsoorexsimggjv';          // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('johnang009@gmail.com', 'FoodHub');
            $mail->addAddress($order['customer_email'], $order['customer_name'] ?: 'Customer');
            $mail->isHTML(true);
            $mail->Subject = "Your FoodHub Order #{$order['id']} is Completed";
            $mail->Body = "
                <div style='font-family:Arial,sans-serif; color:#333;'>
                    <h3>Hi ".htmlspecialchars($order['customer_name']).", your order #{$order['id']} is ready for collection</h3>
                    $table
                    <p style='margin-top:16px;'>Thank you for ordering with <strong>FoodHub</strong>!</p>
                </div>
            ";

            $mail->send();
        } catch (Throwable $e) {
            // Don't fail the API if email fails; just log it.
            error_log("Order completed email failed for order {$order_id}: ".$e->getMessage());
        }
    }
}

echo json_encode(["success" => true, "message" => "Order status updated", "new_status" => $status]);
$conn->close();
