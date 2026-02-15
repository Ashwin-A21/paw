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
        header("Location: pet-details.php?id=$petId#comments");
        exit();
    }
}

// Get Pet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: adopt.php");
    exit();
}

$pet_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch Pet Details
$sql = "SELECT * FROM pets WHERE id = '$pet_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Pet not found.";
    exit();
}

$pet = $result->fetch_assoc();

// Handle Status Update (Owner only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet['added_by']) {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    $conn->query("UPDATE pets SET status='$newStatus' WHERE id='$pet_id'");
    header("Location: pet-details.php?id=$pet_id");
    exit();
}

// Navigation: Previous & Next
$prevSql = "SELECT id FROM pets WHERE id < '$pet_id' AND status='Available' ORDER BY id DESC LIMIT 1";
$nextSql = "SELECT id FROM pets WHERE id > '$pet_id' AND status='Available' ORDER BY id ASC LIMIT 1";

$prevPet = $conn->query($prevSql)->fetch_assoc();
$nextPet = $conn->query($nextSql)->fetch_assoc();

$basePath = '';
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

                <div class="mt-auto pt-8 border-t border-gray-100 flex gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>

                        <?php if ($_SESSION['user_id'] == $pet['added_by']): ?>
                            <!-- Owner View: Change Status -->
                            <form method="POST" class="flex-1 flex gap-2">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent bg-white">
                                    <option value="Available" <?php echo $pet['status'] == 'Available' ? 'selected' : ''; ?>>
                                        Available</option>
                                    <option value="Adopted" <?php echo $pet['status'] == 'Adopted' ? 'selected' : ''; ?>>Adopted
                                    </option>
                                    <option value="Pending" <?php echo $pet['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                                    </option>
                                </select>
                                <button type="submit"
                                    class="px-6 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors">
                                    Update
                                </button>
                            </form>
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
                $commentsQuery = $conn->query("SELECT c.*, u.username, u.profile_image, u.role FROM comments c JOIN users u ON c.user_id = u.id WHERE c.entity_type = 'pet' AND c.entity_id = '{$pet['id']}' ORDER BY c.created_at DESC");
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