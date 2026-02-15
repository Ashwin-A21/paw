<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$message = "";
$error = "";

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqId = intval($_POST['request_id']);

    if (isset($_POST['action']) && $_POST['action'] === 'approve') {
        // Get Request Details
        $req = $conn->query("SELECT * FROM role_requests WHERE id=$reqId")->fetch_assoc();

        if ($req) {
            $userId = $req['user_id'];
            $newRole = $req['requested_role'];
            $orgName = mysqli_real_escape_string($conn, $req['organization_name'] ?? '');
            $orgType = mysqli_real_escape_string($conn, $req['organization_type'] ?? '');

            // Update User
            $upSql = "UPDATE users SET role='$newRole', is_verified=1, organization_name='$orgName', organization_type='$orgType' WHERE id=$userId";

            if ($conn->query($upSql)) {
                $conn->query("UPDATE role_requests SET status='Approved' WHERE id=$reqId");
                $message = "Request approved successfully. User role updated.";
            } else {
                $error = "Error updating user: " . $conn->error;
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reject') {
        if ($conn->query("UPDATE role_requests SET status='Rejected' WHERE id=$reqId")) {
            $message = "Request rejected.";
        } else {
            $error = "Error rejecting request: " . $conn->error;
        }
    }
}

// Fetch Pending Requests
$requests = $conn->query("SELECT r.*, u.username, u.email FROM role_requests r JOIN users u ON r.user_id = u.id WHERE r.status='Pending' ORDER BY r.created_at DESC");

$basePath = '../';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Paw Pal Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        paw: {
                            accent: '#D97706',
                            dark: '#1F2937',
                            light: '#FEF3C7',
                            bg: '#FFFBEB',
                            gray: '#6B7280'
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['DM Serif Display', 'serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 p-6 flex justify-between items-center">
            <h2 class="text-2xl font-serif text-paw-dark">Role Requests</h2>
            <div class="flex items-center gap-4">
                <div class="bg-gray-100 p-2 rounded-full">
                    <i data-lucide="bell" class="w-5 h-5 text-gray-500"></i>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
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

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500">User</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500">Requested Role
                            </th>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500">Organization</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500">Proof</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500">Date</th>
                            <th class="p-4 text-xs font-bold uppercase tracking-widest text-gray-500 text-right">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($requests && $requests->num_rows > 0): ?>
                            <?php while ($row = $requests->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-paw-dark">
                                            <?php echo htmlspecialchars($row['username']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold uppercase tracking-widest">
                                            <?php echo ucfirst($row['requested_role']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-600">
                                        <?php if (!empty($row['organization_name'])): ?>
                                            <div class="font-bold">
                                                <?php echo htmlspecialchars($row['organization_name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <?php echo htmlspecialchars($row['organization_type'] ?? ''); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($row['document_proof']): ?>
                                            <a href="../uploads/proofs/<?php echo htmlspecialchars($row['document_proof']); ?>"
                                                target="_blank"
                                                class="text-paw-accent hover:underline text-sm font-medium flex items-center gap-1">
                                                <i data-lucide="file-check" class="w-4 h-4"></i> View Doc
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">No Proof Uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" onsubmit="return confirm('Approve this request?');">
                                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit"
                                                    class="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors"
                                                    title="Approve">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Reject this request?');">
                                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit"
                                                    class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors"
                                                    title="Reject">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    No pending requests found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>