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
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopt a Pet - Paw Pal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .pet-card {
            transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1), box-shadow 0.5s;
        }

        .pet-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .pet-card:hover img {
            transform: scale(1.05);
        }

        .pet-card img {
            transition: transform 1s cubic-bezier(0.19, 1, 0.22, 1);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased bg-paw-bg transition-colors duration-300">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass shadow-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex justify-between items-center h-20">
                <a href="index.php" class="font-serif text-2xl italic font-bold">Paw Pal<span
                        class="text-paw-accent">.</span></a>
                <div class="hidden md:flex items-center space-x-10">
                    <a href="index.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Home</a>
                    <a href="adopt.php"
                        class="text-sm uppercase tracking-widest text-paw-accent transition-colors">Adopt</a>
                    <a href="rescue.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-alert transition-colors">Rescue</a>
                    <a href="centers.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Verified
                        Partners</a>
                    <a href="blogs.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Success
                        Stories</a>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $dashboardUrl = 'public/index.php';
                        if ($_SESSION['role'] === 'admin')
                            $dashboardUrl = 'admin/index.php';
                        elseif ($_SESSION['role'] === 'volunteer' || $_SESSION['role'] === 'rescuer')
                            $dashboardUrl = 'volunteer/index.php';
                        ?>
                        <a href="<?php echo $dashboardUrl; ?>"
                            class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Dashboard</a>
                        <a href="public/profile.php"
                            class="relative w-10 h-10 rounded-full overflow-hidden border-2 border-paw-accent hover:border-paw-dark transition-colors group">
                            <img src="<?php
                            $username = $currentUser['username'] ?? 'User';
                            $imgSrc = 'https://ui-avatars.com/api/?name=' . urlencode($username);
                            if (!empty($currentUser['profile_image'])) {
                                if (strpos($currentUser['profile_image'], 'http') === 0) {
                                    $imgSrc = $currentUser['profile_image'];
                                } else {
                                    $basePath = 'uploads/users/';
                                    if (file_exists($basePath . $currentUser['profile_image'])) {
                                        $imgSrc = $basePath . htmlspecialchars($currentUser['profile_image']);
                                    }
                                }
                            }
                            echo $imgSrc;
                            ?>" class="w-full h-full object-cover">
                        </a>
                        <a href="logout.php"
                            class="group relative px-6 py-2.5 bg-paw-dark text-white rounded-full overflow-hidden flex items-center justify-center">
                            <span class="relative z-10 text-xs font-bold uppercase tracking-widest">Logout</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="text-sm uppercase tracking-widest hover:text-paw-accent">Login</a>
                        <a href="register.php"
                            class="px-6 py-2 bg-paw-dark text-white rounded-full text-xs uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">Sign
                            Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-16 px-6">
        <div class="max-w-7xl mx-auto text-center">
            <p class="text-sm uppercase tracking-[0.3em] text-paw-accent mb-4">Find Your Companion</p>
            <h1 class="font-serif text-5xl md:text-7xl text-paw-dark mb-6">
                Adopt <span class="italic">Love</span>
            </h1>
            <p class="text-paw-gray max-w-xl mx-auto text-lg">
                Every pet deserves a home. Browse our available animals and take the first step toward saving a life.
            </p>
        </div>
    </section>

    <!-- Filters & Search -->
    <section class="pb-8 px-6">
        <div class="max-w-7xl mx-auto">
            <form method="GET"
                class="bg-white rounded-2xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-end border border-gray-100">
                <!-- Search -->
                <div class="md:col-span-4 relative group">
                    <label
                        class="block text-xs uppercase tracking-widest font-semibold mb-2 text-paw-gray">Search</label>
                    <input type="text" name="search" placeholder="Search by name, breed..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        class="w-full pl-12 pr-4 py-3 bg-white border border-gray-100 rounded-xl focus:outline-none focus:border-paw-accent transition-all shadow-sm group-hover:shadow-md">
                    <i data-lucide="search"
                        class="absolute left-4 top-12 w-5 h-5 text-paw-gray group-hover:text-paw-accent transition-colors"></i>
                </div>

                <!-- Type Filter -->
                <div class="md:col-span-3">
                    <label class="block text-xs uppercase tracking-widest font-semibold mb-2 text-paw-gray">Pet
                        Type</label>
                    <div class="relative">
                        <select name="type"
                            class="w-full pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl appearance-none focus:outline-none focus:border-paw-accent transition-colors cursor-pointer">
                            <option value="">Any Pet Type</option>
                            <option value="Dog" <?php echo isset($_GET['type']) && $_GET['type'] === 'Dog' ? 'selected' : ''; ?>>Dog</option>
                            <option value="Cat" <?php echo isset($_GET['type']) && $_GET['type'] === 'Cat' ? 'selected' : ''; ?>>Cat</option>
                            <option value="Bird" <?php echo isset($_GET['type']) && $_GET['type'] === 'Bird' ? 'selected' : ''; ?>>Bird</option>
                            <option value="Other" <?php echo isset($_GET['type']) && $_GET['type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <i data-lucide="chevron-down"
                            class="absolute right-4 top-3.5 w-4 h-4 text-paw-gray pointer-events-none"></i>
                    </div>
                </div>

                <!-- Gender Filter -->
                <div class="md:col-span-3">
                    <label
                        class="block text-xs uppercase tracking-widest font-semibold mb-2 text-paw-gray">Gender</label>
                    <div class="relative">
                        <select name="gender"
                            class="w-full pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl appearance-none focus:outline-none focus:border-paw-accent transition-colors cursor-pointer">
                            <option value="">Any Gender</option>
                            <option value="Male" <?php echo isset($_GET['gender']) && $_GET['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo isset($_GET['gender']) && $_GET['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                        <i data-lucide="chevron-down"
                            class="absolute right-4 top-3.5 w-4 h-4 text-paw-gray pointer-events-none"></i>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full md:w-auto px-8 py-3 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors col-span-full md:col-span-2">
                    Find Pet
                </button>
            </form>
        </div>
    </section>

    <!-- Pets Grid -->
    <section class="pb-20 pt-8 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php if ($pets->num_rows > 0): ?>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <article class="pet-card bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100">
                            <div class="h-72 overflow-hidden relative">
                                <img src="<?php echo $pet['image'] && file_exists('uploads/pets/' . $pet['image']) ? 'uploads/pets/' . $pet['image'] : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600'; ?>"
                                    alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-full object-cover">
                                <div
                                    class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide text-paw-dark shadow-sm">
                                    <?php echo $pet['status']; ?>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-serif text-2xl font-bold">
                                        <?php echo htmlspecialchars($pet['name']); ?>
                                    </h3>
                                    <span
                                        class="px-3 py-1 bg-paw-accent/10 text-paw-accent text-xs uppercase tracking-wide rounded-full font-bold">
                                        <?php echo $pet['gender']; ?>
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-y-2 gap-x-4 text-sm text-paw-gray mb-4">
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="paw-print" class="w-4 h-4 text-paw-accent"></i>
                                        <?php echo ucfirst($pet['type']); ?>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="dna" class="w-4 h-4 text-paw-accent"></i>
                                        <?php echo htmlspecialchars($pet['breed']); ?>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-4 h-4 text-paw-accent"></i>
                                        <?php echo htmlspecialchars($pet['age']); ?>
                                    </span>
                                </div>
                                <p class="text-paw-gray text-sm mb-6 line-clamp-2 leading-relaxed">
                                    <?php echo htmlspecialchars(substr($pet['description'], 0, 100)); ?>...
                                </p>
                                <a href="adopt-apply.php?pet=<?php echo $pet['id']; ?>"
                                    class="w-full block text-center py-3 border border-paw-dark text-paw-dark rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark hover:text-white transition-colors">
                                    Meet <?php echo htmlspecialchars($pet['name']); ?>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-gray-100">
                        <div class="w-20 h-20 bg-paw-accent/10 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="search-x" class="w-10 h-10 text-paw-accent"></i>
                        </div>
                        <h3 class="font-serif text-3xl mb-4">No Pets Found</h3>
                        <p class="text-paw-gray mb-8">We couldn't find any pets matching your criteria.
                        </p>
                        <a href="adopt.php" class="text-paw-accent hover:underline font-semibold">Clear all filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-paw-dark text-white">
        <div class="max-w-4xl mx-auto text-center px-6">
            <h2 class="font-serif text-4xl md:text-5xl mb-6">Can't Adopt?</h2>
            <p class="text-white/70 mb-8 text-lg">You can still make a difference by volunteering, fostering, or
                donating to help rescue animals.</p>
            <a href="rescue.php"
                class="inline-flex items-center gap-2 px-8 py-4 bg-paw-accent text-white rounded-full text-sm uppercase tracking-widest font-bold hover:bg-white hover:text-paw-dark transition-colors">
                Report a Rescue <i data-lucide="siren" class="w-4 h-4"></i>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-paw-bg border-t border-gray-200 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center text-sm text-paw-gray">
            <p>&copy; 2024 Paw Pal. All rights reserved.</p>
            <p>Built with <i data-lucide="heart" class="inline w-4 h-4 text-paw-alert"></i> for animals</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>