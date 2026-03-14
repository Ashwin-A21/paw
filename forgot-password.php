<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = "Invalid security token. Please try again.";
    } else {
        $email = $_POST['email'];

        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));
            
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $token, $expiry, $email);
            
            if ($update_stmt->execute()) {
                // Send email
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
                
                $subject = "Password Reset - Paw Pal";
                $message = "Hi " . $user['username'] . ",\n\n";
                $message .= "You have requested to reset your password. Click the link below to set a new password:\n";
                $message .= $reset_link . "\n\n";
                $message .= "This link will expire in 1 hour.\n";
                $message .= "If you did not request this, please ignore this email.";
                
                $headers = "From: noreply@pawpal.com\r\n";
                
                // Attempt to send email. If it fails (due to XAMPP no SMTP setup), show link for testing.
                if (@mail($email, $subject, $message, $headers)) {
                    $success = "Password reset instructions have been sent to your email.";
                } else {
                    $success = "Password reset instructions stored locally. (Local Testing: <a href='$reset_link' class='underline font-bold'>Click here to reset password</a>)";
                }
            } else {
                $error = "Something went wrong. Please try again later.";
            }
            $update_stmt->close();
        } else {
            // Do not reveal if email exists or not, standard security practice
            $success = "If your email is registered, you will receive password reset instructions.";
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
    <title>Forgot Password - Paw Pal</title>

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
            <h2 class="font-serif text-5xl mb-4">Reset Password</h2>
            <p class="text-white/80 text-lg">We'll help you get back to your account securely.</p>
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
                <h1 class="font-serif text-4xl mb-2">Forgot Password</h1>
                <p class="text-paw-gray">Enter your email and we'll send you a link to reset your password.</p>
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
            <?php else: ?>

            <form method="POST" class="space-y-6">
                <?php echo csrfField(); ?>
                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email</label>
                    <input type="email" name="email" required placeholder="you@example.com" class="form-input">
                </div>

                <button type="submit"
                    class="w-full py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                    Send Reset Link
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
