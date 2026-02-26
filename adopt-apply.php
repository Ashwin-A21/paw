<?php
session_start();
include 'config.php';

if (!isset($_GET['pet'])) {
    header("Location: adopt.php");
    exit();
}

$petId = (int) $_GET['pet'];
$pet = $conn->query("SELECT p.*, u.username as owner_name FROM pets p LEFT JOIN users u ON p.added_by = u.id WHERE p.id=$petId")->fetch_assoc();

if (!$pet) {
    header("Location: adopt.php");
    exit();
}

// Block users from applying to their own pets
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet['added_by']) {
    header("Location: pet-details.php?id=$petId");
    exit();
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = "Please login to apply for adoption.";
    } else {
        $userId = $_SESSION['user_id'];
        $msg = $_POST['message'];

        // Use Prepared Statements for Security
        $stmt = $conn->prepare("SELECT id FROM adoption_applications WHERE user_id=? AND pet_id=?");
        $stmt->bind_param("ii", $userId, $petId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "You have already applied to adopt this pet.";
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO adoption_applications (user_id, pet_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $userId, $petId, $msg);

            if ($stmt->execute()) {
                $message = "success";

                // Notify pet owner about the new application
                if ($pet['added_by'] && $pet['added_by'] != $userId) {
                    include_once 'includes/notify.php';
                    $applicantName = $_SESSION['username'] ?? 'Someone';
                    createNotification(
                        $conn,
                        $pet['added_by'],
                        'adoption_application',
                        $applicantName . ' wants to adopt your pet "' . $pet['name'] . '"! Review their application.',
                        'manage-applications.php'
                    );
                }
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adopt <?php echo htmlspecialchars($pet['name']); ?> - Paw Pal</title>

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
        body {
            background-color: #F9F8F6;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .form-input {
            width: 100%;
            padding: 1rem;
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
                    <a href="adopt.php" class="text-sm uppercase tracking-widest text-paw-accent">Adopt</a>
                    <a href="rescue.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-alert transition-colors">Rescue</a>
                    <a href="centers.php"
                        class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Verified
                        Partners</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-28 pb-20 px-6">
        <div class="max-w-6xl mx-auto">
            <a href="adopt.php" class="inline-flex items-center gap-2 text-paw-accent hover:underline mb-8">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Pets
            </a>

            <?php if ($message === 'success'): ?>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-12 text-center max-w-2xl mx-auto">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="check-circle" class="w-10 h-10 text-green-600"></i>
                    </div>
                    <h2 class="font-serif text-4xl text-green-800 mb-4">Application Submitted!</h2>
                    <p class="text-green-700 mb-6">Thank you for your interest in adopting
                        <?php echo htmlspecialchars($pet['name']); ?>. We'll review your application and contact you soon.
                    </p>
                    <a href="adopt.php"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold">
                        Browse More Pets
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Pet Image -->
                    <div class="rounded-2xl overflow-hidden shadow-xl h-[500px]">
                        <img src="<?php echo file_exists('uploads/pets/' . $pet['image']) ? 'uploads/pets/' . rawurlencode($pet['image']) : 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=800'; ?>"
                            alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-full object-cover">
                    </div>

                    <!-- Pet Details & Form -->
                    <div>
                        <span
                            class="px-4 py-1.5 bg-paw-accent/10 text-paw-accent text-xs uppercase tracking-widest rounded-full">
                            <?php echo $pet['status']; ?>
                        </span>
                        <h1 class="font-serif text-5xl mt-4 mb-4">
                            <?php echo htmlspecialchars($pet['name']); ?>
                        </h1>

                        <div class="flex flex-wrap gap-4 mb-6">
                            <span class="flex items-center gap-2 text-paw-gray">
                                <i data-lucide="paw-print" class="w-4 h-4"></i> <?php echo ucfirst($pet['type']); ?>
                            </span>
                            <span class="text-paw-gray"><?php echo htmlspecialchars($pet['breed']); ?></span>
                            <span class="text-paw-gray"><?php echo htmlspecialchars($pet['age']); ?></span>
                            <span class="text-paw-gray"><?php echo $pet['gender']; ?></span>
                        </div>

                        <p class="text-paw-gray leading-relaxed mb-8">
                            <?php echo nl2br(htmlspecialchars($pet['description'])); ?>
                        </p>

                        <?php if ($error): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="bg-paw-accent/5 border border-paw-accent/20 rounded-xl p-6 text-center">
                                <p class="text-paw-gray mb-4">Please sign in to apply for adoption</p>
                                <a href="login.php"
                                    class="inline-flex items-center gap-2 px-8 py-3 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                                    Sign In
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="bg-white rounded-2xl p-8 shadow-lg">
                                <h3 class="font-serif text-2xl mb-6">Apply to Adopt
                                    <?php echo htmlspecialchars($pet['name']); ?>
                                </h3>

                                <div class="mb-6">
                                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Tell
                                        us about
                                        yourself</label>
                                    <textarea name="message" required rows="5"
                                        placeholder="Why would you be a great pet parent? Tell us about your home, experience with pets, and why you want to adopt..."
                                        class="form-input resize-none"></textarea>
                                </div>

                                <button type="submit"
                                    class="w-full py-4 bg-paw-accent text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-dark transition-colors">
                                    Submit Application
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>