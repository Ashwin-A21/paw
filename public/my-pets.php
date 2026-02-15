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

// Handle Add/Edit Pet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = $_POST['type'];
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $gender = $_POST['gender'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = 'Available'; // Default status for user-added pets
    $image = 'default_pet.jpg';

    // Image Upload
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
        // Update existing pet (Check ownership)
        $petId = (int) $_POST['pet_id'];
        $checkOwner = $conn->query("SELECT id FROM pets WHERE id=$petId AND added_by=$userId");

        if ($checkOwner->num_rows > 0) {
            $sql = "UPDATE pets SET name='$name', type='$type', breed='$breed', age='$age', gender='$gender', description='$description'";
            if ($image !== 'default_pet.jpg') {
                $sql .= ", image='$image'";
            }
            $sql .= " WHERE id=$petId";

            if ($conn->query($sql)) {
                $message = "Pet details updated successfully!";
            } else {
                $error = "Error updating pet: " . $conn->error;
            }
        } else {
            $error = "You are not authorized to edit this pet.";
        }
    } else {
        // Add new pet
        $sql = "INSERT INTO pets (name, type, breed, age, gender, description, image, status, added_by) 
                VALUES ('$name', '$type', '$breed', '$age', '$gender', '$description', '$image', '$status', $userId)";

        if ($conn->query($sql)) {
            $message = "Pet listed for adoption successfully!";
        } else {
            $error = "Error adding pet: " . $conn->error;
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    // Ensure user owns the pet
    $conn->query("DELETE FROM pets WHERE id=$id AND added_by=$userId");
    header("Location: my-pets.php");
    exit();
}

// Fetch user's pets
$pets = $conn->query("SELECT * FROM pets WHERE added_by=$userId ORDER BY added_at DESC");

// Get pet for editing
$editPet = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $result = $conn->query("SELECT * FROM pets WHERE id=$id AND added_by=$userId");
    $editPet = $result->fetch_assoc();
}

$basePath = '../';
include '../includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-5xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <a href="index.php"
                    class="text-paw-gray hover:text-paw-accent flex items-center gap-2 mb-4 text-sm uppercase tracking-widest transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Dashboard
                </a>
                <h1 class="font-serif text-5xl mb-2 text-paw-dark">My Pets</h1>
                <p class="text-paw-gray">Manage the pets you have listed for adoption.</p>
            </div>

            <?php if (!$editPet): ?>
                <button onclick="document.getElementById('petForm').scrollIntoView({behavior: 'smooth'})"
                    class="px-6 py-3 bg-paw-accent text-white rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20 flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> List New Pet
                </button>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2 border border-green-100">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 flex items-center gap-2 border border-red-100">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Form Section -->
        <div id="petForm"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-paw-bg rounded-bl-full -mr-16 -mt-16 z-0"></div>

            <h2 class="font-serif text-2xl font-bold mb-6 relative z-10">
                <?php echo $editPet ? 'Edit Pet Details' : 'List a Pet for Adoption'; ?>
            </h2>

            <form method="POST" enctype="multipart/form-data" class="space-y-6 relative z-10">
                <?php if ($editPet): ?>
                    <input type="hidden" name="pet_id" value="<?php echo $editPet['id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Pet Name</label>
                        <input type="text" name="name" value="<?php echo $editPet['name'] ?? ''; ?>" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors"
                            placeholder="e.g. Bella">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Type</label>
                        <select name="type" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors bg-white">
                            <option value="dog" <?php echo ($editPet['type'] ?? '') === 'dog' ? 'selected' : ''; ?>>Dog
                            </option>
                            <option value="cat" <?php echo ($editPet['type'] ?? '') === 'cat' ? 'selected' : ''; ?>>Cat
                            </option>
                            <option value="bird" <?php echo ($editPet['type'] ?? '') === 'bird' ? 'selected' : ''; ?>>Bird
                            </option>
                            <option value="rabbit" <?php echo ($editPet['type'] ?? '') === 'rabbit' ? 'selected' : ''; ?>>
                                Rabbit</option>
                            <option value="other" <?php echo ($editPet['type'] ?? '') === 'other' ? 'selected' : ''; ?>>
                                Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Breed</label>
                        <input type="text" name="breed" value="<?php echo $editPet['breed'] ?? ''; ?>"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors"
                            placeholder="e.g. Golden Retriever">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Age</label>
                        <input type="text" name="age" value="<?php echo $editPet['age'] ?? ''; ?>"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors"
                            placeholder="e.g. 2 years">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Gender</label>
                        <select name="gender" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors bg-white">
                            <option value="Male" <?php echo ($editPet['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>
                                Male</option>
                            <option value="Female" <?php echo ($editPet['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent transition-colors resize-none"
                        placeholder="Tell us about the pet's personality, history, etc..."><?php echo $editPet['description'] ?? ''; ?></textarea>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Photo</label>
                    <div class="flex items-center gap-4">
                        <?php if ($editPet && $editPet['image']): ?>
                            <img src="../uploads/pets/<?php echo htmlspecialchars($editPet['image']); ?>"
                                class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:uppercase file:tracking-widest file:bg-paw-bg file:text-paw-dark hover:file:bg-paw-accent hover:file:text-white transition-all cursor-pointer">
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="submit"
                        class="flex-1 py-4 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors shadow-lg shadow-paw-dark/20">
                        <?php echo $editPet ? 'Update Pet Details' : 'List Pet Now'; ?>
                    </button>
                    <?php if ($editPet): ?>
                        <a href="my-pets.php"
                            class="px-8 py-4 border border-gray-200 text-gray-600 rounded-xl font-bold uppercase tracking-widest hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Listed Pets List -->
        <h2 class="font-serif text-2xl font-bold mb-6 text-paw-dark">My Listed Pets</h2>

        <?php if ($pets && $pets->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($pet = $pets->fetch_assoc()): ?>
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-lg transition-all">
                        <div class="relative h-48 overflow-hidden">
                            <img src="../uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div
                                class="absolute top-3 right-3 text-xs font-bold uppercase tracking-widest px-3 py-1 bg-white/90 backdrop-blur rounded-full text-paw-dark shadow-sm">
                                <?php echo $pet['status']; ?>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-serif text-xl font-bold text-paw-dark">
                                        <?php echo htmlspecialchars($pet['name']); ?>
                                    </h3>
                                    <p class="text-xs text-paw-gray uppercase tracking-widest">
                                        <?php echo htmlspecialchars($pet['breed']); ?>
                                    </p>
                                </div>
                                <span class="p-2 bg-paw-bg rounded-lg text-paw-accent">
                                    <?php echo $pet['gender'] == 'Male' ? '<i data-lucide="mars" class="w-4 h-4"></i>' : '<i data-lucide="venus" class="w-4 h-4"></i>'; ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-50">
                                <a href="my-pets.php?edit=<?php echo $pet['id']; ?>"
                                    class="flex-1 py-2 text-center text-xs font-bold uppercase tracking-widest text-paw-accent border border-paw-accent rounded-lg hover:bg-paw-accent hover:text-white transition-colors">
                                    Edit
                                </a>
                                <a href="my-pets.php?delete=<?php echo $pet['id']; ?>"
                                    class="flex-1 py-2 text-center text-xs font-bold uppercase tracking-widest text-red-500 border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                                    onclick="return confirm('Are you sure you want to remove this pet?')">
                                    Remove
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-100">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="dog" class="w-8 h-8 text-gray-300"></i>
                </div>
                <h3 class="font-serif text-xl font-bold text-gray-400 mb-2">No pets listed yet</h3>
                <p class="text-gray-400 text-sm">Use the form above to list a pet for adoption.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>