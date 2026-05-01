<?php
/**
 * Products Management Page
 * Add, edit, delete, and view products
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$isAdmin = isAdmin();
$error = '';

// Handle product deletion
if (isset($_GET['delete'])) {
    if (!$isAdmin) {
        setFlashMessage('error', 'Access denied.');
        header('Location: products.php');
        exit();
    }

    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', 'Product deleted successfully!');
        header('Location: products.php');
        exit();
    } catch(PDOException $e) {
        setFlashMessage('error', 'Failed to delete product.');
    }
}

// Handle product add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin) {
        setFlashMessage('error', 'Access denied.');
        header('Location: products.php');
        exit();
    }

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $product_name = sanitize($_POST['product_name']);
    $category = sanitize($_POST['category']);
    $brand = sanitize($_POST['brand']);
    $buying_price = floatval($_POST['buying_price']);
    $selling_price = floatval($_POST['selling_price']);
    $quantity = intval($_POST['quantity_in_stock']);
    $supplier = sanitize($_POST['supplier_name']);
    
    try {
        if ($id > 0) {
            // Update existing product
            $stmt = $pdo->prepare("UPDATE products SET 
                product_name = ?, category = ?, brand = ?, 
                buying_price = ?, selling_price = ?, quantity_in_stock = ?, 
                supplier_name = ? WHERE id = ?");
            $stmt->execute([$product_name, $category, $brand, $buying_price, 
                           $selling_price, $quantity, $supplier, $id]);
            setFlashMessage('success', 'Product updated successfully!');
        } else {
            // Add new product
            $stmt = $pdo->prepare("INSERT INTO products 
                (product_name, category, brand, buying_price, selling_price, 
                 quantity_in_stock, supplier_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_name, $category, $brand, $buying_price, 
                           $selling_price, $quantity, $supplier]);
            setFlashMessage('success', 'Product added successfully!');
        }
        header('Location: products.php');
        exit();
    } catch(PDOException $e) {
        $error = 'Failed to save product. Please try again.';
    }
}

// Get product for editing
$editProduct = null;
if (isset($_GET['edit'])) {
    $editProduct = getProductById($pdo, intval($_GET['edit']));
}

// Search functionality
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
if (!empty($searchTerm)) {
    $products = searchProducts($pdo, $searchTerm);
} else {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY date_added DESC");
    $products = $stmt->fetchAll();
}

// Separate products by category
$phones = array_filter($products, function($product) {
    return $product['category'] === 'Phone';
});

$accessories = array_filter($products, function($product) {
    return $product['category'] === 'Accessories';
});


$pageTitle = 'Products Management';
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
                    <h1 class="page-title">Products Management</h1>
                    <div class="breadcrumb">Home / Products</div>
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
            
            <!-- Add/Edit Product Form -->
            <?php if ($isAdmin): ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h2>
                    </div>
                    
                    <form method="POST" action="" onsubmit="return validateProductForm()">
                        <input type="hidden" name="id" value="<?php echo $editProduct ? $editProduct['id'] : 0; ?>">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="product_name" class="form-control" 
                                       value="<?php echo $editProduct ? htmlspecialchars($editProduct['product_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="Phone" <?php echo ($editProduct && $editProduct['category'] == 'Phone') ? 'selected' : ''; ?>>Phone</option>
                                    <option value="Accessories" <?php echo ($editProduct && $editProduct['category'] == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Brand *</label>
                                <input type="text" name="brand" class="form-control" 
                                       value="<?php echo $editProduct ? htmlspecialchars($editProduct['brand']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Buying Price (TZS) *</label>
                                <input type="number" name="buying_price" class="form-control" step="0.01" min="0" 
                                       value="<?php echo $editProduct ? $editProduct['buying_price'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Selling Price (TZS) *</label>
                                <input type="number" name="selling_price" class="form-control" step="0.01" min="0" 
                                       value="<?php echo $editProduct ? $editProduct['selling_price'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Quantity in Stock *</label>
                                <input type="number" name="quantity_in_stock" class="form-control" min="0" 
                                       value="<?php echo $editProduct ? $editProduct['quantity_in_stock'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Supplier Name</label>
                                <input type="text" name="supplier_name" class="form-control" 
                                       value="<?php echo $editProduct ? htmlspecialchars($editProduct['supplier_name']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="flex gap-1" style="margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editProduct ? '✓ Update Product' : '+ Add Product'; ?>
                            </button>
                            <?php if ($editProduct): ?>
                                <a href="products.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Products Overview</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">You have view-only access to the product catalog. Only admin users may add or edit products.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Products List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">All Products (<?php echo count($products); ?>)</h2>
                    <form method="GET" action="" class="flex gap-1">
                        <input type="text" name="search" class="form-control" placeholder="Search products..."
                               value="<?php echo htmlspecialchars($searchTerm); ?>" style="max-width: 300px;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if ($searchTerm): ?>
                            <a href="products.php" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Phones Section -->
                <div class="category-section">
                    <h3 class="category-title">📱 Phones (<?php echo count($phones); ?>)</h3>
                    <?php if (count($phones) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product Name</th>
                                        <th>Brand</th>
                                        <th>Buying Price</th>
                                        <th>Selling Price</th>
                                        <th>Stock</th>
                                        <th>Supplier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($phones as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                            <td><?php echo formatCurrency($product['buying_price']); ?></td>
                                            <td><strong><?php echo formatCurrency($product['selling_price']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $product['quantity_in_stock'] < 10 ? 'badge-warning' : 'badge-success'; ?>">
                                                    <?php echo $product['quantity_in_stock']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                                            <td>
                                                <?php if ($isAdmin): ?>
                                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a href="products.php?delete=<?php echo $product['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                                <?php else: ?>
                                                    <span class="text-muted">View only</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No phones found.</p>
                    <?php endif; ?>
                </div>

                <!-- Accessories Section -->
                <div class="category-section">
                    <h3 class="category-title">🔧 Accessories (<?php echo count($accessories); ?>)</h3>
                    <?php if (count($accessories) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product Name</th>
                                        <th>Brand</th>
                                        <th>Buying Price</th>
                                        <th>Selling Price</th>
                                        <th>Stock</th>
                                        <th>Supplier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accessories as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                            <td><?php echo formatCurrency($product['buying_price']); ?></td>
                                            <td><strong><?php echo formatCurrency($product['selling_price']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $product['quantity_in_stock'] < 10 ? 'badge-warning' : 'badge-success'; ?>">
                                                    <?php echo $product['quantity_in_stock']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['supplier_name']); ?></td>
                                            <td>
                                                <?php if ($isAdmin): ?>
                                                    <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a href="products.php?delete=<?php echo $product['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                                <?php else: ?>
                                                    <span class="text-muted">View only</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No accessories found.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    
    <script>
        function validateProductForm() {
            const buyingPrice = parseFloat(document.querySelector('input[name="buying_price"]').value);
            const sellingPrice = parseFloat(document.querySelector('input[name="selling_price"]').value);
            
            if (sellingPrice < buyingPrice) {
                alert('Warning: Selling price is lower than buying price. You will make a loss!');
                return confirm('Do you want to continue anyway?');
            }
            
            return true;
        }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
