<?php
/**
 * Reports Page
 * Generate various business reports
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Get report type
$report_type = isset($_GET['type']) ? sanitize($_GET['type']) : 'daily';

// Date parameters
$date = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');
$month = isset($_GET['month']) ? sanitize($_GET['month']) : date('Y-m');

// Generate reports based on type
switch($report_type) {
    case 'daily':
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE DATE(sale_date) = ? ORDER BY sale_date DESC");
        $stmt->execute([$date]);
        $sales = $stmt->fetchAll();
        
        $summary = calculateProfit($pdo, $date, $date);
        $report_title = "Daily Sales Report - " . formatDate($date);
        break;
        
    case 'monthly':
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $stmt = $pdo->prepare("SELECT * FROM sales 
                               WHERE DATE(sale_date) BETWEEN ? AND ? 
                               ORDER BY sale_date DESC");
        $stmt->execute([$start_date, $end_date]);
        $sales = $stmt->fetchAll();
        
        $summary = calculateProfit($pdo, $start_date, $end_date);
        $report_title = "Monthly Sales Report - " . date('F Y', strtotime($start_date));
        break;
        
    case 'best_selling':
        $best_selling = getBestSellingProducts($pdo, 20);
        $report_title = "Best Selling Products";
        break;
        
    case 'low_stock':
        $low_stock = getLowStockProducts($pdo);
        $report_title = "Low Stock Alert Report";
        break;
        
    default:
        $report_type = 'daily';
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE DATE(sale_date) = ? ORDER BY sale_date DESC");
        $stmt->execute([$date]);
        $sales = $stmt->fetchAll();
        
        $summary = calculateProfit($pdo, $date, $date);
        $report_title = "Daily Sales Report - " . formatDate($date);
}

$pageTitle = 'Reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Phone Shop Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Reports</h1>
                    <div class="breadcrumb">Home / Reports</div>
                </div>
            </div>
            
            <!-- Report Type Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Select Report Type</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="reports.php?type=daily&date=<?php echo date('Y-m-d'); ?>" 
                       class="btn <?php echo $report_type == 'daily' ? 'btn-primary' : 'btn-secondary'; ?>">
                        📅 Daily Report
                    </a>
                    
                    <a href="reports.php?type=monthly&month=<?php echo date('Y-m'); ?>" 
                       class="btn <?php echo $report_type == 'monthly' ? 'btn-primary' : 'btn-secondary'; ?>">
                        📊 Monthly Report
                    </a>
                    
                    <a href="reports.php?type=best_selling" 
                       class="btn <?php echo $report_type == 'best_selling' ? 'btn-primary' : 'btn-secondary'; ?>">
                        🏆 Best Selling
                    </a>
                    
                    <a href="reports.php?type=low_stock" 
                       class="btn <?php echo $report_type == 'low_stock' ? 'btn-primary' : 'btn-secondary'; ?>">
                        ⚠️ Low Stock Alert
                    </a>
                </div>
            </div>
            
            <!-- Report Filters -->
            <?php if ($report_type == 'daily'): ?>
                <div class="card">
                    <form method="GET" action="" class="flex gap-1">
                        <input type="hidden" name="type" value="daily">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">Select Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $date; ?>" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($report_type == 'monthly'): ?>
                <div class="card">
                    <form method="GET" action="" class="flex gap-1">
                        <input type="hidden" name="type" value="monthly">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">Select Month</label>
                            <input type="month" name="month" class="form-control" value="<?php echo $month; ?>" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Report Content -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?php echo $report_title; ?></h2>
                    <button onclick="window.print()" class="btn btn-primary btn-sm no-print">🖨️ Print Report</button>
                </div>
                
                <?php if ($report_type == 'daily' || $report_type == 'monthly'): ?>
                    <!-- Summary Statistics -->
                    <div class="stats-grid" style="margin-bottom: 2rem;">
                        <div class="stat-card">
                            <div class="stat-label">Total Sales</div>
                            <div class="stat-value"><?php echo count($sales); ?></div>
                        </div>
                        
                        <div class="stat-card success">
                            <div class="stat-label">Total Revenue</div>
                            <div class="stat-value"><?php echo formatCurrency($summary['revenue']); ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-label">Total Cost</div>
                            <div class="stat-value"><?php echo formatCurrency($summary['cost']); ?></div>
                        </div>
                        
                        <div class="stat-card success">
                            <div class="stat-label">Net Profit</div>
                            <div class="stat-value"><?php echo formatCurrency($summary['profit']); ?></div>
                        </div>
                    </div>
                    
                    <!-- Sales Details -->
                    <?php if (count($sales) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sale ID</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Total Amount</th>
                                        <th>Profit</th>
                                        <th>Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales as $sale): ?>
                                        <tr>
                                            <td>#<?php echo $sale['id']; ?></td>
                                            <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                            <td><?php echo $sale['customer_name'] ?: '-'; ?></td>
                                            <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                                            <td class="text-success"><strong><?php echo formatCurrency($sale['profit']); ?></strong></td>
                                            <td><span class="badge badge-primary"><?php echo $sale['payment_method']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No sales data available for this period.</p>
                    <?php endif; ?>
                    
                <?php elseif ($report_type == 'best_selling'): ?>
                    <!-- Best Selling Products -->
                    <?php if (count($best_selling) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Total Sold</th>
                                        <th>Total Revenue</th>
                                        <th>Total Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    foreach ($best_selling as $product): 
                                    ?>
                                        <tr>
                                            <td>
                                                <?php if ($rank <= 3): ?>
                                                    <span style="font-size: 1.5rem;">
                                                        <?php echo $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : '🥉'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <strong><?php echo $rank; ?></strong>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                            <td><span class="badge badge-primary"><?php echo $product['category']; ?></span></td>
                                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                            <td><span class="badge badge-success"><?php echo $product['total_sold']; ?> units</span></td>
                                            <td><strong><?php echo formatCurrency($product['total_revenue']); ?></strong></td>
                                            <td class="text-success"><strong><?php echo formatCurrency($product['total_profit']); ?></strong></td>
                                        </tr>
                                    <?php 
                                        $rank++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No sales data available yet.</p>
                    <?php endif; ?>
                    
                <?php elseif ($report_type == 'low_stock'): ?>
                    <!-- Low Stock Products -->
                    <?php if (count($low_stock) > 0): ?>
                        <div class="alert alert-warning">
                            <strong>⚠️ Alert:</strong> You have <?php echo count($low_stock); ?> product(s) with low stock levels!
                        </div>
                        
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Current Stock</th>
                                        <th>Selling Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                            <td><span class="badge badge-primary"><?php echo $product['category']; ?></span></td>
                                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    <?php echo $product['quantity_in_stock']; ?> units
                                                </span>
                                            </td>
                                            <td><?php echo formatCurrency($product['selling_price']); ?></td>
                                            <td>
                                                <?php if ($product['quantity_in_stock'] == 0): ?>
                                                    <span class="badge badge-danger">OUT OF STOCK</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">LOW STOCK</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            ✓ All products have sufficient stock levels!
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>