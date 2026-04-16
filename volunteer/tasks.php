<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Task/Rescue Status Update if submitted
if (isset($_POST['update_status'])) {
    $itemId = (int) $_POST['item_id'];
    $itemType = $_POST['item_type'];
    $newStatus = $_POST['status'];
    
    $proofImage = null;
    if ($newStatus === 'Rescued' && isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === 0) {
        $uploadDir = '../uploads/rescues/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . basename($_FILES['proof_image']['name']);
        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $uploadDir . $fileName)) {
            $proofImage = $fileName;
        }
    }

    if ($itemType === 'task') {
        $conn->query("UPDATE tasks SET status='$newStatus' WHERE id=$itemId AND assigned_to=$userId");
    } else {
        $proofSql = ($proofImage) ? ", proof_image='$proofImage'" : "";
        $conn->query("UPDATE rescue_reports SET status='$newStatus' $proofSql WHERE id=$itemId AND assigned_to=$userId");
    }
}

// Get assigned tasks and rescue missions
$tasksQuery = "
    SELECT 'task' as type, id, title, description, status, due_date, NULL as location, NULL as urgency 
    FROM tasks 
    WHERE assigned_to = $userId
    UNION ALL
    SELECT 'rescue' as type, id, 'Rescue Operation' as title, description, status, reported_at as due_date, location, urgency 
    FROM rescue_reports 
    WHERE assigned_to = $userId
    ORDER BY status DESC, due_date ASC
";
$tasks = $conn->query($tasksQuery);
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
                        <?php if ($tasks && $tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <div class="p-6 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="p-2 rounded-lg <?php echo $task['type'] === 'rescue' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600'; ?>">
                                                    <i data-lucide="<?php echo $task['type'] === 'rescue' ? 'siren' : 'clipboard-check'; ?>" class="w-4 h-4"></i>
                                                </div>
                                                <h3 class="font-serif text-xl font-bold">
                                                    <?php echo htmlspecialchars($task['title']); ?>
                                                </h3>
                                                <span
                                                    class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full 
                                                    <?php echo in_array($task['status'], ['Completed', 'Rescued']) ? 'bg-green-50 text-green-700' :
                                                        ($task['status'] === 'In Progress' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-700'); ?>">
                                                    <?php echo $task['status']; ?>
                                                </span>
                                            </div>
                                            <p class="text-paw-gray mb-4">
                                                <?php echo htmlspecialchars($task['description']); ?>
                                            </p>

                                            <div class="flex flex-wrap items-center gap-6">
                                                <?php if ($task['due_date']): ?>
                                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                                        <i data-lucide="calendar" class="w-3 h-3"></i> 
                                                        <?php echo $task['type'] === 'rescue' ? 'Reported:' : 'Due:'; ?>
                                                        <span class="font-semibold"><?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                                                    </p>
                                                <?php endif; ?>

                                                <?php if ($task['type'] === 'rescue'): ?>
                                                    <p class="text-xs text-paw-alert flex items-center gap-1 font-bold">
                                                        <i data-lucide="map-pin" class="w-3 h-3"></i> 
                                                        <?php echo htmlspecialchars($task['location']); ?>
                                                    </p>
                                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-tighter rounded bg-red-600 text-white">
                                                        <?php echo $task['urgency']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end gap-2">
                                            <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 task-form">
                                                <input type="hidden" name="item_id" value="<?php echo $task['id']; ?>">
                                                <input type="hidden" name="item_type" value="<?php echo $task['type']; ?>">
                                                
                                                <div class="flex flex-col gap-2">
                                                    <select name="status" onchange="handleStatusChange(this, '<?php echo $task['type']; ?>')"
                                                        class="text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-paw-accent font-bold status-select">
                                                        <?php if ($task['type'] === 'task'): ?>
                                                            <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                            <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <?php else: ?>
                                                            <option value="Assigned" <?php echo $task['status'] === 'Assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                            <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                            <option value="Rescued" <?php echo $task['status'] === 'Rescued' ? 'selected' : ''; ?>>Rescued</option>
                                                            <option value="Closed" <?php echo $task['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                                        <?php endif; ?>
                                                    </select>

                                                    <?php if ($task['type'] === 'rescue'): ?>
                                                        <div class="hidden proof-upload-container mt-1">
                                                            <label class="block text-[9px] uppercase tracking-tighter font-bold text-paw-alert mb-1">Proof Image Required</label>
                                                            <input type="file" name="proof_image" accept="image/*" 
                                                                class="text-[10px] w-full border border-paw-alert rounded-md p-1 proof-input">
                                                            <button type="submit" name="update_status" value="1"
                                                                class="mt-2 w-full py-1.5 bg-paw-alert text-white rounded text-[10px] font-bold uppercase transition-colors hover:bg-paw-dark">
                                                                Submit Proof
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <input type="hidden" name="update_status" value="1" class="update-submit-flag">
                                            </form>
                                            <?php if ($task['type'] === 'rescue'): ?>
                                                <a href="../rescue-details.php?id=<?php echo $task['id']; ?>" target="_blank" class="text-[10px] text-paw-gray hover:text-paw-accent transition-colors underline uppercase font-bold tracking-widest">View Mission</a>
                                            <?php endif; ?>
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

    <script>
        lucide.createIcons();

        function handleStatusChange(select, type) {
            const form = select.closest('form');
            const proofContainer = form.querySelector('.proof-upload-container');
            
            if (type === 'rescue' && select.value === 'Rescued') {
                // Show file input and hide automatic submit
                if (proofContainer) {
                    proofContainer.classList.remove('hidden');
                    // We don't submit yet, wait for user to pick file and click "Submit Proof"
                }
            } else {
                // Submit immediately for other cases
                if (proofContainer) proofContainer.classList.add('hidden');
                form.submit();
            }
        }
    </script>
</body>

</html>