<?php
session_start();
include 'config.php';

// Fetch approved success stories
$stories = $conn->query("SELECT ss.*, u.username, u.profile_image, p.name as pet_name, p.image as pet_image 
    FROM success_stories ss 
    LEFT JOIN users u ON ss.user_id = u.id 
    LEFT JOIN pets p ON ss.pet_id = p.id 
    WHERE ss.status = 'approved' 
    ORDER BY ss.created_at DESC");

$basePath = '';
$pageTitle = 'Success Stories - Paw Pal';
$ogDescription = 'Read heartwarming adoption success stories from our Paw Pal community.';
include 'includes/header.php';
?>

<section class="pt-32 pb-12 px-6 relative overflow-hidden bg-paw-bg/30">
    <div class="absolute top-20 right-0 w-96 h-96 bg-green-100/20 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto text-center relative z-10">
        <div class="w-16 h-16 bg-paw-accent/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i data-lucide="sparkles" class="w-8 h-8 text-paw-accent"></i>
        </div>
        <p class="text-sm uppercase tracking-[0.3em] text-paw-accent mb-4">Happy Endings</p>
        <h1 class="font-serif text-5xl md:text-6xl text-paw-dark mb-6">
            Success <span class="italic text-paw-accent">Stories</span>
        </h1>
        <p class="text-paw-gray text-lg max-w-2xl mx-auto mb-8">
            Every adoption is a new beginning. Read the heartwarming stories from families who found their perfect
            companion through Paw Pal.
        </p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="submit-story.php"
                class="inline-flex items-center gap-2 px-8 py-3 bg-paw-accent text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                <i data-lucide="pen-line" class="w-4 h-4"></i> Share Your Story
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="py-12 px-6">
    <div class="max-w-6xl mx-auto">
        <?php if ($stories && $stories->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php while ($story = $stories->fetch_assoc()):
                    $authorImg = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($story['username'] ?? 'User');
                    if (!empty($story['profile_image'])) {
                        if (strpos($story['profile_image'], 'http') === 0) {
                            $authorImg = $story['profile_image'];
                        } else if (file_exists('uploads/users/' . $story['profile_image'])) {
                            $authorImg = 'uploads/users/' . rawurlencode($story['profile_image']);
                        }
                    }
                    ?>
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-xl transition-all duration-300">
                        <!-- Images -->
                        <?php if (!empty($story['before_image']) || !empty($story['after_image']) || !empty($story['pet_image'])): ?>
                            <div class="grid grid-cols-2 h-52">
                                <?php if (!empty($story['before_image'])): ?>
                                    <div class="relative overflow-hidden">
                                        <img src="uploads/stories/<?php echo rawurlencode($story['before_image']); ?>"
                                            class="w-full h-full object-cover" alt="Before">
                                        <div
                                            class="absolute bottom-2 left-2 bg-black/60 text-white text-[9px] px-2 py-0.5 rounded-full uppercase tracking-widest font-bold">
                                            Before</div>
                                    </div>
                                <?php elseif (!empty($story['pet_image'])): ?>
                                    <div class="overflow-hidden">
                                        <img src="uploads/pets/<?php echo rawurlencode($story['pet_image']); ?>"
                                            class="w-full h-full object-cover" alt="Pet">
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-100 flex items-center justify-center">
                                        <i data-lucide="image" class="w-10 h-10 text-gray-300"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($story['after_image'])): ?>
                                    <div class="relative overflow-hidden">
                                        <img src="uploads/stories/<?php echo rawurlencode($story['after_image']); ?>"
                                            class="w-full h-full object-cover" alt="After">
                                        <div
                                            class="absolute bottom-2 left-2 bg-green-500 text-white text-[9px] px-2 py-0.5 rounded-full uppercase tracking-widest font-bold">
                                            After</div>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-50 flex items-center justify-center">
                                        <i data-lucide="heart" class="w-10 h-10 text-paw-accent/20"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="p-6">
                            <h3 class="font-serif text-xl font-bold text-paw-dark mb-2">
                                <?php echo htmlspecialchars($story['title']); ?>
                            </h3>
                            <?php if (!empty($story['pet_name'])): ?>
                                <p class="text-xs text-paw-accent font-bold uppercase tracking-widest mb-3">
                                    <i data-lucide="paw-print" class="w-3 h-3 inline"></i>
                                    <?php echo htmlspecialchars($story['pet_name']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="text-sm text-paw-gray leading-relaxed line-clamp-4 mb-4">
                                <?php echo nl2br(htmlspecialchars(substr($story['story'], 0, 300))); ?>
                            </p>

                            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                <div class="w-8 h-8 rounded-full overflow-hidden border border-gray-200">
                                    <img src="<?php echo $authorImg; ?>" class="w-full h-full object-cover" alt="">
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-paw-dark">
                                        <?php echo htmlspecialchars($story['username']); ?>
                                    </p>
                                    <p class="text-[10px] text-gray-400">
                                        <?php echo date('M d, Y', strtotime($story['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
                <div class="w-20 h-20 bg-paw-accent/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="book-heart" class="w-10 h-10 text-paw-accent/40"></i>
                </div>
                <h3 class="font-serif text-2xl text-gray-400 mb-2">No stories yet</h3>
                <p class="text-gray-400 mb-6">Be the first to share your adoption success story!</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="submit-story.php"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-paw-accent text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors">
                        <i data-lucide="pen-line" class="w-4 h-4"></i> Share Your Story
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>