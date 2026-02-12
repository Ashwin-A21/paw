<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

if (!$user['is_verified']) {
    header("Location: profile.php"); // Only verified users
    exit();
}

$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$blog = null;
$message = "";
$error = "";

if ($blog_id) {
    $blog = $conn->query("SELECT * FROM blogs WHERE id=$blog_id AND author_id=$user_id")->fetch_assoc();
    if (!$blog) {
        header("Location: profile.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $imageUpdate = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/blogs/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imageUpdate = ", image='$imageName'";
            $imageVal = $imageName; // For insert
        }
    }

    if ($blog) {
        // Update
        $sql = "UPDATE blogs SET title='$title', content='$content', status='pending' $imageUpdate WHERE id=$blog_id";
        if ($conn->query($sql)) {
            $message = "Blog updated and sent for approval!";
            $blog = $conn->query("SELECT * FROM blogs WHERE id=$blog_id")->fetch_assoc(); // Refresh
        } else {
            $error = "Error updating blog: " . $conn->error;
        }
    } else {
        // Insert
        $img = isset($imageVal) ? $imageVal : '';
        $sql = "INSERT INTO blogs (title, content, author_id, author, image, status, is_published) 
                VALUES ('$title', '$content', $user_id, '{$user['username']}', '$img', 'pending', 0)";
        if ($conn->query($sql)) {
            $message = "Blog created and sent for approval!";
            $_GET['id'] = $conn->insert_id; // Switch to edit mode
            $blog_id = $conn->insert_id;
            $blog = $conn->query("SELECT * FROM blogs WHERE id=$blog_id")->fetch_assoc();
        } else {
            $error = "Error creating blog: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $blog ? 'Edit Story' : 'Write Story'; ?> - Paw Pal
    </title>
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
                <a href="profile.php" class="text-sm font-medium hover:text-paw-accent transition-colors">Back to
                    Profile</a>
            </div>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-6 py-12 max-w-3xl">
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <h1 class="font-serif text-3xl mb-2">
                <?php echo $blog ? 'Edit Story' : 'New Story'; ?>
            </h1>
            <p class="text-paw-gray mb-8">Share your rescue stories or tips with the community.</p>

            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($blog['title'] ?? ''); ?>"
                        required class="form-input text-lg font-serif">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Content</label>
                    <textarea name="content" rows="12" required
                        class="form-input resize-none leading-relaxed"><?php echo htmlspecialchars($blog['content'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Featured Image</label>
                    <?php if (!empty($blog['image'])): ?>
                        <div class="mb-3">
                            <img src="uploads/blogs/<?php echo htmlspecialchars($blog['image']); ?>"
                                class="h-32 rounded-lg object-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="form-input">
                </div>

                <div class="flex justify-between items-center pt-4">
                    <a href="profile.php" class="text-paw-gray hover:text-paw-dark text-sm font-medium">Cancel</a>
                    <button type="submit"
                        class="px-8 py-3 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                        <?php echo $blog ? 'Update & Request Approval' : 'Submit for Approval'; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
    <script>lucide.createIcons();</script>
</body>

</html>