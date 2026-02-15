<?php
session_start();
include 'config.php';

// Get Pet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: adopt.php");
    exit();
}

$pet_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch Pet Details
$sql = "SELECT * FROM pets WHERE id = '$pet_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Pet not found.";
    exit();
}

$pet = $result->fetch_assoc();

// Handle Status Update (Owner only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet['added_by']) {
    $newStatus = mysqli_real_escape_string($conn, $_POST['status']);
    $conn->query("UPDATE pets SET status='$newStatus' WHERE id='$pet_id'");
    header("Location: pet-details.php?id=$pet_id");
    exit();
}

// Navigation: Previous & Next
$prevSql = "SELECT id FROM pets WHERE id < '$pet_id' AND status='Available' ORDER BY id DESC LIMIT 1";
$nextSql = "SELECT id FROM pets WHERE id > '$pet_id' AND status='Available' ORDER BY id ASC LIMIT 1";

$prevPet = $conn->query($prevSql)->fetch_assoc();
$nextPet = $conn->query($nextSql)->fetch_assoc();

$basePath = '';
include 'includes/header.php';
?>

<section class="pt-32 pb-20 px-6">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb & Back -->
        <div class="mb-8 flex justify-between items-center">
            <a href="adopt.php"
                class="text-paw-gray hover:text-paw-accent flex items-center gap-2 text-sm uppercase tracking-widest font-bold transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Adoptions
            </a>

            <div class="flex gap-4">
                <?php if ($prevPet): ?>
                    <a href="pet-details.php?id=<?php echo $prevPet['id']; ?>"
                        class="flex items-center gap-2 text-paw-dark hover:text-paw-accent font-bold uppercase text-xs tracking-widest transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> Previous Pet
                    </a>
                <?php else: ?>
                    <span
                        class="text-gray-300 font-bold uppercase text-xs tracking-widest flex items-center gap-2 cursor-not-allowed">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> Previous Pet
                    </span>
                <?php endif; ?>

                <?php if ($nextPet): ?>
                    <a href="pet-details.php?id=<?php echo $nextPet['id']; ?>"
                        class="flex items-center gap-2 text-paw-dark hover:text-paw-accent font-bold uppercase text-xs tracking-widest transition-colors">
                        Next Pet <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                <?php else: ?>
                    <span
                        class="text-gray-300 font-bold uppercase text-xs tracking-widest flex items-center gap-2 cursor-not-allowed">
                        Next Pet <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 flex flex-col md:flex-row">

            <!-- Image Section -->
            <div class="md:w-1/2 relative h-96 md:h-auto bg-gray-100">
                <img src="uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>"
                    alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-full object-cover">

                <div class="absolute top-6 left-6">
                    <span
                        class="px-4 py-2 bg-white/90 backdrop-blur rounded-full text-xs font-bold uppercase tracking-widest text-paw-dark shadow-sm">
                        <?php echo $pet['status']; ?>
                    </span>
                </div>
            </div>

            <!-- Details Section -->
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                <div class="mb-2 flex items-center gap-3">
                    <span class="text-paw-accent font-bold uppercase tracking-widest text-sm">
                        <?php echo htmlspecialchars($pet['breed']); ?>
                    </span>
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    <span class="text-gray-500 font-medium text-sm">
                        <?php echo htmlspecialchars($pet['type']); ?>
                    </span>
                </div>

                <h1 class="font-serif text-5xl text-paw-dark mb-6">
                    <?php echo htmlspecialchars($pet['name']); ?>
                </h1>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Age</p>
                        <p class="text-paw-dark font-serif text-xl">
                            <?php echo htmlspecialchars($pet['age']); ?>
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-center">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Gender</p>
                        <p class="text-paw-dark font-serif text-xl flex items-center justify-center gap-2">
                            <?php echo htmlspecialchars($pet['gender']); ?>
                            <?php if ($pet['gender'] == 'Male'): ?>
                                <i data-lucide="mars" class="w-4 h-4 text-blue-400"></i>
                            <?php else: ?>
                                <i data-lucide="venus" class="w-4 h-4 text-pink-400"></i>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="prose prose-stone mb-8 text-gray-600 leading-relaxed">
                    <h3 class="font-serif text-2xl text-paw-dark mb-4">About
                        <?php echo htmlspecialchars($pet['name']); ?>
                    </h3>
                    <p>
                        <?php echo nl2br(htmlspecialchars($pet['description'])); ?>
                    </p>
                </div>

                <div class="mt-auto pt-8 border-t border-gray-100 flex gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>

                        <?php if ($_SESSION['user_id'] == $pet['added_by']): ?>
                            <!-- Owner View: Change Status -->
                            <form method="POST" class="flex-1 flex gap-2">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-paw-accent bg-white">
                                    <option value="Available" <?php echo $pet['status'] == 'Available' ? 'selected' : ''; ?>>
                                        Available</option>
                                    <option value="Adopted" <?php echo $pet['status'] == 'Adopted' ? 'selected' : ''; ?>>Adopted
                                    </option>
                                    <option value="Pending" <?php echo $pet['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                                    </option>
                                </select>
                                <button type="submit"
                                    class="px-6 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest hover:bg-paw-accent transition-colors">
                                    Update
                                </button>
                            </form>
                        <?php elseif ($pet['status'] == 'Available'): ?>
                            <a href="adopt_form.php?pet_id=<?php echo $pet['id']; ?>"
                                class="flex-1 py-4 bg-paw-accent text-white rounded-xl text-center font-bold uppercase tracking-widest border border-transparent hover:bg-paw-dark transition-all shadow-lg shadow-paw-accent/20">
                                Adopt Me
                            </a>
                        <?php else: ?>
                            <button disabled
                                class="flex-1 py-4 bg-gray-200 text-gray-400 rounded-xl text-center font-bold uppercase tracking-widest cursor-not-allowed">
                                Not Available
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php?redirect=pet-details.php?id=<?php echo $pet['id']; ?>"
                            class="flex-1 py-4 bg-paw-dark text-white rounded-xl text-center font-bold uppercase tracking-widest hover:bg-paw-accent transition-all shadow-lg">
                            Login to Adopt
                        </a>
                    <?php endif; ?>

                    <button
                        class="px-6 py-4 border border-gray-200 rounded-xl text-paw-gray hover:bg-gray-50 transition-colors"
                        title="Share">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>