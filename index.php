<?php
/**
 * Dashboard - Home Page
 * Displays overview statistics and charts
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Get dashboard statistics
$stats = getDashboardStats($pdo);
$recentSales = getRecentSales($pdo, 5);
$chartData = getSalesChartData($pdo);

$pageTitle = 'Dashboard';
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
                    <h1 class="page-title">Dashboard</h1>
                    <div class="breadcrumb">Home / Dashboard</div>
                </div>
                <div>
                    <span style="color: #64748b;">Welcome, <strong><?php echo $_SESSION['full_name']; ?></strong></span>
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
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-label">Today's Revenue</div>
                    <div class="stat-value"><?php echo formatCurrency($stats['today_revenue']); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">📈</div>
                    <div class="stat-label">Today's Profit</div>
                    <div class="stat-value"><?php echo formatCurrency($stats['today_profit']); ?></div>
                </div>
                
                <div class="stat-card <?php echo $stats['low_stock'] > 0 ? 'warning' : ''; ?>">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-label">Low Stock Items</div>
                    <div class="stat-value"><?php echo $stats['low_stock']; ?></div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Sales & Profit Trend (Last 7 Days)</h2>
                </div>
                <canvas id="salesChart" height="80"></canvas>
            </div>
            
            <!-- Recent Sales -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Sales</h2>
                    <a href="sales.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                
                <?php if (count($recentSales) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sale ID</th>
                                    <th>Date & Time</th>
                                    <th>Total Amount</th>
                                    <th>Profit</th>
                                    <th>Payment Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td>#<?php echo $sale['id']; ?></td>
                                        <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                        <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                                        <td class="text-success"><strong><?php echo formatCurrency($sale['profit']); ?></strong></td>
                                        <td><span class="badge badge-primary"><?php echo $sale['payment_method']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No sales recorded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const chartData = <?php echo json_encode($chartData); ?>;
        
        const dates = chartData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const revenues = chartData.map(item => parseFloat(item.revenue));
        const profits = chartData.map(item => parseFloat(item.profit));
        
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenues,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Profit',
                        data: profits,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'TZS ' + context.parsed.y.toLocaleString();
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="js/script.js"></script>
</body>
</html>