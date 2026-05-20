/*
* Winter Snow Effect - Lightweight Snow JS
*/

(function () {
    const startSnow = () => {
        if (!document.body) {
            setTimeout(startSnow, 50);
            return;
        }

        // Get settings from WordPress localized script with deep fallbacks
        const s = typeof wse_settings !== 'undefined' ? wse_settings : {};
        const settings = {
            flakeCount: parseInt(s.flakeCount) || 35,
            minSpeed: parseFloat(s.minSpeed) || 0.5,
            maxSpeed: parseFloat(s.maxSpeed) || 1.5
        };

        // Create canvas
        const canvas = document.createElement('canvas');
        canvas.id = 'wse-snow-canvas';
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        let width = window.innerWidth;
        let height = window.innerHeight;

        canvas.width = width;
        canvas.height = height;

        const isMobile = window.innerWidth < 768;

        // If mobile, reduce flake count to 20% of setting (minimum 6)
        const density = isMobile ? 0.2 : 1.0;
        const maxFlakes = Math.max(isMobile ? 6 : 1, Math.floor(settings.flakeCount * density));

        // Snowflakes array
        const flakes = [];

        // Resize handler
        window.addEventListener('resize', function () {
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = width;
            canvas.height = height;
        });

        // Snowflake class
        class Snowflake {
            constructor() {
                this.reset();
                this.y = Math.random() * height; // Initial random Y
            }

            reset() {
                this.x = Math.random() * width;
                this.y = -20;

                // Size: Mobile (20-35px), Desktop (10-30px)
                this.size = isMobile
                    ? Math.random() * 15 + 20
                    : Math.random() * 20 + 10;

                // Speed from settings
                const range = Math.max(0.1, settings.maxSpeed - settings.minSpeed);
                this.speed = Math.random() * range + settings.minSpeed;

                this.swaySpeed = Math.random() * 0.01 + 0.005;
                this.angle = Math.random() * Math.PI * 2;
                this.opacity = Math.random() * 0.3 + 0.6;
            }

            update() {
                this.y += this.speed;
                this.angle += this.swaySpeed;
                this.x += Math.sin(this.angle) * 1.5;

                // Reset if out of bounds
                if (this.y > height) {
                    this.reset();
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
                ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                ctx.shadowBlur = 4;
                ctx.fillText('*', this.x, this.y);
                ctx.shadowBlur = 0;
            }
        }

        // Initialize flakes
        for (let i = 0; i < maxFlakes; i++) {
            flakes.push(new Snowflake());
        }

        // Animation loop
        function animate() {
            ctx.clearRect(0, 0, width, height);
            flakes.forEach(flake => {
                flake.update();
                flake.draw();
            });
            requestAnimationFrame(animate);
        }

        animate();
    };

    // Run when ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        startSnow();
    } else {
        document.addEventListener('DOMContentLoaded', startSnow);
    }
})();
