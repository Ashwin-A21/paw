<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['volunteer', 'rescuer'])) {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = "";
$error = "";

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Password Update Logic
    $passwordSql = "";
    if (!empty($_POST['new_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Verify current password
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
        $updateSql = "UPDATE users SET username='$username', email='$email' $passwordSql WHERE id=$userId";
        if ($conn->query($updateSql)) {
            $_SESSION['username'] = $username;
            $message = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}

$userResult = $conn->query("SELECT * FROM users WHERE id=$userId");
$user = $userResult->fetch_assoc();
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
                    colors: { 'paw-bg': '#F9F8F6', 'paw-dark': '#2D2825', 'paw-accent': '#D4A373', 'paw-alert': '#E07A5F', 'paw-gray': '#9D958F' },
                    fontFamily: { serif: ['"Cormorant Garamond"', 'serif'], sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                }
            }
        }
    </script>
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-paw-dark text-white flex flex-col border-r border-white/10">
            <div class="p-6 border-b border-white/10">
                <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <p class="text-xs text-white/50 mt-1 uppercase tracking-widest">
                    <?php echo ucfirst($role); ?> Panel
                </p>
            </div>
            <nav class="flex-1 p-4">
                <a href="index.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors"><i
                        data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard</a>
                <a href="tasks.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors"><i
                        data-lucide="check-square" class="w-5 h-5"></i> My Tasks</a>
                <a href="rescues.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors"><i
                        data-lucide="siren" class="w-5 h-5"></i> Rescue Reports</a>
                <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                    <i data-lucide="user-circle" class="w-5 h-5"></i> My Profile </a>

                <div class="mt-8 mb-2 px-4 text-xs font-semibold text-white/30 uppercase tracking-widest">
                    Quick Links
                </div>
                <a href="../adopt.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="heart" class="w-5 h-5"></i> Adopt a Pet
                </a>
                <a href="../blogs.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="book-open" class="w-5 h-5"></i> Success Stories
                </a>
                <a href="../public/index.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i> My Adoptions
                </a>
            </nav>
            <div class="p-4 border-t border-white/10">
                <a href="../logout.php" class="flex items-center gap-2 text-white/50 hover:text-white text-sm"><i
                        data-lucide="log-out" class="w-4 h-4"></i> Sign Out</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-3xl mx-auto">
                <h1 class="font-serif text-4xl mb-8">My Profile</h1>

                <?php if ($message): ?>
                    <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2"><i
                            data-lucide="check-circle" class="w-5 h-5"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2"><i
                            data-lucide="alert-circle" class="w-5 h-5"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl shadow-sm p-8">
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm uppercase tracking-widest font-semibold mb-3">Username</label>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                        </div>
                        <div class="pt-8 border-t border-gray-100">
                            <h3 class="font-serif text-2xl mb-6">Change Password</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Current
                                        Password</label>
                                    <input type="password" name="current_password"
                                        placeholder="Enter only if changing password"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm uppercase tracking-widest font-semibold mb-3">New
                                            Password</label>
                                        <input type="password" name="new_password" placeholder="New Password"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm uppercase tracking-widest font-semibold mb-3">Confirm
                                            New Password</label>
                                        <input type="password" name="confirm_password"
                                            placeholder="Confirm New Password"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-6">
                            <button type="submit"
                                class="px-8 py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>

</html>