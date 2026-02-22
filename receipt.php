<?php
/**
 * Receipt Page
 * Display and print sales receipt
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($sale_id == 0) {
    header('Location: sales.php');
    exit();
}

// Get sale details
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();

if (!$sale) {
    header('Location: sales.php');
    exit();
}

// Get sale items
$stmt = $pdo->prepare("SELECT * FROM sale_items WHERE sale_id = ?");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $sale_id; ?> - Phone Shop Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            padding: 2rem;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .receipt-header h1 {
            font-size: 2rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .receipt-header .company-info {
            color: #64748b;
            font-size: 0.925rem;
        }
        
        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
        }
        
        .info-item {
            font-size: 0.925rem;
        }
        
        .info-item strong {
            color: #1e293b;
        }
        
        .info-item span {
            color: #64748b;
        }
        
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        .receipt-table thead {
            background: #1e293b;
            color: white;
        }
        
        .receipt-table th {
            padding: 0.875rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .receipt-table tbody td {
            padding: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.925rem;
        }
        
        .receipt-totals {
            border-top: 2px solid #cbd5e1;
            padding-top: 1rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1rem;
        }
        
        .total-row.grand-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            border-top: 2px solid #cbd5e1;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px dashed #cbd5e1;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>📱 Phone Shop</h1>
            <div class="company-info">
                Sales Receipt<br>
                Tel: +255 XXX XXX XXX | Email: sales@phoneshop.co.tz
            </div>
        </div>
        
        <div class="receipt-info">
            <div class="info-item">
                <strong>Receipt No:</strong> <span>#<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="info-item">
                <strong>Date:</strong> <span><?php echo formatDateTime($sale['sale_date']); ?></span>
            </div>
            <div class="info-item">
                <strong>Customer:</strong> <span><?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?></span>
            </div>
            <div class="info-item">
                <strong>Payment:</strong> <span><?php echo $sale['payment_method']; ?></span>
            </div>
        </div>
        
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                        <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right;"><?php echo formatCurrency($item['unit_price']); ?></td>
                        <td style="text-align: right;"><strong><?php echo formatCurrency($item['subtotal']); ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="receipt-totals">
            <div class="total-row grand-total">
                <span>TOTAL AMOUNT</span>
                <span><?php echo formatCurrency($sale['total_amount']); ?></span>
            </div>
        </div>
        
        <div class="receipt-footer">
            Thank you for your business!<br>
            For inquiries, please contact us at the above details.<br>
            <strong>Goods once sold cannot be returned or exchanged.</strong>
        </div>
        
        <div class="actions no-print">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Receipt</button>
            <a href="new_sale.php" class="btn btn-secondary">← New Sale</a>
            <a href="sales.php" class="btn btn-secondary">View All Sales</a>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>