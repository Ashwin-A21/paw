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
                            <a href="<?php echo $dashboardUrl; ?>"
                                class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Dashboard</a>

                            <a href="<?php echo $basePath; ?>public/profile.php"
                                class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-paw-accent hover:border-paw-dark transition-colors group">
                                <img src="<?php
                                $username = $currentUser['username'] ?? 'User';
                                $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($username);
                                if (!empty($currentUser['profile_image'])) {
                                    if (strpos($currentUser['profile_image'], 'http') === 0) {
                                        $imgSrc = $currentUser['profile_image'];
                                    } else {
                                        // Handle relative path for standard navbar
                                        // uploads is in root/uploads
                                        // If we are in root, $basePath is empty. Uploads is at uploads/
                                        // If we are in public/, $basePath is ../. Uploads is at ../uploads/
                                        $paramsPath = $basePath . 'uploads/users/';
                                        if (file_exists(__DIR__ . '/../uploads/users/' . $currentUser['profile_image'])) {
                                            // Simplify check: just trust the path construction relative to browser URL
                                            $imgSrc = $paramsPath . htmlspecialchars($currentUser['profile_image']);
                                        }
                                    }
                                }
                                echo $imgSrc;
                                ?>" class="w-full h-full object-cover">
                            </a>

                            <a href="<?php echo $basePath; ?>logout.php"
                                class="group relative px-6 py-2.5 bg-paw-dark text-white rounded-full overflow-hidden flex items-center justify-center">
                                <span class="relative z-10 text-xs font-bold uppercase tracking-widest">Logout</span>
                            </a>
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
    <?php endif; ?>