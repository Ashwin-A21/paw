<?php
session_start();
include 'config.php';

// Handle Comment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $commentText = trim($_POST['comment_text'] ?? '');
    $rescueId = (int) ($_POST['entity_id'] ?? 0);
    if (!empty($commentText) && $rescueId > 0) {
        $stmt = $conn->prepare("INSERT INTO comments (entity_type, entity_id, user_id, comment) VALUES ('rescue', ?, ?, ?)");
        $stmt->bind_param("iis", $rescueId, $_SESSION['user_id'], $commentText);
        $stmt->execute();
        $stmt->close();
        header("Location: rescue-details.php?id=$rescueId#comments");
        exit();
    }
}

// Get Rescue ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: rescue.php");
    exit();
}

$rescue_id = (int) $_GET['id'];

// Fetch rescue report
$sql = "SELECT r.*, u.username as reporter_username, u.profile_image as reporter_image, 
        a.username as assignee_username, a.profile_image as assignee_image
        FROM rescue_reports r 
        LEFT JOIN users u ON r.reporter_id = u.id 
        LEFT JOIN users a ON r.assigned_to = a.id
        WHERE r.id = $rescue_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Rescue report not found.";
    exit();
}

$rescue = $result->fetch_assoc();

// Fetch current user
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uResult = $conn->query("SELECT * FROM users WHERE id=$uid");
    if ($uResult && $uResult->num_rows > 0) {
        $currentUser = $uResult->fetch_assoc();
    }
}

$basePath = '';
include 'includes/header.php';

$statusColors = [
    'Reported' => 'bg-blue-50 text-blue-600 border-blue-200',
    'Assigned' => 'bg-purple-50 text-purple-600 border-purple-200',
    'In Progress' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
    'Rescued' => 'bg-green-50 text-green-600 border-green-200',
    'Closed' => 'bg-gray-50 text-gray-500 border-gray-200',
];
$urgencyColors = [
    'Low' => 'bg-green-100 text-green-700',
    'Medium' => 'bg-yellow-100 text-yellow-700',
    'High' => 'bg-orange-100 text-orange-700',
    'Critical' => 'bg-red-100 text-red-700',
];
$urgencyIcons = [
    'Low' => 'shield',
    'Medium' => 'alert-triangle',
    'High' => 'alert-circle',
    'Critical' => 'siren',
];
$statusClass = $statusColors[$rescue['status']] ?? 'bg-gray-50 text-gray-500 border-gray-200';
$urgencyClass = $urgencyColors[$rescue['urgency']] ?? 'bg-gray-100 text-gray-700';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <div class="mb-8 flex justify-between items-center">
            <a href="javascript:history.back()"
                class="text-paw-gray hover:text-paw-alert flex items-center gap-2 text-sm uppercase tracking-widest font-bold transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
            </a>
            <span class="text-xs text-gray-400">Report #
                <?php echo $rescue['id']; ?>
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header Card -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8">
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span
                            class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border <?php echo $statusClass; ?>">
                            <?php echo $rescue['status']; ?>
                        </span>
                        <span
                            class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest <?php echo $urgencyClass; ?>">
                            <?php echo $rescue['urgency']; ?> Urgency
                        </span>
                    </div>

                    <h1 class="font-serif text-4xl text-paw-dark mb-4">
                        <i data-lucide="map-pin" class="w-8 h-8 inline text-paw-alert"></i>
                        <?php echo htmlspecialchars($rescue['location']); ?>
                    </h1>

                    <p class="text-gray-600 leading-relaxed text-lg mb-6">
                        <?php echo nl2br(htmlspecialchars($rescue['description'])); ?>
                    </p>

                    <!-- Status Timeline -->
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Progress</p>
                        <div class="flex items-center gap-3 flex-wrap">
                            <?php
                            $stages = ['Reported', 'Assigned', 'In Progress', 'Rescued', 'Closed'];
                            $currentIdx = array_search($rescue['status'], $stages);
                            foreach ($stages as $idx => $stage):
                                $done = $idx <= $currentIdx;
                                ?>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-3 h-3 rounded-full <?php echo $done ? 'bg-green-400' : 'bg-gray-200'; ?>">
                                    </div>
                                    <span
                                        class="text-xs uppercase tracking-widest <?php echo $done ? 'text-green-600 font-bold' : 'text-gray-300'; ?>">
                                        <?php echo $stage; ?>
                                    </span>
                                </div>
                                <?php if ($idx < count($stages) - 1): ?>
                                    <div
                                        class="flex-1 h-px max-w-[40px] <?php echo $done && $idx < $currentIdx ? 'bg-green-300' : 'bg-gray-100'; ?>">
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Image -->
                <?php if (!empty($rescue['image']) && file_exists('uploads/rescues/' . $rescue['image'])): ?>
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                        <img src="uploads/rescues/<?php echo rawurlencode($rescue['image']); ?>"
                            class="w-full h-80 object-cover" alt="Rescue Image">
                    </div>
                <?php endif; ?>

                <!-- Map -->
                <?php if ($rescue['latitude'] && $rescue['longitude']): ?>
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i data-lucide="map" class="w-5 h-5 text-paw-alert"></i>
                            <span class="text-sm font-bold uppercase tracking-widest text-gray-400">Exact Location</span>
                        </div>
                        <div id="map" style="height: 300px; border-radius: 0.75rem; z-index: 10;"></div>
                        <a href="https://www.google.com/maps?q=<?php echo $rescue['latitude']; ?>,<?php echo $rescue['longitude']; ?>"
                            target="_blank"
                            class="inline-flex items-center gap-2 mt-4 text-sm text-paw-alert hover:underline font-bold uppercase tracking-widest">
                            <i data-lucide="external-link" class="w-3 h-3"></i> Open in Google Maps
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Reporter Info -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Reporter</p>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100">
                            <img src="<?php
                            $rImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($rescue['reporter_name'] ?? 'Anonymous');
                            if (!empty($rescue['reporter_image'])) {
                                if (strpos($rescue['reporter_image'], 'http') === 0) {
                                    $rImgSrc = $rescue['reporter_image'];
                                } else if (file_exists('uploads/users/' . $rescue['reporter_image'])) {
                                    $rImgSrc = 'uploads/users/' . rawurlencode($rescue['reporter_image']);
                                }
                            }
                            echo $rImgSrc;
                            ?>" class="w-full h-full object-cover" alt="Reporter">
                        </div>
                        <div>
                            <p class="font-bold text-paw-dark">
                                <?php echo htmlspecialchars($rescue['reporter_username'] ?? $rescue['reporter_name'] ?? 'Anonymous'); ?>
                            </p>
                            <?php if (!empty($rescue['contact_phone'])): ?>
                                <p class="text-xs text-paw-gray flex items-center gap-1">
                                    <i data-lucide="phone" class="w-3 h-3"></i>
                                    <?php echo htmlspecialchars($rescue['contact_phone']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Time Info -->
                <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Timeline</p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <i data-lucide="clock" class="w-4 h-4 text-blue-500"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Reported</p>
                                <p class="text-sm font-bold text-paw-dark">
                                    <?php echo date('M d, Y \a\t h:i A', strtotime($rescue['reported_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <i data-lucide="refresh-cw" class="w-4 h-4 text-green-500"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Last Updated</p>
                                <p class="text-sm font-bold text-paw-dark">
                                    <?php echo date('M d, Y \a\t h:i A', strtotime($rescue['updated_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Rescuer -->
                <?php if (!empty($rescue['assignee_username'])): ?>
                    <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Assigned Rescuer</p>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-green-200">
                                <img src="<?php
                                $aImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($rescue['assignee_username']);
                                if (!empty($rescue['assignee_image'])) {
                                    if (strpos($rescue['assignee_image'], 'http') === 0) {
                                        $aImgSrc = $rescue['assignee_image'];
                                    } else if (file_exists('uploads/users/' . $rescue['assignee_image'])) {
                                        $aImgSrc = 'uploads/users/' . rawurlencode($rescue['assignee_image']);
                                    }
                                }
                                echo $aImgSrc;
                                ?>" class="w-full h-full object-cover" alt="Rescuer">
                            </div>
                            <div>
                                <p class="font-bold text-paw-dark">
                                    <?php echo htmlspecialchars($rescue['assignee_username']); ?>
                                </p>
                                <p class="text-xs text-green-600 font-bold uppercase tracking-widest">Rescuer</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Share -->
                <button onclick="shareRescue()"
                    class="w-full py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold uppercase tracking-widest text-paw-gray hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 relative">
                    <i data-lucide="share-2" class="w-4 h-4"></i> Share Report
                    <span id="shareTooltip"
                        class="absolute -top-10 left-1/2 -translate-x-1/2 bg-paw-dark text-white text-xs px-3 py-1 rounded-lg opacity-0 transition-opacity pointer-events-none whitespace-nowrap">Link
                        copied!</span>
                </button>
            </div>
        </div>

        <!-- Comments / Discussion Section -->
        <div class="mt-12" id="comments">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-8 md:p-12">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-paw-alert/10 rounded-xl flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-5 h-5 text-paw-alert"></i>
                    </div>
                    <h2 class="font-serif text-3xl text-paw-dark">Discussion</h2>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Comment Form -->
                    <form method="POST" class="mb-8">
                        <input type="hidden" name="add_comment" value="1">
                        <input type="hidden" name="entity_id" value="<?php echo $rescue['id']; ?>">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 border-paw-alert/30">
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
                                <textarea name="comment_text" required rows="3"
                                    placeholder="Add an update, comment, or note..."
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-alert focus:ring-2 focus:ring-paw-alert/10 transition-all resize-none bg-gray-50"></textarea>
                                <div class="flex justify-end mt-2">
                                    <button type="submit"
                                        class="px-6 py-2.5 bg-paw-alert text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-alert/20">
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
                    $commentsQuery = $conn->query("SELECT c.*, u.username, u.profile_image, u.role FROM comments c JOIN users u ON c.user_id = u.id WHERE c.entity_type = 'rescue' AND c.entity_id = '$rescue_id' ORDER BY c.created_at DESC");
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
                            $bubbleClass = $isMe ? 'bg-paw-alert/10 border-paw-alert/20' : 'bg-gray-50 border-gray-100';
                            ?>
                            <div class="flex gap-4 group <?php echo $alignClass; ?>">
                                <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border-2 border-gray-100">
                                    <img src="<?php echo $cImgSrc; ?>" class="w-full h-full object-cover"
                                        alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="<?php echo $bubbleClass; ?> rounded-2xl p-4 border group-hover:border-paw-alert/20 transition-colors inline-block text-left max-w-[80%]">
                                        <div class="flex items-center gap-2 mb-2 <?php echo $isMe ? 'justify-end' : ''; ?>">
                                            <span class="font-bold text-sm text-paw-dark">
                                                <?php echo htmlspecialchars($comment['username']); ?>
                                            </span>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-paw-alert/10 text-paw-alert">
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
                            <p class="text-paw-gray text-sm">No comments yet. Be the first to post an update!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>

<?php if ($rescue['latitude'] && $rescue['longitude']): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([<?php echo $rescue['latitude']; ?>, <?php echo $rescue['longitude']; ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        L.marker([<?php echo $rescue['latitude']; ?>, <?php echo $rescue['longitude']; ?>]).addTo(map)
            .bindPopup('<?php echo addslashes(htmlspecialchars($rescue['location'])); ?>').openPopup();
    </script>
<?php endif; ?>

<script>
    function shareRescue() {
        const url = window.location.href;
        const title = 'Rescue Report - <?php echo addslashes(htmlspecialchars($rescue['location'])); ?>';
        const text = 'Check out this rescue report on Paw Pal: <?php echo addslashes(htmlspecialchars($rescue['location'])); ?>';

        if (navigator.share) {
            navigator.share({ title, text, url }).catch(() => { });
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