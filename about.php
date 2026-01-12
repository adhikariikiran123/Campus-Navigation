<?php
// about.php
if (session_status() === PHP_SESSION_NONE) session_start();
$username = 'Student';
if (!empty($_SESSION['user_name'])) {
    $username = htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | NAVIC ERA - GEHU Campus Navigation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <style>
        :root {
            --navy: #0f172a;
            --navy-light: #1e293b;
            --primary: #3b82f6;
            --secondary: #2563eb;
            --accent: #60a5fa;
            --text: #e2e8f0;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--navy);
            color: var(--text);
            overflow-x: hidden;
            min-height: 100vh;
        }
        #particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        }
        .navbar {
            position: fixed;
            top: 0; width: 100%; padding: 1.5rem 6%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; transition: all 0.4s ease;
            background: rgba(15,23,42,0.95);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(96,165,250,0.2);
        }
        .navbar.scrolled {
            padding: 1rem 6%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .nav-logo {
            font-size: 2.3rem; font-weight: 900;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .user-info {
            color: white; font-weight: 600; font-size: 1.1rem;
            display: flex; align-items: center; gap: 20px;
        }
        .user-info button {
            background: #3b82f6; color: white; border: none;
            padding: 10px 28px; border-radius: 50px; cursor: pointer;
            font-weight: 600; transition: 0.3s;
        }
        .user-info button:hover { background: #2563eb; transform: scale(1.05); }

        .container {
            position: relative;
            z-index: 2;
            max-width: 1100px;
            margin: 130px auto 100px;
            padding: 50px;
            background: rgba(30,41,59,0.5);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            border: 1px solid rgba(96,165,250,0.3);
            box-shadow: 0 30px 80px rgba(0,0,0,0.5);
        }
        h1 {
            font-size: 4rem;
            text-align: center;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2.5rem;
            font-weight: 800;
        }
        h2 {
            font-size: 2.2rem;
            margin: 3rem 0 1.2rem;
            color: #60a5fa;
            font-weight: 700;
            position: relative;
            padding-left: 20px;
        }
        h2::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            width: 6px; height: 30px;
            background: #60a5fa;
            border-radius: 3px;
            transform: translateY(-50%);
        }
        p, li {
            font-size: 1.15rem;
            line-height: 1.9;
            color: #cbd5e1;
        }
        ul {
            margin: 1.5rem 0 2.5rem 3rem;
        }
        li { margin-bottom: 12px; }
        a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover { text-decoration: underline; color: #93c5fd; }

        .footer {
            text-align: center;
            padding: 40px;
            font-size: 1.1rem;
            color: #94a3b8;
            background: rgba(15,23,42,0.9);
            margin-top: 100px;
            border-top: 1px solid rgba(96,165,250,0.2);
        }

        .ai-fab {
            position: fixed; bottom: 40px; right: 40px; width: 72px; height: 72px;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border: none; border-radius: 50%; font-size: 34px; color: white;
            cursor: pointer; box-shadow: 0 12px 45px rgba(59,130,246,0.6);
            z-index: 999; text-decoration: none;
            display: flex; align-items: center; justify-content: center;
            animation: fabPulse 3s infinite;
            transition: transform 0.3s;
        }
        .ai-fab:hover { transform: scale(1.12); }
        @keyframes fabPulse { 0%,100% { box-shadow: 0 12px 45px rgba(59,130,246,0.6); } 50% { box-shadow: 0 12px 65px rgba(59,130,246,0.9); } }
    </style>
</head>
<body>

    <div id="particles"></div>

    <!-- Navbar -->
    <div class="navbar" id="navbar">
        <div class="nav-logo">NAVIC ERA</div>
        <div class="user-info">
            Welcome, <span id="username"><?php echo $username; ?></span> 👋
            <button onclick="location.href='dashboard.php'">🏠 Home</button>
            <button onclick="location.href='logout.php'">🚪 Logout</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h1>About / Help Center</h1>

        <p>
            Welcome to <strong>NAVIC ERA</strong> — the next-generation campus navigation system for 
            <strong>Graphic Era Hill University</strong>. Powered by modern web technologies and AI, 
            we make finding your way across campus effortless, fast, and futuristic.
        </p>

        <h2>🔍 Key Features</h2>
        <ul>
            <li>Fully interactive floor-wise campus maps (Basement → 5th Floor)</li>
            <li>Lightning-fast search for any classroom, lab, office, or facility</li>
            <li>AI-powered shortest & optimal path calculation</li>
            <li>Animated step-by-step routing with glowing arrows</li>
            <li>Augmented Reality (AR) navigation on mobile devices</li>
            <li>24/7 AI Assistant — just ask in plain English!</li>
        </ul>

        <h2>💡 How to Navigate</h2>
        <ul>
            <li><strong>Interactive Campus Map</strong> → Explore every floor visually</li>
            <li><strong>Smart Path Finding</strong> → Enter source & destination → get instant route</li>
            <li><strong>AR Navigation</strong> → Open on phone for real-world overlay guidance</li>
            <li><strong>Floating Robot Icon</strong> → Click anytime to chat with our AI assistant</li>
        </ul>

        <h2>📞 Support & Feedback</h2>
        <p>
            We’re here to help! For queries, suggestions, or issues:
        </p>
        <ul>
            <li>Email: <a href="mailto:gehucodeverse@gmail.com">gehucodeverse@gmail.com</a></li>
        </ul>
        <p style="font-size:1.3rem; margin-top:2rem; color:#60a5fa;">
            Thank you for choosing <strong>NAVIC ERA</strong> — your intelligent campus companion.
        </p>
    </div>

    <div class="footer">
        © 2025 Team CodeVerse | NAVIC ERA - GEHU Campus Navigation System | BCA Batch 2026
    </div>

    <!-- Floating AI Assistant -->
    <a href="campus_ai_ui.php" class="ai-fab" id="aiBtn">
        <i class="fas fa-robot"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        // Username fallback
        const serverName = "<?php echo isset($_SESSION['user_name']) ? addslashes($_SESSION['user_name']) : ''; ?>";
        if (!serverName) {
            const stored = localStorage.getItem('username');
            if (stored) document.getElementById('username').textContent = stored;
        }

        // Particles Background
        particlesJS('particles', {
            particles: {
                number: { value: 90 },
                color: { value: ['#3b82f6', '#60a5fa', '#93c5fd'] },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 },
                move: { enable: true, speed: 1.8 }
            },
            interactivity: { events: { onhover: { enable: true, mode: 'repulse' } } }
        });

        // GSAP Animations
        gsap.from(".container > *", {
            scrollTrigger: { trigger: ".container", start: "top 80%" },
            y: 80, opacity: 0, duration: 1.2, stagger: 0.18, ease: "power4.out"
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 100);
        });

        // Ripple effect on AI FAB
        document.getElementById('aiBtn').addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255,255,255,0.7)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.7s ease-out';
            ripple.style.left = (e.clientX - e.target.offsetLeft - 36) + 'px';
            ripple.style.top = (e.clientY - e.target.offsetTop - 36) + 'px';
            ripple.style.width = ripple.style.height = '72px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 700);
        });

        const style = document.createElement('style');
        style.innerHTML = `@keyframes ripple { to { transform: scale(4.5); opacity: 0; } }`;
        document.head.appendChild(style);
    </script>
</body>
</html>