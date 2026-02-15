<?php
// Determine the current page for active state highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Fetch current user for sidebar display
if (!isset($sidebarUser)) {
    $sidebarUser = null;
    if (isset($_SESSION['user_id']) && isset($conn)) {
        $suid = $_SESSION['user_id'];
        $suResult = $conn->query("SELECT * FROM users WHERE id=$suid");
        if ($suResult && $suResult->num_rows > 0) {
            $sidebarUser = $suResult->fetch_assoc();
        }
    }
}

// Get the role for display
$sidebarRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'volunteer';

// Helper function for active class
function volunteerSidebarLinkClass($page, $currentPage)
{
    if ($page === $currentPage) {
        return 'flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2';
    }
    return 'flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors';
}
?>

<aside class="w-64 bg-paw-dark text-white flex flex-col border-r border-white/10 hidden md:flex">
    <div class="p-6 border-b border-white/10">
        <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                class="text-paw-accent">.</span></a>
        <p class="text-xs text-white/50 mt-1 uppercase tracking-widest">
            <?php echo ucfirst($sidebarRole); ?> Panel
        </p>
    </div>

    <nav class="flex-1 p-4">
        <a href="index.php" class="<?php echo volunteerSidebarLinkClass('index.php', $currentPage); ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
        </a>
        <a href="tasks.php" class="<?php echo volunteerSidebarLinkClass('tasks.php', $currentPage); ?>">
            <i data-lucide="check-square" class="w-5 h-5"></i> My Tasks
        </a>
        <a href="rescues.php" class="<?php echo volunteerSidebarLinkClass('rescues.php', $currentPage); ?>">
            <i data-lucide="siren" class="w-5 h-5"></i> Rescue Reports
        </a>
        <a href="profile.php" class="<?php echo volunteerSidebarLinkClass('profile.php', $currentPage); ?>">
            <i data-lucide="user-circle" class="w-5 h-5"></i> My Profile
        </a>

        <div class="mt-8 mb-2 px-4 text-xs font-semibold text-white/30 uppercase tracking-widest">
            Quick Links
        </div>
        <a href="../adopt.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
            <i data-lucide="heart" class="w-5 h-5"></i> Adopt a Pet
        </a>
        <a href="../blogs.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
            <i data-lucide="book-open" class="w-5 h-5"></i> Stories & Insights
        </a>
        <a href="../public/index.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
            <i data-lucide="clipboard-list" class="w-5 h-5"></i> My Adoptions
        </a>
    </nav>

    <div class="p-4 border-t border-white/10">
        <?php if ($sidebarUser): ?>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20">
                    <img src="<?php
                    $imgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($sidebarUser['username']);
                    if (!empty($sidebarUser['profile_image'])) {
                        if (strpos($sidebarUser['profile_image'], 'http') === 0) {
                            $imgSrc = $sidebarUser['profile_image'];
                        } else {
                            $imgPath = '../uploads/users/';
                            if (file_exists('../uploads/users/' . $sidebarUser['profile_image'])) {
                                $imgSrc = '../uploads/users/' . rawurlencode($sidebarUser['profile_image']);
                            }
                        }
                    }
                    echo $imgSrc;
                    ?>" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-sm font-medium">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </p>
                    <p class="text-xs text-white/50 uppercase">
                        <?php echo ucfirst($sidebarRole); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        <a href="../logout.php"
            class="flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors">
            <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
        </a>
    </div>
</aside>