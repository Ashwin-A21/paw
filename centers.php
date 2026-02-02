<?php
session_start();
include 'config.php';

// Fetch Verified Paw Users (All roles who are verified)
// Fetch Verified Paw Users with Rescue Counts
$centers = $conn->query("
    SELECT u.*, COUNT(r.id) as rescues_count 
    FROM users u 
    LEFT JOIN rescue_reports r ON u.id = r.assigned_to AND r.status IN ('Rescued', 'Closed') 
    WHERE u.is_verified = 1 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified Paw Partners - Paw Pal</title>

    <script src="https://cdn.tailwindcss.com"></script>
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
                        'paw-verified': '#00A884', // WhatsApp Green
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg transition-colors duration-300">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass shadow-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <div class="hidden md:flex items-center space-x-10">
                    <a href="index.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Home</a>
                    <a href="adopt.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Adopt</a>
                    <a href="rescue.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Rescue</a>
                    <a href="centers.php"
                        class="text-sm uppercase tracking-widest text-paw-verified transition-colors">Verified
                        Partners</a>
                    <a href="blogs.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Success
                        Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php"
                            class="px-6 py-2 bg-paw-dark text-white rounded-full text-xs uppercase tracking-widest font-bold">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-12 px-6 relative overflow-hidden">
        <div class="absolute top-20 right-0 w-96 h-96 bg-paw-verified/10 rounded-full blur-3xl"></div>
        <div class="max-w-3xl mx-auto text-center relative z-10">
            <div class="w-16 h-16 bg-paw-verified/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="badge-check" class="w-8 h-8 text-paw-verified"></i>
            </div>
            <p class="text-sm uppercase tracking-[0.3em] text-paw-verified mb-4">Trusted & Verified</p>
            <h1 class="font-serif text-5xl md:text-6xl text-paw-dark mb-6">
                Verified Paw <span class="italic text-paw-verified">Partners</span>
            </h1>
            <p class="text-paw-gray text-lg max-w-xl mx-auto">
                Discover our network of trusted rescue centers and volunteers. Look for the green badge to ensure your
                donations reach the right hands.
            </p>
        </div>
    </section>

    <!-- Centers Grid -->
    <section class="py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php while ($center = $centers->fetch_assoc()): ?>
                    <div
                        class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 relative overflow-hidden group">
                        <!-- Decorative bg -->
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-paw-verified/5 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-150">
                        </div>

                        <div class="relative z-10">
                            <!-- Header with Badge -->
                            <div class="flex items-center gap-4 mb-6">
                                <div class="relative">
                                    <div
                                        class="w-16 h-16 bg-paw-dark text-white rounded-2xl flex items-center justify-center text-2xl font-serif font-bold">
                                        <?php echo substr($center['username'], 0, 1); ?>
                                    </div>
                                    <div
                                        class="absolute -bottom-1 -right-1 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-sm">
                                        <i data-lucide="badge-check" class="w-5 h-5 text-paw-verified"></i>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-serif text-2xl font-bold leading-tight">
                                            <?php echo htmlspecialchars($center['username']); ?>
                                        </h3>
                                        <i data-lucide="badge-check" class="w-5 h-5 text-paw-verified fill-current"></i>
                                    </div>
                                    <div class="flex flex-col gap-1 mt-1">
                                        <span
                                            class="inline-flex items-center gap-1 text-xs uppercase tracking-widest text-paw-verified font-semibold">
                                            Verified <?php echo ucfirst($center['role']); ?>
                                        </span>
                                        <?php if ($center['rescues_count'] > 0): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-500">
                                                <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                                                <?php echo $center['rescues_count']; ?>+ Lives Saved
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex items-start gap-3 text-paw-gray">
                                    <i data-lucide="map-pin" class="w-5 h-5 flex-shrink-0 mt-0.5 text-paw-verified"></i>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($center['address'] ?? 'Location not available'); ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-paw-gray">
                                    <i data-lucide="phone" class="w-5 h-5 flex-shrink-0 text-paw-verified"></i>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($center['phone'] ?? 'Contact not available'); ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-paw-gray">
                                    <i data-lucide="mail" class="w-5 h-5 flex-shrink-0 text-paw-verified"></i>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($center['email']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <a href="donate.php?id=<?php echo $center['id']; ?>"
                                    class="flex-1 py-3 bg-paw-verified text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors text-center shadow-lg shadow-paw-verified/30">
                                    Donate
                                </a>
                                <a href="mailto:<?php echo $center['email']; ?>"
                                    class="flex-1 py-3 border border-paw-dark text-paw-dark rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark hover:text-white transition-colors text-center">
                                    Contact
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($centers->num_rows === 0): ?>
                <div class="text-center py-20 bg-white rounded-3xl">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="search-x" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="font-serif text-2xl text-gray-400">No verified centers found yet.</h3>
                    <p class="text-gray-400 mt-2">Check back later for updates.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200 transition-colors duration-300 mt-12">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal.</p>
            <p>Emergency: <span class="text-paw-alert font-semibold">+91 98765 43210</span></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>