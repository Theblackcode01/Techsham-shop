<?php
/**
 * New Sale Page
 * Process new sales with cart functionality
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    $product = getProductById($pdo, $product_id);
    
    if ($product && $quantity > 0 && $quantity <= $product['quantity_in_stock']) {
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'product_name' => $product['product_name'],
                'selling_price' => $product['selling_price'],
                'buying_price' => $product['buying_price'],
                'quantity' => $quantity
            ];
        }
        
        setFlashMessage('success', 'Product added to cart!');
    } else {
        setFlashMessage('error', 'Invalid quantity or insufficient stock!');
    }
    
    header('Location: new_sale.php');
    exit();
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $index = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
        setFlashMessage('success', 'Item removed from cart!');
    }
    header('Location: new_sale.php');
    exit();
}

// Process sale
if (isset($_POST['process_sale'])) {
    if (count($_SESSION['cart']) > 0) {
        $payment_method = sanitize($_POST['payment_method']);
        $customer_name = sanitize($_POST['customer_name']);
        
        try {
            $pdo->beginTransaction();
            
            // Calculate totals
            $total_amount = 0;
            $total_cost = 0;
            
            foreach ($_SESSION['cart'] as $item) {
                $total_amount += $item['selling_price'] * $item['quantity'];
                $total_cost += $item['buying_price'] * $item['quantity'];
            }
            
            $profit = $total_amount - $total_cost;
            
            // Insert sale
            $stmt = $pdo->prepare("INSERT INTO sales (total_amount, total_cost, profit, payment_method, customer_name, user_id) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$total_amount, $total_cost, $profit, $payment_method, $customer_name, $_SESSION['user_id']]);
            $sale_id = $pdo->lastInsertId();
            
            // Insert sale items and update stock
            foreach ($_SESSION['cart'] as $item) {
                $subtotal = $item['selling_price'] * $item['quantity'];
                $item_profit = ($item['selling_price'] - $item['buying_price']) * $item['quantity'];
                
                $stmt = $pdo->prepare("INSERT INTO sale_items 
                    (sale_id, product_id, product_name, quantity, unit_price, buying_price, subtotal, profit) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $sale_id, $item['product_id'], $item['product_name'], 
                    $item['quantity'], $item['selling_price'], $item['buying_price'],
                    $subtotal, $item_profit
                ]);
                
                // Update product stock
                updateProductStock($pdo, $item['product_id'], $item['quantity']);
            }
            
            $pdo->commit();
            
            // Clear cart
            $_SESSION['sale_id'] = $sale_id;
            $_SESSION['cart'] = [];
            
            header('Location: receipt.php?id=' . $sale_id);
            exit();
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            setFlashMessage('error', 'Failed to process sale. Please try again.');
        }
    }
}

// Get all active products
$stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' AND quantity_in_stock > 0 ORDER BY product_name ASC");
$products = $stmt->fetchAll();

$pageTitle = 'New Sale';
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
                    <h1 class="page-title">New Sale</h1>
                    <div class="breadcrumb">Home / New Sale</div>
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
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Product Selection -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Select Product</h2>
                    </div>
                    
                    <form method="POST" action="" id="addProductForm">
                        <div class="form-group">
                            <label class="form-label">Product *</label>
                            <select name="product_id" id="productSelect" class="form-control" required onchange="updateProductInfo()">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" 
                                            data-price="<?php echo $product['selling_price']; ?>"
                                            data-stock="<?php echo $product['quantity_in_stock']; ?>">
                                        <?php echo htmlspecialchars($product['product_name']); ?> 
                                        (<?php echo $product['brand']; ?>) - 
                                        <?php echo formatCurrency($product['selling_price']); ?> 
                                        [Stock: <?php echo $product['quantity_in_stock']; ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Quantity *</label>
                            <input type="number" name="quantity" id="quantityInput" class="form-control" min="1" value="1" required>
                            <small id="stockInfo" class="text-muted"></small>
                        </div>
                        
                        <div id="pricePreview" style="display: none; margin: 1rem 0; padding: 1rem; background: #f1f5f9; border-radius: 0.5rem;">
                            <div><strong>Unit Price:</strong> <span id="unitPrice"></span></div>
                            <div style="margin-top: 0.5rem;"><strong>Subtotal:</strong> <span id="subtotal" style="font-size: 1.25rem; color: #2563eb;"></span></div>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn btn-primary">+ Add to Cart</button>
                    </form>
                </div>
                
                <!-- Shopping Cart -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Shopping Cart (<?php echo count($_SESSION['cart']); ?> items)</h2>
                    </div>
                    
                    <?php if (count($_SESSION['cart']) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cart_total = 0;
                                    $total_cost = 0;
                                    foreach ($_SESSION['cart'] as $index => $item): 
                                        $subtotal = $item['selling_price'] * $item['quantity'];
                                        $cart_total += $subtotal;
                                        $total_cost += $item['buying_price'] * $item['quantity'];
                                    ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                            <td><?php echo formatCurrency($item['selling_price']); ?></td>
                                            <td><span class="badge badge-primary"><?php echo $item['quantity']; ?></span></td>
                                            <td><strong><?php echo formatCurrency($subtotal); ?></strong></td>
                                            <td>
                                                <a href="new_sale.php?remove=<?php echo $index; ?>" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Remove this item?')">×</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #f1f5f9; border-radius: 0.5rem;">
                            <div class="flex-between" style="font-size: 1.125rem;">
                                <strong>Total Amount:</strong>
                                <strong style="color: #2563eb; font-size: 1.5rem;"><?php echo formatCurrency($cart_total); ?></strong>
                            </div>
                            <div class="flex-between" style="margin-top: 0.5rem; color: #10b981;">
                                <strong>Expected Profit:</strong>
                                <strong><?php echo formatCurrency($cart_total - $total_cost); ?></strong>
                            </div>
                        </div>
                        
                        <!-- Payment Form -->
                        <form method="POST" action="" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Customer Name (Optional)</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Enter customer name">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="Mobile Money">Mobile Money</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="process_sale" class="btn btn-success" style="width: 100%; font-size: 1.125rem;">
                                ✓ Complete Sale
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-center text-muted" style="padding: 2rem;">Cart is empty. Add products to start a sale.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function updateProductInfo() {
            const select = document.getElementById('productSelect');
            const selectedOption = select.options[select.selectedIndex];
            const quantityInput = document.getElementById('quantityInput');
            const stockInfo = document.getElementById('stockInfo');
            const pricePreview = document.getElementById('pricePreview');
            
            if (selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price);
                const stock = parseInt(selectedOption.dataset.stock);
                
                quantityInput.max = stock;
                stockInfo.textContent = 'Available stock: ' + stock;
                
                document.getElementById('unitPrice').textContent = 'TZS ' + price.toLocaleString();
                pricePreview.style.display = 'block';
                
                updateSubtotal();
            } else {
                pricePreview.style.display = 'none';
                stockInfo.textContent = '';
            }
        }
        
        function updateSubtotal() {
            const select = document.getElementById('productSelect');
            const selectedOption = select.options[select.selectedIndex];
            const quantity = parseInt(document.getElementById('quantityInput').value) || 0;
            
            if (selectedOption.value && quantity > 0) {
                const price = parseFloat(selectedOption.dataset.price);
                const subtotal = price * quantity;
                document.getElementById('subtotal').textContent = 'TZS ' + subtotal.toLocaleString();
            }
        }
        
        document.getElementById('quantityInput').addEventListener('input', updateSubtotal);
    </script>
    <script src="js/script.js"></script>
</body>
</html>