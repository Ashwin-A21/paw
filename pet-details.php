<?php
session_start();
include 'config.php';

// Handle Comment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $commentText = trim($_POST['comment_text'] ?? '');
    $petId = (int) ($_POST['entity_id'] ?? 0);
    if (!empty($commentText) && $petId > 0) {
        $stmt = $conn->prepare("INSERT INTO comments (entity_type, entity_id, user_id, comment) VALUES ('pet', ?, ?, ?)");
        $stmt->bind_param("iis", $petId, $_SESSION['user_id'], $commentText);
        $stmt->execute();
        $stmt->close();

        // Notify pet owner about the comment
        include_once 'includes/notify.php';
        $pStmt = $conn->prepare("SELECT added_by, name FROM pets WHERE id = ?");
        $pStmt->bind_param("i", $petId);
        $pStmt->execute();
        $pRow = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();
        if ($pRow && $pRow['added_by'] != $_SESSION['user_id']) {
            createNotification(
                $conn,
                $pRow['added_by'],
                'comment',
                $_SESSION['username'] . ' commented on your pet "' . $pRow['name'] . '"',
                'pet-details.php?id=' . $petId . '#comments'
            );
        }

        header("Location: pet-details.php?id=$petId#comments");
        exit();
    }
}

// Get Pet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: adopt.php");
    exit();
}

$pet_id = (int) $_GET['id'];

// Fetch Pet Details with Poster Info
$sql = "SELECT p.*, 
        u.username as poster_username, u.email as poster_email, u.phone as poster_phone,
        u.profile_image as poster_image, u.role as poster_role, u.created_at as poster_since,
        u.is_verified as poster_verified, u.lives_saved as poster_lives_saved
        FROM pets p
        LEFT JOIN users u ON p.added_by = u.id
        WHERE p.id = $pet_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Pet not found.";
    exit();
}

$pet = $result->fetch_assoc();

// Fetch current user for comments
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $uStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->bind_param("i", $uid);
    $uStmt->execute();
    $uResult = $uStmt->get_result();
    if ($uResult && $uResult->num_rows > 0) {
        $currentUser = $uResult->fetch_assoc();
    }
    $uStmt->close();
}

// Handle Status Update (Owner only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet['added_by']) {
    $allowedStatuses = ['Available', 'Adopted', 'Pending'];
    $newStatus = $_POST['status'];
    if (in_array($newStatus, $allowedStatuses)) {
        $updStmt = $conn->prepare("UPDATE pets SET status = ? WHERE id = ?");
        $updStmt->bind_param("si", $newStatus, $pet_id);
        $updStmt->execute();
        $updStmt->close();

        // If the pet was marked as Adopted, notify commenters
        if ($newStatus === 'Adopted') {
            include_once 'includes/notify.php';
            $commentersStmt = $conn->prepare("SELECT DISTINCT user_id FROM comments WHERE entity_id = ? AND entity_type = 'pet' AND user_id != ?");
            $commentersStmt->bind_param("ii", $pet_id, $pet['added_by']);
            $commentersStmt->execute();
            $commentersResult = $commentersStmt->get_result();
            while ($commenter = $commentersResult->fetch_assoc()) {
                createNotification(
                    $conn,
                    $commenter['user_id'],
                    'pet_adopted_commenter',
                    '🐾 Good news! "' . $pet['name'] . '" was just adopted. View their info and your old comments.',
                    'pet-details.php?id=' . $pet_id
                );
            }
            $commentersStmt->close();
        }
    }
    header("Location: pet-details.php?id=$pet_id");
    exit();
}

// Navigation: Previous & Next
$prevStmt = $conn->prepare("SELECT id FROM pets WHERE id < ? AND status='Available' ORDER BY id DESC LIMIT 1");
$prevStmt->bind_param("i", $pet_id);
$prevStmt->execute();
$prevPet = $prevStmt->get_result()->fetch_assoc();
$prevStmt->close();

$nextStmt = $conn->prepare("SELECT id FROM pets WHERE id > ? AND status='Available' ORDER BY id ASC LIMIT 1");
$nextStmt->bind_param("i", $pet_id);
$nextStmt->execute();
$nextPet = $nextStmt->get_result()->fetch_assoc();
$nextStmt->close();

$basePath = '';
$pageTitle = htmlspecialchars($pet['name']) . ' - Adopt on Paw Pal';
$ogImage = 'uploads/pets/' . rawurlencode($pet['image']);
$ogDescription = htmlspecialchars(substr($pet['description'], 0, 160));
include 'includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb & Back -->
        <div class="mb-8 flex justify-between items-center">
            <a href="adopt.php"
                class="text-paw-gray hover:text-paw-accent flex items-center gap-2 text-sm uppercase tracking-widest font-bold transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Adoptions
            </a>

            <div class="flex gap-4">
                <?php if ($prevPet): ?>
                    <a href="pet-details.php?id=<?php echo $prevPet['id']; ?>"
                        class="flex items-center gap-2 text-paw-dark hover:text-paw-accent font-bold uppercase text-xs tracking-widest transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> Previous Pet
                    </a>
                <?php else: ?>
                    <span
                        class="text-gray-300 font-bold uppercase text-xs tracking-widest flex items-center gap-2 cursor-not-allowed">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> Previous Pet
                    </span>
                <?php endif; ?>

                <?php if ($nextPet): ?>
                    <a href="pet-details.php?id=<?php echo $nextPet['id']; ?>"
                        class="flex items-center gap-2 text-paw-dark hover:text-paw-accent font-bold uppercase text-xs tracking-widest transition-colors">
                        Next Pet <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                <?php else: ?>
                    <span
                        class="text-gray-300 font-bold uppercase text-xs tracking-widest flex items-center gap-2 cursor-not-allowed">
                        Next Pet <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content (2 cols) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">

                    <!-- Image Section -->
                    <div class="md:w-1/2 relative h-96 md:h-auto bg-gray-100">
                        <img src="uploads/pets/<?php echo rawurlencode($pet['image']); ?>"
                            alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-full object-cover">

                        <div class="absolute top-6 left-6">
                            <span
                                class="px-4 py-2 bg-white/90 backdrop-blur rounded-full text-xs font-bold uppercase tracking-widest text-paw-dark shadow-sm">
                                <?php echo $pet['status']; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                        <div class="mb-2 flex items-center gap-3">
                            <span class="text-paw-accent font-bold uppercase tracking-widest text-sm">
                                <?php echo htmlspecialchars($pet['breed']); ?>
                            </span>
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span class="text-gray-500 font-medium text-sm">
                                <?php echo htmlspecialchars($pet['type']); ?>
                            </span>
                        </div>

                        <h1 class="font-serif text-5xl text-paw-dark mb-6">
                            <?php echo htmlspecialchars($pet['name']); ?>
                        </h1>

                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center">
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Age</p>
                                <p class="text-paw-dark font-serif text-xl">
                                    <?php echo htmlspecialchars($pet['age']); ?>
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center">
                                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Gender</p>
                                <p class="text-paw-dark font-serif text-xl flex items-center justify-center gap-2">
                                    <?php echo htmlspecialchars($pet['gender']); ?>
                                    <?php if ($pet['gender'] == 'Male'): ?>
                                        <i data-lucide="mars" class="w-4 h-4 text-blue-400"></i>
                                    <?php else: ?>
                                        <i data-lucide="venus" class="w-4 h-4 text-pink-400"></i>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="prose prose-stone mb-8 text-gray-600 leading-relaxed">
                            <h3 class="font-serif text-2xl text-paw-dark mb-4">About
                                <?php echo htmlspecialchars($pet['name']); ?>
                            </h3>
                            <p>
                                <?php echo nl2br(htmlspecialchars($pet['description'])); ?>
                            </p>
                        </div>

                        <div class="mt-auto pt-8 border-t border-gray-100 flex flex-wrap gap-4">
                            <?php if (isset($_SESSION['user_id'])): ?>

                                <?php if ($_SESSION['user_id'] == $pet['added_by']): ?>
                                    <!-- Owner View: Change Status -->
                                    <form method="POST" class="flex-1 flex flex-wrap sm:flex-nowrap gap-2 min-w-[200px]">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status"
                                            class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent bg-white min-w-[120px]">
                                            <option value="Available" <?php echo $pet['status'] == 'Available' ? 'selected' : ''; ?>>
                                                Available</option>
                                            <option value="Adopted" <?php echo $pet['status'] == 'Adopted' ? 'selected' : ''; ?>>Adopted
                                            </option>
                                            <option value="Pending" <?php echo $pet['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                                            </option>
                                        </select>
                                        <button type="submit"
                                            class="px-6 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors flex-shrink-0">
                                            Update
                                        </button>
                                    </form>
                                    <?php
                                    // Count pending applications for this pet
                                    $appCountStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM adoption_applications WHERE pet_id = ? AND owner_response = 'Pending'");
                                    if ($appCountStmt) {
                                        $appCountStmt->bind_param("i", $pet_id);
                                        $appCountStmt->execute();
                                        $appCountRow = $appCountStmt->get_result()->fetch_assoc();
                                        $pendingAppCount = $appCountRow['cnt'] ?? 0;
                                        $appCountStmt->close();
                                    } else {
                                        $pendingAppCount = 0;
                                    }
                                    ?>
                                    <a href="manage-applications.php"
                                        class="relative px-6 py-3 border-2 border-paw-accent text-paw-accent rounded-xl font-bold uppercase tracking-widest hover:bg-paw-accent hover:text-white transition-colors flex items-center gap-2 text-sm whitespace-nowrap"
                                        title="View adoption applications">
                                        <i data-lucide="inbox" class="w-4 h-4"></i> Applications
                                        <?php if ($pendingAppCount > 0): ?>
                                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                                                <?php echo $pendingAppCount; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                <?php elseif ($pet['status'] == 'Available'): ?>
                                    <a href="adopt-apply.php?pet=<?php echo $pet['id']; ?>"
                                        class="flex-1 py-4 bg-paw-accent text-white rounded-xl text-center font-bold uppercase tracking-widest border border-transparent hover:bg-paw-dark transition-all shadow-lg shadow-paw-accent/20">
                                        Adopt Me
                                    </a>
                                <?php else: ?>
                                    <button disabled
                                        class="flex-1 py-4 bg-gray-200 text-gray-400 rounded-xl text-center font-bold uppercase tracking-widest cursor-not-allowed">
                                        Not Available
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php?redirect=pet-details.php?id=<?php echo $pet['id']; ?>"
                                    class="flex-1 py-4 bg-paw-dark text-white rounded-xl text-center font-bold uppercase tracking-widest hover:bg-paw-accent transition-all shadow-lg">
                                    Login to Adopt
                                </a>
                            <?php endif; ?>

                            <button onclick="sharePet()" id="shareBtn"
                                class="px-6 py-4 border border-gray-200 rounded-xl text-paw-gray hover:bg-gray-50 transition-colors relative"
                                title="Share">
                                <i data-lucide="share-2" class="w-5 h-5"></i>
                                <span id="shareTooltip"
                                    class="absolute -top-10 left-1/2 -translate-x-1/2 bg-paw-dark text-white text-xs px-3 py-1 rounded-lg opacity-0 transition-opacity pointer-events-none whitespace-nowrap">Link
                                    copied!</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Posted By Card -->
                <?php if (!empty($pet['poster_username'])): ?>
                    <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Posted By</p>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="relative w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 flex-shrink-0">
                                <img src="<?php
                                $pImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($pet['poster_username']);
                                if (!empty($pet['poster_image'])) {
                                    if (strpos($pet['poster_image'], 'http') === 0) {
                                        $pImgSrc = $pet['poster_image'];
                                    } else if (file_exists('uploads/users/' . $pet['poster_image'])) {
                                        $pImgSrc = 'uploads/users/' . rawurlencode($pet['poster_image']);
                                    }
                                }
                                echo $pImgSrc;
                                ?>" class="w-full h-full object-cover" alt="Poster">
                                <?php if ($pet['poster_verified']): ?>
                                    <div class="absolute -bottom-0.5 -right-0.5 bg-blue-500 text-white p-0.5 rounded-full border-2 border-white">
                                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-bold text-paw-dark">
                                    <?php echo htmlspecialchars($pet['poster_username']); ?>
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                                        <?php echo ucfirst($pet['poster_role']); ?>
                                    </span>
                                    <?php if (($pet['poster_lives_saved'] ?? 0) > 0): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-green-100 text-green-700 flex items-center gap-0.5">
                                            <i data-lucide="heart" class="w-2.5 h-2.5 fill-current"></i>
                                            <?php echo $pet['poster_lives_saved']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php if (!empty($pet['poster_phone'])): ?>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="p-2 bg-green-50 rounded-lg">
                                        <i data-lucide="phone" class="w-4 h-4 text-green-600"></i>
                                    </div>
                                    <a href="tel:<?php echo htmlspecialchars($pet['poster_phone']); ?>" class="text-paw-dark hover:text-paw-accent transition-colors">
                                        <?php echo htmlspecialchars($pet['poster_phone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($pet['poster_email'])): ?>
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="p-2 bg-blue-50 rounded-lg">
                                        <i data-lucide="mail" class="w-4 h-4 text-blue-500"></i>
                                    </div>
                                    <a href="mailto:<?php echo htmlspecialchars($pet['poster_email']); ?>" class="text-paw-dark hover:text-paw-accent transition-colors truncate">
                                        <?php echo htmlspecialchars($pet['poster_email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center gap-3 text-sm">
                                <div class="p-2 bg-purple-50 rounded-lg">
                                    <i data-lucide="calendar" class="w-4 h-4 text-purple-500"></i>
                                </div>
                                <span class="text-paw-gray">
                                    Member since <?php echo date('M Y', strtotime($pet['poster_since'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Info Card -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Quick Info</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-paw-accent/10 rounded-lg">
                                <i data-lucide="paw-print" class="w-4 h-4 text-paw-accent"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Type</p>
                                <p class="text-sm font-bold text-paw-dark"><?php echo htmlspecialchars(ucfirst($pet['type'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <i data-lucide="clock" class="w-4 h-4 text-blue-500"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Listed On</p>
                                <p class="text-sm font-bold text-paw-dark"><?php echo date('M d, Y', strtotime($pet['added_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Share Button -->
                <button onclick="sharePet()"
                    class="w-full py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold uppercase tracking-widest text-paw-gray hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 relative">
                    <i data-lucide="share-2" class="w-4 h-4"></i> Share Pet
                    <span id="shareTooltip2"
                        class="absolute -top-10 left-1/2 -translate-x-1/2 bg-paw-dark text-white text-xs px-3 py-1 rounded-lg opacity-0 transition-opacity pointer-events-none whitespace-nowrap">Link
                        copied!</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Medical Records -->
    <?php
    $medStmt = $conn->prepare("SELECT * FROM pet_medical_records WHERE pet_id = ? ORDER BY date DESC");
    if ($medStmt) {
        $medStmt->bind_param("i", $pet['id']);
        $medStmt->execute();
        $medRecords = $medStmt->get_result();
        $medStmt->close();
    }
    ?>
    <?php if (isset($medRecords) && $medRecords->num_rows > 0): ?>
        <div class="max-w-6xl mx-auto mt-8">
            <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i data-lucide="stethoscope" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <h2 class="font-serif text-2xl text-paw-dark">Medical Records</h2>
                </div>
                <div class="space-y-4">
                    <?php while ($med = $medRecords->fetch_assoc()):
                        $typeIcons = [
                            'Vaccination' => ['syringe', 'bg-blue-50 text-blue-600'],
                            'Checkup' => ['stethoscope', 'bg-green-50 text-green-600'],
                            'Surgery' => ['scissors', 'bg-red-50 text-red-600'],
                            'Treatment' => ['pill', 'bg-purple-50 text-purple-600'],
                            'Other' => ['file-text', 'bg-gray-50 text-gray-600'],
                        ];
                        $icon = $typeIcons[$med['record_type']] ?? $typeIcons['Other'];
                    ?>
                        <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-2xl">
                            <div class="p-2 rounded-xl <?php echo $icon[1]; ?> flex-shrink-0">
                                <i data-lucide="<?php echo $icon[0]; ?>" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-sm text-paw-dark"><?php echo htmlspecialchars($med['record_type']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($med['date'])); ?></p>
                                </div>
                                <p class="text-sm text-paw-gray mt-1"><?php echo htmlspecialchars($med['description']); ?></p>
                                <?php if (!empty($med['vet_name'])): ?>
                                    <p class="text-xs text-gray-400 mt-1">Dr. <?php echo htmlspecialchars($med['vet_name']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Comments / Log Notes Section -->
    <div class="max-w-6xl mx-auto mt-12" id="comments">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8 md:p-12">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-paw-accent/10 rounded-xl flex items-center justify-center">
                    <i data-lucide="message-circle" class="w-5 h-5 text-paw-accent"></i>
                </div>
                <h2 class="font-serif text-3xl text-paw-dark">Discussion</h2>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Comment Form -->
                <form method="POST" class="mb-8">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="add_comment" value="1">
                    <input type="hidden" name="entity_id" value="<?php echo $pet['id']; ?>">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 border-paw-accent/30">
                            <img src="<?php
                            $uname = $currentUser['username'] ?? 'User';
                            $commentImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($uname);
                            if (!empty($currentUser['profile_image'])) {
                                if (strpos($currentUser['profile_image'], 'http') === 0) {
                                    $commentImgSrc = $currentUser['profile_image'];
                                } else if (file_exists('uploads/users/' . $currentUser['profile_image'])) {
                                    $commentImgSrc = 'uploads/users/' . rawurlencode($currentUser['profile_image']);
                                }
                            }
                            echo $commentImgSrc;
                            ?>" class="w-full h-full object-cover" alt="You">
                        </div>
                        <div class="flex-1">
                            <textarea name="comment_text" required rows="3" placeholder="Write a comment or note..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent focus:ring-2 focus:ring-paw-accent/10 transition-all resize-none bg-gray-50"></textarea>
                            <div class="flex justify-end mt-2">
                                <button type="submit"
                                    class="px-6 py-2.5 bg-paw-accent text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                    Post Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-gray-50 rounded-xl p-6 text-center mb-8 border border-gray-100">
                    <p class="text-paw-gray mb-3">Login to join the discussion</p>
                    <a href="login.php"
                        class="inline-block px-6 py-2 bg-paw-dark text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors">Login</a>
                </div>
            <?php endif; ?>

            <!-- Existing Comments -->
            <div class="space-y-6">
                <?php
                $entityType = 'pet';
                $entityId = $pet['id'];
                $cStmt = $conn->prepare("SELECT c.*, u.username, u.profile_image, u.role FROM comments c JOIN users u ON c.user_id = u.id WHERE c.entity_type = ? AND c.entity_id = ? ORDER BY c.created_at DESC");
                $cStmt->bind_param("si", $entityType, $entityId);
                $cStmt->execute();
                $commentsQuery = $cStmt->get_result();
                if ($commentsQuery && $commentsQuery->num_rows > 0):
                    while ($comment = $commentsQuery->fetch_assoc()):
                        $cImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($comment['username']);
                        if (!empty($comment['profile_image'])) {
                            if (strpos($comment['profile_image'], 'http') === 0) {
                                $cImgSrc = $comment['profile_image'];
                            } else if (file_exists('uploads/users/' . $comment['profile_image'])) {
                                $cImgSrc = 'uploads/users/' . rawurlencode($comment['profile_image']);
                            }
                        }
                        ?>
                        <?php
                        $isMe = (isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id']);
                        $alignClass = $isMe ? 'flex-row-reverse text-right' : '';
                        $bubbleClass = $isMe ? 'bg-paw-accent/10 border-paw-accent/20' : 'bg-gray-50 border-gray-100';
                        ?>
                        <div class="flex gap-4 group <?php echo $alignClass; ?>">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 border-gray-100">
                                <img src="<?php echo $cImgSrc; ?>" class="w-full h-full object-cover"
                                    alt="<?php echo htmlspecialchars($comment['username']); ?>">
                            </div>
                            <div class="flex-1">
                                <div
                                    class="<?php echo $bubbleClass; ?> rounded-2xl p-4 border group-hover:border-paw-accent/20 transition-colors inline-block text-left max-w-[80%]">
                                    <div class="flex items-center gap-2 mb-2 <?php echo $isMe ? 'justify-end' : ''; ?>">
                                        <span
                                            class="font-bold text-sm text-paw-dark"><?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                                            <?php echo ucfirst($comment['role']); ?>
                                        </span>
                                        <span class="text-xs text-paw-gray ml-auto">
                                            <?php echo date('M d, Y \a\t h:i A', strtotime($comment['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else:
                    ?>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="message-circle" class="w-8 h-8 text-gray-300"></i>
                        </div>
                        <p class="text-paw-gray text-sm">No comments yet. Be the first to share your thoughts!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    function sharePet() {
        const url = window.location.href;
        const title = '<?php echo addslashes(htmlspecialchars($pet['name'])); ?> - Adopt on Paw Pal';
        const text = 'Check out <?php echo addslashes(htmlspecialchars($pet['name'])); ?> available for adoption on Paw Pal!';

        if (navigator.share) {
            navigator.share({ title: title, text: text, url: url }).catch(() => { });
        } else {
            navigator.clipboard.writeText(url).then(() => {
                const tooltip = document.getElementById('shareTooltip');
                tooltip.style.opacity = '1';
                setTimeout(() => { tooltip.style.opacity = '0'; }, 2000);
            });
        }
    }
</script>

<?php include 'includes/footer.php'; ?>