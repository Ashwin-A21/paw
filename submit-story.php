<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=submit-story.php");
    exit();
}
include 'config.php';

$error = "";
$success = "";

// Fetch pets this user might have adopted (from applications with status 'Deal')
$userId = $_SESSION['user_id'];
$petsQuery = $conn->query("SELECT p.id, p.name FROM pets p 
    JOIN adoption_applications aa ON p.id = aa.pet_id 
    WHERE aa.user_id = $userId AND aa.owner_response = 'Deal'");
$myPets = [];
if ($petsQuery) {
    while ($row = $petsQuery->fetch_assoc()) {
        $myPets[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = "Invalid security token.";
    } else {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $story = mysqli_real_escape_string($conn, $_POST['story']);
        $petId = !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : 'NULL';

        if (empty($title) || empty($story)) {
            $error = "Please fill in all required fields.";
        } else {
            // Handle image uploads
            include_once 'includes/functions.php';
            $beforeImage = "";
            $afterImage = "";

            if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] === 0) {
                $uploadDir = 'uploads/stories/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $res = handleFileUpload($_FILES['before_image'], $uploadDir);
                if ($res && isset($res['success'])) $beforeImage = $res['filename'];
            }

            if (isset($_FILES['after_image']) && $_FILES['after_image']['error'] === 0) {
                $uploadDir = 'uploads/stories/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $res = handleFileUpload($_FILES['after_image'], $uploadDir);
                if ($res && isset($res['success'])) $afterImage = $res['filename'];
            }

            $sql = "INSERT INTO success_stories (user_id, pet_id, title, story, before_image, after_image, status) 
                    VALUES ($userId, $petId, '$title', '$story', '$beforeImage', '$afterImage', 'pending')";
            
            if ($conn->query($sql)) {
                $success = "Your story has been submitted for approval. Thank you for sharing!";
            } else {
                $error = "Error submitting story: " . $conn->error;
            }
        }
    }
}

$pageTitle = 'Share Your Success Story - Paw Pal';
include 'includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-10 text-center">
            <h1 class="font-serif text-5xl mb-4">Share Your <span class="italic text-paw-accent">Story</span></h1>
            <p class="text-paw-gray">Tell the world about your new journey with your furry friend.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden p-8 md:p-12 border border-blue-50">
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 text-green-700 p-6 rounded-xl mb-6 text-center">
                    <i data-lucide="party-popper" class="w-12 h-12 text-green-500 mx-auto mb-4"></i>
                    <h3 class="font-bold text-xl mb-2"><?php echo $success; ?></h3>
                    <a href="success-stories.php" class="inline-block mt-4 text-green-700 font-bold underline">Go back to stories</a>
                </div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php echo csrfField(); ?>
                    
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Story Title</label>
                        <input type="text" name="title" required placeholder="e.g. A New Beginning for Luna"
                            class="w-full px-6 py-4 border border-gray-200 rounded-2xl focus:outline-none focus:border-paw-accent transition-all text-lg font-serif">
                    </div>

                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Which Pet? (Optional)</label>
                        <select name="pet_id" class="w-full px-6 py-4 border border-gray-200 rounded-2xl focus:outline-none focus:border-paw-accent transition-all bg-white">
                            <option value="">Select a pet you adopted</option>
                            <?php foreach ($myPets as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-paw-gray mt-2 italic">Don't see your pet? You can still share your story!</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Before Photo</label>
                            <label class="group cursor-pointer block border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-paw-accent transition-all bg-gray-50/30">
                                <i data-lucide="image-plus" class="w-8 h-8 mx-auto mb-2 text-gray-400 group-hover:text-paw-accent"></i>
                                <span class="text-sm text-gray-500 block">Click to upload</span>
                                <input type="file" name="before_image" accept="image/*" class="hidden" onchange="previewImg(this, 'beforePreview')">
                                <img id="beforePreview" class="hidden mt-4 w-full h-40 object-cover rounded-xl shadow-sm">
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">After Photo</label>
                            <label class="group cursor-pointer block border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-paw-accent transition-all bg-gray-50/30">
                                <i data-lucide="image-plus" class="w-8 h-8 mx-auto mb-2 text-gray-400 group-hover:text-paw-accent"></i>
                                <span class="text-sm text-gray-500 block">Click to upload</span>
                                <input type="file" name="after_image" accept="image/*" class="hidden" onchange="previewImg(this, 'afterPreview')">
                                <img id="afterPreview" class="hidden mt-4 w-full h-40 object-cover rounded-xl shadow-sm">
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Your Heartwarming Story</label>
                        <textarea name="story" required rows="8" placeholder="Tell us how you met, the first few days, and how your lives have changed..."
                            class="w-full px-6 py-4 border border-gray-200 rounded-2xl focus:outline-none focus:border-paw-accent transition-all resize-none leading-relaxed"></textarea>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                            class="w-full py-4 bg-paw-dark text-white rounded-2xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-all shadow-xl shadow-paw-dark/10">
                            Submit for Approval
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    function previewImg(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(previewId);
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
