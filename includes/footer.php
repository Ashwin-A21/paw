<footer class="relative pt-32 pb-12 bg-paw-bg overflow-hidden" id="contact">
    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-paw-accent/50 to-transparent">
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
                    <a href="<?php echo $basePath; ?>register.php"
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
            <p>&copy;
                <?php echo date('Y'); ?> Paw Pal.
            </p>
            <p>Built for <i data-lucide="heart" class="inline w-3 h-3 text-red-500"></i> Animals</p>
        </div>
    </div>
</footer>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // --- Magnetic Item Logic (Global) ---
    const magneticItems = document.querySelectorAll('.magnetic-item');

    magneticItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.addEventListener('mousemove', magneticMove);
        });

        item.addEventListener('mouseleave', () => {
            item.removeEventListener('mousemove', magneticMove);
            gsap.to(item, { x: 0, y: 0, duration: 0.5, ease: "elastic.out(1, 0.3)" });
        });
    });

    function magneticMove(e) {
        const rect = this.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const deltaX = Math.floor((e.clientX - centerX) * 0.2);
        const deltaY = Math.floor((e.clientY - centerY) * 0.2);

        gsap.to(this, {
            x: deltaX,
            y: deltaY,
            duration: 0.3,
            ease: "power2.out"
        });
    }
</script>
</body>

</html>