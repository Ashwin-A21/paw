<?php
session_start();
include 'config.php';

// Get stats for display
$petsResult = $conn->query("SELECT COUNT(*) as count FROM pets WHERE status='Available'");
$petsCount = $petsResult ? $petsResult->fetch_assoc()['count'] : 0;

// Setup for includes
$basePath = '';
$isTransparentHeader = true;

include 'includes/header.php';
?>

<div class="fixed inset-0 pointer-events-none opacity-40 bg-noise z-0 mix-blend-multiply"></div>

<!-- Note: Navbar is handled by header.php -->

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
                <div class="overflow-hidden"><span class="hero-text block italic text-paw-accent pr-4">Rescue.</span>
                </div>
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
                    Read stories, get vet-verified health tips, and post about your own pets. A space for
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

<?php include 'includes/footer.php'; ?>

<script>
    // --- Specific scripts for Landing Page ---
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

    // Navbar blur effect on scroll (for header.php element)
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