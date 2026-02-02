<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: centers.php");
    exit();
}

$userId = (int) $_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id=$userId AND is_verified=1")->fetch_assoc();

if (!$user) {
    header("Location: centers.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to
        <?php echo htmlspecialchars($user['username']); ?> - Paw Pal
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
                        'paw-verified': '#00A884', // WhatsApp Green
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

        .verified-badge {
            background-color: #00A884;
            color: white;
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased pt-20 bg-paw-bg transition-colors duration-300">

    <div class="max-w-xl mx-auto px-6 py-12">
        <div class="flex justify-between items-center mb-6">
            <a href="centers.php"
                class="inline-flex items-center gap-2 text-paw-gray hover:text-paw-accent transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Verified Centers
            </a>
        </div>

        <div
            class="bg-white rounded-3xl p-8 shadow-xl text-center relative overflow-hidden transition-colors duration-300">
            <div class="absolute top-0 left-0 w-full h-2 bg-paw-verified"></div>

            <div
                class="w-24 h-24 bg-paw-dark text-white rounded-full flex items-center justify-center text-4xl font-serif font-bold mx-auto mb-4 border-4 border-white shadow-lg">
                <?php echo substr($user['username'], 0, 1); ?>
                <div class="absolute bottom-1 right-0 w-8 h-8 rounded-full bg-white flex items-center justify-center">
                    <i data-lucide="badge-check" class="w-6 h-6 text-paw-verified fill-current"></i>
                </div>
            </div>

            <h1 class="font-serif text-3xl font-bold mb-1 flex items-center justify-center gap-2">
                <?php echo htmlspecialchars($user['username']); ?>
                <i data-lucide="badge-check" class="w-6 h-6 text-paw-verified"></i>
            </h1>
            <p class="text-xs uppercase tracking-widest text-paw-verified font-bold mb-6">Verified Paw Partner</p>

            <p class="text-paw-gray mb-8">
                Your donation directly supports this verified partner in their mission to rescue and care for animals.
            </p>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 text-left">
                <div class="flex items-center gap-3 mb-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
                    <p class="font-semibold text-blue-800 text-sm uppercase tracking-wide">Safe
                        Donation</p>
                </div>
                <p class="text-sm text-blue-700">This user has been manually verified by Paw Pal
                    administrators as a
                    trusted entity.</p>
            </div>

            <div class="space-y-6">
                <!-- Preset Amounts -->
                <div>
                    <label class="block text-left text-sm uppercase tracking-widest font-semibold mb-3">Select
                        Amount</label>
                    <div class="grid grid-cols-4 gap-3 mb-3">
                        <button type="button"
                            class="py-3 border border-paw-verified text-paw-verified font-bold rounded-xl hover:bg-paw-verified hover:text-white transition-colors focus:ring-2 ring-paw-verified ring-offset-2">$10</button>
                        <button type="button"
                            class="py-3 border border-paw-verified bg-paw-verified text-white font-bold rounded-xl shadow-md">$25</button>
                        <button type="button"
                            class="py-3 border border-paw-verified text-paw-verified font-bold rounded-xl hover:bg-paw-verified hover:text-white transition-colors focus:ring-2 ring-paw-verified ring-offset-2">$50</button>
                        <button type="button"
                            class="py-3 border border-gray-200 text-gray-500 font-bold rounded-xl hover:border-paw-verified hover:text-paw-verified transition-colors">Custom</button>
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-left text-sm uppercase tracking-widest font-semibold mb-3">Payment
                        Method</label>
                    <div class="space-y-3">
                        <label
                            class="flex items-center gap-4 p-4 border border-paw-verified bg-green-50 rounded-xl cursor-pointer">
                            <input type="radio" name="payment" checked
                                class="w-5 h-5 text-paw-verified focus:ring-paw-verified">
                            <i data-lucide="credit-card" class="w-6 h-6 text-paw-verified"></i>
                            <span class="font-semibold text-paw-dark">Credit / Debit Card</span>
                        </label>
                        <label
                            class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-paw-verified transition-colors">
                            <input type="radio" name="payment"
                                class="w-5 h-5 text-paw-verified focus:ring-paw-verified">
                            <i data-lucide="smartphone" class="w-6 h-6 text-gray-500"></i>
                            <span class="font-semibold text-gray-600">UPI / Wallet</span>
                        </label>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="p-4 bg-gray-50 rounded-xl text-left space-y-2 text-sm border border-gray-100">
                    <div class="flex justify-between">
                        <span class="text-paw-gray">To:</span>
                        <span
                            class="font-medium text-paw-dark"><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-paw-gray">Email:</span>
                        <span class="font-medium"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button"
                        onclick="alert('Thank you! This is a demo, but in a real app, this would process a $25 donation securely.')"
                        class="w-full py-4 bg-paw-verified text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:opacity-90 transition-opacity flex items-center justify-center gap-2 shadow-lg shadow-green-200">
                        <i data-lucide="heart" class="w-4 h-4 fill-current"></i> Donate $25 Now
                    </button>
                    <div class="flex justify-center items-center gap-2 mt-4 text-xs text-paw-gray">
                        <i data-lucide="lock" class="w-3 h-3"></i> 256-bit SSL Encrypted Payment
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>