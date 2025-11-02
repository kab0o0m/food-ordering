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
    die("DB connection failed: " . mysqli_connect_error());
}

// Get date range from query parameters (default: last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$group_by = isset($_GET['group_by']) ? $_GET['group_by'] : 'daily';

// 1. Total Revenue
$revenue_sql = "SELECT SUM(total) as total_revenue, COUNT(*) as total_orders 
                FROM orders 
                WHERE order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$revenue_data = mysqli_fetch_assoc($revenue_result);

// 2. Most Popular Products
$popular_sql = "SELECT oi.product_name, SUM(oi.quantity) as total_sold, 
                SUM(oi.line_total) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                GROUP BY oi.product_name
                ORDER BY total_sold DESC
                LIMIT 10";
$popular_result = mysqli_query($conn, $popular_sql);

// 3. Sales by Category
$category_sql = "SELECT p.category, SUM(oi.quantity) as total_sold, 
                 SUM(oi.line_total) as revenue
                 FROM order_items oi
                 JOIN orders o ON oi.order_id = o.id
                 JOIN products p ON oi.product_id = p.id
                 WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
                 GROUP BY p.category
                 ORDER BY revenue DESC";
$category_result = mysqli_query($conn, $category_sql);

// 4. Sales Over Time (Daily/Weekly/Monthly)
if ($group_by === 'daily') {
    $time_format = 'DATE(o.order_date)';
} else if ($group_by === 'weekly') {
    $time_format = 'YEARWEEK(o.order_date)';
} else {
    $time_format = 'DATE_FORMAT(o.order_date, "%Y-%m")';
}

$time_sql = "SELECT $time_format as period, 
             COUNT(*) as order_count, 
             SUM(total) as revenue
             FROM orders o
             WHERE o.order_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'
             GROUP BY period
             ORDER BY period ASC";
$time_result = mysqli_query($conn, $time_sql);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoodHub - Sales Report</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        .report-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .report-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .date-filter {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .date-filter form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .filter-btn {
            padding: 0.8rem 2rem;
            background-color: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .filter-btn:hover {
            background-color: #5568d3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .stat-card.revenue .stat-value {
            color: #4caf50;
        }

        .stat-card.orders .stat-value {
            color: #667eea;
        }

        .report-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .report-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 0.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .data-table tr:hover {
            background-color: #f8f9fa;
        }

        .rank-badge {
            display: inline-block;
            background-color: #ffc107;
            color: #000;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">FoodHub Admin</div>
        <ul class="nav-links">
            <li><a href="../homepage/menu.php">HOME</a></li>
            <li><a href="sales-report.php">SALES REPORT</a></li>
            <li><a href="product-management.php">PRODUCTS</a></li>
        </ul>
        <button class="account-btn" onclick="window.location.href='../homepage/menu.php'">EXIT ADMIN</button>
    </nav>

    <div class="report-container">
        <!-- Header -->
        <div class="report-header">
            <h1>📊 Sales Report</h1>
            <p>Comprehensive analytics and insights</p>
        </div>

        <!-- Date Filter -->
        <div class="date-filter">
            <form method="GET" action="sales-report.php">
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" required>
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" required>
                </div>
                <div class="filter-group">
                    <label>Group By</label>
                    <select name="group_by">
                        <option value="daily" <?php echo $group_by === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo $group_by === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo $group_by === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>
                </div>
                <button type="submit" class="filter-btn">Apply Filter</button>
            </form>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card revenue">
                <h3>Total Revenue</h3>
                <div class="stat-value">$<?php echo number_format($revenue_data['total_revenue'], 2); ?></div>
            </div>
            <div class="stat-card orders">
                <h3>Total Orders</h3>
                <div class="stat-value"><?php echo $revenue_data['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Average Order Value</h3>
                <div class="stat-value">
                    $<?php echo $revenue_data['total_orders'] > 0 ? number_format($revenue_data['total_revenue'] / $revenue_data['total_orders'], 2) : '0.00'; ?>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="report-section">
            <h2>🏆 Most Popular Products</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($row = mysqli_fetch_assoc($popular_result)): 
                    ?>
                    <tr>
                        <td>
                            <?php if ($rank <= 3): ?>
                                <span class="rank-badge">#<?php echo $rank; ?></span>
                            <?php else: ?>
                                #<?php echo $rank; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo $row['total_sold']; ?> units</td>
                        <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                    </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Sales by Category -->
        <div class="report-section">
            <h2>📦 Sales by Category</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($category_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo $row['total_sold']; ?></td>
                        <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                        <td><?php echo $revenue_data['total_revenue'] > 0 ? number_format(($row['revenue'] / $revenue_data['total_revenue']) * 100, 1) : 0; ?>%</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Sales Over Time -->
        <div class="report-section">
            <h2>📈 Sales Trend (<?php echo ucfirst($group_by); ?>)</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($time_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['period']); ?></td>
                        <td><?php echo $row['order_count']; ?></td>
                        <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>