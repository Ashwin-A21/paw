<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = "";
$error = "";

if (!$userId) {
    header("Location: users.php");
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];
    $isVerified = isset($_POST['is_verified']) ? 1 : 0;
    $livesSaved = (int) $_POST['lives_saved'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);

    $passwordSql = "";
    if (!empty($_POST['new_password'])) {
        $newPassword = $_POST['new_password']; // No hashing as requested
        $passwordSql = ", password='$newPassword'";
    }

    $updateSql = "UPDATE users SET username='$username', email='$email', role='$role', is_verified=$isVerified, lives_saved=$livesSaved, phone='$phone', gender='$gender', dob='$dob' $passwordSql WHERE id=$userId";

    if ($conn->query($updateSql)) {
        $message = "User updated successfully!";
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}

// Fetch user
$userResult = $conn->query("SELECT * FROM users WHERE id=$userId");
if ($userResult->num_rows === 0) {
    header("Location: users.php");
    exit();
}
$user = $userResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Paw Pal Admin</title>
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
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-2xl mx-auto">
                <a href="users.php" class="text-paw-gray hover:text-paw-accent flex items-center gap-2 mb-6"><i
                        data-lucide="arrow-left" class="w-4 h-4"></i> Back to Users</a>
                <h1 class="font-serif text-4xl mb-8">Edit User</h1>

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
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Role</label>
                                <select name="role"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User
                                    </option>
                                    <option value="volunteer" <?php echo $user['role'] === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                    <option value="rescuer" <?php echo $user['role'] === 'rescuer' ? 'selected' : ''; ?>>
                                        Rescuer</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-center pt-8">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_verified" value="1" <?php echo $user['is_verified'] ? 'checked' : ''; ?> class="w-5 h-5 accent-paw-accent rounded">
                                    <span class="font-medium">Verified User</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Lives
                                    Saved</label>
                                <input type="number" name="lives_saved" value="<?php echo $user['lives_saved'] ?? 0; ?>"
                                    min="0"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Phone</label>
                                <input type="text" name="phone"
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Gender</label>
                                <select name="gender"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                                    <option value="">Select</option>
                                    <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Date of
                                    Birth</label>
                                <input type="date" name="dob"
                                    value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100 mt-6">
                            <h3 class="font-serif text-xl mb-4 text-paw-alert">Change Password (Optional)</h3>
                            <input type="text" name="new_password" placeholder="Enter new password to reset"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent">
                            <p class="text-xs text-paw-gray mt-2">Leave blank to keep current password.</p>
                        </div>

                        <div class="pt-6">
                            <button type="submit"
                                class="w-full py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Update
                                User</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>

</html>