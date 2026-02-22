<?php
/**
 * Sales History Page
 * View all sales with filtering options
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Date filtering
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

// Get sales with date filter
$stmt = $pdo->prepare("SELECT * FROM sales 
                       WHERE DATE(sale_date) BETWEEN ? AND ? 
                       ORDER BY sale_date DESC");
$stmt->execute([$start_date, $end_date]);
$sales = $stmt->fetchAll();

// Calculate summary
$summary = calculateProfit($pdo, $start_date, $end_date);

$pageTitle = 'Sales History';
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
                    <h1 class="page-title">Sales History</h1>
                    <div class="breadcrumb">Home / Sales</div>
                </div>
            </div>
            
            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-label">Total Sales</div>
                    <div class="stat-value"><?php echo count($sales); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">💵</div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['revenue']); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💸</div>
                    <div class="stat-label">Total Cost</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['cost']); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">Net Profit</div>
                    <div class="stat-value"><?php echo formatCurrency($summary['profit']); ?></div>
                </div>
            </div>
            
            <!-- Filter Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Filter Sales</h2>
                </div>
                
                <form method="GET" action="" class="flex gap-1">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="<?php echo $start_date; ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="<?php echo $end_date; ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">&nbsp;</label>
                        <a href="sales.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Sales List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Sales (<?php echo count($sales); ?>)</h2>
                </div>
                
                <?php if (count($sales) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Date & Time</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total Amount</th>
                                    <th>Cost</th>
                                    <th>Profit</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): 
                                    // Get item count
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = ?");
                                    $stmt->execute([$sale['id']]);
                                    $item_count = $stmt->fetch()['count'];
                                ?>
                                    <tr>
                                        <td><strong>#<?php echo $sale['id']; ?></strong></td>
                                        <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                        <td><?php echo $sale['customer_name'] ?: '-'; ?></td>
                                        <td><span class="badge badge-primary"><?php echo $item_count; ?> item(s)</span></td>
                                        <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                                        <td><?php echo formatCurrency($sale['total_cost']); ?></td>
                                        <td class="text-success"><strong><?php echo formatCurrency($sale['profit']); ?></strong></td>
                                        <td><span class="badge badge-success"><?php echo $sale['payment_method']; ?></span></td>
                                        <td>
                                            <a href="receipt.php?id=<?php echo $sale['id']; ?>" 
                                               class="btn btn-sm btn-primary" target="_blank">View Receipt</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No sales found for the selected period.</p>
                <?php endif; ?>
            </div>
        </div>
    <script src="js/script.js"></script>
</body>
</html>