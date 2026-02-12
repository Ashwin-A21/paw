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
}

$rescues = $conn->query("SELECT * FROM rescue_reports ORDER BY 
                         CASE urgency 
                            WHEN 'Critical' THEN 1 
                            WHEN 'High' THEN 2 
                            WHEN 'Medium' THEN 3 
                            ELSE 4 
                         END, reported_at DESC");
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
                <h1 class="font-serif text-4xl mb-8">Rescue Reports</h1>

                <div class="grid gap-6">
                    <?php while ($rescue = $rescues->fetch_assoc()): ?>
                        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
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
                                                <span class="px-3 py-1 text-xs rounded-full font-semibold
                                                <?php
                                                echo match ($rescue['urgency']) {
                                                    'Critical' => 'bg-red-100 text-red-700',
                                                    'High' => 'bg-orange-100 text-orange-700',
                                                    'Medium' => 'bg-yellow-100 text-yellow-700',
                                                    default => 'bg-gray-100 text-gray-700'
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>