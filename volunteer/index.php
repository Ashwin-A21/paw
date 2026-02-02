<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get assigned tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE assigned_to=$userId ORDER BY due_date ASC");

// Get active rescue reports
$rescues = $conn->query("SELECT * FROM rescue_reports WHERE status IN ('Reported', 'Assigned', 'In Progress') ORDER BY urgency DESC, reported_at DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($role); ?> Dashboard - Paw Pal</title>

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

<body class="font-sans text-paw-dark antialiased bg-paw-bg transition-colors duration-300">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside
            class="w-64 bg-paw-dark text-white flex flex-col transition-colors duration-300 border-r border-white/10">
            <div class="p-6 border-b border-white/10">
                <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <p class="text-xs text-white/50 mt-1 uppercase tracking-widest"><?php echo ucfirst($role); ?> Panel</p>
            </div>

            <nav class="flex-1 p-4">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                </a>
                <a href="tasks.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="check-square" class="w-5 h-5"></i> My Tasks
                </a>
                <a href="rescues.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="siren" class="w-5 h-5"></i> Rescue Reports
                </a>
            </nav>

            <div class="p-4 border-t border-white/10">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 bg-<?php echo $role === 'rescuer' ? 'paw-alert' : 'blue-500'; ?> rounded-full flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-xs text-white/50 uppercase"><?php echo $role; ?></p>
                    </div>
                </div>
                <a href="../logout.php" class="flex items-center gap-2 text-white/50 hover:text-white text-sm">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto relative">


            <div class="max-w-6xl mx-auto">
                <div class="mb-8">
                    <h1 class="font-serif text-4xl">Welcome,
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </h1>
                    <p class="text-paw-gray">Thank you for helping rescue animals!</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- My Tasks -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden transition-colors duration-300">
                        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="check-square" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h2 class="font-serif text-xl">My Assigned Tasks</h2>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <?php if ($tasks->num_rows > 0): ?>
                                <?php while ($task = $tasks->fetch_assoc()): ?>
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="font-medium">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </p>
                                                <?php if ($task['description']): ?>
                                                    <p class="text-sm text-paw-gray mt-1">
                                                        <?php echo htmlspecialchars(substr($task['description'], 0, 60)); ?>...
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <span
                                                class="px-3 py-1 text-xs rounded-full 
                                            <?php echo $task['status'] === 'Completed' ? 'bg-green-50 text-green-700' :
                                                ($task['status'] === 'In Progress' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-700'); ?>">
                                                <?php echo $task['status']; ?>
                                            </span>
                                        </div>
                                        <?php if ($task['due_date']): ?>
                                            <p class="text-xs text-paw-gray mt-2">Due:
                                                <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-8 text-center text-paw-gray">
                                    <i data-lucide="check-circle" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                                    <p>No tasks assigned</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Active Rescues -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden transition-colors duration-300">
                        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                <i data-lucide="siren" class="w-5 h-5 text-paw-alert"></i>
                            </div>
                            <h2 class="font-serif text-xl">Active Rescue Reports</h2>
                        </div>
                        <div class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
                            <?php if ($rescues->num_rows > 0): ?>
                                <?php while ($rescue = $rescues->fetch_assoc()): ?>
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start justify-between mb-2">
                                            <p class="font-medium">
                                                <?php echo htmlspecialchars($rescue['location']); ?>
                                            </p>
                                            <span class="px-3 py-1 text-xs rounded-full 
                                            <?php
                                            $urgencyClass = match ($rescue['urgency']) {
                                                'Critical' => 'bg-red-100 text-red-700',
                                                'High' => 'bg-orange-100 text-orange-700',
                                                'Medium' => 'bg-yellow-100 text-yellow-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                            echo $urgencyClass;
                                            ?>">
                                                <?php echo $rescue['urgency']; ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-paw-gray">
                                            <?php echo htmlspecialchars(substr($rescue['description'], 0, 80)); ?>...
                                        </p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span
                                                class="text-xs text-paw-gray"><?php echo date('M d, H:i', strtotime($rescue['reported_at'])); ?></span>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-<?php echo $rescue['status'] === 'Reported' ? 'red' : 'yellow'; ?>-50 text-<?php echo $rescue['status'] === 'Reported' ? 'red' : 'yellow'; ?>-700">
                                                <?php echo $rescue['status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-8 text-center text-paw-gray">
                                    <i data-lucide="heart" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                                    <p>No active rescues</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-paw-dark rounded-2xl p-8 text-white transition-colors duration-300">
                    <h3 class="font-serif text-2xl mb-4">Quick Actions</h3>
                    <div class="flex gap-4">
                        <a href="../rescue.php"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-paw-alert text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-white hover:text-paw-dark transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i> Report Rescue
                        </a>
                        <a href="../adopt.php"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-white hover:text-paw-dark transition-colors">
                            <i data-lucide="heart" class="w-4 h-4"></i> View Pets
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>