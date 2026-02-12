<?php
session_start();
include 'config.php';

$blogs = $conn->query("SELECT * FROM blogs WHERE is_published=1 ORDER BY created_at DESC");

// Fetch current user if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uResult = $conn->query("SELECT * FROM users WHERE id=$uid");
    if ($uResult && $uResult->num_rows > 0) {
        $currentUser = $uResult->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Stories - Paw Pal</title>

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

        .blog-card {
            transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .blog-card:hover {
            transform: translateY(-8px);
        }

        .blog-card:hover img {
            transform: scale(1.05);
        }

        .blog-card img {
            transition: transform 1s cubic-bezier(0.19, 1, 0.22, 1);
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
                        class="text-sm uppercase tracking-widest hover:text-paw-alert transition-colors">Rescue</a>
                    <a href="centers.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Verified
                        Partners</a>
                    <a href="blogs.php"
                        class="text-sm uppercase tracking-widest text-paw-accent transition-colors">Success Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $dashboardUrl = 'public/index.php';
                        if ($_SESSION['role'] === 'admin')
                            $dashboardUrl = 'admin/index.php';
                        elseif ($_SESSION['role'] === 'volunteer' || $_SESSION['role'] === 'rescuer')
                            $dashboardUrl = 'volunteer/index.php';
                        ?>
                        <a href="<?php echo $dashboardUrl; ?>"
                            class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Dashboard</a>
                        <a href="public/profile.php"
                            class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-paw-accent hover:border-paw-dark transition-colors group">
                            <img src="<?php
                            $username = $currentUser['username'] ?? 'User';
                            $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($username);
                            if (!empty($currentUser['profile_image'])) {
                                if (strpos($currentUser['profile_image'], 'http') === 0) {
                                    $imgSrc = $currentUser['profile_image'];
                                } else {
                                    $basePath = 'uploads/users/';
                                    if (file_exists($basePath . $currentUser['profile_image'])) {
                                        $imgSrc = $basePath . htmlspecialchars($currentUser['profile_image']);
                                    }
                                }
                            }
                            echo $imgSrc;
                            ?>" class="w-full h-full object-cover">
                        </a>
                        <a href="logout.php"
                            class="group relative px-6 py-2.5 bg-paw-dark text-white rounded-full overflow-hidden flex items-center justify-center">
                            <span class="relative z-10 text-xs font-bold uppercase tracking-widest">Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Login</a>
                        <a href="register.php"
                            class="px-6 py-2 bg-paw-dark text-white rounded-full text-xs uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Sign
                            Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-16 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-sm uppercase tracking-[0.3em] text-paw-accent mb-4">Paw Pal Community</p>
            <h1 class="font-serif text-5xl md:text-7xl text-paw-dark mb-6">
                Success <span class="italic">Stories</span>
            </h1>
            <p class="text-paw-gray max-w-xl mx-auto text-lg mb-8">
                Celebrating the heartwarming journeys of pets finding their forever homes. Join our community and share
                your story.
            </p>
            <a href="create_blog.php"
                class="inline-flex items-center gap-2 px-8 py-3 bg-paw-dark text-white rounded-full text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                Share Your Story <i data-lucide="pen-tool" class="w-4 h-4"></i>
            </a>
        </div>
    </section>

    <!-- Blog Grid -->
    <section class="py-16 px-6">
        <div class="max-w-7xl mx-auto">
            <?php if ($blogs->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <?php while ($blog = $blogs->fetch_assoc()): ?>
                        <article class="blog-card bg-white rounded-2xl overflow-hidden shadow-lg">
                            <div class="h-56 overflow-hidden">
                                <img src="<?php echo $blog['image'] && file_exists('uploads/blogs/' . $blog['image']) ? 'uploads/blogs/' . $blog['image'] : 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600'; ?>"
                                    alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-4 text-xs text-paw-gray uppercase tracking-widest mb-4">
                                    <span><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                                    <span>•</span>
                                    <span><?php echo htmlspecialchars($blog['author']); ?></span>
                                </div>
                                <h3 class="font-serif text-2xl mb-3 hover:text-paw-accent transition-colors">
                                    <a
                                        href="blog-single.php?id=<?php echo $blog['id']; ?>"><?php echo htmlspecialchars($blog['title']); ?></a>
                                </h3>
                                <p class="text-paw-gray text-sm mb-6 line-clamp-3">
                                    <?php echo htmlspecialchars(substr($blog['content'], 0, 150)); ?>...
                                </p>
                                <a href="blog-single.php?id=<?php echo $blog['id']; ?>"
                                    class="inline-flex items-center gap-2 text-sm uppercase tracking-widest font-semibold text-paw-dark border-b border-paw-dark/20 pb-1 hover:border-paw-accent hover:text-paw-accent transition-colors">
                                    Read Story <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl">
                    <div class="w-20 h-20 bg-paw-accent/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="book-open" class="w-10 h-10 text-paw-accent"></i>
                    </div>
                    <h3 class="font-serif text-3xl mb-4">No Stories Yet</h3>
                    <p class="text-paw-gray">Check back soon for updates and heartwarming stories!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-20 bg-white">
        <div class="max-w-2xl mx-auto text-center px-6">
            <h2 class="font-serif text-4xl mb-4">Stay Updated</h2>
            <p class="text-paw-gray mb-8">Get the latest rescue stories and pet care tips delivered
                to your inbox.</p>
            <form class="flex gap-4 max-w-md mx-auto">
                <input type="email" placeholder="Enter your email"
                    class="flex-1 px-6 py-4 border border-gray-200 bg-white rounded-full focus:outline-none focus:border-paw-accent">
                <button type="submit"
                    class="px-8 py-4 bg-paw-dark text-white rounded-full text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                    Subscribe
                </button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal. All rights reserved.</p>
            <p>Built with <i data-lucide="heart" class="inline w-4 h-4 text-paw-alert"></i> for animals</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>