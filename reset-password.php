<?php
session_start();
include 'config.php';

$error = "";
$success = "";
$valid_token = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = "Invalid or missing token.";
} else {
    // Verify token
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $valid_token = true;
    } else {
        $error = "Your password reset link is invalid or has expired.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    // Validate CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = "Invalid security token. Please try again.";
        $valid_token = false;
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 4) {
            $error = "Password must be at least 4 characters.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Update password & clear token
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
            // NOTE: Existing project relies on plaintext mapping based on login.php `if ($password === $row['password'])`. 
            // In a real-world scenario we would use password_hash. I will use plaintext to match the current app configuration.
            $update_stmt->bind_param("ss", $password, $token);
            
            if ($update_stmt->execute()) {
                $success = "Your password has been successfully reset. You can now login.";
                $valid_token = false; // Hide the form on success
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Paw Pal</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        body { background-color: #F9F8F6; }
        .form-input {
            width: 100%; padding: 1rem 1.25rem; border: 1px solid #e5e5e5;
            border-radius: 0.75rem; background: white; transition: border-color 0.3s, box-shadow 0.3s;
            font-size: 1rem;
        }
        .form-input:focus {
            outline: none; border-color: #D4A373; box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.1);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased min-h-screen flex bg-paw-bg transition-colors duration-300">

    <!-- Left: Image -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-paw-dark/60 to-paw-accent/40 z-10"></div>
        <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?q=80&w=1200&auto=format&fit=crop"
            alt="Dog Looking up" class="w-full h-full object-cover">
        <div class="absolute bottom-12 left-12 right-12 z-20 text-white">
            <h2 class="font-serif text-5xl mb-4">Set New Password</h2>
            <p class="text-white/80 text-lg">Create a new secure password for your account.</p>
        </div>
        <a href="index.php" class="absolute top-8 left-8 z-20 font-serif text-2xl italic font-bold text-white">
            Paw Pal<span class="text-paw-accent">.</span>
        </a>
    </div>

    <!-- Right: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 relative">

        <div class="w-full max-w-md">
            <a href="index.php"
                class="lg:hidden font-serif text-2xl italic font-bold text-paw-dark block text-center mb-8">
                Paw Pal<span class="text-paw-accent">.</span>
            </a>

            <div class="text-center mb-10">
                <a href="login.php" class="inline-flex items-center text-sm font-semibold text-paw-gray hover:text-paw-dark mb-6">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Login
                </a>
                <h1 class="font-serif text-4xl mb-2">Create Password</h1>
                <p class="text-paw-gray">Enter your new password below to reset.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?php echo $success; ?>
                </div>
                <div class="mt-8 text-center">
                    <a href="login.php" class="inline-flex items-center gap-2 px-8 py-3 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                        Proceed to Login
                    </a>
                </div>
            <?php elseif ($valid_token): ?>

            <form method="POST" class="space-y-6">
                <?php echo csrfField(); ?>
                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">New Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="form-input">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••" class="form-input">
                </div>

                <button type="submit"
                    class="w-full py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                    Reset Password
                </button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
