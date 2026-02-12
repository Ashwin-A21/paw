<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

// Handle Verification Update
if (isset($_POST['toggle_verification'])) {
    $userId = (int) $_POST['user_id'];
    $currentStatus = (int) $_POST['current_status'];
    $newStatus = $currentStatus ? 0 : 1;

    $conn->query("UPDATE users SET is_verified=$newStatus WHERE id=$userId");
}

// Handle Role Update (Optional but good for management)
if (isset($_POST['update_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = $_POST['role'];
    $conn->query("UPDATE users SET role='$newRole' WHERE id=$userId");
}

// Handle Lives Saved Update
if (isset($_POST['update_lives'])) {
    $userId = (int) $_POST['user_id'];
    $lives = (int) $_POST['lives_saved'];
    $conn->query("UPDATE users SET lives_saved=$lives WHERE id=$userId");
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Paw Pal Admin</title>

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
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-paw-dark text-white flex flex-col">
            <div class="p-6 border-b border-white/10">
                <a href="../index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <p class="text-xs text-white/50 mt-1 uppercase tracking-widest">Admin Panel</p>
            </div>

            <nav class="flex-1 p-4">
                <a href="index.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                </a>
                <a href="pets.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="heart" class="w-5 h-5"></i> Manage Pets
                </a>
                <a href="applications.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i> Applications
                </a>
                <a href="rescues.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="siren" class="w-5 h-5"></i> Rescue Reports
                </a>
                <a href="blogs.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="book-open" class="w-5 h-5"></i> Blog Posts
                </a>
                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                    <i data-lucide="users" class="w-5 h-5"></i> Users
                </a>
                <a href="profile.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="user-circle" class="w-5 h-5"></i> My Profile
                </a>
            </nav>

            <div class="p-4 border-t border-white/10">
                <a href="../logout.php" class="flex items-center gap-2 text-white/50 hover:text-white text-sm">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <h1 class="font-serif text-4xl mb-8">Manage Users</h1>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-sm font-semibold text-paw-gray uppercase tracking-wider">User
                                </th>
                                <th class="px-6 py-4 text-sm font-semibold text-paw-gray uppercase tracking-wider">Role
                                </th>
                                <th class="px-6 py-4 text-sm font-semibold text-paw-gray uppercase tracking-wider">Lives
                                    Saved
                                </th>
                                <th class="px-6 py-4 text-sm font-semibold text-paw-gray uppercase tracking-wider"></th>
                                </th>
                                Status</th>
                                <th class="px-6 py-4 text-sm font-semibold text-paw-gray uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 bg-paw-accent/10 rounded-full flex items-center justify-center text-paw-accent font-bold">
                                                <?php echo substr($user['username'], 0, 1); ?>
                                            </div>
                                            <div>
                                                <p class="font-medium text-paw-dark">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </p>
                                                <p class="text-sm text-paw-gray">
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" onchange="this.form.submit()"
                                                class="bg-transparent text-sm font-medium focus:outline-none cursor-pointer">
                                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="volunteer" <?php echo $user['role'] === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                                <option value="rescuer" <?php echo $user['role'] === 'rescuer' ? 'selected' : ''; ?>>Rescuer</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="number" name="lives_saved" value="<?php echo $user['lives_saved'] ?? 0; ?>" min="0" class="w-16 px-2 py-1 text-sm border rounded-lg focus:outline-none focus:border-paw-accent">
                                            <button type="submit" name="update_lives" class="text-xs text-paw-accent hover:text-paw-dark font-medium">Save</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($user['is_verified']): ?>
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i data-lucide="badge-check" class="w-3 h-3"></i> Verified
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Unverified
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 flex items-center gap-3">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                            Edit
                                        </a>
                                        <form method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="current_status"
                                                value="<?php echo $user['is_verified']; ?>">
                                            <button type="submit" name="toggle_verification"
                                                class="text-sm font-medium 
                                                <?php echo $user['is_verified'] ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'; ?>">
                                                <?php echo $user['is_verified'] ? 'Revoke' : 'Verify'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>