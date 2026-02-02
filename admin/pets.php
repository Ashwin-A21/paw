<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../config.php';

$message = "";

// Handle Add/Edit Pet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = $_POST['type'];
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = $_POST['gender'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = $_POST['status'];
    $image = 'default_pet.jpg';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = '../uploads/pets/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $imageName;
        }
    }

    if (isset($_POST['pet_id']) && !empty($_POST['pet_id'])) {
        $petId = (int) $_POST['pet_id'];
        $sql = "UPDATE pets SET name='$name', type='$type', breed='$breed', age='$age', gender='$gender', description='$description', status='$status'";
        if ($image !== 'default_pet.jpg')
            $sql .= ", image='$image'";
        $sql .= " WHERE id=$petId";
        if ($conn->query($sql))
            $message = "Pet updated successfully!";
    } else {
        $addedBy = $_SESSION['user_id'];
        $sql = "INSERT INTO pets (name, type, breed, age, gender, description, image, status, added_by) VALUES ('$name', '$type', '$breed', '$age', '$gender', '$description', '$image', '$status', $addedBy)";
        if ($conn->query($sql))
            $message = "Pet added successfully!";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM pets WHERE id=$id");
    header("Location: pets.php");
    exit();
}

// Get pet for editing
$editPet = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = $conn->query("SELECT * FROM pets WHERE id=$id");
    $editPet = $result->fetch_assoc();
}

$pets = $conn->query("SELECT * FROM pets ORDER BY added_at DESC");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pets - Paw Pal Admin</title>

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
                <a href="pets.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 text-white mb-2">
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
                        <?php echo $editPet ? 'Edit Pet' : (isset($_GET['action']) && $_GET['action'] === 'add' ? 'Add New Pet' : 'Manage Pets'); ?>
                    </h1>
                    <?php if (!$editPet && (!isset($_GET['action']) || $_GET['action'] !== 'add')): ?>
                        <a href="pets.php?action=add"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Pet
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($message): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($editPet || (isset($_GET['action']) && $_GET['action'] === 'add')): ?>
                    <!-- Add/Edit Form -->
                    <div class="bg-white rounded-2xl shadow-sm p-8">
                        <form method="POST" enctype="multipart/form-data" class="space-y-6">
                            <?php if ($editPet): ?>
                                <input type="hidden" name="pet_id" value="<?php echo $editPet['id']; ?>">
                            <?php endif; ?>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Pet
                                        Name</label>
                                    <input type="text" name="name" value="<?php echo $editPet['name'] ?? ''; ?>" required
                                        class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Type</label>
                                    <select name="type" required class="form-input">
                                        <option value="dog" <?php echo ($editPet['type'] ?? '') === 'dog' ? 'selected' : ''; ?>>Dog</option>
                                        <option value="cat" <?php echo ($editPet['type'] ?? '') === 'cat' ? 'selected' : ''; ?>>Cat</option>
                                        <option value="bird" <?php echo ($editPet['type'] ?? '') === 'bird' ? 'selected' : ''; ?>>Bird</option>
                                        <option value="other" <?php echo ($editPet['type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Breed</label>
                                    <input type="text" name="breed" value="<?php echo $editPet['breed'] ?? ''; ?>"
                                        class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Age</label>
                                    <input type="text" name="age" value="<?php echo $editPet['age'] ?? ''; ?>"
                                        placeholder="e.g., 2 years" class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Gender</label>
                                    <select name="gender" required class="form-input">
                                        <option value="Male" <?php echo ($editPet['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($editPet['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Status</label>
                                <select name="status" class="form-input">
                                    <option value="Available" <?php echo ($editPet['status'] ?? '') === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Pending" <?php echo ($editPet['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Adopted" <?php echo ($editPet['status'] ?? '') === 'Adopted' ? 'selected' : ''; ?>>Adopted</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm uppercase tracking-widest font-semibold mb-2">Description</label>
                                <textarea name="description" rows="4"
                                    class="form-input resize-none"><?php echo $editPet['description'] ?? ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Image</label>
                                <input type="file" name="image" accept="image/*" class="form-input">
                            </div>

                            <div class="flex gap-4">
                                <button type="submit"
                                    class="px-8 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                                    <?php echo $editPet ? 'Update Pet' : 'Add Pet'; ?>
                                </button>
                                <a href="pets.php"
                                    class="px-8 py-3 border border-gray-200 rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-gray-50 transition-colors">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Pets Table -->
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Name</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Type</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Breed</th>
                                    <th
                                        class="text-left px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Status</th>
                                    <th
                                        class="text-right px-6 py-4 text-xs uppercase tracking-widest font-semibold text-paw-gray">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php while ($pet = $pets->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($pet['name']); ?></td>
                                        <td class="px-6 py-4 capitalize"><?php echo $pet['type']; ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($pet['breed']); ?></td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 text-xs rounded-full 
                                        <?php echo $pet['status'] === 'Available' ? 'bg-green-50 text-green-700' :
                                            ($pet['status'] === 'Pending' ? 'bg-yellow-50 text-yellow-700' : 'bg-blue-50 text-blue-700'); ?>">
                                                <?php echo $pet['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="pets.php?edit=<?php echo $pet['id']; ?>"
                                                class="text-paw-accent hover:underline mr-4">Edit</a>
                                            <a href="pets.php?delete=<?php echo $pet['id']; ?>"
                                                onclick="return confirm('Delete this pet?');"
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