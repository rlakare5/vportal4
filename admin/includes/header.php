<?php
$admin = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM admins WHERE id = " . $_SESSION['admin_id']));
?>
<header class="admin-header">
    <div class="header-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search..." id="globalSearch">
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-icon" onclick="toggleTheme()" title="Toggle Theme">
            <i class="fas fa-moon"></i>
        </div>
        
        <div class="header-icon" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="count">3</span>
        </div>
        
        <div class="user-dropdown">
            <img src="../assets/uploads/avatars/<?= $admin['avatar'] ?? 'default.png' ?>" 
                 alt="Admin" class="user-avatar"
                 onerror="this.src='https://via.placeholder.com/40'">
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?></div>
                <div class="user-role"><?= ucfirst($admin['role'] ?? 'Admin') ?></div>
            </div>
        </div>
    </div>
</header>
