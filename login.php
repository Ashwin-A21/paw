<?php
session_start();
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = "Invalid security token. Please try again.";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($password === $row['password']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['username'] = $row['username'];

                if ($row['role'] == 'admin')
                    header("Location: admin/index.php");
                else if ($row['role'] == 'volunteer' || $row['role'] == 'rescuer')
                    header("Location: volunteer/index.php");
                else
                    header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
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
    <title>Login - Paw Pal</title>

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
        body {
            background-color: #F9F8F6;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1px solid #e5e5e5;
            border-radius: 0.75rem;
            background: white;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #D4A373;
            box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.1);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased min-h-screen flex bg-paw-bg transition-colors duration-300">

    <!-- Left: Image -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-paw-dark/60 to-paw-accent/40 z-10"></div>
        <img src="https://images.unsplash.com/photo-1587300003388-59208cc962cb?q=80&w=1200&auto=format&fit=crop"
            alt="Happy Dog" class="w-full h-full object-cover">
        <div class="absolute bottom-12 left-12 right-12 z-20 text-white">
            <h2 class="font-serif text-5xl mb-4">Welcome Back</h2>
            <p class="text-white/80 text-lg">Continue making a difference for animals in need.</p>
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
                <h1 class="font-serif text-4xl mb-2">Sign In</h1>
                <p class="text-paw-gray">Enter your credentials to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?php echo csrfField(); ?>
                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Email</label>
                    <input type="email" name="email" required placeholder="you@example.com" class="form-input">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="form-input">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 accent-paw-accent rounded">
                        <span class="text-paw-gray">Remember me</span>
                    </label>
                    <a href="#" class="text-paw-accent hover:underline">Forgot password?</a>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-paw-gray">
                    Don't have an account?
                    <a href="register.php" class="text-paw-accent font-semibold hover:underline">Sign Up</a>
                </p>
            </div>

            <div class="mt-12 pt-8 border-t border-gray-200 text-center text-xs text-paw-gray">
                <p>By signing in, you agree to our <a href="#" class="underline">Terms</a> and <a href="#"
                        class="underline">Privacy Policy</a></p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>