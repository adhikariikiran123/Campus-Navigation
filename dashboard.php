<?php
require_once 'helpers.php';
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVIC ERA - GEHU Campus Navigation</title>
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
            --card-bg: rgba(30, 41, 59, 0.4);
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
        header { position: relative; height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden; z-index: 2; }
        .hero-bg {
            position: absolute; inset: 0;
            background: url('https://images.unsplash.com/photo-1498243691581-b145660463ae?q=80&w=2952') center/cover no-repeat;
            filter: brightness(0.28) blur(1.5px);
            transform: scale(1.1);
        }
        .hero-content { position: relative; z-index: 10; padding: 2rem; }
        .logo-animated {
            width: 130px; height: 130px; border-radius: 50%; margin-bottom: 2rem;
            box-shadow: 0 0 70px rgba(59,130,246,0.6); animation: float 6s ease-in-out infinite;
            border: 4px solid rgba(96,165,250,0.3);
        }
        .typing-title {
            font-size: 5rem; font-weight: 800;
            background: linear-gradient(90deg, #3b82f6, #60a5fa, #93c5fd);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 1rem; overflow: hidden; white-space: nowrap; border-right: 5px solid #60a5fa;
        }
        .subtitle { font-size: 1.6rem; opacity: 0.9; letter-spacing: 0.8px; }
        .navbar {
            position: fixed; top: 0; width: 100%; padding: 1.5rem 6%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; transition: all 0.4s ease;
        }
        .navbar.scrolled {
            background: rgba(15,23,42,0.95); backdrop-filter: blur(24px);
            padding: 1rem 6%; border-bottom: 1px solid rgba(96,165,250,0.2);
        }
        .nav-logo { 
            font-size: 2.3rem; font-weight: 900;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .features { position: relative; padding: 10rem 5%; z-index: 2; background: rgba(15,23,42,0.7); }
        .section-title {
            text-align: center; font-size: 3.3rem; margin-bottom: 6.5rem;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 700;
        }
        .cards-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.8rem; max-width: 1500px; margin: 0 auto;
        }
        .card {
            background: var(--card-bg); backdrop-filter: blur(18px);
            border-radius: 28px; overflow: hidden;
            border: 1px solid rgba(96,165,250,0.25);
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer;
        }
        .card:hover {
            transform: translateY(-22px) scale(1.04);
            box-shadow: 0 35px 70px rgba(59,130,246,0.35); border-color: #60a5fa;
        }
        .card-icon {
            height: 180px; display: flex; align-items: center; justify-content: center;
            font-size: 5.5rem; background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        .card-body { padding: 2.2rem; text-align: center; }
        .card-title { font-size: 1.7rem; font-weight: 700; margin-bottom: 1rem; color: #f8fafc; }
        .card p { color: #cbd5e1; font-size: 1.02rem; line-height: 1.6; }
        .ai-fab {
            position: fixed; bottom: 40px; right: 40px; width: 72px; height: 72px;
            background: linear-gradient(135deg, #60a5fa, #3b82f6); border: none; border-radius: 50%;
            font-size: 34px; color: white; cursor: pointer;
            box-shadow: 0 12px 45px rgba(59,130,246,0.6); z-index: 999;
            text-decoration: none; display: flex; align-items: center; justify-content: center;
            animation: fabPulse 3s infinite; transition: all 0.3s ease;
        }
        .ai-fab:hover { transform: scale(1.1); }
        @keyframes fabPulse { 0%,100% { box-shadow: 0 12px 45px rgba(59,130,246,0.6); } 50% { box-shadow: 0 12px 65px rgba(59,130,246,0.9); } }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-25px); } }
    </style>
</head>
<body>

    <div id="particles"></div>

    <!-- Hero Header -->
    <header>
        <div class="hero-bg"></div>
        <div class="navbar" id="navbar">
            <div class="nav-logo">NAVIC ERA</div>
            <div style="color:white; font-weight:600; font-size:1.1rem;">
                Welcome, <span id="username">Student</span> 👋
                <button onclick="location.href='logout.php'" style="margin-left:20px; background:#3b82f6; color:white; border:none; padding:10px 28px; border-radius:50px; cursor:pointer; font-weight:600;">
                    Logout
                </button>
            </div>
        </div>

        <div class="hero-content">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTVwBN1I5lKeX0qttb9DXw9gRIC3Y19VEFKsg&s" alt="GEHU Logo" class="logo-animated">
            <h1 class="typing-title">NAVIC ERA</h1>
            <p class="subtitle">GEHU Campus Navigation System</p>
        </div>
    </header>

    <!-- Features Section -->
    <section class="features">
        <h2 class="section-title">Explore Powerful Features</h2>

        <div class="cards-grid">
            <div class="card" onclick="location.href='view_map.php'">
                <div class="card-icon">🗺️</div>
                <div class="card-body">
                    <h3 class="card-title">Interactive Campus Map</h3>
                    <p>Explore every building and floor in stunning detail</p>
                </div>
            </div>

            <div class="card" onclick="location.href='find_path.php'">
                <div class="card-icon">🧭</div>
                <div class="card-body">
                    <h3 class="card-title">Smart Path Finding</h3>
                    <p>Shortest & fastest routes calculated instantly</p>
                </div>
            </div>

            <!-- AR Navigation card removed as requested -->

            <div class="card" onclick="location.href='about.php'">
                <div class="card-icon">📚</div>
                <div class="card-body">
                    <h3 class="card-title">Help Center</h3>
                    <p>Guides, tutorials and FAQs</p>
                </div>
            </div>

            <div class="card" onclick="location.href='contact.php'">
                <div class="card-icon">✉️</div>
                <div class="card-body">
                    <h3 class="card-title">Contact & Feedback</h3>
                    <p>Reach out to us or share your valuable suggestions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Assistant Button → ai_query.php -->
<a href="campus_ai_ui.php" class="ai-fab" id="aiBtn" title="Open Campus AI">
    <i class="fas fa-robot"></i>
</a>

    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        document.getElementById('username').textContent = localStorage.getItem('username') || 'Student';

        particlesJS('particles', {
            particles: {
                number: { value: 85 },
                color: { value: ['#3b82f6', '#60a5fa', '#93c5fd'] },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 },
                move: { enable: true, speed: 1.8 }
            },
            interactivity: { events: { onhover: { enable: true, mode: 'repulse' } } }
        });

        gsap.from(".typing-title", { width: 0, duration: 4.5, ease: "power2.inOut" });
        gsap.from(".subtitle", { opacity: 0, y: 50, duration: 1.5, delay: 4.5 });

        gsap.utils.toArray('.card').forEach((card, i) => {
            gsap.from(card, {
                scrollTrigger: { trigger: card, start: "top 82%" },
                y: 180, opacity: 0, duration: 1.2, delay: i * 0.18, ease: "back.out(1.6)"
            });
        });

        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 100);
        });

        document.getElementById('aiBtn').addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.style.position = 'absolute'; ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255,255,255,0.7)'; ripple.style.transform = 'scale(0)';
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