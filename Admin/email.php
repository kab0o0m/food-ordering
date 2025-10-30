<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

function sendOrderEmail($name, $email, $phone, $cart, $total) {
    $mail = new PHPMailer(true);

    // Build order details table
    $orderDetails = "<table border='1' cellspacing='0' cellpadding='6'>
                        <tr><th>Item</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
    foreach ($cart as $item) {
        $lineTotal = $item['price'] * $item['qty'];
        $orderDetails .= "<tr>
                            <td>{$item['name']}</td>
                            <td>{$item['qty']}</td>
                            <td>$" . number_format($item['price'], 2) . "</td>
                            <td>$" . number_format($lineTotal, 2) . "</td>
                          </tr>";
    }
    $orderDetails .= "<tr><td colspan='3'><strong>Grand Total</strong></td><td><strong>$" . number_format($total, 2) . "</strong></td></tr>";
    $orderDetails .= "</table>";

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'johnang009@gmail.com';
        $mail->Password   = 'mlgsoorexsimggjv'; // Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('johnang009@gmail.com', 'FoodHub');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your FoodHub Order Confirmation';
        $mail->Body    = "
            <h3>Thank you for your order, $name!</h3>
            <p>We’ve received your order and will process it soon.</p>
            <p><strong>Contact:</strong> $phone</p>
            <h4>Order Details:</h4>
            $orderDetails
            <p>We will notify you when your order is ready. Thank you for choosing <strong>FoodHub</strong>!</p>
        ";

        $mail->send();
        error_log("✅ Email sent to $email for order by $name.");
    } catch (Exception $e) {
        error_log("❌ Email could not be sent to $email. Error: {$mail->ErrorInfo}");
    }
}
?>
