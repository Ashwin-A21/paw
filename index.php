<?php
session_start();
include 'config.php';

// Get stats for display
$petsResult = $conn->query("SELECT COUNT(*) as count FROM pets WHERE status='Available'");
$petsCount = $petsResult ? $petsResult->fetch_assoc()['count'] : 0;

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
    <title>Paw Pal | Rescue, Adopt, & Connect</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

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
                        'paw-accent': '#D4A373', /* Warm Biscuit/Latte Color */
                        'paw-alert': '#E07A5F',  /* Muted Red for Emergency */
                        'paw-gray': '#9D958F',
                        'paw-card': '#FFFFFF',
                    },
                    fontFamily: {
                        serif: ['"Cormorant Garamond"', 'serif'],
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    backgroundImage: {
                        'noise': "url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPgo8cmVjdCB3aWR0aD0iNCIgaGVpZ2h0PSI0IiBmaWxsPSIjZmZmIi8+CjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNjY2MiIG9wYWNpdHk9IjAuMiIvPgo8L3N2Zz4=')",
                    }
                }
            }
        }
    </script>
    <script>
        // Force Light Mode Logic - Clear any dark mode persistence
        if (localStorage.getItem('color-theme') === 'dark') {
            localStorage.removeItem('color-theme');
        }
        document.documentElement.classList.remove('dark');
    </script>

    <style>
        body {
            background-color: #F9F8F6;
            overflow-x: hidden;
        }

        /* Utility Classes */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* Hide scrollbar for cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #F9F8F6;
        }

        ::-webkit-scrollbar-thumb {
            background: #D1CEC7;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #D4A373;
        }

        .image-hover-container {
            overflow: hidden;
        }

        .image-hover-container img {
            transition: transform 1.2s cubic-bezier(0.19, 1, 0.22, 1);
        }

        .image-hover-container:hover img {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="font-sans text-paw-dark antialiased selection:bg-paw-accent selection:text-white">

    <div class="fixed inset-0 pointer-events-none opacity-40 bg-noise z-0 mix-blend-multiply"></div>

    <nav class="fixed w-full z-50 transition-all duration-300 top-0" id="navbar">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex justify-between items-center h-24">
                <a href="index.php" class="magnetic-item relative z-10 group" data-cursor-text="Home">
                    <span class="font-serif text-3xl italic font-bold tracking-tight">Paw Pal<span
                            class="text-paw-accent">.</span></span>
                </a>

                <div class="hidden md:flex items-center space-x-12">
                    <a href="adopt.php"
                        class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-accent transition-colors duration-300">Adopt</a>
                    <a href="rescue.php"
                        class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-alert transition-colors duration-300">Rescue</a>
                    <a href="centers.php"
                        class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-accent transition-colors duration-300">Verified
                        Partners</a>
                    <a href="blogs.php"
                        class="magnetic-item text-sm uppercase tracking-widest hover:text-paw-accent transition-colors duration-300">Community</a>
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
                        <a href="login.php"
                            class="text-sm uppercase tracking-widest hover:text-paw-accent transition-colors">Login</a>
                        <a href="register.php"
                            class="group relative px-8 py-3 bg-paw-dark text-white rounded-full overflow-hidden flex items-center justify-center">
                            <div
                                class="absolute inset-0 w-full h-full bg-paw-accent transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left">
                            </div>
                            <span
                                class="relative z-10 text-xs font-bold uppercase tracking-widest group-hover:text-white transition-colors">Sign
                                Up</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="md:hidden magnetic-item">
                    <i data-lucide="menu" class="w-8 h-8"></i>
                </div>
            </div>
        </div>
    </nav>

    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
        <div class="absolute top-1/4 right-[10%] w-96 h-96 bg-paw-accent/10 rounded-full blur-3xl parallax-bg"
            data-speed="0.05"></div>
        <div class="absolute bottom-1/4 left-[5%] w-72 h-72 bg-paw-alert/5 rounded-full blur-3xl parallax-bg"
            data-speed="-0.03"></div>

        <div
            class="relative z-10 max-w-7xl mx-auto px-6 lg:px-12 w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <div class="lg:col-span-7 flex flex-col justify-center">
                <div class="overflow-hidden mb-4">
                    <h2 class="hero-text text-sm md:text-base font-medium tracking-[0.2em] text-paw-gray uppercase">
                        Revolutionizing Pet Welfare</h2>
                </div>

                <h1 class="font-serif text-6xl md:text-8xl lg:text-9xl leading-[0.9] mb-8 text-paw-dark">
                    <div class="overflow-hidden"><span class="hero-text block">Connect.</span></div>
                    <div class="overflow-hidden"><span
                            class="hero-text block italic text-paw-accent pr-4">Rescue.</span></div>
                    <div class="overflow-hidden"><span class="hero-text block">Love.</span></div>
                </h1>

                <div class="overflow-hidden">
                    <p class="hero-text text-lg md:text-xl text-paw-gray max-w-md leading-relaxed">
                        Streamline the adoption of street dogs, access reliable medical facts, and coordinate immediate
                        rescues to save lives.
                    </p>
                </div>

                <div class="mt-12 flex items-center space-x-8 hero-opacity opacity-0">
                    <div class="h-[1px] w-16 bg-paw-dark/20"></div>
                    <span class="text-xs uppercase tracking-widest font-semibold">Join the Movement</span>
                </div>
            </div>

            <div class="lg:col-span-5 relative h-[60vh] hidden lg:block hero-image opacity-0 translate-y-20">
                <div
                    class="absolute inset-0 bg-paw-accent/5 rounded-t-full rounded-b-full transform rotate-3 scale-95 border border-paw-accent/20">
                </div>
                <div
                    class="absolute inset-0 overflow-hidden rounded-t-full rounded-b-full image-hover-container shadow-2xl">
                    <img src="https://images.unsplash.com/photo-1544568100-847a948585b9?q=80&w=1000&auto=format&fit=crop"
                        alt="Happy Adopted Dog" class="w-full h-full object-cover transition-all duration-700">
                </div>
                <div class="absolute bottom-10 -left-10 glass p-6 rounded-2xl shadow-lg transform parallax-element"
                    data-speed="0.08">
                    <p class="font-serif text-2xl italic text-paw-dark">Lives Saved</p>
                    <p class="text-4xl font-bold font-serif">1,204+</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-32 relative" id="features">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-16">

                <div class="md:col-span-1 service-card opacity-0 group cursor-pointer magnetic-item">
                    <h3
                        class="text-sm font-bold uppercase tracking-widest text-paw-gray mb-6 flex items-center gap-2 group-hover:text-paw-accent transition-colors">
                        <i data-lucide="home" class="w-4 h-4"></i> Adoption & Sales
                    </h3>
                    <p class="font-serif text-3xl leading-snug mb-4">
                        Find your perfect companion or rehome pets responsibly.
                    </p>
                    <div class="w-full h-[1px] bg-paw-dark/10 group-hover:bg-paw-accent transition-colors duration-500">
                    </div>
                </div>

                <div class="md:col-span-1 service-card opacity-0 group cursor-pointer magnetic-item">
                    <h3
                        class="text-sm font-bold uppercase tracking-widest text-paw-gray mb-6 flex items-center gap-2 group-hover:text-paw-alert transition-colors">
                        <i data-lucide="siren" class="w-4 h-4"></i> Emergency Rescue
                    </h3>
                    <p class="font-serif text-3xl leading-snug mb-4">
                        Coordinate immediate aid for injured street animals in real-time.
                    </p>
                    <div class="w-full h-[1px] bg-paw-dark/10 group-hover:bg-paw-alert transition-colors duration-500">
                    </div>
                </div>

                <div class="md:col-span-1 service-card opacity-0 group cursor-pointer magnetic-item">
                    <h3
                        class="text-sm font-bold uppercase tracking-widest text-paw-gray mb-6 flex items-center gap-2 group-hover:text-blue-400 transition-colors">
                        <i data-lucide="stethoscope" class="w-4 h-4"></i> Verified Partners
                    </h3>
                    <p class="font-serif text-3xl leading-snug mb-4">
                        Vetted breeders, shelters, and vet clinics you can trust.
                    </p>
                    <div class="w-full h-[1px] bg-paw-dark/10 group-hover:bg-blue-400 transition-colors duration-500">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 bg-white" id="platform">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row justify-between items-end mb-20">
                <div>
                    <h2 class="font-serif text-5xl md:text-6xl text-paw-dark mb-4">The Platform</h2>
                    <p class="text-paw-gray">Tools designed for impact.</p>
                </div>
            </div>

            <div class="project-item group relative mb-32 grid grid-cols-1 md:grid-cols-12 gap-8 items-center cursor-none-target"
                id="adopt">
                <div class="md:col-span-7 overflow-hidden rounded-lg image-hover-container shadow-xl">
                    <img src="https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?q=80&w=2000&auto=format&fit=crop"
                        alt="Adoption Gallery"
                        class="w-full h-[500px] object-cover grayscale group-hover:grayscale-0 transition-all duration-700">
                </div>
                <div class="md:col-span-5 md:pl-10 relative">
                    <span
                        class="text-9xl absolute -top-20 -left-10 font-serif text-gray-100 -z-10 opacity-50 select-none">01</span>
                    <h3 class="text-4xl font-serif mb-4 group-hover:text-paw-accent transition-colors duration-300">
                        Adopt & Rehome</h3>
                    <p class="text-paw-gray mb-6 leading-relaxed">
                        Browse profiles of street dogs and pets needing homes. Use filters for breed, age, and location
                        to find your match, or list pets for adoption/sale responsibly.
                    </p>
                    <ul class="flex flex-wrap gap-3 mb-8">
                        <li class="px-3 py-1 bg-gray-100 rounded-full text-xs uppercase tracking-wide text-gray-600">
                            Street Dogs</li>
                        <li class="px-3 py-1 bg-gray-100 rounded-full text-xs uppercase tracking-wide text-gray-600">
                            Verified Breeders</li>
                    </ul>
                    <a href="adopt.php"
                        class="inline-flex items-center gap-2 text-sm uppercase tracking-widest font-semibold border-b border-paw-dark/20 pb-1 group-hover:border-paw-accent transition-colors">
                        Browse Pets <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <div class="project-item alert-item group relative mb-32 grid grid-cols-1 md:grid-cols-12 gap-8 items-center cursor-none-target"
                id="rescue">
                <div class="md:col-span-5 md:pr-10 md:text-right order-2 md:order-1 relative">
                    <span
                        class="text-9xl absolute -top-20 -right-10 font-serif text-gray-100 -z-10 opacity-50 select-none">02</span>
                    <h3 class="text-4xl font-serif mb-4 group-hover:text-paw-alert transition-colors duration-300">Rapid
                        Rescue</h3>
                    <p class="text-paw-gray mb-6 leading-relaxed">
                        Witness an injured animal? Pin the location, upload a photo, and alert nearby volunteers and
                        NGOs instantly. Every second counts.
                    </p>
                    <ul class="flex flex-wrap gap-3 mb-8 md:justify-end">
                        <li class="px-3 py-1 bg-red-50 text-red-800 rounded-full text-xs uppercase tracking-wide">
                            Geo-Tagging</li>
                        <li class="px-3 py-1 bg-red-50 text-red-800 rounded-full text-xs uppercase tracking-wide">SOS
                            Alerts</li>
                    </ul>
                    <a href="rescue.php"
                        class="inline-flex items-center gap-2 text-sm uppercase tracking-widest font-semibold border-b border-paw-dark/20 pb-1 group-hover:border-paw-alert text-paw-alert transition-colors">
                        Report Emergency <i data-lucide="siren" class="w-4 h-4"></i>
                    </a>
                </div>
                <div
                    class="md:col-span-7 overflow-hidden rounded-lg image-hover-container shadow-xl order-1 md:order-2 ring-1 ring-paw-alert/20">
                    <img src="https://images.unsplash.com/photo-1583512603805-3cc6b41f3edb?q=80&w=2000&auto=format&fit=crop"
                        alt="Rescue Operation"
                        class="w-full h-[500px] object-cover grayscale group-hover:grayscale-0 transition-all duration-700">
                </div>
            </div>

            <div class="project-item group relative grid grid-cols-1 md:grid-cols-12 gap-8 items-center cursor-none-target"
                id="community">
                <div class="md:col-span-7 overflow-hidden rounded-lg image-hover-container shadow-xl">
                    <img src="https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?q=80&w=2000&auto=format&fit=crop"
                        alt="Community Stories"
                        class="w-full h-[500px] object-cover grayscale group-hover:grayscale-0 transition-all duration-700">
                </div>
                <div class="md:col-span-5 md:pl-10 relative">
                    <span
                        class="text-9xl absolute -top-20 -left-10 font-serif text-gray-100 -z-10 opacity-50 select-none">03</span>
                    <h3 class="text-4xl font-serif mb-4 group-hover:text-blue-400 transition-colors duration-300">
                        Community & Insights</h3>
                    <p class="text-paw-gray mb-6 leading-relaxed">
                        Read success stories, get vet-verified health tips, and post about your own pets. A space for
                        pet owners to learn and share.
                    </p>
                    <ul class="flex flex-wrap gap-3 mb-8">
                        <li class="px-3 py-1 bg-gray-100 rounded-full text-xs uppercase tracking-wide text-gray-600">
                            Blogs</li>
                        <li class="px-3 py-1 bg-gray-100 rounded-full text-xs uppercase tracking-wide text-gray-600">
                            Forums</li>
                    </ul>
                    <a href="blogs.php"
                        class="inline-flex items-center gap-2 text-sm uppercase tracking-widest font-semibold border-b border-paw-dark/20 pb-1 group-hover:border-blue-400 transition-colors">
                        Read Stories <i data-lucide="book-open" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 overflow-hidden bg-paw-dark text-white/90">
        <div class="marquee-container flex whitespace-nowrap overflow-hidden">
            <div class="marquee-content flex gap-12 animate-marquee">
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Rescue</span>
                <span class="text-6xl md:text-8xl font-serif text-paw-accent">Adopt</span>
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Care</span>
                <span class="text-6xl md:text-8xl font-serif">Heal</span>
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Connect</span>
                <span class="text-6xl md:text-8xl font-serif text-paw-alert">Protect</span>
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Rescue</span>
                <span class="text-6xl md:text-8xl font-serif text-paw-accent">Adopt</span>
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Care</span>
                <span class="text-6xl md:text-8xl font-serif">Heal</span>
                <span class="text-6xl md:text-8xl font-serif italic text-white/20">Connect</span>
                <span class="text-6xl md:text-8xl font-serif text-paw-alert">Protect</span>
            </div>
        </div>
    </section>

    <footer class="relative pt-32 pb-12 bg-paw-bg overflow-hidden" id="contact">
        <div
            class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-paw-accent/50 to-transparent">
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-12 text-center relative z-10">
            <p class="text-sm uppercase tracking-[0.3em] text-paw-accent mb-6">Make a Difference</p>
            <h2
                class="font-serif text-6xl md:text-8xl text-paw-dark mb-12 hover:italic transition-all duration-300 cursor-pointer magnetic-item">
                help@pawpal.org
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-left mb-20 border-t border-gray-200 pt-12">
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-widest mb-4">Headquarters</h4>
                    <p class="text-paw-gray">Majibail, Kerala<br>India 671323</p>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-widest mb-4">Connect</h4>
                    <div class="flex flex-col gap-2">
                        <a href="register.php"
                            class="text-paw-gray hover:text-paw-accent transition-colors w-fit">Volunteer</a>
                        <a href="#" class="text-paw-gray hover:text-paw-accent transition-colors w-fit">Partner NGOs</a>
                        <a href="#" class="text-paw-gray hover:text-paw-accent transition-colors w-fit">Donate</a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-sm uppercase tracking-widest mb-4">Emergency Newsletter</h4>
                    <form class="flex border-b border-paw-gray/30 pb-2">
                        <input type="email" placeholder="Email for Alerts"
                            class="bg-transparent w-full outline-none placeholder-paw-gray/50">
                        <button type="submit"
                            class="text-xs uppercase font-bold tracking-widest hover:text-paw-alert text-paw-alert">Subscribe</button>
                    </form>
                </div>
            </div>

            <div class="flex justify-between items-center text-xs uppercase tracking-widest text-paw-gray/50">
                <p>&copy; 2024 Paw Pal.</p>
                <p>Built for <i data-lucide="heart" class="inline w-3 h-3 text-red-500"></i> Animals</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // --- Magnetic Item Logic (Kept Active for Buttons) ---
        const magneticItems = document.querySelectorAll('.magnetic-item');

        // Hover effects for magnetic items (Movement Logic)
        magneticItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                // Magnetic Pull Effect
                item.addEventListener('mousemove', magneticMove);
            });

            item.addEventListener('mouseleave', () => {
                item.removeEventListener('mousemove', magneticMove);
                // Reset item position
                gsap.to(item, { x: 0, y: 0, duration: 0.5, ease: "elastic.out(1, 0.3)" });
            });
        });

        // Magnetic Move Function
        function magneticMove(e) {
            const rect = this.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const deltaX = Math.floor((e.clientX - centerX) * 0.2); // Strength of pull
            const deltaY = Math.floor((e.clientY - centerY) * 0.2);

            gsap.to(this, {
                x: deltaX,
                y: deltaY,
                duration: 0.3,
                ease: "power2.out"
            });
        }

        // --- GSAP Scroll Animations ---
        gsap.registerPlugin(ScrollTrigger);

        // Hero Reveal
        const heroTl = gsap.timeline();

        heroTl.from(".hero-text", {
            y: "120%",
            stagger: 0.1,
            duration: 1.2,
            ease: "power4.out",
            delay: 0.2
        })
            .to(".hero-opacity", {
                opacity: 1,
                duration: 1,
            }, "-=0.5")
            .to(".hero-image", {
                opacity: 1,
                y: 0,
                duration: 1.2,
                ease: "power2.out"
            }, "-=1");

        // Service Cards Reveal
        gsap.utils.toArray('.service-card').forEach((card, i) => {
            gsap.fromTo(card,
                { opacity: 0, y: 50 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 1,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: card,
                        start: "top 80%",
                        toggleActions: "play none none reverse"
                    },
                    delay: i * 0.2
                }
            );
        });

        // Project Items Parallax & Reveal
        gsap.utils.toArray('.project-item').forEach((item) => {
            gsap.fromTo(item,
                { opacity: 0, y: 100 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 1,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: item,
                        start: "top 75%",
                    }
                }
            );
        });

        // Parallax Background Shapes
        document.addEventListener("mousemove", (e) => {
            document.querySelectorAll(".parallax-bg").forEach(layer => {
                const speed = layer.getAttribute("data-speed");
                const x = (window.innerWidth - e.pageX * speed) / 100;
                const y = (window.innerHeight - e.pageY * speed) / 100;

                layer.style.transform = `translateX(${x}px) translateY(${y}px)`;
            });
        });

        // Navbar blur effect on scroll
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('glass');
                navbar.classList.add('shadow-sm');
                navbar.classList.remove('h-24');
                navbar.classList.add('h-20');
            } else {
                navbar.classList.remove('glass');
                navbar.classList.remove('shadow-sm');
                navbar.classList.remove('h-20');
                navbar.classList.add('h-24');
            }
        });

        // Footer Marquee Animation Setup
        gsap.to(".marquee-content", {
            xPercent: -50,
            repeat: -1,
            duration: 20,
            ease: "linear"
        });

    </script>
</body>

</html>