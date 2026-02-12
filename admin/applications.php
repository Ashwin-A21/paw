<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

// Handle Status Update
if (isset($_POST['update_status'])) {
    $appId = (int) $_POST['app_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE adoption_applications SET status='$status' WHERE id=$appId");

    if ($status === 'Approved') {
        $app = $conn->query("SELECT pet_id FROM adoption_applications WHERE id=$appId")->fetch_assoc();
        $conn->query("UPDATE pets SET status='Adopted' WHERE id=" . $app['pet_id']);
    }
}

$applications = $conn->query("SELECT aa.*, u.username, u.email, p.name as pet_name, p.type 
                              FROM adoption_applications aa 
                              JOIN users u ON aa.user_id = u.id 
                              JOIN pets p ON aa.pet_id = p.id 
                              ORDER BY aa.application_date DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Paw Pal Admin</title>

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
            <div class="max-w-5xl mx-auto">
                <h1 class="font-serif text-4xl mb-8">Adoption Applications</h1>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                    Applicant</th>
                                <th
                                    class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                    Pet</th>
                                <th
                                    class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                    Date</th>
                                <th
                                    class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                    Status</th>
                                <th
                                    class="text-right px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php while ($app = $applications->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-medium"><?php echo htmlspecialchars($app['username']); ?></p>
                                        <p class="text-sm text-paw-gray"><?php echo htmlspecialchars($app['email']); ?></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium"><?php echo htmlspecialchars($app['pet_name']); ?></p>
                                        <p class="text-sm text-paw-gray capitalize"><?php echo $app['type']; ?></p>
                                    </td>
                                    <td class="px-6 py-4 text-paw-gray">
                                        <?php echo date('M d, Y', strtotime($app['application_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-3 py-1 text-xs rounded-full 
                                        <?php echo $app['status'] === 'Pending' ? 'bg-yellow-50 text-yellow-700' :
                                            ($app['status'] === 'Approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'); ?>">
                                            <?php echo $app['status']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" class="inline-flex gap-2">
                                            <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                            <select name="status"
                                                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-paw-accent">
                                                <option value="Pending" <?php echo $app['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo $app['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Rejected" <?php echo $app['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                            <button type="submit" name="update_status"
                                                class="px-4 py-1.5 bg-paw-accent text-white rounded-lg text-sm font-medium hover:bg-paw-dark transition-colors">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>