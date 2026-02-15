<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $rescueId = (int) $_POST['rescue_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE rescue_reports SET status='$status' WHERE id=$rescueId");
    header("Location: rescues.php");
    exit();
}

// Active reports (not rescued/closed) - sorted by urgency priority then date
$activeRescues = $conn->query("SELECT * FROM rescue_reports WHERE status NOT IN ('Rescued', 'Closed') ORDER BY 
                         CASE urgency 
                            WHEN 'Critical' THEN 1 
                            WHEN 'High' THEN 2 
                            WHEN 'Medium' THEN 3 
                            ELSE 4 
                         END, reported_at DESC");

// Rescued/Closed reports - sorted by most recently updated
$resolvedRescues = $conn->query("SELECT * FROM rescue_reports WHERE status IN ('Rescued', 'Closed') ORDER BY updated_at DESC");

$activeCount = $activeRescues ? $activeRescues->num_rows : 0;
$resolvedCount = $resolvedRescues ? $resolvedRescues->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescue Reports - Paw Pal Admin</title>

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
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <h1 class="font-serif text-4xl mb-2">Rescue Reports</h1>
                <p class="text-paw-gray mb-8">Manage and track rescue operations</p>

                <!-- Tab Navigation -->
                <div class="flex gap-6 mb-8 border-b border-gray-200">
                    <button onclick="showTab('active')" id="tab-active"
                        class="tab-btn pb-3 border-b-2 border-paw-alert font-bold text-paw-dark transition-colors flex items-center gap-2">
                        <i data-lucide="siren" class="w-4 h-4 text-paw-alert"></i>
                        Active Reports
                        <span class="bg-paw-alert/10 text-paw-alert px-2 py-0.5 rounded-full text-[10px] font-bold">
                            <?php echo $activeCount; ?>
                        </span>
                    </button>
                    <button onclick="showTab('resolved')" id="tab-resolved"
                        class="tab-btn pb-3 border-b-2 border-transparent text-paw-gray hover:text-paw-dark transition-colors flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Rescued / Closed
                        <span class="bg-green-50 text-green-600 px-2 py-0.5 rounded-full text-[10px] font-bold">
                            <?php echo $resolvedCount; ?>
                        </span>
                    </button>
                </div>

                <!-- Active Reports Panel -->
                <div id="panel-active" class="tab-panel grid gap-6">
                    <?php if ($activeCount > 0): ?>
                        <?php while ($rescue = $activeRescues->fetch_assoc()): ?>
                            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-4
                                <?php echo match ($rescue['urgency']) {
                                    'Critical' => 'border-red-500',
                                    'High' => 'border-orange-500',
                                    'Medium' => 'border-yellow-500',
                                    default => 'border-gray-300'
                                }; ?>">
                                <div class="flex">
                                    <?php if ($rescue['image'] && file_exists('../uploads/rescues/' . $rescue['image'])): ?>
                                        <div class="w-48 h-48 flex-shrink-0">
                                            <img src="../uploads/rescues/<?php echo rawurlencode($rescue['image']); ?>"
                                                class="w-full h-full object-cover">
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1 p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div>
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-widest
                                                    <?php
                                                    echo match ($rescue['urgency']) {
                                                        'Critical' => 'bg-red-100 text-red-700 animate-pulse',
                                                        'High' => 'bg-orange-100 text-orange-700',
                                                        'Medium' => 'bg-yellow-100 text-yellow-700',
                                                        default => 'bg-gray-100 text-gray-700'
                                                    };
                                                    ?>">
                                                        <?php echo $rescue['urgency']; ?>
                                                    </span>
                                                    <span class="px-3 py-1 text-xs rounded-full font-semibold
                                                    <?php
                                                    echo match ($rescue['status']) {
                                                        'Reported' => 'bg-blue-50 text-blue-600',
                                                        'Assigned' => 'bg-purple-50 text-purple-600',
                                                        'In Progress' => 'bg-yellow-50 text-yellow-700',
                                                        default => 'bg-gray-100 text-gray-600'
                                                    };
                                                    ?>">
                                                        <?php echo $rescue['status']; ?>
                                                    </span>
                                                    <span class="text-xs text-paw-gray">
                                                        <?php echo date('M d, Y H:i', strtotime($rescue['reported_at'])); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-serif text-xl">
                                                    <?php echo htmlspecialchars($rescue['location']); ?>
                                                </h3>
                                                <?php if ($rescue['latitude'] && $rescue['longitude']): ?>
                                                    <a href="https://www.google.com/maps?q=<?php echo $rescue['latitude']; ?>,<?php echo $rescue['longitude']; ?>"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1 text-sm text-paw-alert hover:underline mt-1">
                                                        <i data-lucide="map-pin" class="w-3 h-3"></i> View Exact Location
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <form method="POST" class="flex gap-2">
                                                <input type="hidden" name="rescue_id" value="<?php echo $rescue['id']; ?>">
                                                <select name="status"
                                                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-paw-alert">
                                                    <option value="Reported" <?php echo $rescue['status'] === 'Reported' ? 'selected' : ''; ?>>Reported</option>
                                                    <option value="Assigned" <?php echo $rescue['status'] === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                    <option value="In Progress" <?php echo $rescue['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Rescued" <?php echo $rescue['status'] === 'Rescued' ? 'selected' : ''; ?>>Rescued</option>
                                                    <option value="Closed" <?php echo $rescue['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                </select>
                                                <button type="submit" name="update_status"
                                                    class="px-4 py-2 bg-paw-alert text-white rounded-lg text-sm font-medium hover:bg-paw-dark transition-colors">
                                                    Update
                                                </button>
                                            </form>
                                        </div>
                                        <p class="text-paw-gray mb-4"><?php echo htmlspecialchars($rescue['description']); ?>
                                        </p>
                                        <div class="flex gap-6 text-sm text-paw-gray">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="user" class="w-4 h-4"></i>
                                                <?php echo htmlspecialchars($rescue['reporter_name']); ?>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="phone" class="w-4 h-4"></i>
                                                <?php echo htmlspecialchars($rescue['contact_phone']); ?>
                                            </span>
                                            <a href="../rescue-details.php?id=<?php echo $rescue['id']; ?>"
                                                class="flex items-center gap-1 text-paw-accent hover:text-paw-dark transition-colors font-medium">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl p-12 text-center">
                            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="check-circle" class="w-8 h-8 text-green-400"></i>
                            </div>
                            <h3 class="font-serif text-xl mb-2">All Clear!</h3>
                            <p class="text-paw-gray">No active rescue reports at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Resolved Reports Panel -->
                <div id="panel-resolved" class="tab-panel grid gap-6 hidden">
                    <?php if ($resolvedCount > 0): ?>
                        <?php while ($rescue = $resolvedRescues->fetch_assoc()): ?>
                            <div
                                class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-4 border-green-400 opacity-80 hover:opacity-100 transition-opacity">
                                <div class="flex">
                                    <?php if ($rescue['image'] && file_exists('../uploads/rescues/' . $rescue['image'])): ?>
                                        <div class="w-48 h-48 flex-shrink-0">
                                            <img src="../uploads/rescues/<?php echo $rescue['image']; ?>"
                                                class="w-full h-full object-cover">
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1 p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div>
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span
                                                        class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-widest bg-green-100 text-green-700">
                                                        <?php echo $rescue['status']; ?>
                                                    </span>
                                                    <span class="px-3 py-1 text-xs rounded-full font-semibold
                                                    <?php
                                                    echo match ($rescue['urgency']) {
                                                        'Critical' => 'bg-red-50 text-red-500',
                                                        'High' => 'bg-orange-50 text-orange-500',
                                                        'Medium' => 'bg-yellow-50 text-yellow-600',
                                                        default => 'bg-gray-50 text-gray-500'
                                                    };
                                                    ?>">
                                                        <?php echo $rescue['urgency']; ?>
                                                    </span>
                                                    <span class="text-xs text-paw-gray">
                                                        <?php echo date('M d, Y H:i', strtotime($rescue['reported_at'])); ?>
                                                    </span>
                                                </div>
                                                <h3 class="font-serif text-xl">
                                                    <?php echo htmlspecialchars($rescue['location']); ?>
                                                </h3>
                                            </div>
                                            <form method="POST" class="flex gap-2">
                                                <input type="hidden" name="rescue_id" value="<?php echo $rescue['id']; ?>">
                                                <select name="status"
                                                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-paw-alert">
                                                    <option value="Reported" <?php echo $rescue['status'] === 'Reported' ? 'selected' : ''; ?>>Reported</option>
                                                    <option value="Assigned" <?php echo $rescue['status'] === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                    <option value="In Progress" <?php echo $rescue['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Rescued" <?php echo $rescue['status'] === 'Rescued' ? 'selected' : ''; ?>>Rescued</option>
                                                    <option value="Closed" <?php echo $rescue['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                </select>
                                                <button type="submit" name="update_status"
                                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-medium hover:bg-paw-dark transition-colors">
                                                    Update
                                                </button>
                                            </form>
                                        </div>
                                        <p class="text-paw-gray mb-4"><?php echo htmlspecialchars($rescue['description']); ?>
                                        </p>
                                        <div class="flex gap-6 text-sm text-paw-gray">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="user" class="w-4 h-4"></i>
                                                <?php echo htmlspecialchars($rescue['reporter_name']); ?>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="phone" class="w-4 h-4"></i>
                                                <?php echo htmlspecialchars($rescue['contact_phone']); ?>
                                            </span>
                                            <a href="../rescue-details.php?id=<?php echo $rescue['id']; ?>"
                                                class="flex items-center gap-1 text-paw-accent hover:text-paw-dark transition-colors font-medium">
                                                <i data-lucide="external-link" class="w-4 h-4"></i>
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl p-12 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-300"></i>
                            </div>
                            <h3 class="font-serif text-xl mb-2">No Resolved Reports</h3>
                            <p class="text-paw-gray">Rescued and closed reports will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function showTab(tab) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-paw-alert', 'border-green-500', 'text-paw-dark', 'font-bold');
                b.classList.add('border-transparent', 'text-paw-gray');
            });

            document.getElementById('panel-' + tab).classList.remove('hidden');
            const activeTab = document.getElementById('tab-' + tab);
            activeTab.classList.remove('border-transparent', 'text-paw-gray');
            activeTab.classList.add(tab === 'active' ? 'border-paw-alert' : 'border-green-500', 'text-paw-dark', 'font-bold');

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    </script>
</body>

</html>