<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$message = "";
$error = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($conn->query("DELETE FROM feedback WHERE id=$id")) {
        $message = "Feedback deleted successfully.";
    } else {
        $error = "Error deleting feedback.";
    }
}

// Filter
$filterRating = isset($_GET['rating']) ? (int) $_GET['rating'] : 0;

// Query
$sql = "SELECT f.*, u.username, u.email, u.profile_image, u.role 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id";
if ($filterRating > 0 && $filterRating <= 5) {
    $sql .= " WHERE f.rating = $filterRating";
}
$sql .= " ORDER BY f.created_at DESC";

$feedbacks = $conn->query($sql);

// Stats
$statsResult = $conn->query("SELECT COUNT(*) as total, AVG(rating) as avg_rating, 
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM feedback");
$stats = $statsResult->fetch_assoc();
$totalFeedbacks = (int) $stats['total'];
$avgRating = $totalFeedbacks > 0 ? round($stats['avg_rating'], 1) : 0;

$basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feedback - Paw Pal Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        paw: {
                            accent: '#D97706',
                            dark: '#1F2937',
                            light: '#FEF3C7',
                            bg: '#FFFBEB',
                            gray: '#6B7280'
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['DM Serif Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 p-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-serif text-paw-dark">User Feedback</h2>
                <p class="text-sm text-paw-gray mt-1">Monitor and manage feedback from all users</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="bg-gray-100 p-2 rounded-full">
                    <i data-lucide="message-square-heart" class="w-5 h-5 text-gray-500"></i>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <?php if ($message): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Average Rating -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Average Rating</p>
                        <div class="p-2 bg-yellow-50 rounded-xl">
                            <i data-lucide="star" class="w-4 h-4 text-yellow-500"></i>
                        </div>
                    </div>
                    <p class="font-serif text-4xl font-bold text-paw-dark">
                        <?php echo $avgRating; ?><span class="text-lg text-gray-400">/5</span>
                    </p>
                    <div class="flex gap-1 mt-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-4 h-4" viewBox="0 0 24 24"
                                fill="<?php echo $i <= round($avgRating) ? '#FBBF24' : 'none'; ?>"
                                stroke="<?php echo $i <= round($avgRating) ? '#F59E0B' : '#D1D5DB'; ?>" stroke-width="1.5">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Total Feedback -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Total Feedback</p>
                        <div class="p-2 bg-blue-50 rounded-xl">
                            <i data-lucide="message-circle" class="w-4 h-4 text-blue-500"></i>
                        </div>
                    </div>
                    <p class="font-serif text-4xl font-bold text-paw-dark">
                        <?php echo $totalFeedbacks; ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">From all users</p>
                </div>

                <!-- 5-Star Count -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400">5-Star Reviews</p>
                        <div class="p-2 bg-green-50 rounded-xl">
                            <i data-lucide="thumbs-up" class="w-4 h-4 text-green-500"></i>
                        </div>
                    </div>
                    <p class="font-serif text-4xl font-bold text-paw-dark">
                        <?php echo (int) $stats['five_star']; ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        <?php echo $totalFeedbacks > 0 ? round(((int) $stats['five_star'] / $totalFeedbacks) * 100) : 0; ?>%
                        of total
                    </p>
                </div>

                <!-- Rating Breakdown -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Breakdown</p>
                    <?php
                    $starCounts = [5 => (int) $stats['five_star'], 4 => (int) $stats['four_star'], 3 => (int) $stats['three_star'], 2 => (int) $stats['two_star'], 1 => (int) $stats['one_star']];
                    foreach ($starCounts as $star => $count):
                        $pct = $totalFeedbacks > 0 ? round(($count / $totalFeedbacks) * 100) : 0;
                        ?>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xs font-semibold w-3 text-gray-500">
                                <?php echo $star; ?>
                            </span>
                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="#FBBF24" stroke="#F59E0B"
                                stroke-width="1.5">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-yellow-400 h-1.5 rounded-full" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                            <span class="text-[10px] text-gray-400 w-6 text-right">
                                <?php echo $count; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Filter:</span>
                <a href="feedback.php"
                    class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all <?php echo $filterRating === 0 ? 'bg-paw-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200'; ?>">
                    All
                </a>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <a href="feedback.php?rating=<?php echo $i; ?>"
                        class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all flex items-center gap-1 <?php echo $filterRating === $i ? 'bg-paw-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-100 border border-gray-200'; ?>">
                        <?php echo $i; ?>
                        <svg class="w-3 h-3" viewBox="0 0 24 24"
                            fill="<?php echo $filterRating === $i ? '#FFF' : '#FBBF24'; ?>"
                            stroke="<?php echo $filterRating === $i ? '#FFF' : '#F59E0B'; ?>" stroke-width="1.5">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                        </svg>
                    </a>
                <?php endfor; ?>
            </div>

            <!-- Feedback Cards -->
            <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($fb = $feedbacks->fetch_assoc()): ?>
                        <div
                            class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-4">
                                    <!-- User Avatar -->
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-gray-100 flex-shrink-0">
                                        <img src="<?php
                                        $avatarSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($fb['username']);
                                        if (!empty($fb['profile_image'])) {
                                            if (strpos($fb['profile_image'], 'http') === 0) {
                                                $avatarSrc = $fb['profile_image'];
                                            } else {
                                                $localPath = '../uploads/users/' . $fb['profile_image'];
                                                if (file_exists($localPath)) {
                                                    $avatarSrc = $localPath;
                                                }
                                            }
                                        }
                                        echo $avatarSrc;
                                        ?>" class="w-full h-full object-cover"
                                            alt="<?php echo htmlspecialchars($fb['username']); ?>">
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-paw-dark">
                                            <?php echo htmlspecialchars($fb['username']); ?>
                                        </h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-400">
                                                <?php echo htmlspecialchars($fb['email']); ?>
                                            </span>
                                            <span class="text-gray-300">·</span>
                                            <span
                                                class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest bg-gray-100 text-gray-500">
                                                <?php echo ucfirst($fb['role']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="flex gap-0.5 justify-end">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                                <svg class="w-4 h-4" viewBox="0 0 24 24"
                                                    fill="<?php echo $s <= $fb['rating'] ? '#FBBF24' : 'none'; ?>"
                                                    stroke="<?php echo $s <= $fb['rating'] ? '#F59E0B' : '#D1D5DB'; ?>"
                                                    stroke-width="1.5">
                                                    <polygon
                                                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            <?php echo date('M d, Y · h:i A', strtotime($fb['created_at'])); ?>
                                        </p>
                                    </div>

                                    <a href="feedback.php?delete=<?php echo $fb['id']; ?>"
                                        onclick="return confirm('Delete this feedback?')"
                                        class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4 pl-16">
                                <p class="text-gray-600 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($fb['message'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="message-square" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <h3 class="font-serif text-xl text-gray-400 mb-1">No feedback yet</h3>
                    <p class="text-sm text-gray-400">User feedback will appear here once submitted.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>