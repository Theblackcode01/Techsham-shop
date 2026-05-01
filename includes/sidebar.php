<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" title="Toggle Menu" aria-expanded="false" aria-controls="main-navigation" aria-label="Toggle Menu">☰</button>

<!-- Sidebar Navigation -->
<div class="sidebar" id="main-navigation" role="navigation">
    <div class="sidebar-header">
        <h2>Techsham Shop</h2>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-item">
            <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
        </div>
        
        <div class="nav-item">
            <a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                Products
            </a>
        </div>
        
        <div class="nav-item">
            <a href="new_sale.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'new_sale.php' ? 'active' : ''; ?>">
                New Sale
            </a>
        </div>

        <?php if (isAdmin()): ?>
            <div class="nav-item">
                <a href="sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                    Sales
                </a>
            </div>
            
            <div class="nav-item">
                <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    Reports
                </a>
            </div>
            
            <div class="nav-item">
                <a href="manage_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    Manage Users
                </a>
            </div>
        <?php endif; ?>
        
        <div class="nav-item">
            <a href="edit_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : ''; ?>">
                Edit Profile
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['full_name']; ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['role']); ?></div>
            </div>
        </div>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
</div>