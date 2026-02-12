<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Ensure $userId is available here

    // 1. Handle Profile Image Update Only
    if (isset($_POST['action']) && $_POST['action'] === 'update_image') {
        $imageUpdate = "";
        $imageProcessed = false;

        // Default Avatar
        if (isset($_POST['default_avatar']) && !empty($_POST['default_avatar'])) {
            $defaultAvatar = mysqli_real_escape_string($conn, $_POST['default_avatar']);
            $imageUpdate = "profile_image='$defaultAvatar'";
            $imageProcessed = true;
        }

        // File Upload (Priority)
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $uploadDir = '../uploads/users/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
            $targetPath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $imageUpdate = "profile_image='$imageName'";
            }
        }

        if (!empty($imageUpdate)) {
            $updateSql = "UPDATE users SET $imageUpdate WHERE id=$userId";
            if ($conn->query($updateSql)) {
                $message = "Profile picture updated successfully!";
                // Refresh to show changes immediately
                header("Location: profile.php");
                exit();
            } else {
                $error = "Error updating image: " . $conn->error;
            }
        }
    }
    // 2. Handle Profile Info Update (Standard Form)
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_info') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);

        // Password Update Logic
        $passwordSql = "";
        if (!empty($_POST['new_password'])) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            $verify = $conn->query("SELECT password FROM users WHERE id=$userId");
            $row = $verify->fetch_assoc();

            if ($currentPassword !== $row['password']) {
                $error = "Current password is incorrect.";
            } elseif ($newPassword !== $confirmPassword) {
                $error = "New passwords do not match.";
            } else {
                $passwordSql = ", password='$newPassword'";
            }
        }

        if (empty($error)) {
            $updateSql = "UPDATE users SET username='$username', email='$email', phone='$phone', gender='$gender', dob='$dob' $passwordSql WHERE id=$userId";
            if ($conn->query($updateSql)) {
                $_SESSION['username'] = $username;
                $message = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . $conn->error;
            }
        }
    }
}

// Fetch current user data
$userResult = $conn->query("SELECT * FROM users WHERE id=$userId");
$user = $userResult->fetch_assoc();

$basePath = '../';
include '../includes/header.php';
?>

<style>
    .glass {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
    }
</style>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-10">
            <a href="index.php"
                class="text-paw-gray hover:text-paw-accent flex items-center gap-2 mb-4 text-sm uppercase tracking-widest"><i
                    data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard</a>
            <h1 class="font-serif text-5xl mb-2">My Profile</h1>
            <p class="text-paw-gray">Manage your account details and security.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden p-8">
            <?php if ($message): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- User Header -->
            <div class="flex flex-col md:flex-row items-center gap-8 mb-8 pb-8 border-b border-gray-100">
                <div class="relative w-32 h-32 flex-shrink-0 group cursor-pointer" onclick="openImageModal()">
                    <img src="<?php
                    $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']); // Default fallback
                    if (!empty($user['profile_image'])) {
                        if (strpos($user['profile_image'], 'http') === 0) {
                            $imgSrc = $user['profile_image']; // It's a URL (default avatar)
                        } else {
                            $basePath = '../uploads/users/';
                            // Check if file exists to prevent broken images
                            if (file_exists($basePath . $user['profile_image'])) {
                                $imgSrc = $basePath . htmlspecialchars($user['profile_image']);
                            }
                        }
                    }
                    echo $imgSrc;
                    ?>" alt="Profile"
                        class="w-full h-full object-cover rounded-full border-4 border-paw-bg shadow-inner bg-paw-bg">

                    <!-- Overlay -->
                    <div
                        class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="camera" class="w-8 h-8 text-white"></i>
                    </div>

                    <?php if ($user['is_verified']): ?>
                        <div class="absolute bottom-0 right-0 bg-blue-500 text-white p-1.5 rounded-full border-4 border-white"
                            title="Verified User">
                            <i data-lucide="badge-check" class="w-5 h-5"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center md:text-left">
                    <h2 class="font-serif text-3xl font-bold mb-2">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-3">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                        <?php if (($user['lives_saved'] ?? 0) > 0): ?>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-green-100 text-green-700 flex items-center gap-1">
                                <i data-lucide="heart" class="w-3 h-3 fill-current"></i>
                                <?php echo $user['lives_saved']; ?> Lives Saved
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($user['is_verified']): ?>
                        <a href="../create_blog.php"
                            class="inline-flex items-center gap-2 text-sm text-paw-accent hover:underline font-medium">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i> Share a Success Story
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_info">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Full Name</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                            required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Phone</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Gender</label>
                        <select name="gender"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors bg-white">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>
                                Male</option>
                            <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>
                                Female</option>
                            <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>
                                Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Date of
                            Birth</label>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                </div>

                <div class="pt-8 border-t border-gray-100">
                    <h3 class="font-serif text-2xl mb-6">Change Password</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Current
                                Password</label>
                            <input type="password" name="current_password" placeholder="Enter only if changing password"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">New
                                    Password</label>
                                <input type="password" name="new_password" placeholder="New Password"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Confirm
                                    New Password</label>
                                <input type="password" name="confirm_password" placeholder="Confirm New Password"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="px-8 py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<!-- Image Update Modal -->
<div id="imageModal" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeImageModal()"></div>

    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-8 relative transform transition-all scale-100">
            <button type="button" onclick="closeImageModal()"
                class="absolute top-4 right-4 p-2 hover:bg-gray-100 rounded-full transition-colors">
                <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
            </button>

            <h2 class="font-serif text-2xl font-bold mb-6 text-center">Update Profile Picture</h2>

            <form method="POST" enctype="multipart/form-data" action="profile.php">
                <input type="hidden" name="action" value="update_image">

                <!-- File Upload -->
                <div class="mb-8 text-center">
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-4 text-gray-500">Upload
                        New Photo</label>
                    <label class="cursor-pointer inline-block relative group">
                        <div
                            class="w-32 h-32 rounded-full border-4 border-dashed border-gray-200 flex items-center justify-center bg-gray-50 group-hover:bg-gray-100 transition-colors overflow-hidden">
                            <img id="previewImage" src="#" class="hidden w-full h-full object-cover">
                            <div id="uploadPlaceholder" class="text-center p-4">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                                <span class="text-xs text-gray-500 font-medium">Click to upload</span>
                            </div>
                        </div>
                        <input type="file" name="profile_image" accept="image/*" class="hidden"
                            onchange="previewFile(this)">
                    </label>
                </div>

                <!-- Separator -->
                <div class="relative flex items-center gap-4 mb-8">
                    <div class="h-px bg-gray-200 flex-grow"></div>
                    <span class="text-xs font-bold uppercase text-gray-400">OR</span>
                    <div class="h-px bg-gray-200 flex-grow"></div>
                </div>

                <!-- Avatar Selection -->
                <label
                    class="block text-sm uppercase tracking-widest font-semibold mb-4 text-center text-gray-500">Choose
                    an Avatar</label>
                <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide justify-center mb-6">
                    <?php
                    $seeds = ['Felix', 'Aneka', 'Mittens', 'Bella', 'Charlie', 'Max', 'Luna', 'Oliver'];
                    foreach ($seeds as $seed) {
                        $avatarUrl = "https://api.dicebear.com/9.x/adventurer/svg?seed=" . $seed;
                        echo '<label class="cursor-pointer relative group flex-shrink-0">
                                <input type="radio" name="default_avatar" value="' . $avatarUrl . '" class="peer sr-only">
                                <div class="relative">
                                    <img src="' . $avatarUrl . '" class="w-14 h-14 rounded-full border-2 border-transparent peer-checked:border-paw-accent hover:border-paw-accent/50 transition-all bg-white shadow-sm peer-checked:scale-110">
                                    <div class="absolute -right-1 -top-1 bg-paw-accent text-white rounded-full p-0.5 hidden peer-checked:block shadow-md">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                    </div>
                                </div>
                            </label>';
                    }
                    ?>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeImageModal()"
                        class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold uppercase text-sm tracking-widest hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit"
                        class="flex-1 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase text-sm tracking-widest hover:bg-paw-accent transition-colors shadow-lg shadow-paw-dark/20">Save
                        Picture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openImageModal() {
        document.getElementById('imageModal').classList.remove('hidden');
    }
    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }
    function previewFile(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewImage').classList.remove('hidden');
                document.getElementById('uploadPlaceholder').classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>

</html>