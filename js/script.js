/**
 * Phone Shop Management System
 * JavaScript - Form Validation and UI Enhancements
 */

// Form validation helper functions
const Validator = {
    // Validate email format
    isValidEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Validate phone number (basic)
    isValidPhone: function(phone) {
        const re = /^[\d\s\-\+\(\)]+$/;
        return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
    },
    
    // Validate required field
    isRequired: function(value) {
        return value.trim() !== '';
    },
    
    // Validate number range
    isInRange: function(value, min, max) {
        const num = parseFloat(value);
        return !isNaN(num) && num >= min && num <= max;
    },
    
    // Validate positive number
    isPositive: function(value) {
        const num = parseFloat(value);
        return !isNaN(num) && num > 0;
    }
};

// Product form validation
function validateProductForm() {
    const productName = document.querySelector('input[name="product_name"]');
    const category = document.querySelector('select[name="category"]');
    const brand = document.querySelector('input[name="brand"]');
    const buyingPrice = document.querySelector('input[name="buying_price"]');
    const sellingPrice = document.querySelector('input[name="selling_price"]');
    const quantity = document.querySelector('input[name="quantity_in_stock"]');
    
    // Check required fields
    if (!Validator.isRequired(productName.value)) {
        alert('Product name is required!');
        productName.focus();
        return false;
    }
    
    if (!Validator.isRequired(category.value)) {
        alert('Please select a category!');
        category.focus();
        return false;
    }
    
    if (!Validator.isRequired(brand.value)) {
        alert('Brand is required!');
        brand.focus();
        return false;
    }
    
    // Validate prices
    if (!Validator.isPositive(buyingPrice.value)) {
        alert('Buying price must be greater than 0!');
        buyingPrice.focus();
        return false;
    }
    
    if (!Validator.isPositive(sellingPrice.value)) {
        alert('Selling price must be greater than 0!');
        sellingPrice.focus();
        return false;
    }
    
    // Check if selling price is lower than buying price
    const buying = parseFloat(buyingPrice.value);
    const selling = parseFloat(sellingPrice.value);
    
    if (selling < buying) {
        const confirmed = confirm(
            'WARNING: Selling price (TZS ' + selling.toLocaleString() + ') is lower than buying price (TZS ' + buying.toLocaleString() + ').\n\n' +
            'You will make a LOSS of TZS ' + (buying - selling).toLocaleString() + ' per unit!\n\n' +
            'Do you want to continue anyway?'
        );
        if (!confirmed) {
            sellingPrice.focus();
            return false;
        }
    }
    
    // Validate quantity
    const qty = parseInt(quantity.value);
    if (isNaN(qty) || qty < 0) {
        alert('Quantity must be 0 or greater!');
        quantity.focus();
        return false;
    }
    
    return true;
}

// Sale form validation
function validateSaleForm() {
    const product = document.querySelector('select[name="product_id"]');
    const quantity = document.querySelector('input[name="quantity"]');
    
    if (!product || product.value === '') {
        alert('Please select a product!');
        product.focus();
        return false;
    }
    
    const selectedOption = product.options[product.selectedIndex];
    const availableStock = parseInt(selectedOption.dataset.stock);
    const requestedQty = parseInt(quantity.value);
    
    if (isNaN(requestedQty) || requestedQty <= 0) {
        alert('Quantity must be greater than 0!');
        quantity.focus();
        return false;
    }
    
    if (requestedQty > availableStock) {
        alert('Insufficient stock! Only ' + availableStock + ' unit(s) available.');
        quantity.focus();
        return false;
    }
    
    return true;
}

// Confirm deletion
function confirmDelete(itemName) {
    return confirm('Are you sure you want to delete "' + itemName + '"?\n\nThis action cannot be undone!');
}

// Format currency input
function formatCurrencyInput(input) {
    let value = input.value.replace(/[^\d.]/g, '');
    const parts = value.split('.');
    
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    if (parts[1] && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    input.value = value;
}

// Auto-calculate profit
function calculateProfit() {
    const buyingPrice = document.querySelector('input[name="buying_price"]');
    const sellingPrice = document.querySelector('input[name="selling_price"]');
    const profitDisplay = document.getElementById('profitPreview');
    
    if (buyingPrice && sellingPrice && profitDisplay) {
        const buying = parseFloat(buyingPrice.value) || 0;
        const selling = parseFloat(sellingPrice.value) || 0;
        const profit = selling - buying;
        const margin = buying > 0 ? ((profit / buying) * 100).toFixed(2) : 0;
        
        profitDisplay.innerHTML = 
            '<strong>Profit per unit:</strong> TZS ' + profit.toLocaleString() + 
            ' (' + margin + '% margin)';
        
        if (profit < 0) {
            profitDisplay.style.color = '#ef4444';
        } else if (profit > 0) {
            profitDisplay.style.color = '#10b981';
        } else {
            profitDisplay.style.color = '#64748b';
        }
    }
}

// Update product info in sale form
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
        quantityInput.value = Math.min(1, stock);
        
        stockInfo.textContent = 'Available stock: ' + stock + ' units';
        stockInfo.style.color = stock < 10 ? '#f59e0b' : '#64748b';
        
        document.getElementById('unitPrice').textContent = 'TZS ' + price.toLocaleString();
        pricePreview.style.display = 'block';
        
        updateSubtotal();
    } else {
        pricePreview.style.display = 'none';
        stockInfo.textContent = '';
    }
}

// Update subtotal in sale form
function updateSubtotal() {
    const select = document.getElementById('productSelect');
    const selectedOption = select.options[select.selectedIndex];
    const quantity = parseInt(document.getElementById('quantityInput').value) || 0;
    
    if (selectedOption.value && quantity > 0) {
        const price = parseFloat(selectedOption.dataset.price);
        const stock = parseInt(selectedOption.dataset.stock);
        const subtotal = price * quantity;
        
        document.getElementById('subtotal').textContent = 'TZS ' + subtotal.toLocaleString();
        
        // Warn if quantity exceeds stock
        if (quantity > stock) {
            document.getElementById('quantityInput').style.borderColor = '#ef4444';
            alert('Warning: Requested quantity exceeds available stock!');
        } else {
            document.getElementById('quantityInput').style.borderColor = '#cbd5e1';
        }
    }
}

// Search functionality
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell) {
                const textValue = cell.textContent || cell.innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Print functionality
function printReceipt() {
    window.print();
}

// Confirm before leaving page with unsaved changes
let formChanged = false;

function markFormChanged() {
    formChanged = true;
}

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Reset form changed flag on submit
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            formChanged = false;
        });
        
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('change', markFormChanged);
        });
    });
});

// Auto-hide flash messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000); // Hide after 5 seconds
    });
});

// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Number formatting helper
function formatNumber(num, decimals = 2) {
    return parseFloat(num).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

// Date formatting helper
function formatDate(date) {
    const d = new Date(date);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return d.toLocaleDateString('en-US', options);
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Console log for debugging (can be removed in production)
console.log('Phone Shop Management System - JavaScript Loaded Successfully');

/* ========================================
   RESPONSIVE DESIGN - Mobile Menu Toggle
   ======================================== */

// Initialize mobile menu functionality
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (!menuToggle) return;
    
    // Toggle sidebar visibility
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('active');
    });
    
    // Close sidebar when clicking on a nav item
    const navItems = document.querySelectorAll('.nav-item a');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    });
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    
    // Handle window resize to remove active class on larger screens
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
        }
    });
});

/* ========================================
   RESPONSIVE UTILITIES
   ======================================== */

// Utility function to get viewport dimensions
function getViewportSize() {
    return {
        width: window.innerWidth,
        height: window.innerHeight,
        isMobile: window.innerWidth < 480,
        isTablet: window.innerWidth >= 480 && window.innerWidth <= 1024,
        isDesktop: window.innerWidth > 1024
    };
}

// Utility function to handle touch events on buttons
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn, button');
    buttons.forEach(btn => {
        btn.addEventListener('touchend', function() {
            // Remove active state after touch
            this.style.opacity = '1';
        });
        
        btn.addEventListener('touchstart', function() {
            // Visual feedback on touch
            this.style.opacity = '0.8';
        });
    });
});
