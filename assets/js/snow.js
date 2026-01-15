/*
* Winter Snow Effect - Lightweight Snow JS
*/

(function () {
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
    // < 768px (mobile): 6 flakes (as requested)
    // >= 768px (desktop): 35 flakes
    const isMobile = window.innerWidth < 768;
    const maxFlakes = isMobile ? 6 : 35;

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
            this.x = Math.random() * width;
            this.y = Math.random() * height;

            // Adjust size based on device
            // Mobile: 20px - 35px (Bigger flakes)
            // Desktop: 10px - 30px
            this.size = isMobile
                ? Math.random() * 15 + 20
                : Math.random() * 20 + 10;

            this.speed = Math.random() * 1 + 0.5; // Moderate gentle speed
            this.sway = Math.random() - 0.5;
            this.swaySpeed = Math.random() * 0.01 + 0.005;
            this.angle = Math.random() * Math.PI * 2;
            this.opacity = Math.random() * 0.3 + 0.6; // Higher opacity (0.6 to 0.9) for visibility
        }

        update() {
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
        ctx.clearRect(0, 0, width, height);
        flakes.forEach(flake => {
            flake.update();
            flake.draw();
        });
        requestAnimationFrame(animate);
    }

    animate();
})();
