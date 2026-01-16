/*
* Winter Snow Effect - Lightweight Snow JS
*/

(function () {
    // Wait for DOM to be ready
    function initSnow() {
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
            console.log('Winter Snow Effect: Disabled due to reduced motion preference');
            return; // Exit early if user prefers reduced motion
        }

        // Ensure body exists
        if (!document.body) {
            console.log('Winter Snow Effect: Body not found, retrying...');
            setTimeout(initSnow, 100);
            return;
        }

        console.log('Winter Snow Effect: Initializing...', settings);

        // Check if canvas already exists (avoid duplicates)
        let canvas = document.getElementById('wse-snow-canvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'wse-snow-canvas';
            document.body.appendChild(canvas);
            console.log('Winter Snow Effect: Canvas created');
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.error('Winter Snow Effect: Could not get 2D context');
            return;
        }

        let width = window.innerWidth;
        let height = window.innerHeight;

        canvas.width = width;
        canvas.height = height;

        // Snowflakes array
        const flakes = [];
        // Adjust flake count based on screen width
        const isMobile = window.innerWidth < 768;
        const maxFlakes = isMobile ? settings.flakeCountMobile : settings.flakeCountDesktop;
        
        console.log('Winter Snow Effect: Creating', maxFlakes, 'snowflakes (isMobile:', isMobile, ')');

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
                // Set font before drawing
                ctx.font = `${this.size}px Arial, sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;

                // Shadow for visibility
                ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                ctx.shadowBlur = 4;

                // Draw snowflake character
                ctx.fillText('*', this.x, this.y);

                // Reset shadow to avoid affecting other potential draws if shared context (good practice)
                ctx.shadowBlur = 0;
                ctx.shadowColor = 'transparent';
            }
        }

        // Initialize flakes
        for (let i = 0; i < maxFlakes; i++) {
            flakes.push(new Snowflake());
        }
        
        console.log('Winter Snow Effect: Initialized', flakes.length, 'snowflakes');

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

        // Test: Draw a test snowflake immediately to verify canvas works
        if (flakes.length > 0) {
            ctx.clearRect(0, 0, width, height);
            flakes[0].draw();
            console.log('Winter Snow Effect: Test draw completed at position', flakes[0].x, flakes[0].y);
        }

        // Start animation
        animate();
        console.log('Winter Snow Effect: Animation started');

        // Cleanup on page unload (optional, but good practice)
        window.addEventListener('beforeunload', function () {
            if (animationId) {
                cancelAnimationFrame(animationId);
            }
        });
    }

    // Initialize when DOM is ready - try multiple methods
    function tryInit() {
        if (document.body) {
            initSnow();
        } else if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSnow);
            // Fallback in case DOMContentLoaded already fired
            setTimeout(initSnow, 100);
        } else {
            // DOM is already ready
            initSnow();
        }
    }

    // Try immediately
    tryInit();
    
    // Also try on window load as fallback
    window.addEventListener('load', function() {
        // Only init if canvas doesn't exist yet
        if (!document.getElementById('wse-snow-canvas')) {
            console.log('Winter Snow Effect: Retrying initialization on window load');
            initSnow();
        }
    });
})();
