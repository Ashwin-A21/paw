<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);

    $role = $_POST['role'] ?? 'user';

    // Validate role
    $allowedRoles = ['user', 'volunteer', 'rescuer'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'user';
    }

    $check = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check);

    if ($result->num_rows > 0) {
        $error = "Email already registered.";
    } else {
        $sql = "INSERT INTO users (username, email, password, role, phone, gender, dob) 
                VALUES ('$username', '$email', '$password', '$role', '$phone', '$gender', '$dob')";
        if ($conn->query($sql) === TRUE) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Paw Pal</title>

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

        .role-card {
            transition: all 0.3s;
        }

        .role-card:hover {
            transform: translateY(-2px);
        }

        .role-card.selected {
            border-color: #D4A373;
            background: rgba(212, 163, 115, 0.05);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased min-h-screen flex bg-paw-bg transition-colors duration-300">

    <!-- Left: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 relative">

        <div class="w-full max-w-md">
            <a href="index.php"
                class="font-serif text-2xl italic font-bold text-paw-dark block text-center lg:text-left mb-8">
                Paw Pal<span class="text-paw-accent">.</span>
            </a>

            <div class="mb-8">
                <h1 class="font-serif text-4xl mb-2">Join the Movement</h1>
                <p class="text-paw-gray">Create your account to help rescue animals</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <?php echo $success; ?> <a href="login.php" class="font-semibold underline">Login here</a>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Full Name</label>
                        <input type="text" name="username" required placeholder="John Doe" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">DOB</label>
                        <input type="date" name="dob" required class="form-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Gender</label>
                        <select name="gender" required class="form-input">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Phone</label>
                        <input type="tel" name="phone" required placeholder="123-456-7890" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Email</label>
                    <input type="email" name="email" required placeholder="you@example.com" class="form-input">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-2">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="form-input">
                </div>

                <div>
                    <label class="block text-sm uppercase tracking-widest font-semibold mb-3">I want to</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="role-card cursor-pointer border-2 border-gray-200 rounded-xl p-4 text-center"
                            onclick="selectRole(this, 'user')">
                            <input type="radio" name="role" value="user" checked class="hidden">
                            <i data-lucide="heart" class="w-6 h-6 mx-auto mb-2 text-paw-accent"></i>
                            <span class="text-sm font-medium block">Adopt</span>
                        </label>
                        <label class="role-card cursor-pointer border-2 border-gray-200 rounded-xl p-4 text-center"
                            onclick="selectRole(this, 'volunteer')">
                            <input type="radio" name="role" value="volunteer" class="hidden">
                            <i data-lucide="users" class="w-6 h-6 mx-auto mb-2 text-blue-500"></i>
                            <span class="text-sm font-medium block">Volunteer</span>
                        </label>
                        <label class="role-card cursor-pointer border-2 border-gray-200 rounded-xl p-4 text-center"
                            onclick="selectRole(this, 'rescuer')">
                            <input type="radio" name="role" value="rescuer" class="hidden">
                            <i data-lucide="siren" class="w-6 h-6 mx-auto mb-2 text-paw-alert"></i>
                            <span class="text-sm font-medium block">Rescue</span>
                        </label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-paw-dark text-white rounded-xl text-sm uppercase tracking-widest font-bold hover:bg-paw-accent transition-colors">
                    Create Account
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-paw-gray">
                    Already have an account?
                    <a href="login.php" class="text-paw-accent font-semibold hover:underline">Sign In</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Right: Image -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-bl from-paw-accent/40 to-paw-dark/60 z-10"></div>
        <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?q=80&w=1200&auto=format&fit=crop"
            alt="Rescued Animals" class="w-full h-full object-cover">
        <div class="absolute bottom-12 left-12 right-12 z-20 text-white">
            <h2 class="font-serif text-5xl mb-4">Make a Difference</h2>
            <p class="text-white/80 text-lg">Join thousands helping rescue and rehome animals every day.</p>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Select first role by default
        document.querySelector('.role-card').classList.add('selected');

        function selectRole(el, role) {
            document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
        }


    </script>
</body>

</html>