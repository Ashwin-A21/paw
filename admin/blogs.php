<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$message = "";

// Handle Add/Edit Blog
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = '../uploads/blogs/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        }
    }

    if (isset($_POST['blog_id']) && !empty($_POST['blog_id'])) {
        $blogId = (int) $_POST['blog_id'];
        $sql = "UPDATE blogs SET title='$title', content='$content', author='$author', is_published=$isPublished";
        if ($image)
            $sql .= ", image='$image'";
        $sql .= " WHERE id=$blogId";
        if ($conn->query($sql))
            $message = "Blog updated successfully!";
    } else {
        $sql = "INSERT INTO blogs (title, content, author, image, is_published) VALUES ('$title', '$content', '$author', '$image', $isPublished)";
        if ($conn->query($sql))
            $message = "Blog created successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM blogs WHERE id=$id");
    header("Location: blogs.php");
    exit();
}

// Get blog for editing
$editBlog = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = $conn->query("SELECT * FROM blogs WHERE id=$id");
    $editBlog = $result->fetch_assoc();
}

$blogs = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts - Paw Pal Admin</title>

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
            font-size: 0.9rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #D4A373;
        }
    </style>
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
                <a href="blogs.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
                    <i data-lucide="book-open" class="w-5 h-5"></i> Blog Posts
                </a>
                <a href="users.php"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-white/70 hover:bg-white/5 hover:text-white mb-2 transition-colors">
                    <i data-lucide="users" class="w-5 h-5"></i> Users
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
            <div class="max-w-5xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="font-serif text-4xl">
                        <?php echo $editBlog ? 'Edit Post' : (isset($_GET['action']) ? 'Create Post' : 'Blog Posts'); ?>
                    </h1>
                    <?php if (!$editBlog && !isset($_GET['action'])): ?>
                        <a href="blogs.php?action=add"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i> New Post
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($editBlog || isset($_GET['action'])): ?>
                    <!-- Add/Edit Form -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <?php if ($editBlog): ?>
                                <input type="hidden" name="blog_id" value="<?php echo $editBlog['id']; ?>">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Title</label>
                                <input type="text" name="title" value="<?php echo $editBlog['title'] ?? ''; ?>" required
                                    class="form-input">
                            </div>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Author</label>
                                <input type="text" name="author"
                                    value="<?php echo $editBlog['author'] ?? $_SESSION['username']; ?>" required
                                    class="form-input">
                            </div>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Content</label>
                                <textarea name="content" rows="10" required
                                    class="form-input resize-none"><?php echo $editBlog['content'] ?? ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Featured
                                    Image</label>
                                <input type="file" name="image" accept="image/*" class="form-input">
                            </div>

                            <div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="is_published" <?php echo ($editBlog['is_published'] ?? 1) ? 'checked' : ''; ?> class="w-5 h-5 accent-paw-accent rounded">
                                    <span class="text-sm font-medium">Publish immediately</span>
                                </label>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit"
                                    class="px-8 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                                    <?php echo $editBlog ? 'Update Post' : 'Publish Post'; ?>
                                </button>
                                <a href="blogs.php"
                                    class="px-8 py-3 border border-gray-200 rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-gray-50 transition-colors">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Blogs Table -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Title</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Author</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Date</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Status</th>
                                    <th
                                        class="text-right px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php while ($blog = $blogs->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($blog['title']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($blog['author']); ?></td>
                                        <td class="px-6 py-4 text-paw-gray">
                                            <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 text-xs rounded-full <?php echo $blog['is_published'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600'; ?>">
                                                <?php echo $blog['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="blogs.php?edit=<?php echo $blog['id']; ?>"
                                                class="text-paw-accent hover:underline mr-4">Edit</a>
                                            <a href="blogs.php?delete=<?php echo $blog['id']; ?>"
                                                onclick="return confirm('Delete this post?');"
                                                class="text-paw-alert hover:underline">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>