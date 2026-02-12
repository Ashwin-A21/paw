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

$basePath = '../';
$hideNavbar = true;
$hideFooter = true;
include '../includes/header.php';
?>

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

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
                            <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Username</label>
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
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Confirm
                                        New Password</label>
                                    <input type="password" name="confirm_password" placeholder="Confirm New Password"
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
<?php include '../includes/footer.php'; ?>