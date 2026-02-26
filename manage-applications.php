<?php
session_start();
include 'config.php';
include_once 'includes/notify.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

// Handle Deal / No Deal action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_action'])) {
    $appId = (int) $_POST['app_id'];
    $action = $_POST['app_action']; // 'Deal' or 'No Deal'
    $ownerNotes = trim($_POST['owner_notes'] ?? '');

    // Verify this application belongs to one of the current user's pets
    $verifyStmt = $conn->prepare("SELECT aa.id, aa.user_id, aa.pet_id, p.name as pet_name, p.added_by 
                                   FROM adoption_applications aa 
                                   JOIN pets p ON aa.pet_id = p.id 
                                   WHERE aa.id = ? AND p.added_by = ?");
    $verifyStmt->bind_param("ii", $appId, $userId);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result()->fetch_assoc();
    $verifyStmt->close();

    if ($verifyResult) {
        if ($action === 'Deal') {
            // Approve: set owner_response, update pet status to Pending
            $updStmt = $conn->prepare("UPDATE adoption_applications SET owner_response = 'Deal', owner_notes = ? WHERE id = ?");
            $updStmt->bind_param("si", $ownerNotes, $appId);
            $updStmt->execute();
            $updStmt->close();

            // Update pet status to Adopted
            $petStmt = $conn->prepare("UPDATE pets SET status = 'Adopted' WHERE id = ?");
            $petStmt->bind_param("i", $verifyResult['pet_id']);
            $petStmt->execute();
            $petStmt->close();

            // Reject all other pending applications for this pet
            $rejectStmt = $conn->prepare("UPDATE adoption_applications SET owner_response = 'No Deal', owner_notes = 'Another applicant was selected.' WHERE pet_id = ? AND id != ? AND owner_response = 'Pending'");
            $rejectStmt->bind_param("ii", $verifyResult['pet_id'], $appId);
            $rejectStmt->execute();
            $rejectStmt->close();

            // Notify the approved applicant
            createNotification(
                $conn,
                $verifyResult['user_id'],
                'adoption_deal',
                '🎉 Great news! Your adoption application for "' . $verifyResult['pet_name'] . '" has been accepted! It\'s a Deal!',
                'pet-details.php?id=' . $verifyResult['pet_id']
            );

            // Get Applicant Name for commenter notifications
            $appUserStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $appUserStmt->bind_param("i", $verifyResult['user_id']);
            $appUserStmt->execute();
            $appUserRes = $appUserStmt->get_result()->fetch_assoc();
            $applicantUsername = $appUserRes['username'];
            $appUserStmt->close();

            // Notify all commenters that the pet was adopted
            $commentersStmt = $conn->prepare("SELECT DISTINCT user_id FROM comments WHERE entity_id = ? AND entity_type = 'pet' AND user_id != ? AND user_id != ?");
            // Don't notify the applicant (they already know) or the owner
            $commentersStmt->bind_param("iii", $verifyResult['pet_id'], $verifyResult['user_id'], $userId);
            $commentersStmt->execute();
            $commentersResult = $commentersStmt->get_result();
            while ($commenter = $commentersResult->fetch_assoc()) {
                createNotification(
                    $conn,
                    $commenter['user_id'],
                    'pet_adopted_commenter',
                    '🐾 Good news! "' . $verifyResult['pet_name'] . '" was just adopted by ' . $applicantUsername . '. View their info and your old comments.',
                    'pet-details.php?id=' . $verifyResult['pet_id']
                );
            }
            $commentersStmt->close();

            // Notify other rejected applicants
            $otherStmt = $conn->prepare("SELECT user_id FROM adoption_applications WHERE pet_id = ? AND id != ? AND user_id != ?");
            $otherStmt->bind_param("iii", $verifyResult['pet_id'], $appId, $verifyResult['user_id']);
            $otherStmt->execute();
            $otherResult = $otherStmt->get_result();
            while ($other = $otherResult->fetch_assoc()) {
                createNotification(
                    $conn,
                    $other['user_id'],
                    'adoption_no_deal',
                    'Your adoption application for "' . $verifyResult['pet_name'] . '" was not selected. Don\'t give up — there are more pets waiting for a home!',
                    'adopt.php'
                );
            }
            $otherStmt->close();

            $successMsg = "Deal! You've accepted this application. The applicant has been notified.";
        } elseif ($action === 'No Deal') {
            $updStmt = $conn->prepare("UPDATE adoption_applications SET owner_response = 'No Deal', owner_notes = ? WHERE id = ?");
            $updStmt->bind_param("si", $ownerNotes, $appId);
            $updStmt->execute();
            $updStmt->close();

            // Notify the rejected applicant
            createNotification(
                $conn,
                $verifyResult['user_id'],
                'adoption_no_deal',
                'Your adoption application for "' . $verifyResult['pet_name'] . '" was declined by the owner.',
                'adopt.php'
            );

            $successMsg = "No Deal. The applicant has been notified.";
        }
    }

    header("Location: manage-applications.php" . (isset($successMsg) ? "?msg=" . urlencode($successMsg) : ""));
    exit();
}

// Fetch all applications for current user's pets
$appsQuery = $conn->prepare("
    SELECT aa.*, 
           u.username as applicant_name, u.email as applicant_email, u.phone as applicant_phone,
           u.profile_image as applicant_image, u.role as applicant_role,
           p.name as pet_name, p.image as pet_image, p.breed, p.type, p.status as pet_status
    FROM adoption_applications aa
    JOIN users u ON aa.user_id = u.id
    JOIN pets p ON aa.pet_id = p.id
    WHERE p.added_by = ?
    ORDER BY 
        CASE aa.owner_response WHEN 'Pending' THEN 0 ELSE 1 END,
        aa.application_date DESC
");
$appsQuery->bind_param("i", $userId);
$appsQuery->execute();
$applications = $appsQuery->get_result();
$appsQuery->close();

// Count stats
$totalApps = 0;
$pendingApps = 0;
$dealApps = 0;
$noDealApps = 0;
$appsList = [];
while ($row = $applications->fetch_assoc()) {
    $appsList[] = $row;
    $totalApps++;
    if ($row['owner_response'] === 'Pending')
        $pendingApps++;
    elseif ($row['owner_response'] === 'Deal')
        $dealApps++;
    else
        $noDealApps++;
}

$basePath = '';
$pageTitle = 'Manage Applications - Paw Pal';
include 'includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-5xl mx-auto">

        <!-- Page Header -->
        <div class="mb-8">
            <a href="adopt.php"
                class="text-paw-gray hover:text-paw-accent flex items-center gap-2 text-sm uppercase tracking-widest font-bold transition-colors mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Adoptions
            </a>
            <h1 class="font-serif text-5xl text-paw-dark mb-3">Adoption Applications</h1>
            <p class="text-paw-gray text-lg">Review and respond to adoption requests for your pets</p>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['msg'])): ?>
            <div
                class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                <p class="text-sm font-medium">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
                <p class="font-serif text-3xl text-paw-dark">
                    <?php echo $totalApps; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-gray-400 mt-1">Total</p>
            </div>
            <div class="bg-yellow-50 rounded-2xl p-5 shadow-sm border border-yellow-100 text-center">
                <p class="font-serif text-3xl text-yellow-700">
                    <?php echo $pendingApps; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-yellow-600 mt-1">Pending</p>
            </div>
            <div class="bg-green-50 rounded-2xl p-5 shadow-sm border border-green-100 text-center">
                <p class="font-serif text-3xl text-green-700">
                    <?php echo $dealApps; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-green-600 mt-1">Deals</p>
            </div>
            <div class="bg-red-50 rounded-2xl p-5 shadow-sm border border-red-100 text-center">
                <p class="font-serif text-3xl text-red-600">
                    <?php echo $noDealApps; ?>
                </p>
                <p class="text-[10px] uppercase tracking-widest font-bold text-red-500 mt-1">No Deals</p>
            </div>
        </div>

        <!-- Applications List -->
        <?php if (empty($appsList)): ?>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                </div>
                <h2 class="font-serif text-2xl text-paw-dark mb-2">No Applications Yet</h2>
                <p class="text-paw-gray">When someone applies to adopt your pets, their applications will appear here.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($appsList as $app):
                    // Applicant image
                    $appImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($app['applicant_name']);
                    if (!empty($app['applicant_image'])) {
                        if (strpos($app['applicant_image'], 'http') === 0) {
                            $appImgSrc = $app['applicant_image'];
                        } else if (file_exists('uploads/users/' . $app['applicant_image'])) {
                            $appImgSrc = 'uploads/users/' . rawurlencode($app['applicant_image']);
                        }
                    }

                    $isPending = $app['owner_response'] === 'Pending';
                    $isDeal = $app['owner_response'] === 'Deal';
                    $isNoDeal = $app['owner_response'] === 'No Deal';

                    $cardBorder = $isPending ? 'border-yellow-200' : ($isDeal ? 'border-green-200' : 'border-red-200');
                    $statusBg = $isPending ? 'bg-yellow-50 text-yellow-700' : ($isDeal ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600');
                    $statusLabel = $isPending ? 'Awaiting Your Response' : ($isDeal ? '✅ Deal' : '❌ No Deal');
                    ?>
                    <div
                        class="bg-white rounded-3xl shadow-sm border <?php echo $cardBorder; ?> overflow-hidden transition-all hover:shadow-md">
                        <div class="flex flex-col md:flex-row">
                            <!-- Pet Thumbnail -->
                            <div class="md:w-48 h-48 md:h-auto flex-shrink-0 relative">
                                <img src="uploads/pets/<?php echo rawurlencode($app['pet_image']); ?>"
                                    alt="<?php echo htmlspecialchars($app['pet_name']); ?>" class="w-full h-full object-cover">
                                <div class="absolute bottom-3 left-3">
                                    <span
                                        class="px-3 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-bold uppercase tracking-widest text-paw-dark shadow-sm">
                                        <?php echo htmlspecialchars($app['pet_name']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Application Content -->
                            <div class="flex-1 p-6 md:p-8">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                                    <!-- Applicant Info -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 flex-shrink-0">
                                            <img src="<?php echo $appImgSrc; ?>" class="w-full h-full object-cover"
                                                alt="<?php echo htmlspecialchars($app['applicant_name']); ?>">
                                        </div>
                                        <div>
                                            <p class="font-bold text-paw-dark">
                                                <?php echo htmlspecialchars($app['applicant_name']); ?>
                                            </p>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                                                    <?php echo ucfirst($app['applicant_role']); ?>
                                                </span>
                                                <span class="text-xs text-paw-gray">
                                                    <?php echo date('M d, Y', strtotime($app['application_date'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <span
                                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest <?php echo $statusBg; ?> self-start">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </div>

                                <!-- Applicant Message -->
                                <div class="bg-gray-50 rounded-2xl p-4 mb-4 border border-gray-100">
                                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                        <i data-lucide="message-square" class="w-3 h-3 inline"></i> Message from Applicant
                                    </p>
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        <?php echo nl2br(htmlspecialchars($app['message'] ?? 'No message provided.')); ?>
                                    </p>
                                </div>

                                <!-- Contact Info -->
                                <div class="flex flex-wrap gap-3 mb-4">
                                    <?php if (!empty($app['applicant_email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($app['applicant_email']); ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100 transition-colors">
                                            <i data-lucide="mail" class="w-3 h-3"></i>
                                            <?php echo htmlspecialchars($app['applicant_email']); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($app['applicant_phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($app['applicant_phone']); ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-xs font-medium hover:bg-green-100 transition-colors">
                                            <i data-lucide="phone" class="w-3 h-3"></i>
                                            <?php echo htmlspecialchars($app['applicant_phone']); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Owner Notes (if already responded) -->
                                <?php if (!$isPending && !empty($app['owner_notes'])): ?>
                                    <div
                                        class="<?php echo $isDeal ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'; ?> rounded-2xl p-4 border">
                                        <p
                                            class="text-xs font-bold uppercase tracking-widest <?php echo $isDeal ? 'text-green-600' : 'text-red-500'; ?> mb-1">
                                            Your Response</p>
                                        <p class="text-sm <?php echo $isDeal ? 'text-green-700' : 'text-red-600'; ?>">
                                            <?php echo nl2br(htmlspecialchars($app['owner_notes'])); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons (only for pending) -->
                                <?php if ($isPending): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="flex flex-col sm:flex-row gap-3" id="actionArea_<?php echo $app['id']; ?>">
                                            <!-- Deal Button -->
                                            <button onclick="showDealModal(<?php echo $app['id']; ?>, 'Deal')"
                                                class="flex-1 py-3 bg-green-500 text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-green-600 transition-colors shadow-lg shadow-green-500/20 flex items-center justify-center gap-2 cursor-pointer">
                                                <i data-lucide="handshake" class="w-4 h-4"></i> Deal
                                            </button>
                                            <!-- No Deal Button -->
                                            <button onclick="showDealModal(<?php echo $app['id']; ?>, 'No Deal')"
                                                class="flex-1 py-3 bg-red-500 text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20 flex items-center justify-center gap-2 cursor-pointer">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i> No Deal
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal Overlay -->
<div id="dealModal"
    class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center opacity-0 invisible transition-all duration-300 px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 transform scale-95 transition-transform duration-300"
        id="dealModalContent">
        <div class="text-center mb-6">
            <div id="modalIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
            <h3 class="font-serif text-3xl text-paw-dark" id="modalTitle"></h3>
            <p class="text-paw-gray text-sm mt-2" id="modalSubtitle"></p>
        </div>

        <form method="POST" id="dealForm">
            <input type="hidden" name="app_id" id="modalAppId">
            <input type="hidden" name="app_action" id="modalAction">

            <div class="mb-6">
                <label class="block text-sm uppercase tracking-widest font-semibold mb-2 text-paw-dark">Add a note
                    (optional)</label>
                <textarea name="owner_notes" rows="3" placeholder="Write a message to the applicant..."
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent focus:ring-2 focus:ring-paw-accent/10 transition-all resize-none bg-gray-50"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeDealModal()"
                    class="flex-1 py-3 border border-gray-200 text-paw-gray rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-gray-50 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn"
                    class="flex-1 py-3 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors cursor-pointer">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showDealModal(appId, action) {
        const modal = document.getElementById('dealModal');
        const content = document.getElementById('dealModalContent');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const subtitle = document.getElementById('modalSubtitle');
        const submitBtn = document.getElementById('modalSubmitBtn');

        document.getElementById('modalAppId').value = appId;
        document.getElementById('modalAction').value = action;

        if (action === 'Deal') {
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-green-100';
            icon.innerHTML = '<svg class="w-8 h-8 text-green-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/></svg>';
            title.textContent = "It's a Deal! 🎉";
            subtitle.textContent = "You're accepting this adoption application. The pet will be marked as adopted.";
            submitBtn.className = 'flex-1 py-3 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors cursor-pointer bg-green-500 hover:bg-green-600';
        } else {
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100';
            icon.innerHTML = '<svg class="w-8 h-8 text-red-600" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>';
            title.textContent = "No Deal";
            subtitle.textContent = "You're declining this adoption application. The applicant will be notified.";
            submitBtn.className = 'flex-1 py-3 text-white rounded-xl text-sm font-bold uppercase tracking-widest transition-colors cursor-pointer bg-red-500 hover:bg-red-600';
        }

        modal.classList.remove('opacity-0', 'invisible');
        modal.classList.add('opacity-100', 'visible');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function closeDealModal() {
        const modal = document.getElementById('dealModal');
        const content = document.getElementById('dealModalContent');
        modal.classList.add('opacity-0', 'invisible');
        modal.classList.remove('opacity-100', 'visible');
        content.classList.add('scale-95');
        content.classList.remove('scale-100');
    }

    // Close modal on backdrop click
    document.getElementById('dealModal').addEventListener('click', function (e) {
        if (e.target === this) closeDealModal();
    });
</script>

<?php include 'includes/footer.php'; ?>