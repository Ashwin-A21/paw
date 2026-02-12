<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Status Update
if (isset($_POST['update_status'])) {
    $reportId = (int) $_POST['report_id'];
    $newStatus = $_POST['status'];
    $rescuedByOption = $_POST['rescued_by_option'] ?? '';

    $sql = "UPDATE rescue_reports SET status='$newStatus'";

    // If marked as Rescued and "By Me" is selected, assign to current user
    if ($newStatus === 'Rescued' && $rescuedByOption === 'me') {
        $sql .= ", assigned_to=$userId";
    }

    $sql .= " WHERE id=$reportId";
    $conn->query($sql);
}

// Get all active rescues
$rescues = $conn->query("SELECT * FROM rescue_reports ORDER BY urgency DESC, reported_at DESC");

// Get My Rescues (History)
$myRescues = $conn->query("SELECT * FROM rescue_reports WHERE assigned_to=$userId AND status='Rescued' ORDER BY updated_at DESC");

$basePath = '../';
include '../includes/header.php';
?>

<!-- Confirmation Modal -->
<div id="rescueModal" class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 shadow-2xl">
        <h3 class="font-serif text-2xl mb-4 text-center">Rescue Confirmation</h3>
        <p class="text-paw-gray text-center mb-6">Who performed this rescue?</p>

        <form id="rescueForm" method="POST" class="flex flex-col gap-3">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="report_id" id="modalReportId">
            <input type="hidden" name="status" value="Rescued">

            <button type="submit" name="rescued_by_option" value="me"
                class="w-full py-3 bg-paw-accent text-white rounded-xl font-bold hover:bg-paw-dark transition-colors flex items-center justify-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4"></i> I Rescued It
            </button>
            <button type="submit" name="rescued_by_option" value="other"
                class="w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> Someone Else
            </button>
            <button type="button" onclick="closeModal()"
                class="mt-2 text-sm text-paw-gray hover:text-paw-alert underline text-center">Cancel</button>
        </form>
    </div>
</div>

<div class="flex min-h-screen pt-20">
    <!-- Sidebar -->
    <aside class="w-64 bg-paw-dark text-white flex flex-col border-r border-white/10 hidden md:flex">
        <div class="p-6 border-b border-white/10">
            <p class="text-xs text-white/50 mt-1 uppercase tracking-widest"><?php echo ucfirst($role); ?> Panel</p>
        </div>

        <nav class="flex-1 p-4">
            <a href="index.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
            </a>
            <a href="tasks.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="check-square" class="w-5 h-5"></i> My Tasks
            </a>
            <a href="rescues.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                <i data-lucide="siren" class="w-5 h-5"></i> Rescue Reports
            </a>
            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="user-circle" class="w-5 h-5"></i> My Profile
            </a>

            <div class="mt-8 mb-2 px-4 text-xs font-semibold text-white/30 uppercase tracking-widest">
                Quick Links
            </div>
            <a href="../adopt.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="heart" class="w-5 h-5"></i> Adopt a Pet
            </a>
            <a href="../blogs.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="book-open" class="w-5 h-5"></i> Success Stories
            </a>
            <a href="../public/index.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i> My Adoptions
            </a>
        </nav>

        <div class="p-4 border-t border-white/10">
            <a href="../logout.php" class="flex items-center gap-2 text-white/50 hover:text-white text-sm">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="font-serif text-4xl mb-2">Rescue Reports</h1>
                    <p class="text-paw-gray">View and update emergency situations.</p>
                </div>
                <a href="../rescue.php"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-paw-alert text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> New Report
                </a>
            </div>

            <!-- Tabs -->
            <div class="flex gap-6 mb-8 border-b border-gray-200">
                <button onclick="showTab('active')" id="tab-active"
                    class="pb-3 border-b-2 border-paw-dark font-bold text-paw-dark transition-colors">Active
                    Reports</button>
                <button onclick="showTab('history')" id="tab-history"
                    class="pb-3 border-b-2 border-transparent text-paw-gray hover:text-paw-dark transition-colors">My
                    Rescue History</button>
            </div>

            <!-- Active Reports List -->
            <div id="view-active" class="grid grid-cols-1 gap-6">
                <?php if ($rescues->num_rows > 0): ?>
                    <?php while ($rescue = $rescues->fetch_assoc()): ?>
                        <div
                            class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-4 
                                <?php echo $rescue['urgency'] === 'Critical' ? 'border-red-500' : ($rescue['urgency'] === 'High' ? 'border-orange-500' : 'border-yellow-500'); ?>">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-widest
                                                <?php echo $rescue['urgency'] === 'Critical' ? 'bg-red-100 text-red-700' : ($rescue['urgency'] === 'High' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700'); ?>">
                                            <?php echo $rescue['urgency']; ?>
                                        </span>
                                        <span class="text-sm text-paw-gray">Reported:
                                            <?php echo date('M d, H:i', strtotime($rescue['reported_at'])); ?></span>
                                    </div>

                                    <!-- Status Update Form -->
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="report_id" value="<?php echo $rescue['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" onchange="handleStatusChange(this, <?php echo $rescue['id']; ?>)"
                                            class="text-sm border border-gray-200 rounded-lg px-3 py-1 bg-white focus:outline-none focus:border-paw-accent cursor-pointer font-medium">
                                            <option value="Reported" <?php echo $rescue['status'] === 'Reported' ? 'selected' : ''; ?>>Reported</option>
                                            <option value="Assigned" <?php echo $rescue['status'] === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                                            <option value="In Progress" <?php echo $rescue['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="Rescued" <?php echo $rescue['status'] === 'Rescued' ? 'selected' : ''; ?>>Rescued</option>
                                            <option value="Closed" <?php echo $rescue['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </form>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="md:col-span-2">
                                        <h3 class="font-serif text-2xl mb-2">
                                            <?php echo htmlspecialchars($rescue['location']); ?>
                                        </h3>
                                        <p class="text-paw-gray leading-relaxed mb-4">
                                            <?php echo htmlspecialchars($rescue['description']); ?>
                                        </p>
                                        <!-- Fixed Contact Info Check -->
                                        <?php if (isset($rescue['contact_phone']) && $rescue['contact_phone']): ?>
                                            <div
                                                class="flex items-center gap-2 text-sm text-paw-gray bg-gray-50 px-4 py-2 rounded-lg w-fit">
                                                <i data-lucide="phone" class="w-4 h-4"></i>
                                                <?php echo htmlspecialchars($rescue['contact_phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if ($rescue['image']): ?>
                                            <div class="w-full h-48 rounded-xl overflow-hidden">
                                                <img src="../uploads/rescues/<?php echo $rescue['image']; ?>"
                                                    class="w-full h-full object-cover" alt="Rescue Image">
                                            </div>
                                        <?php else: ?>
                                            <div
                                                class="w-full h-48 rounded-xl bg-gray-100 flex items-center justify-center text-paw-gray">
                                                <i data-lucide="image-off" class="w-8 h-8 opacity-50"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-12 text-center text-paw-gray bg-white rounded-2xl">
                        <i data-lucide="siren" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                        <h3 class="text-xl font-medium mb-2">No Active Reports</h3>
                        <p>Great! There are no active rescue reports at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- My History List (Hidden by default) -->
            <div id="view-history" class="hidden grid grid-cols-1 gap-6">
                <?php if ($myRescues->num_rows > 0): ?>
                    <?php while ($rescue = $myRescues->fetch_assoc()): ?>
                        <div
                            class="bg-white rounded-2xl shadow-sm overflow-hidden border-l-4 border-green-500 opacity-75 hover:opacity-100 transition-opacity">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="px-3 py-1 text-xs rounded-full font-bold uppercase tracking-widest bg-green-100 text-green-700">
                                            Rescued by You
                                        </span>
                                        <span class="text-sm text-paw-gray">Reported:
                                            <?php echo date('M d, Y', strtotime($rescue['reported_at'])); ?></span>
                                    </div>
                                </div>
                                <h3 class="font-serif text-xl mb-2"><?php echo htmlspecialchars($rescue['location']); ?>
                                </h3>
                                <p class="text-sm text-paw-gray"><?php echo htmlspecialchars($rescue['description']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-12 text-center text-paw-gray bg-white rounded-2xl">
                        <i data-lucide="award" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                        <h3 class="text-xl font-medium mb-2">No Rescue History Yet</h3>
                        <p>Mark reports as "Rescued by Me" to see them here.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script>
    function handleStatusChange(select, id) {
        if (select.value === 'Rescued') {
            // Prevent immediate submission
            event.preventDefault();
            // Show modal
            document.getElementById('modalReportId').value = id;
            document.getElementById('rescueModal').classList.remove('hidden');

            // Reset select to previous value (optional, helps UI consistency if cancelled)
            // For now, simpler to just let the modal handle the action
        } else {
            // Submit normally for other statuses
            select.form.submit();
        }
    }

    function closeModal() {
        document.getElementById('rescueModal').classList.add('hidden');
        // Ideally reset the select boxes here if needed, but page refresh on submit handles it
        window.location.reload();
    }

    function showTab(tabName) {
        const activeView = document.getElementById('view-active');
        const historyView = document.getElementById('view-history');
        const activeTab = document.getElementById('tab-active');
        const historyTab = document.getElementById('tab-history');

        if (tabName === 'active') {
            activeView.classList.remove('hidden');
            historyView.classList.add('hidden');
            activeTab.classList.add('border-paw-dark', 'text-paw-dark');
            activeTab.classList.remove('border-transparent', 'text-paw-gray');
            historyTab.classList.remove('border-paw-dark', 'text-paw-dark');
            historyTab.classList.add('border-transparent', 'text-paw-gray');
        } else {
            activeView.classList.add('hidden');
            historyView.classList.remove('hidden');
            historyTab.classList.add('border-paw-dark', 'text-paw-dark');
            historyTab.classList.remove('border-transparent', 'text-paw-gray');
            activeTab.classList.remove('border-paw-dark', 'text-paw-dark');
            activeTab.classList.add('border-transparent', 'text-paw-gray');
        }
    }
</script>
<?php include '../includes/footer.php'; ?>