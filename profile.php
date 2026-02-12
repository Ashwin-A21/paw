<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);

    // Handle Image Upload
    $imageUpdate = "";
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $uploadDir = 'uploads/users/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
        $targetPath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $imageUpdate = ", profile_image='$imageName'";
        }
    }

    $sql = "UPDATE users SET username='$username', phone='$phone', gender='$gender', dob='$dob' $imageUpdate WHERE id=$user_id";

    if ($conn->query($sql)) {
        $message = "Profile updated successfully!";
        // Update session name if changed
        $_SESSION['username'] = $username;
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// Fetch User Data
$sql = "SELECT * FROM users WHERE id=$user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// If Verified, fetch their blogs
$blogs = [];
if ($user['is_verified']) {
    $blogSql = "SELECT * FROM blogs WHERE author_id=$user_id ORDER BY created_at DESC";
    $blogResult = $conn->query($blogSql);
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Paw Pal</title>

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
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e5e5;
            border-radius: 0.75rem;
            background: white;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #D4A373;
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                    class="text-paw-accent">.</span></a>
            <div class="flex items-center gap-6">
                <a href="index.php" class="text-sm font-medium hover:text-paw-accent transition-colors">Home</a>
                <a href="logout.php" class="text-sm font-medium hover:text-paw-alert transition-colors">Sign Out</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-6 py-12 max-w-5xl">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar / User Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 text-center sticky top-24">
                    <div class="relative w-32 h-32 mx-auto mb-4">
                        <img src="<?php echo !empty($user['profile_image']) ? 'uploads/users/' . htmlspecialchars($user['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']); ?>"
                            alt="Profile"
                            class="w-full h-full object-cover rounded-full border-4 border-paw-bg shadow-inner">
                        <?php if ($user['is_verified']): ?>
                            <div class="absolute bottom-0 right-0 bg-blue-500 text-white p-1.5 rounded-full border-4 border-white"
                                title="Verified User">
                                <i data-lucide="badge-check" class="w-5 h-5"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="font-serif text-2xl font-bold mb-1 flex items-center justify-center gap-2">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h1>
                    <p class="text-paw-gray text-sm mb-4">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>

                    <div class="flex items-center justify-center gap-2 mb-6">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-paw-accent/10 text-paw-accent">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                        <?php if (isset($user['lives_saved']) && $user['lives_saved'] > 0): ?>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-green-100 text-green-700 flex items-center gap-1">
                                <i data-lucide="heart" class="w-3 h-3 fill-current"></i>
                                <?php echo $user['lives_saved']; ?> Lives Saved
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-gray-100 pt-6 text-left space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-paw-gray">Member since</span>
                            <span class="font-medium">
                                <?php echo date('M Y', strtotime($user['created_at'])); ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-paw-gray">Status</span>
                            <?php if ($user['is_verified']): ?>
                                <span class="text-green-600 font-medium flex items-center gap-1"><i
                                        data-lucide="check-circle" class="w-3 h-3"></i> Verified</span>
                            <?php else: ?>
                                <span class="text-gray-500 font-medium">Unverified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-8">

                <?php if ($message): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Profile Form -->
                <div class="bg-white rounded-2xl shadow-sm p-8">
                    <h2 class="font-serif text-2xl mb-6 flex items-center gap-3">
                        <i data-lucide="user-cog" class="w-6 h-6 text-paw-accent"></i>
                        Edit Profile
                    </h2>

                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Full
                                    Name</label>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required
                                    class="form-input">
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Phone</label>
                                <input type="tel" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="Add phone number" class="form-input">
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Gender</label>
                                <select name="gender" class="form-input">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Date of
                                    Birth</label>
                                <input type="date" name="dob"
                                    value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" class="form-input">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Profile
                                Picture</label>
                            <input type="file" name="profile_image" accept="image/*" class="form-input">
                            <p class="text-xs text-paw-gray mt-1">Leave empty to keep current picture.</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" name="update_profile"
                                class="px-6 py-3 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Blog Section (Verified Users Only) -->
                <?php if ($user['is_verified']): ?>
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="font-serif text-2xl flex items-center gap-3">
                                <i data-lucide="pen-tool" class="w-6 h-6 text-paw-accent"></i>
                                My Stories
                            </h2>
                            <a href="create_blog.php"
                                class="px-4 py-2 bg-paw-accent/10 text-paw-accent rounded-lg text-sm font-bold hover:bg-paw-accent hover:text-white transition-colors">
                                Write New Story
                            </a>
                        </div>

                        <?php if ($blogResult && $blogResult->num_rows > 0): ?>
                            <div class="space-y-4">
                                <?php while ($blog = $blogResult->fetch_assoc()): ?>
                                    <div
                                        class="border border-gray-100 rounded-xl p-4 flex gap-4 items-center hover:bg-gray-50 transition-colors">
                                        <?php if ($blog['image']): ?>
                                            <img src="uploads/blogs/<?php echo htmlspecialchars($blog['image']); ?>"
                                                class="w-16 h-16 rounded-lg object-cover">
                                        <?php else: ?>
                                            <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i data-lucide="image-off" class="w-6 h-6 text-gray-300"></i>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex-1">
                                            <h3 class="font-bold text-lg mb-1">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                            </h3>
                                            <div class="flex items-center gap-3 text-xs">
                                                <span class="text-paw-gray">
                                                    <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                                </span>
                                                <?php
                                                $statusColors = [
                                                    'approved' => 'text-green-600 bg-green-50',
                                                    'pending' => 'text-yellow-600 bg-yellow-50',
                                                    'rejected' => 'text-red-600 bg-red-50'
                                                ];
                                                $status = $blog['status'] ?? ($blog['is_published'] ? 'approved' : 'pending');
                                                $colorClass = $statusColors[$status] ?? 'text-gray-600 bg-gray-50';
                                                ?>
                                                <span
                                                    class="px-2 py-0.5 rounded-full <?php echo $colorClass; ?> font-medium capitalize">
                                                    <?php echo $status; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <a href="edit_blog.php?id=<?php echo $blog['id']; ?>"
                                            class="p-2 text-paw-gray hover:text-paw-dark">
                                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-paw-gray">
                                <p>You haven't posted any stories yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <script>lucide.createIcons();</script>
</body>

</html>