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

// Handle owner approve/reject of adoption applications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_application'])) {
    $appId = (int) ($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($appId > 0 && in_array($action, ['Approved', 'Rejected'])) {
        // Verify this application is for one of the user's pets
        $verifyStmt = $conn->prepare("SELECT aa.id, aa.user_id, aa.pet_id, p.name as pet_name, p.added_by 
            FROM adoption_applications aa 
            JOIN pets p ON aa.pet_id = p.id 
            WHERE aa.id = ? AND p.added_by = ?");
        $verifyStmt->bind_param("ii", $appId, $userId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows > 0) {
            $appRow = $verifyResult->fetch_assoc();

            // Update application status
            $updateStmt = $conn->prepare("UPDATE adoption_applications SET status = ? WHERE id = ?");
            $updateStmt->bind_param("si", $action, $appId);
            $updateStmt->execute();
            $updateStmt->close();

            // If approved, mark pet as Adopted
            if ($action === 'Approved') {
                $conn->query("UPDATE pets SET status='Adopted' WHERE id=" . $appRow['pet_id']);
                // Reject all other pending applications for this pet
                $rejectStmt = $conn->prepare("UPDATE adoption_applications SET status='Rejected' WHERE pet_id = ? AND id != ? AND status='Pending'");
                $rejectStmt->bind_param("ii", $appRow['pet_id'], $appId);
                $rejectStmt->execute();
                $rejectStmt->close();
            }

            // Notify the applicant
            include_once '../includes/notify.php';
            $statusWord = $action === 'Approved' ? 'approved' : 'rejected';
            createNotification(
                $conn,
                $appRow['user_id'],
                'adoption_status',
                'Your adoption application for "' . $appRow['pet_name'] . '" has been ' . $statusWord . '!',
                'public/index.php'
            );
        }
        $verifyStmt->close();
    }
    header("Location: index.php");
    exit();
}

// Get user's own applications (as an applicant)
$applications = $conn->query("SELECT aa.*, p.name as pet_name, p.type, p.image 
                               FROM adoption_applications aa 
                               JOIN pets p ON aa.pet_id = p.id 
                               WHERE aa.user_id = $userId 
                               ORDER BY aa.application_date DESC");

// Get applications for user's listed pets (as pet owner)
$ownerApps = $conn->query("SELECT aa.*, u.username as applicant_name, u.email as applicant_email, u.phone as applicant_phone,
                                   p.name as pet_name, p.type, p.image as pet_image
                            FROM adoption_applications aa
                            JOIN users u ON aa.user_id = u.id
                            JOIN pets p ON aa.pet_id = p.id
                            WHERE p.added_by = $userId
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

        <!-- Applications For My Pets (Owner View) -->
        <?php if ($ownerApps && $ownerApps->num_rows > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mt-8">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="font-serif text-2xl">Applications for My Pets</h2>
                    <p class="text-sm text-paw-gray mt-1">Review and manage adoption requests for pets you've listed</p>
                </div>

                <div class="divide-y divide-gray-50">
                    <?php while ($oApp = $ownerApps->fetch_assoc()): ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start gap-4">
                                <!-- Pet Image -->
                                <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                                    <img src="<?php echo file_exists('../uploads/pets/' . $oApp['pet_image']) ? '../uploads/pets/' . rawurlencode($oApp['pet_image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=100'; ?>"
                                        alt="<?php echo htmlspecialchars($oApp['pet_name']); ?>"
                                        class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                        <h3 class="font-serif text-lg">
                                            <span
                                                class="text-paw-accent"><?php echo htmlspecialchars($oApp['applicant_name']); ?></span>
                                            wants to adopt <strong><?php echo htmlspecialchars($oApp['pet_name']); ?></strong>
                                        </h3>
                                        <span
                                            class="px-3 py-1 text-xs rounded-full 
                                            <?php echo $oApp['status'] === 'Pending' ? 'bg-yellow-50 text-yellow-700' :
                                                ($oApp['status'] === 'Approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'); ?>">
                                            <?php echo $oApp['status']; ?>
                                        </span>
                                    </div>

                                    <!-- Applicant contact info -->
                                    <div class="flex flex-wrap gap-3 text-xs text-paw-gray mb-2">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($oApp['applicant_email']); ?>"
                                                class="hover:text-paw-accent">
                                                <?php echo htmlspecialchars($oApp['applicant_email']); ?>
                                            </a>
                                        </span>
                                        <?php if (!empty($oApp['applicant_phone'])): ?>
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="phone" class="w-3 h-3"></i>
                                                <a href="tel:<?php echo htmlspecialchars($oApp['applicant_phone']); ?>"
                                                    class="hover:text-paw-accent">
                                                    <?php echo htmlspecialchars($oApp['applicant_phone']); ?>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            <?php echo date('M d, Y', strtotime($oApp['application_date'])); ?>
                                        </span>
                                    </div>

                                    <!-- Applicant message -->
                                    <?php if (!empty($oApp['message'])): ?>
                                        <div class="bg-gray-50 rounded-xl p-3 text-sm text-paw-gray mb-3">
                                            <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mb-1">Message
                                            </p>
                                            <?php echo nl2br(htmlspecialchars($oApp['message'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Actions (only if Pending) -->
                                    <?php if ($oApp['status'] === 'Pending'): ?>
                                        <div class="flex gap-2">
                                            <form method="POST" class="inline">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="manage_application" value="1">
                                                <input type="hidden" name="app_id" value="<?php echo $oApp['id']; ?>">
                                                <input type="hidden" name="action" value="Approved">
                                                <button type="submit"
                                                    class="px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-green-600 transition-colors flex items-center gap-1">
                                                    <i data-lucide="check" class="w-3 h-3"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" class="inline">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="manage_application" value="1">
                                                <input type="hidden" name="app_id" value="<?php echo $oApp['id']; ?>">
                                                <input type="hidden" name="action" value="Rejected">
                                                <button type="submit"
                                                    class="px-4 py-2 bg-red-50 text-red-500 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-100 transition-colors flex items-center gap-1">
                                                    <i data-lucide="x" class="w-3 h-3"></i> Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>