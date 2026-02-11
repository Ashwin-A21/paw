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
            $_SESSION['username'] = $username; // Update session
            $message = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}

// Fetch current user data
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
        body {
            background-color: #F9F8F6;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <div class="hidden md:flex items-center space-x-10">
                    <a href="index.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Dashboard</a>
                    <a href="../adopt.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Adopt</a>
                    <a href="../blogs.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Blog</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <span class="text-sm text-paw-gray">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                    <a href="../logout.php"
                        class="px-6 py-2 bg-paw-dark text-white rounded-full text-xs uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>

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

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Username</label>
                            <input type="text" name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email
                                Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors">
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

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal.</p>
            <p>Built with <i data-lucide="heart" class="inline w-4 h-4 text-paw-alert"></i> for animals</p>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>

</html>