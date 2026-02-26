<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$userId = (int) $_GET['id'];

// Fetch user
$stmt = $conn->prepare("SELECT id, username, email, phone, role, profile_image, address, gender, dob, is_verified, lives_saved, created_at, organization_name FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Stats
$petsListed = $conn->query("SELECT COUNT(*) as cnt FROM pets WHERE added_by = $userId")->fetch_assoc()['cnt'];
$rescuesReported = $conn->query("SELECT COUNT(*) as cnt FROM rescue_reports WHERE reporter_id = $userId")->fetch_assoc()['cnt'];
$rescuesDone = $conn->query("SELECT COUNT(*) as cnt FROM rescue_reports WHERE assigned_to = $userId AND status IN ('Rescued','Closed')")->fetch_assoc()['cnt'];
$blogPosts = $conn->query("SELECT COUNT(*) as cnt FROM blogs WHERE author_id = $userId AND status='approved'")->fetch_assoc()['cnt'];
$commentCount = $conn->query("SELECT COUNT(*) as cnt FROM comments WHERE user_id = $userId")->fetch_assoc()['cnt'];

// Pets listed by this user
$userPets = $conn->query("SELECT * FROM pets WHERE added_by = $userId ORDER BY added_at DESC LIMIT 6");

// Recent blog posts
$userBlogs = $conn->query("SELECT * FROM blogs WHERE author_id = $userId AND status='approved' ORDER BY created_at DESC LIMIT 3");

$displayName = !empty($user['organization_name']) ? $user['organization_name'] : $user['username'];

$basePath = '';
$pageTitle = $displayName . ' - Paw Pal';
$ogDescription = $displayName . ' is a ' . ucfirst($user['role']) . ' on Paw Pal with ' . $user['lives_saved'] . ' lives saved.';
include 'includes/header.php';

// Profile image
$imgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($user['username']);
if (!empty($user['profile_image'])) {
    if (strpos($user['profile_image'], 'http') === 0) {
        $imgSrc = $user['profile_image'];
    } else if (file_exists('uploads/users/' . $user['profile_image'])) {
        $imgSrc = 'uploads/users/' . rawurlencode($user['profile_image']);
    }
}
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-5xl mx-auto">

        <!-- Profile Header -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12 mb-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <div class="w-28 h-28 rounded-3xl overflow-hidden border-4 border-paw-accent/20 shadow-lg">
                        <img src="<?php echo $imgSrc; ?>" class="w-full h-full object-cover"
                            alt="<?php echo htmlspecialchars($displayName); ?>">
                    </div>
                    <?php if ($user['is_verified']): ?>
                        <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1.5 shadow-md">
                            <i data-lucide="badge-check" class="w-6 h-6 text-paw-verified fill-current"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="font-serif text-4xl text-paw-dark mb-2">
                        <?php echo htmlspecialchars($displayName); ?>
                    </h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-4">
                        <span
                            class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <?php if ($user['is_verified']): ?>
                            <span
                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-green-50 text-green-600">
                                Verified
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-paw-gray">
                        <?php if (!empty($user['address'])): ?>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-4 h-4 text-paw-accent"></i>
                                <?php echo htmlspecialchars($user['address']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-paw-accent"></i>
                            Member since
                            <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>

                    <!-- Contact -->
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-4">
                        <?php if (!empty($user['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-xl text-sm text-paw-gray hover:bg-paw-accent hover:text-white hover:border-paw-accent transition-all">
                                <i data-lucide="mail" class="w-4 h-4"></i> Email
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($user['phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($user['phone']); ?>"
                                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-xl text-sm text-paw-gray hover:bg-paw-accent hover:text-white hover:border-paw-accent transition-all">
                                <i data-lucide="phone" class="w-4 h-4"></i> Call
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $user['lives_saved']; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Lives Saved</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $petsListed; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Pets Listed</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $rescuesReported; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Reports Filed</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $rescuesDone; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Rescues Done</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center col-span-2 md:col-span-1">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $blogPosts; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Blog Posts</p>
            </div>
        </div>

        <!-- Pets Listed -->
        <?php if ($userPets && $userPets->num_rows > 0): ?>
            <div class="mb-8">
                <h2 class="font-serif text-2xl text-paw-dark mb-6">Pets Listed for Adoption</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while ($pet = $userPets->fetch_assoc()): ?>
                        <a href="pet-details.php?id=<?php echo $pet['id']; ?>"
                            class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all border border-gray-100 group">
                            <div class="h-48 overflow-hidden">
                                <img src="uploads/pets/<?php echo rawurlencode($pet['image']); ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    alt="<?php echo htmlspecialchars($pet['name']); ?>">
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-paw-dark">
                                        <?php echo htmlspecialchars($pet['name']); ?>
                                    </h3>
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest <?php echo $pet['status'] === 'Available' ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500'; ?>">
                                        <?php echo $pet['status']; ?>
                                    </span>
                                </div>
                                <p class="text-xs text-paw-gray mt-1">
                                    <?php echo htmlspecialchars($pet['breed']); ?> ·
                                    <?php echo $pet['age']; ?> yrs
                                </p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Blog Posts -->
        <?php if ($userBlogs && $userBlogs->num_rows > 0): ?>
            <div>
                <h2 class="font-serif text-2xl text-paw-dark mb-6">Blog Posts</h2>
                <div class="space-y-4">
                    <?php while ($blog = $userBlogs->fetch_assoc()): ?>
                        <a href="blog-details.php?id=<?php echo $blog['id']; ?>"
                            class="block bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all border border-gray-100">
                            <h3 class="font-bold text-lg text-paw-dark mb-1">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </h3>
                            <p class="text-sm text-paw-gray line-clamp-2">
                                <?php echo htmlspecialchars(substr($blog['content'], 0, 200)); ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                            </p>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>