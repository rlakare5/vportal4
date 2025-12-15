document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initTheme();
    initAlerts();
});

function initSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

function initTheme() {
    const savedTheme = localStorage.getItem('adminTheme') || 'dark';
    document.body.classList.toggle('dark-theme', savedTheme === 'dark');
    document.body.classList.toggle('light-theme', savedTheme === 'light');
}

function toggleTheme() {
    const isDark = document.body.classList.contains('dark-theme');
    document.body.classList.toggle('dark-theme', !isDark);
    document.body.classList.toggle('light-theme', isDark);
    localStorage.setItem('adminTheme', isDark ? 'light' : 'dark');
    
    const icon = document.querySelector('.header-icon[onclick="toggleTheme()"] i');
    if (icon) {
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
}

function initAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

function showNotification(type, message) {
    const container = document.querySelector('.content-area') || document.body;
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    container.insertBefore(alert, container.firstChild);
    
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0
    }).format(amount);
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

const globalSearch = document.getElementById('globalSearch');
if (globalSearch) {
    globalSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                window.location.href = 'search.php?q=' + encodeURIComponent(query);
            }
        }
    });
}
