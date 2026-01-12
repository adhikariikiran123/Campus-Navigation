<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';

$errors = [];
$success = '';

// Escape function
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// STEP 1 — VERIFY IDENTITIY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify') {

    $studentId = trim($_POST['studentId'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $dob_raw   = trim($_POST['dob'] ?? '');

    if ($studentId === '') $errors[] = "Student ID is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($dob_raw === '') $errors[] = "Date of birth is required.";

    // Validate DOB
    $dob = null;
    if ($dob_raw !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $dob_raw);
        if (!$d) {
            $errors[] = "Invalid date format.";
        } else {
            $dob = $d->format('Y-m-d');
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getPDO();

            $q = $pdo->prepare("SELECT id, email, student_id, dob FROM users WHERE (email = :email OR student_id = :sid) LIMIT 1");
            $q->execute([':email' => $email, ':sid' => $studentId]);
            $user = $q->fetch();

            if (!$user) {
                $errors[] = "User not found.";
            } else {
                if (!empty($user['dob']) && $user['dob'] !== $dob) {
                    $errors[] = "Date of birth does not match.";
                }
            }

            if (empty($errors)) {
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_expires'] = time() + 900; // 15 minutes
            }

        } catch (PDOException $ex) {
            $errors[] = "Database error: " . $ex->getMessage();
        }
    }
}

// STEP 2 — SET NEW PASSWORD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_password') {

    if (empty($_SESSION['reset_user_id']) || time() > ($_SESSION['reset_expires'] ?? 0)) {
        $errors[] = "Session expired. Please try again.";
    } else {
        $pw  = $_POST['password'] ?? '';
        $pw2 = $_POST['confirmPassword'] ?? '';

        if ($pw === '' || $pw2 === '') $errors[] = "Both password fields are required.";
        if ($pw !== $pw2) $errors[] = "Passwords do not match.";
        if (strlen($pw) < 6) $errors[] = "Password must be at least 6 characters.";

        if (empty($errors)) {
            try {
                $pdo = getPDO();

                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $u = $pdo->prepare("UPDATE users SET password_hash = :ph WHERE id = :id");
                $u->execute([
                    ':ph' => $hash,
                    ':id' => $_SESSION['reset_user_id']
                ]);

                unset($_SESSION['reset_user_id'], $_SESSION['reset_expires']);
                $success = "Password changed successfully! You can now log in.";

            } catch (PDOException $ex) {
                $errors[] = "Error updating password: " . $ex->getMessage();
            }
        }
    }
}

$step2 = !empty($_SESSION['reset_user_id']) && time() < ($_SESSION['reset_expires'] ?? 0);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        /* SAME AS LOGIN PAGE */
        body {
            font-family: Calibri, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: url("https://d34vm3j4h7f97z.cloudfront.net/optimized/3X/b/7/b778214d201f0560b91fe542b6ee517324ab0973_2_1024x576.jpeg");
            background-size: cover;
            background-repeat: no-repeat;
            margin: 0;
        }
        .main {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            padding: 25px;
            width: 400px;
            text-align: center;
            background-color: rgba(255,255,255,0.85);
        }
        .logo {
            width: 380px;
            margin-bottom: 10px;
        }
        h2 {
            color: #2E7D32;
            margin-bottom: 15px;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
            color: #444;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
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
        .error { background:#ffecec; padding:10px; border-left:4px solid red; margin-bottom:10px; }
        .success { background:#e8ffea; padding:10px; border-left:4px solid green; margin-bottom:10px; }
        a { color:#2E7D32; text-decoration:none; font-weight:bold; }
    </style>
</head>

<body>
<div class="main">
    <img src="https://student.gehu.ac.in/Account/showClientLoginPageLogo" class="logo">

    <h2>Forgot Password</h2>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach($errors as $e) echo "<div>".e($e)."</div>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?= e($success) ?></div>
        <a href="login.php">← Back to Login</a>
    <?php endif; ?>

    <?php if(!$success && !$step2): ?>
    <!-- STEP 1: VERIFY FORM -->
    <p>Please verify your details to continue.</p>

    <form method="POST">
        <input type="hidden" name="action" value="verify">

        <label>Student ID</label>
        <input type="text" name="studentId" required>

        <label>Email ID</label>
        <input type="email" name="email" required>

        <label>Date of Birth</label>
        <input type="date" name="dob" required>

        <button type="submit">Verify</button>
    </form>

    <p><a href="login.php">← Back to Login</a></p>

    <?php endif; ?>


    <?php if(!$success && $step2): ?>
    <!-- STEP 2: SET NEW PASSWORD -->
    <p>Enter your new password.</p>

    <form method="POST">
        <input type="hidden" name="action" value="set_password">

        <label>New Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirmPassword" required>

        <button type="submit">Save New Password</button>
    </form>

    <?php endif; ?>

    <footer style="margin-top:10px; font-size:13px;">
        © 2025 Team CodeVerse | GEHU Campus Navigation System
    </footer>
</div>
</body>
</html>