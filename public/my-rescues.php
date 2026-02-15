<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];

// Fetch reports filed by the user
$myReports = $conn->query("SELECT * FROM rescue_reports WHERE reporter_id = $userId ORDER BY reported_at DESC");

// Fetch rescues assigned to / done by the user
$myRescues = $conn->query("SELECT r.*, u.username as reporter_username FROM rescue_reports r LEFT JOIN users u ON r.reporter_id = u.id WHERE r.assigned_to = $userId ORDER BY r.updated_at DESC");

// Stats
$totalReported = $myReports ? $myReports->num_rows : 0;
$totalRescued = $myRescues ? $myRescues->num_rows : 0;

$basePath = '../';
include '../includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-6xl mx-auto">

        <!-- Page Header -->
        <div class="mb-10">
            <a href="index.php"
                class="text-paw-gray hover:text-paw-accent flex items-center gap-2 mb-4 text-sm uppercase tracking-widest transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
            </a>
            <h1 class="font-serif text-5xl mb-2 text-paw-dark">My Rescue Activity</h1>
            <p class="text-paw-gray text-lg">Track all your rescue reports and rescue missions.</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Reports Filed</p>
                    <div class="p-2.5 bg-orange-50 rounded-xl">
                        <i data-lucide="file-text" class="w-5 h-5 text-paw-alert"></i>
                    </div>
                </div>
                <p class="font-serif text-4xl font-bold text-paw-dark">
                    <?php echo $totalReported; ?>
                </p>
                <p class="text-xs text-gray-400 mt-1">Rescue reports you've submitted</p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Rescues Done</p>
                    <div class="p-2.5 bg-green-50 rounded-xl">
                        <i data-lucide="heart-handshake" class="w-5 h-5 text-green-500"></i>
                    </div>
                </div>
                <p class="font-serif text-4xl font-bold text-paw-dark">
                    <?php echo $totalRescued; ?>
                </p>
                <p class="text-xs text-gray-400 mt-1">Animals you've helped rescue</p>
            </div>

            <div
                class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col justify-center items-center text-center">
                <a href="../rescue.php"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-paw-alert text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-alert/20">
                    <i data-lucide="plus" class="w-4 h-4"></i> File New Report
                </a>
                <p class="text-xs text-gray-400 mt-3">Spotted an animal in distress?</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-200 mb-8">
            <button onclick="showTab('reports')" id="tab-reports"
                class="tab-btn px-6 py-3 text-sm font-bold uppercase tracking-widest border-b-2 border-paw-accent text-paw-accent transition-colors">
                <span class="flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    Reports by Me
                    <span class="bg-paw-accent/10 text-paw-accent px-2 py-0.5 rounded-full text-[10px]">
                        <?php echo $totalReported; ?>
                    </span>
                </span>
            </button>
            <button onclick="showTab('rescues')" id="tab-rescues"
                class="tab-btn px-6 py-3 text-sm font-bold uppercase tracking-widest border-b-2 border-transparent text-gray-400 hover:text-paw-dark transition-colors">
                <span class="flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Rescues Done by Me
                    <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-[10px]">
                        <?php echo $totalRescued; ?>
                    </span>
                </span>
            </button>
        </div>

        <!-- Tab: Reports by Me -->
        <div id="panel-reports" class="tab-panel">
            <?php if ($totalReported > 0): ?>
                <div class="space-y-4">
                    <?php while ($report = $myReports->fetch_assoc()): ?>
                        <?php
                        $statusColors = [
                            'Reported' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'Assigned' => 'bg-purple-50 text-purple-600 border-purple-200',
                            'In Progress' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'Rescued' => 'bg-green-50 text-green-600 border-green-200',
                            'Closed' => 'bg-gray-50 text-gray-500 border-gray-200',
                        ];
                        $urgencyColors = [
                            'Low' => 'bg-green-100 text-green-700',
                            'Medium' => 'bg-yellow-100 text-yellow-700',
                            'High' => 'bg-orange-100 text-orange-700',
                            'Critical' => 'bg-red-100 text-red-700',
                        ];
                        $statusClass = $statusColors[$report['status']] ?? 'bg-gray-50 text-gray-500 border-gray-200';
                        $urgencyClass = $urgencyColors[$report['urgency']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <div
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            <div class="flex flex-col md:flex-row">
                                <!-- Image -->
                                <?php if (!empty($report['image'])): ?>
                                    <div class="w-full md:w-48 h-40 md:h-auto flex-shrink-0 overflow-hidden">
                                        <img src="../uploads/rescues/<?php echo htmlspecialchars($report['image']); ?>"
                                            class="w-full h-full object-cover" alt="Rescue">
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1 p-6">
                                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border <?php echo $statusClass; ?>">
                                                <?php echo $report['status']; ?>
                                            </span>
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest <?php echo $urgencyClass; ?>">
                                                <?php echo $report['urgency']; ?> Urgency
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-400">
                                            <?php echo date('M d, Y · h:i A', strtotime($report['reported_at'])); ?>
                                        </span>
                                    </div>

                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        <?php echo htmlspecialchars($report['description']); ?>
                                    </p>

                                    <div class="flex flex-wrap items-center gap-4 text-sm text-paw-gray">
                                        <span class="flex items-center gap-1.5">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-paw-alert"></i>
                                            <?php echo htmlspecialchars($report['location']); ?>
                                        </span>
                                        <?php if (!empty($report['animal_type'])): ?>
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="paw-print" class="w-3.5 h-3.5 text-paw-accent"></i>
                                                <?php echo htmlspecialchars($report['animal_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($report['contact_phone'])): ?>
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="phone" class="w-3.5 h-3.5 text-green-500"></i>
                                                <?php echo htmlspecialchars($report['contact_phone']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Status Timeline -->
                                    <?php if ($report['status'] !== 'Reported'): ?>
                                        <div class="mt-4 pt-4 border-t border-gray-50">
                                            <div class="flex items-center gap-3">
                                                <?php
                                                $stages = ['Reported', 'Assigned', 'In Progress', 'Rescued', 'Closed'];
                                                $currentIdx = array_search($report['status'], $stages);
                                                foreach ($stages as $idx => $stage):
                                                    $done = $idx <= $currentIdx;
                                                    ?>
                                                    <div class="flex items-center gap-1.5">
                                                        <div
                                                            class="w-2 h-2 rounded-full <?php echo $done ? 'bg-green-400' : 'bg-gray-200'; ?>">
                                                        </div>
                                                        <span
                                                            class="text-[10px] uppercase tracking-widest <?php echo $done ? 'text-green-600 font-bold' : 'text-gray-300'; ?>">
                                                            <?php echo $stage; ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($idx < count($stages) - 1): ?>
                                                        <div
                                                            class="flex-1 h-px max-w-[30px] <?php echo $done && $idx < $currentIdx ? 'bg-green-300' : 'bg-gray-100'; ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-100">
                    <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="file-text" class="w-8 h-8 text-orange-200"></i>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-gray-400 mb-2">No reports yet</h3>
                    <p class="text-gray-400 text-sm mb-6">You haven't filed any rescue reports yet.</p>
                    <a href="../rescue.php"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-paw-alert text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i> Report a Rescue
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Rescues Done by Me -->
        <div id="panel-rescues" class="tab-panel hidden">
            <?php if ($totalRescued > 0): ?>
                <div class="space-y-4">
                    <?php while ($rescue = $myRescues->fetch_assoc()): ?>
                        <?php
                        $statusColors = [
                            'Reported' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'Assigned' => 'bg-purple-50 text-purple-600 border-purple-200',
                            'In Progress' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'Rescued' => 'bg-green-50 text-green-600 border-green-200',
                            'Closed' => 'bg-gray-50 text-gray-500 border-gray-200',
                        ];
                        $urgencyColors = [
                            'Low' => 'bg-green-100 text-green-700',
                            'Medium' => 'bg-yellow-100 text-yellow-700',
                            'High' => 'bg-orange-100 text-orange-700',
                            'Critical' => 'bg-red-100 text-red-700',
                        ];
                        $statusClass = $statusColors[$rescue['status']] ?? 'bg-gray-50 text-gray-500 border-gray-200';
                        $urgencyClass = $urgencyColors[$rescue['urgency']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <div
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                            <div class="flex flex-col md:flex-row">
                                <!-- Image -->
                                <?php if (!empty($rescue['image'])): ?>
                                    <div class="w-full md:w-48 h-40 md:h-auto flex-shrink-0 overflow-hidden">
                                        <img src="../uploads/rescues/<?php echo htmlspecialchars($rescue['image']); ?>"
                                            class="w-full h-full object-cover" alt="Rescue">
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1 p-6">
                                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border <?php echo $statusClass; ?>">
                                                <?php echo $rescue['status']; ?>
                                            </span>
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest <?php echo $urgencyClass; ?>">
                                                <?php echo $rescue['urgency']; ?> Urgency
                                            </span>
                                            <?php if ($rescue['status'] === 'Rescued'): ?>
                                                <span
                                                    class="flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-green-500 text-white">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2">
                                                        <polyline points="20 6 9 17 4 12" />
                                                    </svg>
                                                    Completed
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs text-gray-400">
                                            <?php echo date('M d, Y · h:i A', strtotime($rescue['reported_at'])); ?>
                                        </span>
                                    </div>

                                    <!-- Reporter Info -->
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="text-xs text-gray-400">Reported by:</span>
                                        <span class="text-xs font-semibold text-paw-dark">
                                            <?php echo htmlspecialchars($rescue['reporter_username'] ?? $rescue['reporter_name'] ?? 'Anonymous'); ?>
                                        </span>
                                    </div>

                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        <?php echo htmlspecialchars($rescue['description']); ?>
                                    </p>

                                    <div class="flex flex-wrap items-center gap-4 text-sm text-paw-gray">
                                        <span class="flex items-center gap-1.5">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-paw-alert"></i>
                                            <?php echo htmlspecialchars($rescue['location']); ?>
                                        </span>
                                        <?php if (!empty($rescue['animal_type'])): ?>
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="paw-print" class="w-3.5 h-3.5 text-paw-accent"></i>
                                                <?php echo htmlspecialchars($rescue['animal_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($rescue['contact_phone'])): ?>
                                            <span class="flex items-center gap-1.5">
                                                <i data-lucide="phone" class="w-3.5 h-3.5 text-green-500"></i>
                                                <?php echo htmlspecialchars($rescue['contact_phone']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Status Timeline -->
                                    <div class="mt-4 pt-4 border-t border-gray-50">
                                        <div class="flex items-center gap-3">
                                            <?php
                                            $stages = ['Reported', 'Assigned', 'In Progress', 'Rescued', 'Closed'];
                                            $currentIdx = array_search($rescue['status'], $stages);
                                            foreach ($stages as $idx => $stage):
                                                $done = $idx <= $currentIdx;
                                                ?>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-2 h-2 rounded-full <?php echo $done ? 'bg-green-400' : 'bg-gray-200'; ?>">
                                                    </div>
                                                    <span
                                                        class="text-[10px] uppercase tracking-widest <?php echo $done ? 'text-green-600 font-bold' : 'text-gray-300'; ?>">
                                                        <?php echo $stage; ?>
                                                    </span>
                                                </div>
                                                <?php if ($idx < count($stages) - 1): ?>
                                                    <div
                                                        class="flex-1 h-px max-w-[30px] <?php echo $done && $idx < $currentIdx ? 'bg-green-300' : 'bg-gray-100'; ?>">
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-100">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="heart-handshake" class="w-8 h-8 text-green-200"></i>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-gray-400 mb-2">No rescues yet</h3>
                    <p class="text-gray-400 text-sm">Rescues assigned to you will appear here.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
    function showTab(tab) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        // Deactivate all tabs
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-paw-accent', 'text-paw-accent');
            b.classList.add('border-transparent', 'text-gray-400');
        });

        // Show selected panel
        document.getElementById('panel-' + tab).classList.remove('hidden');
        // Activate selected tab
        const activeTab = document.getElementById('tab-' + tab);
        activeTab.classList.add('border-paw-accent', 'text-paw-accent');
        activeTab.classList.remove('border-transparent', 'text-gray-400');

        // Re-init lucide icons for newly visible content
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>