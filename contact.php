<?php
// contact.php
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Database Configuration
$db_available = false;
if (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
    if (function_exists('getPDO')) $db_available = true;
}

$errors = [];
$success = '';

// Helper to keep input values after error
function old($name) {
    return isset($_POST[$name]) ? htmlspecialchars($_POST[$name], ENT_QUOTES, 'UTF-8') : '';
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nameRaw    = trim($_POST['name'] ?? '');
    $emailRaw   = trim($_POST['email'] ?? '');
    $messageRaw = trim($_POST['message'] ?? '');

    // Validation
    if ($nameRaw === '')    $errors[] = 'Please enter your name.';
    if ($emailRaw === '')   $errors[] = 'Please enter your email.';
    if ($messageRaw === '') $errors[] = 'Please enter your message.';
    if ($emailRaw !== '' && !filter_var($emailRaw, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email address.';

    if (empty($errors)) {
        
        // A. Save to Database
        $savedToDb = false;
        if ($db_available) {
            try {
                $pdo = getPDO();
                // Ensure your table 'feedbacks' exists with columns: id, name, email, message, created_at
                $stmt = $pdo->prepare("INSERT INTO feedbacks (name, email, message) VALUES (:name, :email, :message)");
                $stmt->execute([
                    ':name' => $nameRaw, 
                    ':email' => $emailRaw, 
                    ':message' => $messageRaw
                ]);
                $savedToDb = true;
            } catch (PDOException $ex) {
                // Log error locally if needed, but don't stop the email process
                error_log("DB Error: " . $ex->getMessage());
            }
        }

        // B. Send to Formspree API via cURL (Background Request)
        $formspree_sent = false;
        $formspree_url = "https://formspree.io/f/xdkqdpwd";

        $postData = [
            'name' => $nameRaw,
            'email' => $emailRaw,
            'message' => $messageRaw,
            '_subject' => "New Contact from NAVIC ERA: $nameRaw" // Optional custom subject
        ];

        $ch = curl_init($formspree_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json' // Tell Formspree we want JSON back
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Formspree returns 200 on success
        if ($http_code === 200) {
            $formspree_sent = true;
        }

        // C. Final Success Logic
        if ($savedToDb || $formspree_sent) {
            $success = 'Thank you! Your feedback has been recorded and sent to the team.';
            // Clear inputs on success
            $_POST = [];
        } else {
            $errors[] = 'System error: Unable to submit feedback to database or email provider.';
        }
    }
}

$username = $_SESSION['user_name'] ?? 'Student';
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact / Feedback | NAVIC ERA</title>
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
            max-width: 900px;
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
            margin-bottom: 1.5rem;
            font-weight: 800;
        }
        .lead {
            text-align: center;
            font-size: 1.3rem;
            color: #94a3b8;
            margin-bottom: 3rem;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #60a5fa;
        }
        input, textarea {
            width: 100%;
            padding: 16px;
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(96,165,250,0.3);
            border-radius: 16px;
            color: white;
            font-size: 1.1rem;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 20px rgba(96,165,250,0.4);
        }
        textarea { min-height: 160px; resize: vertical; }
        .btn {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.4s;
        }
        .btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(59,130,246,0.4);
        }
        .alert {
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            font-weight: 600;
        }
        .error { background: rgba(239,68,68,0.2); border-left: 6px solid #ef4444; color: #fca5a5; }
        .success { background: rgba(34,197,94,0.2); border-left: 6px solid #22c55e; color: #86efac; }

        .contact-info {
            margin-top: 50px;
            text-align: center;
            padding: 30px;
            background: rgba(15,23,42,0.6);
            border-radius: 20px;
            border: 1px solid rgba(96,165,250,0.2);
        }
        .contact-info h3 {
            font-size: 1.8rem;
            color: #60a5fa;
            margin-bottom: 20px;
        }
        .contact-info p { font-size: 1.1rem; color: #cbd5e1; margin: 12px 0; }
        .contact-info a { color: #60a5fa; text-decoration: none; }
        .contact-info a:hover { text-decoration: underline; }

        .footer {
            text-align: center;
            padding: 40px;
            font-size: 1.1rem;
            color: #94a3b8;
            background: rgba(15,23,42,0.9);
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
        }
        .ai-fab:hover { transform: scale(1.12); }
        @keyframes fabPulse { 0%,100% { box-shadow: 0 12px 45px rgba(59,130,246,0.6); } 50% { box-shadow: 0 12px 65px rgba(59,130,246,0.9); } }
    </style>
</head>
<body>

    <div id="particles"></div>

    <div class="navbar" id="navbar">
        <div class="nav-logo">NAVIC ERA</div>
        <div class="user-info">
            Welcome, <span id="username"><?= $username ?></span> 👋
            <button onclick="location.href='dashboard.php'">🏠 Home</button>
            <button onclick="location.href='logout.php'">🚪 Logout</button>
        </div>
    </div>

    <div class="container">
        <h1>Contact / Feedback</h1>
        <p class="lead">We’d love to hear from you! Share your thoughts, suggestions, or report any issues.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $e) echo '<div>⚠ ' . $e . '</div>'; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success">✓ <?= $success ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label for="name">Full Name</label>
            <input id="name" name="name" type="text" placeholder="Enter your name" required value="<?= old('name') ?>">

            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" placeholder="your.email@gehu.ac.in" required value="<?= old('email') ?>">

            <label for="message">Your Message / Feedback</label>
            <textarea id="message" name="message" placeholder="Tell us everything..." required><?= old('message') ?></textarea>

            <button type="submit" class="btn">Send Feedback 🚀</button>
        </form>

        <div class="contact-info">
            <h3>📞 Get in Touch</h3>
            <p><strong>Developed by:</strong> Team CodeVerse (BCA 2023–26)</p>
            <p><strong>Institution:</strong> Graphic Era Hill University, Dehradun</p>
            <p><strong>Email:</strong> <a href="mailto:gehucodeverse@gmail.com">gehucodeverse@gmail.com</a></p>
        </div>
    </div>

    <div class="footer">
        © 2025 Team CodeVerse | NAVIC ERA - GEHU Campus Navigation System | BCA Batch 2026
    </div>

    <a href="campus_ai_ui.php" class="ai-fab" id="aiBtn">
        <i class="fas fa-robot"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        // Fallback username
        if (!"<?= addslashes($username) ?>") {
            const stored = localStorage.getItem('username');
            if (stored) document.getElementById('username').textContent = stored;
        }

        particlesJS('particles', {
            particles: { number: { value: 90 }, color: { value: ['#3b82f6','#60a5fa','#93c5fd'] },
                opacity: { value: 0.5, random: true }, size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 },
                move: { enable: true, speed: 1.8 }
            },
            interactivity: { events: { onhover: { enable: true, mode: 'repulse' } } }
        });

        gsap.from(".container > *", {
            scrollTrigger: { trigger: ".container", start: "top 80%" },
            y: 80, opacity: 0, duration: 1.2, stagger: 0.18, ease: "power4.out"
        });

        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 100);
        });

        // Ripple for AI FAB
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