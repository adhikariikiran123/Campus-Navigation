<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function old($name) {
    return isset($_POST[$name]) ? htmlspecialchars($_POST[$name], ENT_QUOTES, 'UTF-8') : '';
}

$errors = [];
$success = "";

function clearPost() {
    foreach ($_POST as $k => $v) unset($_POST[$k]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['fname'] ?? '');
    $last_name  = trim($_POST['lname'] ?? '');
    $email_raw  = trim($_POST['email'] ?? '');
    $dob_raw    = trim($_POST['dob'] ?? '');
    $student_id = trim($_POST['studentId'] ?? '');
    $gender_raw = trim($_POST['gender'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirmPassword'] ?? '';

    if ($first_name === '') $errors[] = 'First name is required.';
    if ($last_name === '')  $errors[] = 'Last name is required.';
    if ($email_raw === '')  $errors[] = 'Email is required.';
    if ($dob_raw === '')    $errors[] = 'Date of birth is required.';
    if ($student_id === '') $errors[] = 'Student ID is required.';
    if ($gender_raw === '') $errors[] = 'Gender is required.';
    if ($password === '' || $confirm === '') $errors[] = 'Password and confirm password are required.';

    $email = strtolower($email_raw);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    $dob = null;
    if ($dob_raw !== '') {
        $dobj = DateTime::createFromFormat('Y-m-d', $dob_raw);
        $derr = DateTime::getLastErrors();
        if ($dobj === false || $derr['warning_count'] > 0 || $derr['error_count'] > 0) {
            $errors[] = 'Invalid date format. Use YYYY-MM-DD.';
        } else {
            $dob = $dobj->format('Y-m-d');
        }
    }

    $gender_map = ['male'=>'Male','female'=>'Female','other'=>'Other'];
    if (!isset($gender_map[$gender_raw])) {
        $errors[] = 'Invalid gender selection.';
    }
    $gender = $gender_map[$gender_raw] ?? '';

    if (empty($errors)) {
        try {
            $pdo = getPDO();

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :em OR student_id = :sid LIMIT 1");
            $stmt->execute([':em'=>$email, ':sid'=>$student_id]);
            if ($stmt->fetch()) {
                $errors[] = "Email or Student ID already exists!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $full_name = trim($first_name . ' ' . $last_name);

                $insert = $pdo->prepare("
                    INSERT INTO users
                    (first_name, last_name, dob, student_id, gender, name, email, password_hash)
                    VALUES (:fn, :ln, :dob, :sid, :gen, :nm, :em, :ph)
                ");
                $insert->execute([
                    ':fn' => $first_name,
                    ':ln' => $last_name,
                    ':dob' => $dob,
                    ':sid' => $student_id,
                    ':gen' => $gender,
                    ':nm' => $full_name,
                    ':em' => $email,
                    ':ph' => $hash
                ]);

                $success = "Registration successful ✔";
                clearPost();
            }

        } catch (PDOException $ex) {
            $errors[] = "Database error: " . $ex->getMessage();
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Create Account</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Background: full-bleed image, cover, centered.
   Card uses translucent white so form stays readable on the image. */
body{
  font-family:Arial,Helvetica,sans-serif;
  margin:0;
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  background-image: url("https://d34vm3j4h7f97z.cloudfront.net/optimized/3X/b/7/b778214d201f0560b91fe542b6ee517324ab0973_2_1024x576.jpeg");
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  color: #111;
}

/* subtle dark overlay to increase contrast */
.bg-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10,10,10,0.35);
  pointer-events: none;
  z-index: 0;
}

.card{
  background: rgba(255,255,255,0.96); /* slightly translucent */
  padding:25px;
  border-radius:12px;
  box-shadow:0 12px 30px rgba(0,0,0,0.25);
  width:420px;
  z-index: 1;
}

.logo{ display:block; width:72px; height:72px; margin:0 auto 8px; border-radius:50%; box-shadow:0 6px 18px rgba(0,0,0,0.12); }

h1{ text-align:center; color:#2e8b57; margin:0 0 12px; font-size:22px; }
.error-box{ background:#ffe0e0; padding:10px; border-left:4px solid #ff4d4d; margin-bottom:12px; }
.success-box{ background:#d9ffe2; padding:10px; border-left:4px solid #1fbf4b; margin-bottom:12px; font-weight:bold; color:#1a7f33; }
.form-group{ margin-bottom:12px; }
label{ font-weight:bold; margin-bottom:5px; display:block; color:#333; }
input, select { width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; }
button{ width:100%; padding:12px; background:#2e8b57; color:white; border:none; border-radius:6px; font-size:16px; margin-top:10px; cursor:pointer; }
.note{ text-align:center; font-size:14px; margin-top:10px; color:#444; }
@media (max-width:480px){
  .card{ width: calc(100% - 32px); padding:18px; border-radius:10px; }
}
</style>
</head>

<body>
<div class="bg-overlay" aria-hidden="true"></div>

<div class="card">
    <img class="logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTVwBN1I5lKeX0qttb9DXw9gRIC3Y19VEFKsg&s" alt="logo">
    <h1>Create Your Account</h1>

<?php if (!empty($errors)): ?>
<div class="error-box">
  <?php foreach ($errors as $e) echo "<div>" . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="success-box"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" novalidate>

<div class="form-group">
<label>First Name</label>
<input name="fname" value="<?= old('fname') ?>" required>
</div>

<div class="form-group">
<label>Last Name</label>
<input name="lname" value="<?= old('lname') ?>" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" name="email" value="<?= old('email') ?>" required>
</div>

<div class="form-group">
<label>Date of Birth</label>
<input type="date" name="dob" value="<?= old('dob') ?>" required>
</div>

<div class="form-group">
<label>Student ID</label>
<input name="studentId" value="<?= old('studentId') ?>" required>
</div>

<div class="form-group">
<label>Gender</label>
<select name="gender" required>
    <option value="">Select Gender</option>
    <option value="male" <?= (old('gender')==='male')?'selected':'' ?>>Male</option>
    <option value="female" <?= (old('gender')==='female')?'selected':'' ?>>Female</option>
    <option value="other" <?= (old('gender')==='other')?'selected':'' ?>>Other</option>
</select>
</div>

<div class="form-group">
<label>Create Password</label>
<input type="password" name="password" required>
</div>

<div class="form-group">
<label>Confirm Password</label>
<input type="password" name="confirmPassword" required>
</div>

<button type="submit">Register</button>

</form>

<p class="note">
Already have an account? <a href="login.php">Login here</a>
</p>

</div>
</body>
</html>