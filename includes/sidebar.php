<?php
// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'public';
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <i data-lucide="paw-print"></i>
        <span>PawRescue</span>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
            <a href="pets.php" class="<?php echo $currentPage === 'pets.php' ? 'active' : ''; ?>">
                <i data-lucide="heart"></i> Manage Pets
            </a>
            <a href="applications.php" class="<?php echo $currentPage === 'applications.php' ? 'active' : ''; ?>">
                <i data-lucide="clipboard-list"></i> Applications
            </a>
            <a href="rescues.php" class="<?php echo $currentPage === 'rescues.php' ? 'active' : ''; ?>">
                <i data-lucide="siren"></i> Rescue Reports
            </a>
            <a href="blogs.php" class="<?php echo $currentPage === 'blogs.php' ? 'active' : ''; ?>">
                <i data-lucide="book-open"></i> Blogs
            </a>
            <a href="users.php" class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i data-lucide="users"></i> Users
            </a>
        <?php elseif ($role === 'volunteer'): ?>
            <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
            <a href="tasks.php" class="<?php echo $currentPage === 'tasks.php' ? 'active' : ''; ?>">
                <i data-lucide="check-square"></i> My Tasks
            </a>
            <a href="rescues.php" class="<?php echo $currentPage === 'rescues.php' ? 'active' : ''; ?>">
                <i data-lucide="siren"></i> Rescue Reports
            </a>
        <?php else: ?>
            <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i data-lucide="layout-dashboard"></i> Dashboard
            </a>
            <a href="applications.php" class="<?php echo $currentPage === 'applications.php' ? 'active' : ''; ?>">
                <i data-lucide="clipboard-list"></i> My Applications
            </a>
            <a href="favorites.php" class="<?php echo $currentPage === 'favorites.php' ? 'active' : ''; ?>">
                <i data-lucide="heart"></i> Favorites
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i data-lucide="user"></i>
            </div>
            <div class="user-details">
                <span>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <span class="role">
                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                </span>
            </div>
        </div>
        <a href="../logout.php"
            style="display:block; margin-top:1rem; color:rgba(255,255,255,0.7); text-decoration:none; font-size:0.875rem;">
            <i data-lucide="log-out" style="width:16px;height:16px;vertical-align:middle;margin-right:0.5rem;"></i>
            Logout
        </a>
    </div>
</aside>