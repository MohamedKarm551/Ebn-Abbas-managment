export function initParticlesBg(options = {}) {
    const defaultColors = ['#06b6d4', '#f59e42', '#6366f1', '#f43f5e', '#22d3ee'];
    const POINTS = options.points || 60;
    const DIST = options.dist || 140;
    const MOUSE_DIST = options.mouseDist || 180;
    const COLORS = options.colors || defaultColors;

    let canvas = document.getElementById('bg-particles-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'bg-particles-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = 0;
        canvas.style.left = 0;
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.zIndex = 0;
        canvas.style.pointerEvents = 'none';
        document.body.prepend(canvas);
    }
    const ctx = canvas.getContext('2d');
    let width = window.innerWidth, height = window.innerHeight;
    let mouse = { x: null, y: null };

    function resize() {
        width = window.innerWidth;
        height = window.innerHeight;
        canvas.width = width;
        canvas.height = height;
    }
    resize();
    window.addEventListener('resize', resize);

    let points = [];
    for (let i = 0; i < POINTS; i++) {
        points.push({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.7,
            vy: (Math.random() - 0.5) * 0.7,
            color: COLORS[Math.floor(Math.random() * COLORS.length)]
        });
    }

    window.addEventListener('mousemove', e => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });
    window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });

    function draw() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0; i < POINTS; i++) {
            for (let j = i + 1; j < POINTS; j++) {
                let dx = points[i].x - points[j].x;
                let dy = points[i].y - points[j].y;
                let dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < DIST) {
                    ctx.save();
                    ctx.globalAlpha = 1 - dist / DIST;
                    ctx.strokeStyle = "#b6b6b6";
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(points[i].x, points[i].y);
                    ctx.lineTo(points[j].x, points[j].y);
                    ctx.stroke();
                    ctx.restore();
                }
            }
        }
        if (mouse.x !== null && mouse.y !== null) {
            for (let i = 0; i < POINTS; i++) {
                let dx = points[i].x - mouse.x;
                let dy = points[i].y - mouse.y;
                let dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < MOUSE_DIST) {
                    ctx.save();
                    ctx.globalAlpha = 1 - dist / MOUSE_DIST;
                    ctx.strokeStyle = "#06b6d4";
                    ctx.lineWidth = 1.5;
                    ctx.beginPath();
                    ctx.moveTo(points[i].x, points[i].y);
                    ctx.lineTo(mouse.x, mouse.y);
                    ctx.stroke();
                    ctx.restore();
                    points[i].vx += (mouse.x - points[i].x) * 0.0005;
                    points[i].vy += (mouse.y - points[i].y) * 0.0005;
                }
            }
        }
        for (let i = 0; i < POINTS; i++) {
            ctx.save();
            ctx.beginPath();
            ctx.arc(points[i].x, points[i].y, 3.2, 0, Math.PI * 2);
            ctx.fillStyle = points[i].color;
            ctx.shadowColor = points[i].color;
            ctx.shadowBlur = 8;
            ctx.fill();
            ctx.restore();
        }
    }

    function update() {
        for (let i = 0; i < POINTS; i++) {
            points[i].x += points[i].vx;
            points[i].y += points[i].vy;
            if (points[i].x < 0 || points[i].x > width) points[i].vx *= -1;
            if (points[i].y < 0 || points[i].y > height) points[i].vy *= -1;
        }
    }

    function animate() {
        update();
        draw();
        requestAnimationFrame(animate);
    }
    animate();
}