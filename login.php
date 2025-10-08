<?php
// login.php
session_start();
require_once 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if ($password === '') $errors[] = 'Password required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>SkillBridge — Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center">
  <div class="card w-full max-w-md shadow-lg">
    <div class="card-body">
      <h2 class="card-title">Login</h2>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <ul class="list-disc pl-5">
            <?php foreach ($errors as $e): ?><li><?=htmlspecialchars($e)?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="label"><span class="label-text">Email</span></label>
          <input type="email" name="email" class="input input-bordered w-full" required>
        </div>
        <div>
          <label class="label"><span class="label-text">Password</span></label>
          <input type="password" name="password" class="input input-bordered w-full" required>
        </div>

        <div class="card-actions justify-end">
          <button class="btn btn-primary" type="submit">Login</button>
          <a class="btn btn-ghost" href="register.php">Register</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
