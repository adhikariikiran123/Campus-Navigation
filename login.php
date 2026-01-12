<?php
// login.php
// Requires: db.php (getPDO), optional helpers.php (csrf_token(), csrf_verify())
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (file_exists(__DIR__ . '/helpers.php')) require_once __DIR__ . '/helpers.php';

// --- CSRF fallback if helpers.php not present ---
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('csrf_verify')) {
    function csrf_verify($token) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($token) || empty($_SESSION['csrf_token'])) return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// --- CAPTCHA: support AJAX refresh ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['refresh_captcha'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) $captcha .= $chars[random_int(0, strlen($chars) - 1)];
    $_SESSION['captcha_text'] = $captcha;
    header('Content-Type: application/json');
    echo json_encode(['captcha' => $captcha]);
    exit;
}

// Ensure a captcha exists on initial load
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['captcha_text'])) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) $captcha .= $chars[random_int(0, strlen($chars) - 1)];
    $_SESSION['captcha_text'] = $captcha;
}

// --- Handle POST (login) ---
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission (CSRF).";
    } else {
        // read inputs
        $identifier = trim($_POST['studentId'] ?? ''); // can be student id or email
        $password   = $_POST['password'] ?? '';
        $captcha_in = trim($_POST['captcha_input'] ?? '');

        // basic validation
        if ($identifier === '') $errors[] = "Student ID or Email is required.";
        if ($password === '') $errors[] = "Password is required.";
        if ($captcha_in === '') $errors[] = "Captcha is required.";

        // captcha verify (server-side)
        if (!empty($_SESSION['captcha_text'])) {
            if ($captcha_in === '' || !hash_equals($_SESSION['captcha_text'], $captcha_in)) {
                $errors[] = "Invalid captcha. Please try again.";
            }
        } else {
            $errors[] = "Captcha expired. Please refresh and try again.";
        }

        // only attempt DB check if no errors
        if (empty($errors)) {
            try {
                $pdo = getPDO();
                // allow login by email OR student_id
                $sql = "SELECT id, password_hash, is_active, name, email, student_id FROM users WHERE email = ? OR student_id = ? LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$identifier, $identifier]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $errors[] = "Invalid credentials.";
                } elseif (isset($user['is_active']) && !$user['is_active']) {
                    $errors[] = "Account not active. Contact admin.";
                } else {
                    // success
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'] ?? ($user['email'] ?? $user['student_id'] ?? 'Student');
                    // clear captcha after successful login
                    unset($_SESSION['captcha_text']);
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (Exception $ex) {
                $errors[] = "Database error: " . $ex->getMessage();
            }
        }
    }

    // on any POST (success or failure) regenerate captcha to prevent replay
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha = '';
    for ($i = 0; $i < 6; $i++) $captcha .= $chars[random_int(0, strlen($chars) - 1)];
    $_SESSION['captcha_text'] = $captcha;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student Login Page</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body {
            font-family: Calibri, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: url("https://d34vm3j4h7f97z.cloudfront.net/optimized/3X/b/7/b778214d201f0560b91fe542b6ee517324ab0973_2_1024x576.jpeg");
            background-size: cover;
            background-repeat: no-repeat;
            flex-direction: column;
            margin: 0;
        }

        .main {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            padding: 20px;
            width: 420px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.88);
        }

        h1 {
            color: #4CAF50;
            margin: 0 0 8px;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            color: #555;
            font-weight: bold;
        }

        input[type=text],
        input[type=password] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            margin: 10px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 10px;
            font-size: 16px;
        }

        .logo {
            width: 320px;
            height: auto;
            margin-bottom: 8px;
        }

        .captcha-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 8px 10px;
            margin: 8px 0;
        }

        #captcha-text {
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
            font-weight: bold;
            color: #2E7D32;
        }

        .refresh-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 6px 10px;
            cursor: pointer;
        }

        .error-box {
            background: #ffecec;
            border-left: 4px solid #ff4d4d;
            padding: 10px;
            margin-bottom: 12px;
            text-align: left;
        }

        .note {
            font-size: 13px;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    <div class="main">
        <img src="https://student.gehu.ac.in/Account/showClientLoginPageLogo" alt="Student Logo" class="logo">
        <h1>Student Login</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</div>'; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" onsubmit="return clientValidateCaptcha()">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

            <label for="studentId">Student ID or Email</label>
            <input type="text" id="studentId" name="studentId" placeholder="Enter your Student ID or Email" required value="<?php echo htmlspecialchars($_POST['studentId'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password" required>

            <label for="captcha">Captcha</label>
            <div class="captcha-box">
                <span id="captcha-text"><?php echo htmlspecialchars($_SESSION['captcha_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                <button type="button" class="refresh-btn" id="refreshCaptcha">↻</button>
            </div>
            <input type="text" id="captcha-input" name="captcha_input" placeholder="Enter Captcha" required>

            <button type="submit">Login</button>
        </form>

        <p class="note">Forgot <a href="forgot_password.php">password?</a></p>
        <p class="note">Not registered? <a href="register.php">Create an account</a></p>

        <footer style="margin-top: 12px; font-size: 13px; color: #555;">
            © 2025 Graphic Era Hill University | Campus Navigation System
        </footer>
    </div>

    <script>
        // client-side check just for UX (server enforces captcha regardless)
        function clientValidateCaptcha() {
            const entered = document.getElementById('captcha-input').value.trim();
            if (!entered) {
                alert("Please enter the captcha text shown above.");
                return false;
            }
            return true;
        }

        // refresh captcha via AJAX endpoint
        document.getElementById('refreshCaptcha').addEventListener('click', async function () {
            try {
                const resp = await fetch('login.php?refresh_captcha=1');
                if (!resp.ok) throw new Error('Network error');
                const j = await resp.json();
                if (j.captcha) {
                    document.getElementById('captcha-text').textContent = j.captcha;
                    document.getElementById('captcha-input').value = '';
                }
            } catch (err) {
                alert('Could not refresh captcha. Try reloading the page.');
            }
        });
    </script>
</body>

</html>