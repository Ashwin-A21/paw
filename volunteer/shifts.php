<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'user';
if (!in_array($role, ['volunteer', 'rescuer', 'admin'])) {
    header("Location: ../index.php");
    exit();
}

include '../config.php';

$userId = $_SESSION['user_id'];

// Handle add shift
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shift'])) {
    $date = $_POST['date'] ?? '';
    $hours = (float) ($_POST['hours'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (!empty($date) && $hours > 0) {
        $stmt = $conn->prepare("INSERT INTO volunteer_shifts (user_id, date, hours, description, status) VALUES (?, ?, ?, ?, 'Scheduled')");
        $stmt->bind_param("isds", $userId, $date, $hours, $description);
        $stmt->execute();
        $stmt->close();
        header("Location: shifts.php?success=1");
        exit();
    }
}

// Handle mark complete
if (isset($_GET['complete'])) {
    $shiftId = (int) $_GET['complete'];
    $stmt = $conn->prepare("UPDATE volunteer_shifts SET status = 'Completed' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $shiftId, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: shifts.php");
    exit();
}

// Fetch shifts
$stmt = $conn->prepare("SELECT * FROM volunteer_shifts WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$shifts = $stmt->get_result();
$stmt->close();

// Stats
$totalHours = $conn->query("SELECT COALESCE(SUM(hours), 0) as total FROM volunteer_shifts WHERE user_id = $userId AND status = 'Completed'")->fetch_assoc()['total'];
$totalShifts = $conn->query("SELECT COUNT(*) as cnt FROM volunteer_shifts WHERE user_id = $userId AND status = 'Completed'")->fetch_assoc()['cnt'];
$upcomingShifts = $conn->query("SELECT COUNT(*) as cnt FROM volunteer_shifts WHERE user_id = $userId AND status = 'Scheduled' AND date >= CURDATE()")->fetch_assoc()['cnt'];

$basePath = '../';
include '../includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="font-serif text-4xl mb-1">Volunteer Shifts</h1>
                <p class="text-paw-gray">Track your volunteer hours and shifts</p>
            </div>
            <button onclick="document.getElementById('addShiftModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-6 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i> Log Shift
            </button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-50 text-green-700 px-6 py-4 rounded-2xl mb-6 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5"></i> Shift logged successfully!
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm text-center">
                <p class="font-serif text-3xl text-paw-accent">
                    <?php echo number_format($totalHours, 1); ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Total Hours</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $totalShifts; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Completed</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm text-center">
                <p class="font-serif text-3xl text-blue-500">
                    <?php echo $upcomingShifts; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Upcoming</p>
            </div>
        </div>

        <!-- Shifts List -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="font-serif text-xl">My Shifts</h2>
            </div>

            <?php if ($shifts->num_rows > 0): ?>
                <div class="divide-y divide-gray-50">
                    <?php while ($shift = $shifts->fetch_assoc()):
                        $statusColors = [
                            'Scheduled' => 'bg-blue-50 text-blue-600',
                            'Completed' => 'bg-green-50 text-green-600',
                            'Cancelled' => 'bg-gray-100 text-gray-500'
                        ];
                        $color = $statusColors[$shift['status']] ?? 'bg-gray-100 text-gray-500';
                        ?>
                        <div class="p-5 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-paw-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="clock" class="w-5 h-5 text-paw-accent"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-paw-dark">
                                        <?php echo date('M d, Y', strtotime($shift['date'])); ?>
                                    </p>
                                    <p class="text-sm text-paw-gray">
                                        <?php echo $shift['hours']; ?> hours
                                        <?php echo !empty($shift['description']) ? '· ' . htmlspecialchars(substr($shift['description'], 0, 60)) : ''; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full <?php echo $color; ?>">
                                    <?php echo $shift['status']; ?>
                                </span>
                                <?php if ($shift['status'] === 'Scheduled'): ?>
                                    <a href="?complete=<?php echo $shift['id']; ?>"
                                        class="text-xs text-paw-accent hover:underline font-bold">Mark Done</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-off" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <p class="text-gray-400 mb-2">No shifts logged yet</p>
                    <p class="text-sm text-gray-400">Start tracking your volunteer hours!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Add Shift Modal -->
<div id="addShiftModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-serif text-2xl">Log a Shift</h3>
            <button onclick="document.getElementById('addShiftModal').classList.add('hidden')"
                class="p-2 hover:bg-gray-100 rounded-xl cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="add_shift" value="1">
            <?php echo csrfField(); ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-paw-dark mb-1">Date</label>
                    <input type="date" name="date" required
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20">
                </div>
                <div>
                    <label class="block text-sm font-bold text-paw-dark mb-1">Hours</label>
                    <input type="number" name="hours" step="0.5" min="0.5" max="24" required placeholder="e.g. 3"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20">
                </div>
                <div>
                    <label class="block text-sm font-bold text-paw-dark mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="What did you do?"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20 resize-none"></textarea>
                </div>
            </div>
            <button type="submit"
                class="w-full mt-6 py-3 bg-paw-accent text-white rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-paw-dark transition-colors">
                Log Shift
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>