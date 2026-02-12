<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

// Get stats
$petsResult = $conn->query("SELECT COUNT(*) as count FROM pets");
$petsCount = $petsResult->fetch_assoc()['count'];

$adoptionsResult = $conn->query("SELECT COUNT(*) as count FROM adoption_applications WHERE status='Pending'");
$pendingAdoptions = $adoptionsResult->fetch_assoc()['count'];

$rescuesResult = $conn->query("SELECT COUNT(*) as count FROM rescue_reports WHERE status IN ('Reported', 'Assigned', 'In Progress')");
$activeRescues = $rescuesResult->fetch_assoc()['count'];

$usersResult = $conn->query("SELECT COUNT(*) as count FROM users");
$usersCount = $usersResult->fetch_assoc()['count'];

// Recent Applications
$recentApps = $conn->query("SELECT aa.*, u.username, p.name as pet_name FROM adoption_applications aa 
                            JOIN users u ON aa.user_id = u.id 
                            JOIN pets p ON aa.pet_id = p.id 
                            ORDER BY aa.application_date DESC LIMIT 5");

// Recent Rescues
$recentRescues = $conn->query("SELECT * FROM rescue_reports ORDER BY reported_at DESC LIMIT 5");

// Current User (for sidebar)
$uid = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT * FROM users WHERE id=$uid");
$currentUser = $userQuery->fetch_assoc();

$basePath = '../';
$hideNavbar = true;
$hideFooter = true;
include '../includes/header.php';
?>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-paw-dark text-white flex flex-col hidden md:flex">
        <div class="p-6 border-b border-white/10">
            <p class="text-xs text-white/50 mt-1 uppercase tracking-widest">Admin Panel</p>
        </div>

        <nav class="flex-1 p-4">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="pets.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="heart" class="w-5 h-5"></i> Manage Pets
            </a>
            <a href="applications.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i> Applications
            </a>
            <a href="rescues.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="siren" class="w-5 h-5"></i> Rescue Reports
            </a>
            <a href="blogs.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="book-open" class="w-5 h-5"></i> Blog Posts
            </a>
            <a href="users.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="users" class="w-5 h-5"></i> Users
            </a>
            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="user-circle" class="w-5 h-5"></i> My Profile
            </a>
        </nav>

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20">
                    <img src="<?php
                    $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($currentUser['username']);
                    if (!empty($currentUser['profile_image'])) {
                        if (strpos($currentUser['profile_image'], 'http') === 0) {
                            $imgSrc = $currentUser['profile_image'];
                        } else {
                            $basePath = '../uploads/users/';
                            if (file_exists($basePath . $currentUser['profile_image'])) {
                                $imgSrc = $basePath . htmlspecialchars($currentUser['profile_image']);
                            }
                        }
                    }
                    echo $imgSrc;
                    ?>" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-xs text-white/50 uppercase">Admin</p>
                </div>
            </div>
            <a href="../logout.php"
                class="flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="font-serif text-4xl">Dashboard</h1>
                    <p class="text-paw-gray">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </p>
                </div>
                <a href="pets.php?action=add"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Pet
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-paw-accent/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="heart" class="w-6 h-6 text-paw-accent"></i>
                        </div>
                        <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">Active</span>
                    </div>
                    <p class="font-serif text-4xl mb-1"><?php echo $petsCount; ?></p>
                    <p class="text-paw-gray text-sm">Total Pets</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="clipboard-list" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                        <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Pending</span>
                    </div>
                    <p class="font-serif text-4xl mb-1"><?php echo $pendingAdoptions; ?></p>
                    <p class="text-paw-gray text-sm">Applications</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="siren" class="w-6 h-6 text-paw-alert"></i>
                        </div>
                        <span class="text-xs text-paw-alert bg-red-50 px-2 py-1 rounded-full">Active</span>
                    </div>
                    <p class="font-serif text-4xl mb-1"><?php echo $activeRescues; ?></p>
                    <p class="text-paw-gray text-sm">Rescue Reports</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                        </div>
                    </div>
                    <p class="font-serif text-4xl mb-1"><?php echo $usersCount; ?></p>
                    <p class="text-paw-gray text-sm">Registered Users</p>
                </div>
            </div>

            <!-- Recent Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Applications -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-serif text-xl">Recent Applications</h2>
                        <a href="applications.php" class="text-paw-accent text-sm hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <?php if ($recentApps->num_rows > 0): ?>
                            <?php while ($app = $recentApps->fetch_assoc()): ?>
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($app['username']); ?></p>
                                        <p class="text-sm text-paw-gray">applying for
                                            <?php echo htmlspecialchars($app['pet_name']); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 text-xs rounded-full 
                                        <?php echo $app['status'] === 'Pending' ? 'bg-yellow-50 text-yellow-700' :
                                            ($app['status'] === 'Approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'); ?>">
                                        <?php echo $app['status']; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-paw-gray">No applications yet</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Rescues -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-serif text-xl">Recent Rescues</h2>
                        <a href="rescues.php" class="text-paw-alert text-sm hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <?php if ($recentRescues->num_rows > 0): ?>
                            <?php while ($rescue = $recentRescues->fetch_assoc()): ?>
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($rescue['location']); ?></p>
                                        <p class="text-sm text-paw-gray">
                                            <?php echo date('M d, H:i', strtotime($rescue['reported_at'])); ?>
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 text-xs rounded-full 
                                        <?php echo $rescue['status'] === 'Reported' ? 'bg-red-50 text-red-700' :
                                            ($rescue['status'] === 'Rescued' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700'); ?>">
                                        <?php echo $rescue['status']; ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-8 text-center text-paw-gray">No rescue reports</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>