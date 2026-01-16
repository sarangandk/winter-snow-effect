/*
* Winter Snow Effect - Lightweight Snow JS (Robust Version)
*/

(function () {
    let animationId = null;
    let canvas = null;
    let ctx = null;
    let flakes = [];
    let width = 0;
    let height = 0;
    let dpr = window.devicePixelRatio || 1;
    let isPaused = false;
    let lastLogTime = 0;

    // Get settings from PHP (with fallback defaults)
    const settings = typeof wseSettings !== 'undefined' ? wseSettings : {
        flakeCountMobile: 12,
        flakeCountDesktop: 50,
        flakeSizeMin: 12,
        flakeSizeMax: 40,
        flakeSpeedMin: 0.5,
        flakeSpeedMax: 1.5,
        flakeOpacityMin: 0.6,
        flakeOpacityMax: 0.9,
        respectReducedMotion: true,
        pauseOnScroll: false,
        pauseOnInactive: true,
    };

    function log(msg, ...args) {
        console.log(`%cWinter Snow Effect: %c${msg}`, "color: #2271b1; font-weight: bold;", "color: inherit;", ...args);
    }

    function initSnow() {
        // Check for reduced motion preference
        if (settings.respectReducedMotion && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            log('Disabled due to OS "Reduced Motion" setting.');
            return;
        }

        // Avoid double initialization
        if (document.getElementById('wse-snow-canvas')) {
            return;
        }

        const container = document.body || document.documentElement;
        if (!container) {
            setTimeout(initSnow, 100);
            return;
        }

        log('Initializing...', settings);

        canvas = document.createElement('canvas');
        canvas.id = 'wse-snow-canvas';
        canvas.setAttribute('data-wse-version', '2.1');
        container.appendChild(canvas);

        ctx = canvas.getContext('2d', { alpha: true });
        if (!ctx) {
            console.error('Winter Snow Effect: Could not get 2D context');
            return;
        }

        updateDimensions();
        createFlakes();
        setupListeners();

        log(`Initialized with ${flakes.length} flakes. Canvas size: ${canvas.width}x${canvas.height}`);

        animate();
    }

    function updateDimensions() {
        width = window.innerWidth || document.documentElement.clientWidth;
        height = window.innerHeight || document.documentElement.clientHeight;
        dpr = window.devicePixelRatio || 1;

        canvas.width = width * dpr;
        canvas.height = height * dpr;

        // Context scaling is reset when canvas dimensions change
        ctx.scale(dpr, dpr);
    }

    function createFlakes() {
        const isMobile = width < 768;
        const maxFlakes = isMobile ? settings.flakeCountMobile : settings.flakeCountDesktop;

        flakes = [];
        for (let i = 0; i < maxFlakes; i++) {
            flakes.push(new Snowflake());
        }
    }

    class Snowflake {
        constructor() {
            this.reset(true);
        }

        reset(isInitial = false) {
            this.x = Math.random() * width;
            this.y = isInitial ? Math.random() * height : -20;

            const sizeRange = settings.flakeSizeMax - settings.flakeSizeMin;
            this.size = Math.random() * sizeRange + settings.flakeSizeMin;

            const speedRange = settings.flakeSpeedMax - settings.flakeSpeedMin;
            this.speed = Math.random() * speedRange + settings.flakeSpeedMin;

            this.swaySpeed = Math.random() * 0.02 + 0.01;
            this.angle = Math.random() * Math.PI * 2;

            const opacityRange = settings.flakeOpacityMax - settings.flakeOpacityMin;
            this.opacity = Math.random() * opacityRange + settings.flakeOpacityMin;

            // Random very subtle blue tint for visibility
            this.color = Math.random() > 0.8 ? 'rgba(230, 245, 255, ' : 'rgba(255, 255, 255, ';
        }

        update() {
            if (isPaused) return;

            this.y += this.speed;
            this.angle += this.swaySpeed;
            this.x += Math.sin(this.angle) * 1.5;

            if (this.y > height + this.size) {
                this.reset();
            }

            if (this.x > width + this.size) {
                this.x = -this.size;
            } else if (this.x < -this.size) {
                this.x = width + this.size;
            }
        }

        draw() {
            ctx.save();
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size / 2, 0, Math.PI * 2);

            ctx.fillStyle = this.color + this.opacity + ')';

            // Slightly stronger shadow for visibility
            ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
            ctx.shadowBlur = 4;

            ctx.fill();
            ctx.restore();
        }
    }

    function animate(time) {
        if (!isPaused) {
            ctx.clearRect(0, 0, width, height);
            flakes.forEach(flake => {
                flake.update();
                flake.draw();
            });

            // Diagnostic log every 10 seconds to console if running
            if (time - lastLogTime > 10000) {
                log('Animation running smoothly...');
                lastLogTime = time;
            }
        }
        animationId = requestAnimationFrame(animate);
    }

    function setupListeners() {
        window.addEventListener('resize', () => {
            updateDimensions();
        }, { passive: true });

        if (settings.pauseOnScroll) {
            let scrollTimeout;
            window.addEventListener('scroll', () => {
                isPaused = true;
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    isPaused = false;
                }, 150);
            }, { passive: true });
        }

        if (settings.pauseOnInactive) {
            document.addEventListener('visibilitychange', () => {
                isPaused = document.hidden;
                if (!isPaused) log('Tab became active, resuming snow.');
            });
        }
    }

    // Try multiple init methods
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSnow);
    } else {
        initSnow();
    }

    // Final fallback
    window.addEventListener('load', () => {
        setTimeout(() => {
            if (!document.getElementById('wse-snow-canvas')) {
                log('Late initialization triggered.');
                initSnow();
            }
        }, 500);
    });

})();
