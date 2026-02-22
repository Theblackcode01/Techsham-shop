<?php
/**
 * Helper Functions File
 * Contains reusable functions for the system
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'TZS ' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('d M Y h:i A', strtotime($datetime));
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
    $stats['total_products'] = $stmt->fetch()['total'];
    
    // Today's sales
    $stmt = $pdo->query("SELECT COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue 
                         FROM sales WHERE DATE(sale_date) = CURDATE()");
    $today = $stmt->fetch();
    $stats['today_sales'] = $today['total'];
    $stats['today_revenue'] = $today['revenue'];
    
    // Today's profit
    $stmt = $pdo->query("SELECT COALESCE(SUM(profit), 0) as profit 
                         FROM sales WHERE DATE(sale_date) = CURDATE()");
    $stats['today_profit'] = $stmt->fetch()['profit'];
    
    // Low stock products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products 
                         WHERE quantity_in_stock < 10 AND status = 'active'");
    $stats['low_stock'] = $stmt->fetch()['total'];
    
    return $stats;
}

/**
 * Get recent sales
 */
function getRecentSales($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT * FROM sales ORDER BY sale_date DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get product by ID
 */
function getProductById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update product stock
 */
function updateProductStock($pdo, $product_id, $quantity_sold) {
    $stmt = $pdo->prepare("UPDATE products SET quantity_in_stock = quantity_in_stock - ? 
                           WHERE id = ?");
    return $stmt->execute([$quantity_sold, $product_id]);
}

/**
 * Calculate profit for date range
 */
function calculateProfit($pdo, $start_date, $end_date) {
    $stmt = $pdo->prepare("SELECT 
                            COALESCE(SUM(total_amount), 0) as revenue,
                            COALESCE(SUM(total_cost), 0) as cost,
                            COALESCE(SUM(profit), 0) as profit
                           FROM sales 
                           WHERE DATE(sale_date) BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetch();
}

/**
 * Get sales chart data for last 7 days
 */
function getSalesChartData($pdo) {
    $stmt = $pdo->query("SELECT 
                            DATE(sale_date) as date,
                            COALESCE(SUM(total_amount), 0) as revenue,
                            COALESCE(SUM(profit), 0) as profit
                         FROM sales 
                         WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                         GROUP BY DATE(sale_date)
                         ORDER BY date ASC");
    return $stmt->fetchAll();
}

/**
 * Search products
 */
function searchProducts($pdo, $search_term) {
    $search = "%$search_term%";
    $stmt = $pdo->prepare("SELECT * FROM products 
                           WHERE (product_name LIKE ? OR brand LIKE ? OR category LIKE ?) 
                           AND status = 'active'
                           ORDER BY product_name ASC");
    $stmt->execute([$search, $search, $search]);
    return $stmt->fetchAll();
}

/**
 * Get best selling products
 */
function getBestSellingProducts($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT * FROM best_selling_products LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get low stock products
 */
function getLowStockProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM low_stock_products");
    return $stmt->fetchAll();
}

/**
 * Backup database
 */
function backupDatabase() {
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $command = "mysqldump -u " . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . $backup_file;
    system($command, $output);
    return $backup_file;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

/**
 * Get user by ID
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id, username, full_name, email, role, created_at, last_login FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update user profile (name, email, optional password)
 */
function updateUserProfile($pdo, $id, $full_name, $email, $new_password = null) {
    if ($new_password && strlen($new_password) > 0) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
        return $stmt->execute([$full_name, $email, $hashed, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$full_name, $email, $id]);
    }
}
?>