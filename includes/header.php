<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Assume config.php is included by the parent, or include it if needed
// include_once __DIR__ . '/../config.php'; // This path depends on where header is included from. 
// Better to rely on parent including it or use absolute path if we knew it.
// For now, let's assume parent includes config.php which connects to DB ($conn)

if (!isset($basePath)) {
    $basePath = '';
}

// Fetch current user if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    // We assume $conn is available from config.php
    if (isset($conn)) {
        $uStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $uStmt->bind_param("i", $uid);
        $uStmt->execute();
        $uResult = $uStmt->get_result();
        if ($uResult && $uResult->num_rows > 0) {
            $currentUser = $uResult->fetch_assoc();
        }
        $uStmt->close();
    }
}

// Navbar styling logic
$navClass = "fixed w-full z-50 transition-all duration-300 top-0";
if (isset($isTransparentHeader) && $isTransparentHeader) {
    // Keep default transparent state, JS will handle scroll
} else {
    // Default for other pages: always glass
    $navClass .= " glass shadow-sm h-20";
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Paw Pal | Rescue, Adopt, & Connect'; ?></title>

    <!-- Open Graph / Social Sharing -->
    <?php if (isset($ogDescription)): ?>
        <meta name="description" content="<?php echo $ogDescription; ?>">
        <meta property="og:title" content="<?php echo $pageTitle ?? 'Paw Pal'; ?>">
        <meta property="og:description" content="<?php echo $ogDescription; ?>">
        <meta property="og:type" content="website">
        <?php if (isset($ogImage)): ?>
            <meta property="og:image" content="<?php echo $ogImage; ?>">
        <?php endif; ?>
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo $pageTitle ?? 'Paw Pal'; ?>">
        <meta name="twitter:description" content="<?php echo $ogDescription; ?>">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'paw-bg': '#F9F8F6',
                        'paw-dark': '#2D2825',
                        'paw-accent': '#D4A373',
                        'paw-alert': '#E07A5F',
                        'paw-gray': '#9D958F',
                        'paw-card': '#FFFFFF',
                        'paw-verified': '#00A884',
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    backgroundImage: {
                        'noise': "url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIi8+CjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNjY2MiIG9wYWNpdHk9IjAuMiIvPgo8L3N2Zz4=')",
                    }
                }
            }
        }
    </script>
    <script>
        // Dark Mode — check saved preference
        if (localStorage.getItem('paw-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <style>
        body {
            background-color: #F9F8F6;
            overflow-x: hidden;
        }

        /* Dark Mode Overrides */
        .dark body,
        html.dark body {
            background-color: #1a1816;
            color: #e0dcd8;
        }

        .dark .bg-white,
        html.dark .bg-white {
            background-color: #2a2624 !important;
        }

        .dark .bg-paw-bg\/30 {
            background-color: rgba(26, 24, 22, 0.3) !important;
        }

        .dark .text-paw-dark,
        html.dark .text-paw-dark {
            color: #f0ece8 !important;
        }

        .dark .text-gray-700,
        .dark .text-gray-600 {
            color: #c0b8b0 !important;
        }

        .dark .text-gray-400,
        .dark .text-gray-500 {
            color: #8a827a !important;
        }

        .dark .border-gray-100,
        .dark .border-gray-200 {
            border-color: #3a3634 !important;
        }

        .dark .bg-gray-50,
        .dark .bg-gray-100 {
            background-color: #232120 !important;
        }

        .dark .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
        }

        .dark .glass {
            background: rgba(42, 38, 36, 0.9) !important;
            border-bottom-color: rgba(60, 56, 52, 0.5) !important;
        }

        .dark input,
        .dark textarea,
        .dark select {
            background-color: #2a2624 !important;
            color: #e0dcd8 !important;
            border-color: #3a3634 !important;
        }

        .dark .divide-gray-50> :not([hidden])~ :not([hidden]) {
            border-color: #3a3634 !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #F9F8F6;
        }

        ::-webkit-scrollbar-thumb {
            background: #D1CEC7;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #D4A373;
        }

        /* Common animations */
        .image-hover-container {
            overflow: hidden;
        }

        .image-hover-container img {
            transition: transform 1.2s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .image-hover-container:hover img {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased selection:bg-paw-accent selection:text-white">

    <?php if (!isset($hideNavbar) || !$hideNavbar): ?>
        <nav class="<?php echo $navClass; ?>" id="navbar">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div
                    class="<?php echo (isset($isTransparentHeader) && $isTransparentHeader) ? 'h-24' : 'h-20'; ?> flex justify-between items-center transition-all duration-300">
                    <a href="<?php echo $basePath; ?>index.php" class="magnetic-item relative z-10 group">
                        <span class="font-serif text-3xl italic font-bold tracking-tight">Paw Pal<span
                                class="text-paw-accent">.</span></span>
                    </a>

                    <div class="hidden md:flex items-center space-x-12">
                        <a href="<?php echo $basePath; ?>adopt.php"
                            class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-accent transition-colors duration-300">Adopt</a>
                        <a href="<?php echo $basePath; ?>rescue.php"
                            class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-alert transition-colors duration-300">Rescue</a>
                        <a href="<?php echo $basePath; ?>centers.php"
                            class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-verified transition-colors duration-300">Verified
                            Partners</a>
                        <a href="<?php echo $basePath; ?>blogs.php"
                            class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-accent transition-colors duration-300">Community</a>
                    </div>

                    <div class="hidden md:flex items-center gap-4">
                        <!-- Dark Mode Toggle -->
                        <button onclick="toggleDarkMode()" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer" title="Toggle dark mode">
                            <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-yellow-400"></i>
                            <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-paw-dark"></i>
                        </button>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            $dashboardUrl = $basePath . 'public/index.php';
                            if (isset($_SESSION['role'])) {
                                if ($_SESSION['role'] === 'admin')
                                    $dashboardUrl = $basePath . 'admin/index.php';
                                elseif ($_SESSION['role'] === 'volunteer' || $_SESSION['role'] === 'rescuer')
                                    $dashboardUrl = $basePath . 'volunteer/index.php';
                            }
                            ?>

                            <?php
                            // Fetch unread notification count
                            $notifCount = 0;
                            $ncStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
                            if ($ncStmt) {
                                $ncStmt->bind_param("i", $_SESSION['user_id']);
                                $ncStmt->execute();
                                $ncRow = $ncStmt->get_result()->fetch_assoc();
                                $notifCount = $ncRow['cnt'] ?? 0;
                                $ncStmt->close();
                            }
                            ?>

                            <!-- Notification Bell -->
                            <div class="relative" id="notifBellContainer">
                                <button onclick="toggleNotifDropdown()"
                                    class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                                    <i data-lucide="bell" class="w-5 h-5 text-paw-dark"></i>
                                    <?php if ($notifCount > 0): ?>
                                        <span id="notifBadge"
                                            class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                                            <?php echo $notifCount > 9 ? '9+' : $notifCount; ?>
                                        </span>
                                    <?php endif; ?>
                                </button>

                                <!-- Notification Dropdown -->
                                <div id="notifDropdown"
                                    class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 opacity-0 invisible transform -translate-y-2 transition-all duration-200 z-[999] overflow-hidden">
                                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                                        <p class="font-bold text-sm text-paw-dark">Notifications</p>
                                        <button onclick="markAllRead()"
                                            class="text-xs text-paw-accent hover:underline cursor-pointer">Mark all
                                            read</button>
                                    </div>
                                    <div id="notifList" class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                                        <div class="p-6 text-center text-gray-400 text-sm">Loading...</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="relative" id="profileDropdownContainer">
                                <button onclick="toggleProfileDropdown()"
                                    class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-paw-accent hover:border-paw-dark transition-colors focus:outline-none focus:ring-2 focus:ring-paw-accent focus:ring-offset-2 cursor-pointer">
                                    <img src="<?php
                                    $username = $currentUser['username'] ?? 'User';
                                    $imgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($username);
                                    if (!empty($currentUser['profile_image'])) {
                                        if (strpos($currentUser['profile_image'], 'http') === 0) {
                                            $imgSrc = $currentUser['profile_image'];
                                        } else {
                                            $paramsPath = $basePath . 'uploads/users/';
                                            if (file_exists(__DIR__ . '/../uploads/users/' . $currentUser['profile_image'])) {
                                                $imgSrc = $paramsPath . rawurlencode($currentUser['profile_image']);
                                            }
                                        }
                                    }
                                    echo $imgSrc;
                                    ?>" class="w-full h-full object-cover" alt="Profile">
                                </button>

                                <!-- Dropdown Menu -->
                                <div id="profileDropdown"
                                    class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 py-3 opacity-0 invisible transform -translate-y-2 transition-all duration-200 z-[999]">

                                    <!-- User Info Header -->
                                    <div class="px-5 py-3 border-b border-gray-100">
                                        <p class="font-serif text-lg font-bold text-paw-dark truncate">
                                            <?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?>
                                        </p>
                                        <p class="text-xs text-paw-gray truncate">
                                            <?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>
                                        </p>
                                        <span
                                            class="inline-block mt-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                                            <?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>
                                        </span>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-2">
                                        <a href="<?php echo $basePath; ?>public/profile.php"
                                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-paw-bg hover:text-paw-accent transition-colors">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                            My Profile
                                        </a>
                                        <a href="<?php echo $dashboardUrl; ?>"
                                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-paw-bg hover:text-paw-accent transition-colors">
                                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                            Dashboard
                                        </a>
                                        <a href="<?php echo $basePath; ?>public/my-pets.php"
                                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-paw-bg hover:text-paw-accent transition-colors">
                                            <i data-lucide="heart" class="w-4 h-4"></i>
                                            My Pets
                                        </a>
                                        <a href="<?php echo $basePath; ?>public/my-rescues.php"
                                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-gray-700 hover:bg-paw-bg hover:text-paw-accent transition-colors">
                                            <i data-lucide="siren" class="w-4 h-4"></i>
                                            My Rescue Reports
                                        </a>
                                    </div>

                                    <!-- Logout -->
                                    <div class="border-t border-gray-100 pt-2">
                                        <a href="<?php echo $basePath; ?>logout.php"
                                            class="flex items-center gap-3 px-5 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                            <i data-lucide="log-out" class="w-4 h-4"></i>
                                            Logout
                                        </a>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>login.php"
                                class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Login</a>
                            <a href="<?php echo $basePath; ?>register.php"
                                class="group relative px-8 py-3 bg-paw-dark text-white rounded-full overflow-hidden flex items-center justify-center">
                                <div
                                    class="absolute inset-0 w-full h-full bg-paw-accent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left">
                                </div>
                                <span
                                    class="relative z-10 text-xs font-bold uppercase tracking-widest group-hover:text-white transition-colors">Sign
                                    Up</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <button onclick="toggleMobileMenu()" class="md:hidden magnetic-item cursor-pointer z-50 relative"
                        id="mobileMenuBtn">
                        <i data-lucide="menu" class="w-8 h-8" id="mobileMenuIcon"></i>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay"
            class="fixed inset-0 bg-black/50 z-40 opacity-0 invisible transition-all duration-300 md:hidden"
            onclick="toggleMobileMenu()"></div>

        <!-- Mobile Menu Panel -->
        <div id="mobileMenuPanel"
            class="fixed top-0 right-0 w-80 h-full bg-white z-50 transform translate-x-full transition-transform duration-300 ease-out md:hidden shadow-2xl overflow-y-auto">
            <div class="p-6">
                <!-- Close Button -->
                <div class="flex justify-between items-center mb-8">
                    <span class="font-serif text-2xl italic font-bold">Paw Pal<span class="text-paw-accent">.</span></span>
                    <button onclick="toggleMobileMenu()"
                        class="p-2 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- User Info (if logged in) -->
                <?php if (isset($_SESSION['user_id']) && $currentUser): ?>
                    <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-paw-accent">
                                <img src="<?php echo $imgSrc ?? 'https://api.dicebear.com/9.x/toon-head/svg?seed=User'; ?>"
                                    class="w-full h-full object-cover" alt="Profile">
                            </div>
                            <div>
                                <p class="font-bold text-sm"><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?>
                                </p>
                                <span
                                    class="text-[10px] uppercase tracking-widest font-bold text-paw-accent"><?php echo ucfirst($_SESSION['role'] ?? 'user'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Nav Links -->
                <nav class="space-y-1 mb-6">
                    <a href="<?php echo $basePath; ?>adopt.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-paw-dark hover:bg-paw-accent/10 hover:text-paw-accent transition-colors">
                        <i data-lucide="heart" class="w-5 h-5"></i> Adopt
                    </a>
                    <a href="<?php echo $basePath; ?>rescue.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-paw-dark hover:bg-paw-alert/10 hover:text-paw-alert transition-colors">
                        <i data-lucide="siren" class="w-5 h-5"></i> Rescue
                    </a>
                    <a href="<?php echo $basePath; ?>centers.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-paw-dark hover:bg-paw-verified/10 hover:text-paw-verified transition-colors">
                        <i data-lucide="badge-check" class="w-5 h-5"></i> Verified Partners
                    </a>
                    <a href="<?php echo $basePath; ?>blogs.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-paw-dark hover:bg-paw-accent/10 hover:text-paw-accent transition-colors">
                        <i data-lucide="book-open" class="w-5 h-5"></i> Community
                    </a>
                </nav>

                <div class="border-t border-gray-100 pt-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <nav class="space-y-1 mb-4">
                            <a href="<?php echo $basePath; ?>public/profile.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <i data-lucide="user" class="w-4 h-4"></i> My Profile
                            </a>
                            <a href="<?php echo $dashboardUrl ?? ($basePath . 'public/index.php'); ?>"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                            </a>
                            <a href="<?php echo $basePath; ?>public/my-pets.php"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                <i data-lucide="heart" class="w-4 h-4"></i> My Pets
                            </a>
                        </nav>
                        <a href="<?php echo $basePath; ?>logout.php"
                            class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-red-50 text-red-500 rounded-xl text-sm font-bold">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                        </a>
                    <?php else: ?>
                        <div class="space-y-3">
                            <a href="<?php echo $basePath; ?>login.php"
                                class="block w-full py-3 border border-paw-dark text-paw-dark rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark hover:text-white transition-colors">
                                Login
                            </a>
                            <a href="<?php echo $basePath; ?>register.php"
                                class="block w-full py-3 bg-paw-dark text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors">
                                Sign Up
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            function toggleProfileDropdown() {
                const dropdown = document.getElementById('profileDropdown');
                if (!dropdown) return;

                if (dropdown.classList.contains('invisible')) {
                    dropdown.classList.remove('invisible', 'opacity-0', '-translate-y-2');
                    dropdown.classList.add('visible', 'opacity-100', 'translate-y-0');
                    // Re-init lucide icons for dropdown items
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    dropdown.classList.add('invisible', 'opacity-0', '-translate-y-2');
                    dropdown.classList.remove('visible', 'opacity-100', 'translate-y-0');
                }
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                const container = document.getElementById('profileDropdownContainer');
                const dropdown = document.getElementById('profileDropdown');
                if (container && dropdown && !container.contains(e.target)) {
                    dropdown.classList.add('invisible', 'opacity-0', '-translate-y-2');
                    dropdown.classList.remove('visible', 'opacity-100', 'translate-y-0');
                }

                // Close notification dropdown
                const notifContainer = document.getElementById('notifBellContainer');
                const notifDD = document.getElementById('notifDropdown');
                if (notifContainer && notifDD && !notifContainer.contains(e.target)) {
                    notifDD.classList.add('invisible', 'opacity-0', '-translate-y-2');
                    notifDD.classList.remove('visible', 'opacity-100', 'translate-y-0');
                }
            });

            // Notification functions
            function toggleNotifDropdown() {
                const dd = document.getElementById('notifDropdown');
                if (!dd) return;
                if (dd.classList.contains('invisible')) {
                    dd.classList.remove('invisible', 'opacity-0', '-translate-y-2');
                    dd.classList.add('visible', 'opacity-100', 'translate-y-0');
                    fetchNotifications();
                } else {
                    dd.classList.add('invisible', 'opacity-0', '-translate-y-2');
                    dd.classList.remove('visible', 'opacity-100', 'translate-y-0');
                }
            }

            function fetchNotifications() {
                const basePath = '<?php echo $basePath; ?>';
                fetch(basePath + 'api/notifications.php?action=list')
                    .then(r => r.json())
                    .then(data => {
                        const list = document.getElementById('notifList');
                        if (!data.success || data.notifications.length === 0) {
                            list.innerHTML = '<div class="p-6 text-center text-gray-400 text-sm">No notifications yet</div>';
                            return;
                        }
                        let html = '';
                        data.notifications.forEach(n => {
                            const isRead = n.is_read == 1;
                            const timeAgo = new Date(n.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                            html += `<a href="${n.link ? basePath + n.link : '#'}" class="block px-5 py-3 hover:bg-gray-50 transition-colors ${!isRead ? 'bg-paw-accent/5' : ''}">
                                <p class="text-sm ${!isRead ? 'font-semibold text-paw-dark' : 'text-gray-600'}">${n.message}</p>
                                <p class="text-[10px] text-gray-400 mt-1">${timeAgo}</p>
                            </a>`;
                        });
                        list.innerHTML = html;
                    })
                    .catch(() => {
                        document.getElementById('notifList').innerHTML = '<div class="p-6 text-center text-gray-400 text-sm">Could not load</div>';
                    });
            }

            function markAllRead() {
                const basePath = '<?php echo $basePath; ?>';
                const formData = new FormData();
                formData.append('action', 'mark_all_read');
                fetch(basePath + 'api/notifications.php?action=mark_all_read', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const badge = document.getElementById('notifBadge');
                            if (badge) badge.remove();
                            fetchNotifications();
                        }
                    });
            }
        </script>

        <script>
            function toggleMobileMenu() {
                const panel = document.getElementById('mobileMenuPanel');
                const overlay = document.getElementById('mobileMenuOverlay');
                if (!panel || !overlay) return;

                const isOpen = !panel.classList.contains('translate-x-full');
                if (isOpen) {
                    panel.classList.add('translate-x-full');
                    overlay.classList.add('opacity-0', 'invisible');
                    overlay.classList.remove('opacity-100', 'visible');
                    document.body.style.overflow = '';
                } else {
                    panel.classList.remove('translate-x-full');
                    overlay.classList.remove('opacity-0', 'invisible');
                    overlay.classList.add('opacity-100', 'visible');
                    document.body.style.overflow = 'hidden';
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
        </script>

        <script>
            function toggleDarkMode() {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('paw-theme', isDark ? 'dark' : 'light');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        </script>
    <?php endif; ?>