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

$basePath = '';
include 'includes/header.php';
?>

<style>
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
        <?php if ($blogs && $blogs->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php while ($blog = $blogs->fetch_assoc()): ?>
                    <article class="blog-card bg-white rounded-2xl overflow-hidden shadow-lg group">
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

<?php include 'includes/footer.php'; ?>