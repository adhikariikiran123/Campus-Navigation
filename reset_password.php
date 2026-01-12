<?php
require_once 'db.php';
require_once 'helpers.php';

$errors = [];
$token = $_GET['token'] ?? $_POST['token'] ?? null;
$email = $_GET['email'] ?? $_POST['email'] ?? null;

if (!$token || !$email) {
    flash_set('error', 'Invalid or missing password reset token.');
    header('Location: forgot_password.php');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE email = ? AND reset_token = ?");
$stmt->execute([$email, $token]);
$user = $stmt->fetch();

if (!$user) {
    flash_set('error', 'Invalid token or email.');
    header('Location: forgot_password.php');
    exit;
}

if (new DateTime() > new DateTime($user['reset_expires'])) {
    flash_set('error', 'Reset link has expired.');
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid form submission.";
    } else {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $password2) $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            flash_set('success', 'Password has been reset. You may now login.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Reset Password</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Reset Password</h1>
  <?php if (!empty($errors)): ?><div class="error"><?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e) . "</div>"; ?></div><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
    <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">
    <input type="hidden" name="email" value="<?=htmlspecialchars($email)?>">
    <label>New Password</label>
    <input type="password" name="password" required>
    <label>Confirm Password</label>
    <input type="password" name="password2" required>
    <button type="submit">Set new password</button>
  </form>
</div>
</body>
</html>
