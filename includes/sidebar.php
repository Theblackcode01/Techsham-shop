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
            <a href="sales.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : ''; ?>">
                Sales
            </a>
        </div>
        
        <div class="nav-item">
            <a href="new_sale.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'new_sale.php' ? 'active' : ''; ?>">
                New Sale
            </a>
        </div>
        
        <div class="nav-item">
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                Reports
            </a>
        </div>
        
        <div class="nav-item">
            <a href="edit_profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : ''; ?>">
                Edit Profile
            </a>
        </div>

        <div class="nav-item">
            <a href="logout.php">
                Logout
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
            </div>
            <div>
                <div style="font-weight: 600;"><?php echo $_SESSION['full_name']; ?></div>
                <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo ucfirst($_SESSION['role']); ?></div>
            </div>
        </div>
    </div>
</div>