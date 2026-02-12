<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Task Status Update if submitted
if (isset($_POST['update_status'])) {
    $taskId = (int) $_POST['task_id'];
    $newStatus = $_POST['status'];
    $conn->query("UPDATE tasks SET status='$newStatus' WHERE id=$taskId AND assigned_to=$userId");
}

// Get assigned tasks
$tasks = $conn->query("SELECT * FROM tasks WHERE assigned_to=$userId ORDER BY due_date ASC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Paw Pal</title>

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
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="font-serif text-4xl mb-2">My Tasks</h1>
                        <p class="text-paw-gray">Manage your assigned responsibilities.</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-50">
                        <?php if ($tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <div class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="font-serif text-xl font-bold">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </h3>
                                                <span
                                                    class="px-3 py-1 text-xs rounded-full 
                                                    <?php echo $task['status'] === 'Completed' ? 'bg-green-50 text-green-700' :
                                                        ($task['status'] === 'In Progress' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-700'); ?>">
                                                    <?php echo $task['status']; ?>
                                                </span>
                                            </div>
                                            <p class="text-paw-gray mb-4">
                                                <?php echo htmlspecialchars($task['description']); ?>
                                            </p>

                                            <?php if ($task['due_date']): ?>
                                                <p class="text-sm text-red-500 flex items-center gap-2">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i> Due:
                                                    <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ml-6">
                                            <form method="POST" class="flex flex-col gap-2">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <select name="status" onchange="this.form.submit()"
                                                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-paw-accent">
                                                    <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="p-12 text-center text-paw-gray">
                                <i data-lucide="check-circle" class="w-16 h-16 mx-auto mb-4 opacity-30"></i>
                                <h3 class="text-xl font-medium mb-2">All Caught Up!</h3>
                                <p>You have no pending tasks assigned.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>