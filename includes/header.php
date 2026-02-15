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
        $uResult = $conn->query("SELECT * FROM users WHERE id=$uid");
        if ($uResult && $uResult->num_rows > 0) {
            $currentUser = $uResult->fetch_assoc();
        }
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
    <title>Paw Pal | Rescue, Adopt, & Connect</title>

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
        // Force Light Mode Logic
        if (localStorage.getItem('color-theme') === 'dark') {
            localStorage.removeItem('color-theme');
        }
        document.documentElement.classList.remove('dark');
    </script>

    <style>
        body {
            background-color: #F9F8F6;
            overflow-x: hidden;
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
                                                $imgSrc = $paramsPath . htmlspecialchars($currentUser['profile_image']);
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
                                            <?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></p>
                                        <p class="text-xs text-paw-gray truncate">
                                            <?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
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
                                        <a href="<?php echo $basePath; ?>rescue.php"
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

                    <div class="md:hidden magnetic-item">
                        <i data-lucide="menu" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        </nav>

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
            document.addEventListener('click', function(e) {
                const container = document.getElementById('profileDropdownContainer');
                const dropdown = document.getElementById('profileDropdown');
                if (container && dropdown && !container.contains(e.target)) {
                    dropdown.classList.add('invisible', 'opacity-0', '-translate-y-2');
                    dropdown.classList.remove('visible', 'opacity-100', 'translate-y-0');
                }
            });
        </script>
    <?php endif; ?>