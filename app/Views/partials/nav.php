<?php
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uriPath = explode('?', $uri)[0];
?>
<header class="navbar">
    <div class="navbar-container">
        <!-- Brand Logo & Title -->
        <div class="navbar-brand">
            <div class="brand-logo">
                <svg class="brand-logo-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <span class="brand-text">Training CRM</span>
        </div>
        
        <!-- Navigation Links (Only visible if logged in) -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar-menu">
            <a href="/dashboard" class="menu-item <?= $uriPath === '/dashboard' ? 'active' : '' ?>">
                <span>Dashboard</span>
            </a>
            
            <a href="/leads" class="menu-item <?= ($uriPath === '/leads' || strpos($uriPath, '/leads/edit') === 0) ? 'active' : '' ?>">
                <span>Course Leads</span>
            </a>
            
            <a href="/leads/create" class="menu-item <?= $uriPath === '/leads/create' ? 'active' : '' ?>">
                <span>Thêm Lead</span>
            </a>
            
            <a href="/enrollments" class="menu-item <?= ($uriPath === '/enrollments' || strpos($uriPath, '/enrollments/edit') === 0) ? 'active' : '' ?>">
                <span>Enrollments</span>
            </a>
            
            <a href="/enrollments/create" class="menu-item <?= $uriPath === '/enrollments/create' ? 'active' : '' ?>">
                <span>Thêm Học Viên</span>
            </a>
            
            <a href="/health" class="menu-item <?= $uriPath === '/health' ? 'active' : '' ?>">
                <span>Health</span>
            </a>
        </nav>

        <!-- Profile Section & Logout -->
        <div class="navbar-user" style="display: flex; align-items: center; gap: 10px;">
            <span class="user-name"><?= e($_SESSION['user_name']) ?></span>
            <span class="user-role-badge <?= e($_SESSION['user_role'] ?? 'staff') ?>"><?= ucfirst(e($_SESSION['user_role'] ?? 'staff')) ?></span>
            <form method="POST" action="/logout" class="inline-logout-form" style="display:inline-block; margin-left: 10px;">
                <?= csrf_field() ?>
                <button type="submit" class="logout-btn-link" style="background:none; border:none; color:var(--danger-text); cursor:pointer; font-weight:600; font-size:0.85rem;">Đăng xuất</button>
            </form>
        </div>
        <?php else: ?>
        <!-- Public navigation links (optional, e.g. login link) -->
        <nav class="navbar-menu">
            <a href="/" class="menu-item <?= $uriPath === '/' ? 'active' : '' ?>">
                <span>Đăng Ký Khóa Học</span>
            </a>
            <a href="/login" class="menu-item <?= $uriPath === '/login' ? 'active' : '' ?>">
                <span>Đăng nhập Admin</span>
            </a>
        </nav>
        <?php endif; ?>
    </div>
</header>
