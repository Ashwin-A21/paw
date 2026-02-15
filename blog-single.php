<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: blogs.php");
    exit();
}

$id = (int) $_GET['id'];
$blog = $conn->query("SELECT * FROM blogs WHERE id=$id")->fetch_assoc();

if (!$blog) {
    header("Location: blogs.php");
    exit();
}

// Check if published or if current user is author/admin
$canView = false;
if ($blog['is_published'] && ($blog['status'] ?? 'approved') === 'approved') {
    $canView = true;
} elseif (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $blog['author_id']) {
        $canView = true;
    }
}

if (!$canView) {
    header("Location: blogs.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Paw Pal</title>

    <script src="https://cdn.tailwindcss.com"></script>
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
        body {
            background-color: #F9F8F6;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .prose p {
            margin-bottom: 1.5rem;
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
                    <a href="blogs.php"
                        class="text-sm uppercase tracking-widest text-paw-accent transition-colors">Stories &
                        Insights</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Image -->
    <?php if ($blog['image'] && file_exists('uploads/blogs/' . $blog['image'])): ?>
        <div class="pt-20 h-[50vh] relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-paw-dark/50 to-paw-dark/20"></div>
            <img src="uploads/blogs/<?php echo $blog['image']; ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>"
                class="w-full h-full object-cover">
        </div>
    <?php else: ?>
        <div class="pt-32"></div>
    <?php endif; ?>

    <!-- Article -->
    <article class="py-16 px-6">
        <div class="max-w-3xl mx-auto">
            <a href="blogs.php" class="inline-flex items-center gap-2 text-paw-accent hover:underline mb-8">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Articles
            </a>

            <header class="mb-12">
                <div class="flex items-center gap-4 text-sm text-paw-gray uppercase tracking-widest mb-6">
                    <span><?php echo date('F d, Y', strtotime($blog['created_at'])); ?></span>
                    <span>•</span>
                    <span>By <?php echo htmlspecialchars($blog['author']); ?></span>
                </div>
                <h1 class="font-serif text-5xl md:text-6xl leading-tight mb-6">
                    <?php echo htmlspecialchars($blog['title']); ?>
                </h1>
            </header>

            <div class="prose text-lg leading-relaxed text-paw-dark/80">
                <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
            </div>

            <!-- Share -->
            <div class="mt-16 pt-8 border-t border-gray-200">
                <p class="text-sm uppercase tracking-widest font-semibold text-paw-gray mb-4">Share
                    this story</p>
                <div class="flex gap-4">
                    <a href="#"
                        class="w-10 h-10 bg-paw-dark rounded-full flex items-center justify-center text-white hover:bg-paw-accent transition-colors">
                        <i data-lucide="twitter" class="w-4 h-4"></i>
                    </a>
                    <a href="#"
                        class="w-10 h-10 bg-paw-dark rounded-full flex items-center justify-center text-white hover:bg-paw-accent transition-colors">
                        <i data-lucide="facebook" class="w-4 h-4"></i>
                    </a>
                    <a href="#"
                        class="w-10 h-10 bg-paw-dark rounded-full flex items-center justify-center text-white hover:bg-paw-accent transition-colors">
                        <i data-lucide="link" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </article>

    <!-- CTA -->
    <section class="py-20 bg-paw-dark text-white transition-colors duration-300">
        <div class="max-w-4xl mx-auto text-center px-6">
            <h2 class="font-serif text-4xl md:text-5xl mb-6">Make a Difference Today</h2>
            <p class="text-white/70 mb-8 text-lg">Whether you adopt, volunteer, or donate – every action helps save a
                life.</p>
            <div class="flex justify-center gap-4">
                <a href="adopt.php"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-paw-accent text-white rounded-full text-sm uppercase tracking-widest font-bold hover:bg-white hover:text-paw-dark transition-colors">
                    Adopt a Pet
                </a>
                <a href="rescue.php"
                    class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 text-white rounded-full text-sm uppercase tracking-widest font-bold hover:bg-white hover:text-paw-dark transition-colors">
                    Report Rescue
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal.</p>
            <p>Built with <i data-lucide="heart" class="inline w-4 h-4 text-paw-alert"></i> for animals</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>