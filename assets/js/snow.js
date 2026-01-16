/*
* Winter Snow Effect - Lightweight Snow JS
*/

(function () {
    // Get settings from PHP (with fallback defaults)
    const settings = typeof wseSettings !== 'undefined' ? wseSettings : {
        flakeCountMobile: 6,
        flakeCountDesktop: 35,
        flakeSizeMin: 10,
        flakeSizeMax: 30,
        flakeSpeedMin: 0.5,
        flakeSpeedMax: 1.5,
        flakeOpacityMin: 0.6,
        flakeOpacityMax: 0.9,
        respectReducedMotion: true,
        pauseOnScroll: false,
        pauseOnInactive: true,
    };

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (settings.respectReducedMotion && prefersReducedMotion) {
        return; // Exit early if user prefers reduced motion
    }

    // Create canvas
    const canvas = document.createElement('canvas');
    canvas.id = 'wse-snow-canvas';
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    let width = window.innerWidth;
    let height = window.innerHeight;

    canvas.width = width;
    canvas.height = height;

    // Snowflakes array
    const flakes = [];
    // Adjust flake count based on screen width
    const isMobile = window.innerWidth < 768;
    const maxFlakes = isMobile ? settings.flakeCountMobile : settings.flakeCountDesktop;

    // Animation state
    let isPaused = false;
    let animationId = null;

    // Resize handler
    function handleResize() {
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = width;
        canvas.height = height;
    }
    window.addEventListener('resize', handleResize);

    // Performance optimizations: pause on scroll
    if (settings.pauseOnScroll) {
        let scrollTimeout;
        window.addEventListener('scroll', function () {
            isPaused = true;
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function () {
                isPaused = false;
            }, 150); // Resume after 150ms of no scrolling
        }, { passive: true });
    }

    // Performance optimizations: pause on inactive tab
    if (settings.pauseOnInactive) {
        document.addEventListener('visibilitychange', function () {
            isPaused = document.hidden;
        });
    }

    // Snowflake class
    class Snowflake {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;

            // Use settings for size range
            const sizeRange = settings.flakeSizeMax - settings.flakeSizeMin;
            this.size = Math.random() * sizeRange + settings.flakeSizeMin;

            // Use settings for speed range
            const speedRange = settings.flakeSpeedMax - settings.flakeSpeedMin;
            this.speed = Math.random() * speedRange + settings.flakeSpeedMin;

            this.sway = Math.random() - 0.5;
            this.swaySpeed = Math.random() * 0.01 + 0.005;
            this.angle = Math.random() * Math.PI * 2;

            // Use settings for opacity range
            const opacityRange = settings.flakeOpacityMax - settings.flakeOpacityMin;
            this.opacity = Math.random() * opacityRange + settings.flakeOpacityMin;
        }

        update() {
            if (isPaused) {
                return; // Don't update position when paused
            }

            this.y += this.speed;
            this.angle += this.swaySpeed;
            this.x += Math.sin(this.angle) * 1.5;

            // Wrap around screen
            if (this.y > height) {
                this.y = -10;
                this.x = Math.random() * width;
            }
            if (this.x > width) {
                this.x = 0;
            } else if (this.x < 0) {
                this.x = width;
            }
        }

        draw() {
            ctx.font = `${this.size}px sans-serif`;
            ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;

            // Shadow for visibility
            ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
            ctx.shadowBlur = 4;

            ctx.fillText('*', this.x, this.y);

            // Reset shadow to avoid affecting other potential draws if shared context (good practice)
            ctx.shadowBlur = 0;
        }
    }

    // Initialize flakes
    for (let i = 0; i < maxFlakes; i++) {
        flakes.push(new Snowflake());
    }

    // Animation loop
    function animate() {
        if (!isPaused) {
            ctx.clearRect(0, 0, width, height);
            flakes.forEach(flake => {
                flake.update();
                flake.draw();
            });
        }
        animationId = requestAnimationFrame(animate);
    }

    // Start animation
    animate();

    // Cleanup on page unload (optional, but good practice)
    window.addEventListener('beforeunload', function () {
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
    });
})();
