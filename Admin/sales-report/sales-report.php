<?php
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

// --- Date Range ---
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$group_by = $_GET['group_by'] ?? 'daily';

// --- 1. Total Revenue ---
$revenue_sql = "SELECT SUM(total) AS total_revenue, COUNT(*) AS total_orders 
                FROM orders 
                WHERE order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$revenue_data = mysqli_fetch_assoc($revenue_result) ?: ["total_revenue" => 0, "total_orders" => 0];

// --- 2. Most Popular Products ---
$popular_sql = "SELECT oi.product_name, SUM(oi.quantity) AS total_sold, SUM(oi.line_total) AS revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                GROUP BY oi.product_name
                ORDER BY total_sold DESC
                LIMIT 10";
$popular_result = mysqli_query($conn, $popular_sql);
$popular_data = mysqli_fetch_all($popular_result, MYSQLI_ASSOC) ?: [];

// --- 3. Sales by Category ---
$category_sql = "SELECT p.category, SUM(oi.quantity) AS total_sold, SUM(oi.line_total) AS revenue
                 FROM order_items oi
                 JOIN orders o ON oi.order_id = o.id
                 JOIN products p ON oi.product_id = p.id
                 WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                 GROUP BY p.category
                 ORDER BY revenue DESC";
$category_result = mysqli_query($conn, $category_sql);
$category_data = mysqli_fetch_all($category_result, MYSQLI_ASSOC) ?: [];

// --- 4. Sales Over Time ---
if ($group_by === 'daily') {
    $time_format = 'DATE(o.order_date)';
} elseif ($group_by === 'weekly') {
    $time_format = 'YEARWEEK(o.order_date)';
} else {
    $time_format = 'DATE_FORMAT(o.order_date, "%Y-%m")';
}

$time_sql = "SELECT $time_format AS period, COUNT(*) AS order_count, SUM(total) AS revenue
             FROM orders o
             WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
             GROUP BY period
             ORDER BY period ASC";
$time_result = mysqli_query($conn, $time_sql);
$time_data = mysqli_fetch_all($time_result, MYSQLI_ASSOC) ?: [];

mysqli_close($conn);

// --- Output JSON ---
echo json_encode([
    "success" => true,
    "revenue" => $revenue_data,
    "popular" => $popular_data,
    "category" => $category_data,
    "trend" => $time_data,
]);
?>
