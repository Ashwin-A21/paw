<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$view = isset($_GET['view']) ? $_GET['view'] : 'all';

if ($view === 'history') {
    // Successful adoptions only (detailed view)
    $applications = $conn->query("SELECT aa.*, 
                                      u_adopter.username as adopter_name, u_adopter.email as adopter_email, u_adopter.phone as adopter_phone,
                                      u_seller.username as seller_name, u_seller.email as seller_email, u_seller.phone as seller_phone,
                                      p.name as pet_name, p.type, p.breed, p.image as pet_image
                               FROM adoption_applications aa 
                               JOIN users u_adopter ON aa.user_id = u_adopter.id 
                               JOIN pets p ON aa.pet_id = p.id 
                               JOIN users u_seller ON p.added_by = u_seller.id 
                               WHERE aa.owner_response = 'Deal'
                               ORDER BY aa.application_date DESC");
} else {
    // All applications
    $applications = $conn->query("SELECT aa.*, u.username, u.email, p.name as pet_name, p.type 
                                  FROM adoption_applications aa 
                                  JOIN users u ON aa.user_id = u.id 
                                  JOIN pets p ON aa.pet_id = p.id 
                                  ORDER BY aa.application_date DESC");
}
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
                <h1 class="font-serif text-4xl mb-2">Adoption Applications</h1>
                <p class="text-paw-gray mb-8">Manage and track all adoption activities on the platform.</p>

                <!-- Tabs -->
                <div class="flex gap-4 mb-8 border-b border-gray-100">
                    <a href="applications.php?view=all" 
                       class="pb-4 px-2 text-sm font-bold uppercase tracking-widest transition-all <?php echo $view !== 'history' ? 'text-paw-accent border-b-2 border-paw-accent' : 'text-paw-gray hover:text-paw-dark'; ?>">
                        All Requests
                    </a>
                    <a href="applications.php?view=history" 
                       class="pb-4 px-2 text-sm font-bold uppercase tracking-widest transition-all <?php echo $view === 'history' ? 'text-paw-accent border-b-2 border-paw-accent' : 'text-paw-gray hover:text-paw-dark'; ?>">
                        Success History
                    </a>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                                <?php if ($view === 'history'): ?>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Pet</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Adopter</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Seller</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Date & Location</th>
                                <?php else: ?>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Applicant</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Pet</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Date</th>
                                    <th class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">Result</th>
                                <?php endif; ?>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if ($applications->num_rows > 0): ?>
                                <?php while ($app = $applications->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <?php if ($view === 'history'): ?>
                                            <!-- History View Items -->
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0">
                                                        <img src="../uploads/pets/<?php echo rawurlencode($app['pet_image']); ?>" class="w-full h-full object-cover">
                                                    </div>
                                                    <p class="font-bold text-sm"><?php echo htmlspecialchars($app['pet_name']); ?></p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium text-sm"><?php echo htmlspecialchars($app['adopter_name']); ?></p>
                                                <p class="text-[10px] text-paw-gray italic"><?php echo htmlspecialchars($app['adopter_email']); ?></p>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-paw-gray">
                                                <?php echo htmlspecialchars($app['seller_name']); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-xs"><?php echo date('M d, Y', strtotime($app['application_date'])); ?></p>
                                                <p class="text-[10px] text-paw-accent italic"><?php echo htmlspecialchars($app['pickup_location'] ?: 'Standard'); ?></p>
                                            </td>
                                        <?php else: ?>
                                            <!-- All View Items -->
                                            <td class="px-6 py-4">
                                                <p class="font-medium"><?php echo htmlspecialchars($app['adopter_name'] ?? $app['username']); ?></p>
                                                <p class="text-[10px] text-paw-gray"><?php echo htmlspecialchars($app['email']); ?></p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="font-medium"><?php echo htmlspecialchars($app['pet_name']); ?></p>
                                                <p class="text-sm text-paw-gray capitalize"><?php echo $app['type']; ?></p>
                                            </td>
                                            <td class="px-6 py-4 text-paw-gray text-sm">
                                                <?php echo date('M d, Y', strtotime($app['application_date'])); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 text-[10px] rounded-full <?php echo $app['owner_response'] === 'Pending' ? 'bg-gray-100 text-gray-600' : ($app['owner_response'] === 'Deal' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'); ?>">
                                                    <?php echo $app['owner_response'] === 'Deal' ? '🤝 Deal' : ($app['owner_response'] === 'No Deal' ? '❌ No Deal' : 'Pending'); ?>
                                                </span>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-paw-gray italic">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>