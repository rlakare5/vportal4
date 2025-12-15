document.addEventListener('DOMContentLoaded', function() {
    initWishlist();
    initCompare();
    initAlerts();
    initFormValidation();
});

function toggleMobileMenu() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (mobileMenu) {
        mobileMenu.classList.toggle('active');
        const icon = menuBtn.querySelector('i');
        if (mobileMenu.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
}

document.addEventListener('click', function(e) {
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (mobileMenu && mobileMenu.classList.contains('active')) {
        if (!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
            mobileMenu.classList.remove('active');
            const icon = menuBtn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
});

function addToWishlist(productId) {
    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'add', product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
        } else {
            if (data.message === 'Please login first') {
                window.location.href = 'login.php';
            } else {
                showAlert('error', data.message);
            }
        }
    })
    .catch(error => {
        showAlert('error', 'Something went wrong. Please try again.');
    });
}

function removeFromWishlist(productId) {
    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'remove', product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            const item = document.querySelector(`[data-product-id="${productId}"]`);
            if (item) {
                item.remove();
            }
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'Something went wrong. Please try again.');
    });
}

function initWishlist() {
    const wishlistBtns = document.querySelectorAll('.wishlist-btn');
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            addToWishlist(productId);
        });
    });
}

let compareList = JSON.parse(localStorage.getItem('compareList') || '[]');

function addToCompare(productId) {
    if (compareList.includes(productId)) {
        showAlert('warning', 'This bike is already in compare list');
        return;
    }
    
    if (compareList.length >= 4) {
        showAlert('warning', 'You can compare maximum 4 bikes at a time');
        return;
    }
    
    compareList.push(productId);
    localStorage.setItem('compareList', JSON.stringify(compareList));
    showAlert('success', 'Added to compare list');
    updateCompareCount();
}

function removeFromCompare(productId) {
    compareList = compareList.filter(id => id !== productId);
    localStorage.setItem('compareList', JSON.stringify(compareList));
    showAlert('success', 'Removed from compare list');
    updateCompareCount();
    
    if (window.location.pathname.includes('compare.php')) {
        location.reload();
    }
}

function clearCompare() {
    compareList = [];
    localStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareCount();
    
    if (window.location.pathname.includes('compare.php')) {
        location.reload();
    }
}

function updateCompareCount() {
    const countBadge = document.querySelector('.compare-count');
    if (countBadge) {
        countBadge.textContent = compareList.length;
        countBadge.style.display = compareList.length > 0 ? 'flex' : 'none';
    }
}

function initCompare() {
    updateCompareCount();
}

function quickView(productId) {
    const modal = document.getElementById('quickViewModal');
    if (!modal) {
        console.log('Quick view for product:', productId);
        return;
    }
    
    fetch(`ajax/product.php?id=${productId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('qv-name').textContent = data.product.name;
            document.getElementById('qv-price').textContent = formatCurrency(data.product.sale_price || data.product.base_price);
            document.getElementById('qv-description').textContent = data.product.short_description;
            document.getElementById('qv-image').src = data.product.main_image;
            document.getElementById('qv-link').href = `bike-details.php?slug=${data.product.slug}`;
            modal.classList.add('active');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${getAlertIcon(type)}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;margin-left:auto;cursor:pointer;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:400px;';
    document.body.appendChild(container);
    return container;
}

function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function initAlerts() {
    const alerts = document.querySelectorAll('.alert[data-dismiss]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, parseInt(alert.dataset.dismiss) || 5000);
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('error', 'Please fill in all required fields');
            }
        });
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});
