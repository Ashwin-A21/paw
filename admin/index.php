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

// Analytics Data — Adoptions per month (last 6 months)
$adoptionTrend = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $label = date('M', strtotime("-$i months"));
    $cnt = $conn->query("SELECT COUNT(*) as c FROM adoption_applications WHERE application_date BETWEEN '$monthStart' AND '$monthEnd'")->fetch_assoc()['c'];
    $adoptionTrend[] = ['label' => $label, 'count' => (int) $cnt];
}

// Pet type distribution
$petTypes = $conn->query("SELECT type, COUNT(*) as cnt FROM pets GROUP BY type ORDER BY cnt DESC");
$typeData = [];
while ($t = $petTypes->fetch_assoc())
    $typeData[] = $t;

// Rescue status distribution
$rescueStatuses = $conn->query("SELECT status, COUNT(*) as cnt FROM rescue_reports GROUP BY status");
$statusData = [];
while ($s = $rescueStatuses->fetch_assoc())
    $statusData[] = $s;

// Current User (for sidebar)
$uid = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT * FROM users WHERE id=$uid");
$currentUser = $userQuery->fetch_assoc();

$basePath = '../';
$hideNavbar = true;
$hideFooter = true;
include '../includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

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

            <!-- Analytics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <!-- Adoption Trends -->
                <div class="bg-white rounded-2xl shadow-sm p-6 lg:col-span-2">
                    <h3 class="font-serif text-xl mb-4">Adoption Applications Trend</h3>
                    <canvas id="adoptionChart" height="120"></canvas>
                </div>

                <!-- Pet Types -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-serif text-xl mb-4">Pet Types</h3>
                    <canvas id="petTypeChart" height="200"></canvas>
                </div>

                <!-- Rescue Status -->
                <div class="bg-white rounded-2xl shadow-sm p-6 lg:col-span-3">
                    <h3 class="font-serif text-xl mb-4">Rescue Reports by Status</h3>
                    <div class="max-w-md mx-auto">
                        <canvas id="rescueChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Adoption Trends Bar Chart
    new Chart(document.getElementById('adoptionChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($adoptionTrend, 'label')); ?>,
            datasets: [{
                label: 'Applications',
                data: <?php echo json_encode(array_column($adoptionTrend, 'count')); ?>,
                backgroundColor: 'rgba(212, 163, 115, 0.6)',
                borderColor: '#D4A373',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Pet Type Doughnut
    new Chart(document.getElementById('petTypeChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($typeData, 'type')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map('intval', array_column($typeData, 'cnt'))); ?>,
                backgroundColor: ['#D4A373', '#E07A5F', '#00A884', '#6366F1', '#F59E0B', '#EC4899'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, cutout: '65%' }
    });

    // Rescue Status Doughnut
    new Chart(document.getElementById('rescueChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($statusData, 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map('intval', array_column($statusData, 'cnt'))); ?>,
                backgroundColor: ['#EF4444', '#F59E0B', '#3B82F6', '#10B981', '#6B7280'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, cutout: '65%' }
    });
</script>

<?php include '../includes/footer.php'; ?>