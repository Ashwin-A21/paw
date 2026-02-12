<?php
session_start();
include 'config.php';

// Build Query
$sql = "SELECT * FROM pets WHERE status='Available'";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " AND (name LIKE '%$search%' OR breed LIKE '%$search%')";
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $sql .= " AND type = '$type'";
}

if (isset($_GET['gender']) && !empty($_GET['gender'])) {
    $gender = mysqli_real_escape_string($conn, $_GET['gender']);
    $sql .= " AND gender = '$gender'";
}

$sql .= " ORDER BY added_at DESC";
$pets = $conn->query($sql);

// Fetch current user if logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uResult = $conn->query("SELECT * FROM users WHERE id=$uid");
    if ($uResult && $uResult->num_rows > 0) {
        $currentUser = $uResult->fetch_assoc();
    }
}

$basePath = '';
include 'includes/header.php';
?>

    <!-- Hero -->
    <section class="pt-32 pb-12 px-6 relative overflow-hidden">
        <div class="absolute top-20 left-0 w-96 h-96 bg-paw-accent/10 rounded-full blur-3xl"></div>
        <div class="max-w-3xl mx-auto text-center relative z-10">
            <h1 class="font-serif text-5xl md:text-6xl text-paw-dark mb-6">
                Find Your New <span class="italic text-paw-accent">Best Friend</span>
            </h1>
            <p class="text-paw-gray text-lg max-w-xl mx-auto mb-10">
                Browse our available pets and give them the loving home they deserve.
            </p>
        </div>
    </section>

    <!-- Filters -->
    <section class="px-6 mb-12 sticky top-24 z-40">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-white/50 p-4">
                <form method="GET" class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                        <input type="text" name="search" placeholder="Search by name or breed..."
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-paw-accent/20 outline-none transition-all">
                    </div>
                    
                    <div class="flex gap-4 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                        <select name="type" class="px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20 cursor-pointer min-w-[140px]">
                            <option value="">All Types</option>
                            <option value="Dog" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Dog') ? 'selected' : ''; ?>>Dogs</option>
                            <option value="Cat" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Cat') ? 'selected' : ''; ?>>Cats</option>
                            <option value="Other" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Other') ? 'selected' : ''; ?>>Others</option>
                        </select>
                        
                        <select name="gender" class="px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20 cursor-pointer min-w-[140px]">
                            <option value="">Any Gender</option>
                            <option value="Male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                        
                        <button type="submit" class="px-8 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-paw-accent transition-colors shadow-lg">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Pets Grid -->
    <section class="pb-20 px-6">
        <div class="max-w-7xl mx-auto">
            <?php if ($pets && $pets->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-gray-100 flex flex-col h-full">
                            <div class="relative h-72 overflow-hidden">
                                <img src="<?php echo 'uploads/pets/' . htmlspecialchars($pet['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-paw-dark shadow-sm">
                                    <?php echo $pet['age']; ?> Years
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                                    <span class="text-white font-serif italic text-lg">Click to view details</span>
                                </div>
                            </div>
                            
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-serif text-3xl font-bold text-paw-dark"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                    <?php if($pet['gender'] == 'Male'): ?>
                                        <i data-lucide="mars" class="w-6 h-6 text-blue-400"></i>
                                    <?php else: ?>
                                        <i data-lucide="venus" class="w-6 h-6 text-pink-400"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-paw-accent text-sm font-semibold uppercase tracking-widest mb-4"><?php echo htmlspecialchars($pet['breed']); ?></p>
                                
                                <p class="text-paw-gray text-sm line-clamp-3 mb-6 flex-1">
                                    <?php echo htmlspecialchars($pet['description']); ?>
                                </p>
                                
                                <div class="pt-6 border-t border-gray-100 mt-auto">
                                    <div class="flex gap-3">
                                        <a href="pet-details.php?id=<?php echo $pet['id']; ?>" class="flex-1 py-3 border border-paw-dark text-paw-dark rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark hover:text-white transition-colors">
                                            Details
                                        </a>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="adopt_form.php?pet_id=<?php echo $pet['id']; ?>" class="flex-1 py-3 bg-paw-accent text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                                Adopt
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php" class="flex-1 py-3 bg-paw-accent text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                                Login to Adopt
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
    </section>

<?php include 'includes/footer.php'; ?>