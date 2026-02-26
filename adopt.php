<?php
session_start();
include 'config.php';
include_once 'includes/pagination.php';

// Build Query
$baseSql = "FROM pets p LEFT JOIN users u ON p.added_by = u.id WHERE p.status='Available'";
$countSql = "SELECT COUNT(*) as total " . $baseSql;
$sql = "SELECT p.*, u.username as poster_username, u.profile_image as poster_image " . $baseSql;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchClause = " AND (p.name LIKE '%$search%' OR p.breed LIKE '%$search%')";
    $countSql .= $searchClause;
    $sql .= $searchClause;
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $type = mysqli_real_escape_string($conn, $_GET['type']);
    $typeClause = " AND p.type = '$type'";
    $countSql .= $typeClause;
    $sql .= $typeClause;
}

if (isset($_GET['gender']) && !empty($_GET['gender'])) {
    $gender = mysqli_real_escape_string($conn, $_GET['gender']);
    $genderClause = " AND p.gender = '$gender'";
    $countSql .= $genderClause;
    $sql .= $genderClause;
}

$sql .= " ORDER BY p.added_at DESC";

// Pagination
$totalResult = $conn->query($countSql);
$totalItems = $totalResult->fetch_assoc()['total'];
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$pagination = getPaginationData($totalItems, 9, $currentPage);

$sql .= " LIMIT {$pagination['perPage']} OFFSET {$pagination['offset']}";
$pets = $conn->query($sql);

// Build URL for pagination links (preserve filters)
$paginationUrl = 'adopt.php?';
$params = [];
if (!empty($_GET['search']))
    $params[] = 'search=' . urlencode($_GET['search']);
if (!empty($_GET['type']))
    $params[] = 'type=' . urlencode($_GET['type']);
if (!empty($_GET['gender']))
    $params[] = 'gender=' . urlencode($_GET['gender']);
$paginationUrl .= implode('&', $params);

// Fetch current user if logged in
$currentUser = null;
$userFavorites = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $uResult = $conn->query("SELECT * FROM users WHERE id=$uid");
    if ($uResult && $uResult->num_rows > 0) {
        $currentUser = $uResult->fetch_assoc();
    }
    // Fetch user's favorites
    $favResult = $conn->query("SELECT pet_id FROM favorites WHERE user_id = $uid");
    if ($favResult) {
        while ($fRow = $favResult->fetch_assoc()) {
            $userFavorites[] = $fRow['pet_id'];
        }
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
                    <select name="type"
                        class="px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20 cursor-pointer min-w-[140px]">
                        <option value="">All Types</option>
                        <option value="Dog" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Dog') ? 'selected' : ''; ?>>Dogs</option>
                        <option value="Cat" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Cat') ? 'selected' : ''; ?>>Cats</option>
                        <option value="Other" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Other') ? 'selected' : ''; ?>>Others</option>
                    </select>

                    <select name="gender"
                        class="px-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-paw-accent/20 cursor-pointer min-w-[140px]">
                        <option value="">Any Gender</option>
                        <option value="Male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>

                    <button type="submit"
                        class="px-8 py-3 bg-paw-dark text-white rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-paw-accent transition-colors shadow-lg">
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
                    <div
                        class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-gray-100 flex flex-col h-full">
                        <div class="relative h-72 overflow-hidden">
                            <img src="<?php echo 'uploads/pets/' . rawurlencode($pet['image']); ?>"
                                alt="<?php echo htmlspecialchars($pet['name']); ?>"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php $isFav = in_array($pet['id'], $userFavorites); ?>
                                <button onclick="toggleFav(this, <?php echo $pet['id']; ?>)"
                                    class="absolute top-4 left-4 w-9 h-9 rounded-full flex items-center justify-center transition-all duration-300 z-10 cursor-pointer <?php echo $isFav ? 'bg-red-500 text-white shadow-lg shadow-red-500/30' : 'bg-white/80 backdrop-blur text-gray-400 hover:text-red-500'; ?>"
                                    data-fav="<?php echo $isFav ? '1' : '0'; ?>">
                                    <i data-lucide="heart" class="w-4 h-4 <?php echo $isFav ? 'fill-current' : ''; ?>"></i>
                                </button>
                            <?php endif; ?>
                            <div
                                class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-paw-dark shadow-sm">
                                <?php echo $pet['age']; ?> Years
                            </div>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
                                <span class="text-white font-serif italic text-lg">Click to view details</span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-serif text-3xl font-bold text-paw-dark">
                                    <?php echo htmlspecialchars($pet['name']); ?>
                                </h3>
                                <?php if ($pet['gender'] == 'Male'): ?>
                                    <i data-lucide="mars" class="w-6 h-6 text-blue-400"></i>
                                <?php else: ?>
                                    <i data-lucide="venus" class="w-6 h-6 text-pink-400"></i>
                                <?php endif; ?>
                            </div>
                            <p class="text-paw-accent text-sm font-semibold uppercase tracking-widest mb-4">
                                <?php echo htmlspecialchars($pet['breed']); ?>
                            </p>

                            <p class="text-paw-gray text-sm line-clamp-3 mb-4 flex-1">
                                <?php echo htmlspecialchars($pet['description']); ?>
                            </p>

                            <!-- Poster Info -->
                            <?php if (!empty($pet['poster_username'])): ?>
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
                                        <img src="<?php
                                        $cardImgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($pet['poster_username']);
                                        if (!empty($pet['poster_image'])) {
                                            if (strpos($pet['poster_image'], 'http') === 0) {
                                                $cardImgSrc = $pet['poster_image'];
                                            } else if (file_exists('uploads/users/' . $pet['poster_image'])) {
                                                $cardImgSrc = 'uploads/users/' . rawurlencode($pet['poster_image']);
                                            }
                                        }
                                        echo $cardImgSrc;
                                        ?>" class="w-full h-full object-cover" alt="">
                                    </div>
                                    <span class="text-xs text-paw-gray">Posted by <strong
                                            class="text-paw-dark"><?php echo htmlspecialchars($pet['poster_username']); ?></strong></span>
                                </div>
                            <?php endif; ?>

                            <div class="pt-6 border-t border-gray-100 mt-auto">
                                <div class="flex gap-3">
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet['added_by']): ?>
                                        <a href="pet-details.php?id=<?php echo $pet['id']; ?>"
                                            class="flex-1 py-3 border border-paw-dark text-paw-dark rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark hover:text-white transition-colors">
                                            Manage
                                        </a>
                                        <a href="manage-applications.php"
                                            class="flex-1 py-3 bg-paw-accent text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                            Applications
                                        </a>
                                    <?php else: ?>
                                        <a href="pet-details.php?id=<?php echo $pet['id']; ?>"
                                            class="flex-1 py-3 border border-paw-dark text-paw-dark rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark hover:text-white transition-colors">
                                            Details
                                        </a>
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <a href="adopt-apply.php?pet=<?php echo $pet['id']; ?>"
                                                class="flex-1 py-3 bg-paw-accent text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                                Adopt
                                            </a>
                                        <?php else: ?>
                                            <a href="login.php"
                                                class="flex-1 py-3 bg-paw-accent text-white rounded-xl text-center text-sm font-bold uppercase tracking-widest hover:bg-paw-dark transition-colors shadow-lg shadow-paw-accent/20">
                                                Login to Adopt
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="col-span-full text-center py-20 text-gray-500">
                <p class="text-xl font-bold">No pets found</p>
                <p>Try different search criteria.</p>
            </div>
        <?php endif; ?>

        <?php renderPagination($pagination['currentPage'], $pagination['totalPages'], $paginationUrl); ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    function toggleFav(btn, petId) {
        const formData = new FormData();
        formData.append('pet_id', petId);
        fetch('api/toggle-favorite.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const icon = btn.querySelector('[data-lucide]');
                if (data.favorited) {
                    btn.className = 'absolute top-4 left-4 w-9 h-9 rounded-full flex items-center justify-center transition-all duration-300 z-10 cursor-pointer bg-red-500 text-white shadow-lg shadow-red-500/30';
                    btn.dataset.fav = '1';
                    if (icon) icon.classList.add('fill-current');
                    btn.style.transform = 'scale(1.2)';
                    setTimeout(() => btn.style.transform = '', 200);
                } else {
                    btn.className = 'absolute top-4 left-4 w-9 h-9 rounded-full flex items-center justify-center transition-all duration-300 z-10 cursor-pointer bg-white/80 backdrop-blur text-gray-400 hover:text-red-500';
                    btn.dataset.fav = '0';
                    if (icon) icon.classList.remove('fill-current');
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }
</script>