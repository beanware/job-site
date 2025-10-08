<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$user_id]);

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications — SkillConnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a class="btn btn-ghost text-xl">SkillConnect</a>
    <div class="flex-1"></div>
    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
    <a href="logout.php" class="btn btn-error ml-2">Logout</a>
  </div>

  <div class="max-w-4xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Notifications</h2>
    <div class="space-y-3">
      <?php foreach ($notes as $n): ?>
        <div class="alert <?= $n['is_read']?'':'alert-info' ?>">
          <span><?=htmlspecialchars($n['message'])?></span>
          <span class="text-xs text-gray-400"><?=$n['created_at']?></span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($notes)): ?><p>No notifications yet.</p><?php endif; ?>
    </div>
  </div>
</body>
</html>
