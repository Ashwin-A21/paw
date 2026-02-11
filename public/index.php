<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Get user's applications
$applications = $conn->query("SELECT aa.*, p.name as pet_name, p.type, p.image 
                               FROM adoption_applications aa 
                               JOIN pets p ON aa.pet_id = p.id 
                               WHERE aa.user_id = $userId 
                               ORDER BY aa.application_date DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Paw Pal</title>

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
    <style>
        body {
            background-color: #F9F8F6;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <div class="hidden md:flex items-center space-x-10">
                    <a href="../index.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Home</a>
                    <a href="../adopt.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Adopt</a>
                    <a href="../blogs.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Blog</a>
                    <a href="profile.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Profile</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <?php if (in_array($role, ['volunteer', 'rescuer'])): ?>
                            <a href="../volunteer/index.php" class="px-5 py-2 border border-paw-dark rounded-full text-xs uppercase tracking-widest font-bold hover:bg-paw-dark hover:text-white transition-colors">
                                Volunteer Panel
                            </a>
                    <?php endif; ?>
                    <span class="text-sm text-paw-gray"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../logout.php"
                        class="px-6 py-2 bg-paw-dark text-white rounded-full text-xs uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-32 pb-20 px-6">
        <div class="max-w-5xl mx-auto">
            <div class="mb-10">
                <h1 class="font-serif text-5xl mb-2">My Dashboard</h1>
                <p class="text-paw-gray">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-paw-accent/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="clipboard-list" class="w-6 h-6 text-paw-accent"></i>
                        </div>
                        <div>
                            <p class="font-serif text-3xl"><?php echo $applications->num_rows; ?></p>
                            <p class="text-paw-gray text-sm">Applications</p>
                        </div>
                    </div>
                </div>
                <a href="../adopt.php"
                    class="bg-paw-accent/5 rounded-2xl p-6 border-2 border-dashed border-paw-accent/30 hover:border-paw-accent transition-colors group">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-paw-accent/10 rounded-xl flex items-center justify-center group-hover:bg-paw-accent group-hover:text-white transition-colors">
                            <i data-lucide="heart" class="w-6 h-6 text-paw-accent group-hover:text-white"></i>
                        </div>
                        <div>
                            <p class="font-serif text-xl">Browse Pets</p>
                            <p class="text-paw-gray text-sm">Find your match</p>
                        </div>
                    </div>
                </a>
                <a href="../rescue.php"
                    class="bg-paw-alert/5 rounded-2xl p-6 border-2 border-dashed border-paw-alert/30 hover:border-paw-alert transition-colors group">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-paw-alert/10 rounded-xl flex items-center justify-center group-hover:bg-paw-alert group-hover:text-white transition-colors">
                            <i data-lucide="siren" class="w-6 h-6 text-paw-alert group-hover:text-white"></i>
                        </div>
                        <div>
                            <p class="font-serif text-xl">Report Rescue</p>
                            <p class="text-paw-gray text-sm">Help an animal</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Applications -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="font-serif text-2xl">My Adoption Applications</h2>
                </div>

                <?php if ($applications->num_rows > 0): ?>
                    <div class="divide-y divide-gray-50">
                        <?php while ($app = $applications->fetch_assoc()): ?>
                            <div class="p-6 flex items-center gap-6 hover:bg-gray-50 transition-colors">
                                <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                                    <img src="<?php echo file_exists('../uploads/pets/' . $app['image']) ? '../uploads/pets/' . $app['image'] : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=100'; ?>"
                                        alt="<?php echo htmlspecialchars($app['pet_name']); ?>"
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-serif text-xl"><?php echo htmlspecialchars($app['pet_name']); ?></h3>
                                    <p class="text-sm text-paw-gray capitalize"><?php echo $app['type']; ?> • Applied
                                        <?php echo date('M d, Y', strtotime($app['application_date'])); ?>
                                    </p>
                                </div>
                                <span
                                    class="px-4 py-2 text-sm rounded-full 
                            <?php echo $app['status'] === 'Pending' ? 'bg-yellow-50 text-yellow-700' :
                                ($app['status'] === 'Approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'); ?>">
                                    <?php echo $app['status']; ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-paw-accent/10 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="heart" class="w-10 h-10 text-paw-accent"></i>
                        </div>
                        <h3 class="font-serif text-2xl mb-2">No Applications Yet</h3>
                        <p class="text-paw-gray mb-6">You haven't applied to adopt any pets yet.</p>
                        <a href="../adopt.php"
                            class="inline-flex items-center gap-2 px-8 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                            Browse Pets <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal.</p>
            <p>Built with <i data-lucide="heart" class="inline w-4 h-4 text-paw-alert"></i> for animals</p>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>

</html>