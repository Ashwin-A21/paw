<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Fetch current user details
$userResult = $conn->query("SELECT * FROM users WHERE id=$userId");
$currentUser = $userResult->fetch_assoc();

// Get user's applications
$applications = $conn->query("SELECT aa.*, p.name as pet_name, p.type, p.image 
                               FROM adoption_applications aa 
                               JOIN pets p ON aa.pet_id = p.id 
                               WHERE aa.user_id = $userId 
                               ORDER BY aa.application_date DESC");

$basePath = '../';
include '../includes/header.php';
?>

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
                                    alt="<?php echo htmlspecialchars($app['pet_name']); ?>" class="w-full h-full object-cover">
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

<?php include '../includes/footer.php'; ?>