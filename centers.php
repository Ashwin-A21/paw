<?php
session_start();
include 'config.php';

// Filter by Type
$typeFilter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

// Query ALL Verified Partners
$sql = "SELECT *, 
    CASE 
        WHEN role = 'organization' THEN 'Organization'
        ELSE 'Individual' 
    END AS effective_type
    FROM users WHERE is_verified = 1 AND role != 'admin'";

if (!empty($typeFilter)) {
    if ($typeFilter === 'Individual') {
        $sql .= " AND role != 'organization'";
    } else {
        // Assuming typeFilter 'Organization' maps to role 'organization'
        // If exact match needed:
        if ($typeFilter === 'Organization') {
            $sql .= " AND role = 'organization'";
        }
    }
}
$sql .= " ORDER BY lives_saved DESC";

$centers = $conn->query($sql);

// Function to calculate Paw Score
function calculatePawScore($conn, $userId, $livesSaved)
{
    $score = 0;

    // Rescues: 10 points each
    $score += $livesSaved * 10;

    // Rescue reports assigned & completed: 15 points each
    $rescuesDone = $conn->query("SELECT COUNT(*) as cnt FROM rescue_reports WHERE assigned_to = $userId AND status IN ('Rescued','Closed')");
    if ($rescuesDone) {
        $row = $rescuesDone->fetch_assoc();
        $score += ($row['cnt'] ?? 0) * 15;
    }

    // Rescue reports filed: 5 points each
    $reportsFiled = $conn->query("SELECT COUNT(*) as cnt FROM rescue_reports WHERE reporter_id = $userId");
    if ($reportsFiled) {
        $row = $reportsFiled->fetch_assoc();
        $score += ($row['cnt'] ?? 0) * 5;
    }

    // Pets listed for adoption: 5 points each
    $petsListed = $conn->query("SELECT COUNT(*) as cnt FROM pets WHERE added_by = $userId");
    if ($petsListed) {
        $row = $petsListed->fetch_assoc();
        $score += ($row['cnt'] ?? 0) * 5;
    }

    // Blog posts: 3 points each
    $blogs = $conn->query("SELECT COUNT(*) as cnt FROM blogs WHERE author_id = $userId AND status='approved'");
    if ($blogs) {
        $row = $blogs->fetch_assoc();
        $score += ($row['cnt'] ?? 0) * 3;
    }

    return $score;
}

// Function to get badge info based on score
function getBadgeInfo($score)
{
    if ($score >= 200)
        return ['title' => 'Elite Rescuer', 'color' => 'bg-purple-500 text-white', 'icon' => 'crown'];
    if ($score >= 150)
        return ['title' => 'Legend', 'color' => 'bg-yellow-500 text-white', 'icon' => 'trophy'];
    if ($score >= 100)
        return ['title' => 'Hero', 'color' => 'bg-red-500 text-white', 'icon' => 'shield'];
    if ($score >= 50)
        return ['title' => 'Guardian', 'color' => 'bg-blue-500 text-white', 'icon' => 'star'];
    if ($score >= 20)
        return ['title' => 'Rising Star', 'color' => 'bg-green-500 text-white', 'icon' => 'trending-up'];
    return ['title' => 'Newcomer', 'color' => 'bg-gray-400 text-white', 'icon' => 'sparkles'];
}

$basePath = '';
include 'includes/header.php';
?>

<!-- Hero -->
<section class="pt-32 pb-12 px-6 relative overflow-hidden bg-paw-bg/30">
    <div class="absolute top-20 right-0 w-96 h-96 bg-paw-verified/10 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto text-center relative z-10">
        <div class="w-16 h-16 bg-paw-verified/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <i data-lucide="badge-check" class="w-8 h-8 text-paw-verified"></i>
        </div>
        <p class="text-sm uppercase tracking-[0.3em] text-paw-verified mb-4">Trusted & Verified</p>
        <h1 class="font-serif text-5xl md:text-6xl text-paw-dark mb-6">
            Verified Paw <span class="italic text-paw-verified">Partners</span>
        </h1>
        <p class="text-paw-gray text-lg max-w-2xl mx-auto mb-10">
            Meet our heroes. These individuals and organizations dedicate their lives to rescuing and caring for
            animals.
            Top performers are recognized for their impact.
        </p>

        <!-- Category Filter -->
        <div class="flex flex-wrap justify-center gap-4">
            <a href="centers.php"
                class="px-6 py-2 rounded-full text-sm font-bold uppercase tracking-widest transition-all <?php echo empty($typeFilter) ? 'bg-paw-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-100'; ?>">
                All
            </a>
            <a href="centers.php?type=Individual"
                class="px-6 py-2 rounded-full text-sm font-bold uppercase tracking-widest transition-all <?php echo $typeFilter === 'Individual' ? 'bg-paw-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-100'; ?>">
                Individual
            </a>
            <a href="centers.php?type=Organization"
                class="px-6 py-2 rounded-full text-sm font-bold uppercase tracking-widest transition-all <?php echo $typeFilter === 'Organization' ? 'bg-paw-dark text-white' : 'bg-white text-gray-500 hover:bg-gray-100'; ?>">
                Organization
            </a>
        </div>
    </div>
</section>

<!-- Centers Grid -->
<section class="py-12 px-6">
    <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $rank = 1;
            while ($center = $centers->fetch_assoc()):
                $isTop3 = $rank <= 3;
                $pawScore = calculatePawScore($conn, $center['id'], $center['lives_saved']);
                $badge = getBadgeInfo($pawScore);

                // Determine display name: prefer organization_name, then username
                $displayName = !empty($center['organization_name']) ? $center['organization_name'] : $center['username'];
                
                $effectiveType = $center['effective_type'] ?? 'Individual';
                $userRole = !empty($center['role']) ? ucfirst($center['role']) : 'Member';
                ?>
                <div
                    class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 relative overflow-hidden group flex flex-col">

                    <!-- Rank Badge (Top 3) -->
                    <?php if ($isTop3): ?>
                        <div
                            class="absolute top-0 left-0 bg-yellow-400 text-white px-4 py-1 rounded-br-2xl text-xs font-bold uppercase tracking-widest shadow-sm z-20">
                            #<?php echo $rank; ?> Top Rescuer
                        </div>
                    <?php endif; ?>

                    <div class="relative z-10 flex-1">
                        <!-- Header with Badge -->
                        <div class="flex items-center gap-4 mb-4">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <img src="<?php
                                $imgSrc = 'https://api.dicebear.com/9.x/toon-head/svg?seed=' . urlencode($center['username']);

                                if (!empty($center['profile_image'])) {
                                    if (strpos($center['profile_image'], 'http') === 0) {
                                        $imgSrc = $center['profile_image'];
                                    } else {
                                        if (file_exists('uploads/users/' . $center['profile_image'])) {
                                            $imgSrc = 'uploads/users/' . rawurlencode($center['profile_image']);
                                        }
                                    }
                                }
                                echo $imgSrc;
                                ?>"
                                    class="w-full h-full object-cover rounded-2xl border-2 <?php echo $isTop3 ? 'border-yellow-400' : 'border-gray-100'; ?>">

                                <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1 shadow-sm">
                                    <i data-lucide="badge-check" class="w-5 h-5 text-paw-verified fill-current"></i>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-serif text-xl font-bold leading-tight text-paw-dark">
                                    <?php echo htmlspecialchars($displayName); ?>
                                </h3>
                                <div class="flex flex-col gap-1 mt-1">
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if ($effectiveType === 'Organization'): ?>
                                            <span
                                                class="text-[10px] uppercase tracking-widest font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                                                Organization
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="text-[10px] uppercase tracking-widest font-bold px-2 py-0.5 rounded-full bg-gray-100 text-paw-dark">
                                                <?php echo $userRole; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paw Score Badge -->
                        <div class="flex items-center gap-2 mb-6">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest <?php echo $badge['color']; ?> shadow-sm">
                                <i data-lucide="<?php echo $badge['icon']; ?>" class="w-3 h-3"></i>
                                <?php echo $badge['title']; ?>
                            </span>
                            <span class="text-xs font-bold text-paw-dark bg-gray-100 px-2.5 py-1 rounded-full">
                                <?php echo $pawScore; ?> pts
                            </span>
                        </div>

                        <div class="space-y-3 mb-8">
                            <div class="flex items-start gap-3 text-paw-gray">
                                <i data-lucide="map-pin" class="w-4 h-4 flex-shrink-0 mt-1 text-paw-verified"></i>
                                <span class="text-sm">
                                    <?php echo htmlspecialchars($center['address'] ?? 'Location not disclosed'); ?>
                                </span>
                            </div>
                            <?php if (!empty($center['phone'])): ?>
                                <div class="flex items-center gap-3 text-paw-gray">
                                    <i data-lucide="phone" class="w-4 h-4 flex-shrink-0 text-paw-verified"></i>
                                    <span class="text-sm">
                                        <?php echo htmlspecialchars($center['phone']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="bg-gray-50 rounded-2xl p-4 mb-6 border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total Rescues</p>
                            <p class="font-serif text-2xl font-bold text-paw-dark"><?php echo $center['lives_saved']; ?></p>
                        </div>
                        <i data-lucide="heart-handshake" class="w-8 h-8 text-paw-accent/20"></i>
                    </div>

                    <div class="flex gap-3 mt-auto">
                        <?php if ($effectiveType === 'Organization'): ?>
                            <a href="donate.php?id=<?php echo $center['id']; ?>"
                                class="flex-1 py-3 bg-paw-verified text-white rounded-xl text-xs uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors text-center shadow-lg shadow-paw-verified/20 flex items-center justify-center gap-2">
                                <i data-lucide="heart" class="w-3 h-3"></i> Donate
                            </a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo $center['email']; ?>"
                            class="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl text-xs uppercase tracking-widest font-bold hover:bg-gray-50 transition-colors text-center">
                            Contact
                        </a>
                    </div>
                </div>
                <?php
                $rank++;
            endwhile;
            ?>
        </div>

        <?php if ($centers->num_rows === 0): ?>
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="search-x" class="w-10 h-10 text-gray-400"></i>
                </div>
                <h3 class="font-serif text-2xl text-gray-400 mb-2">No verified partners found.</h3>
                <p class="text-gray-400">Be the first to join our trusted network!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>