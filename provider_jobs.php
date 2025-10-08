<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM providers WHERE user_id=?");
$stmt->execute([$user_id]);
$provider_id = $stmt->fetchColumn();

// Fetch jobs linked to provider
$stmt = $pdo->prepare("
  SELECT j.id, j.title, j.description, j.status, j.location, j.completed_at, u.full_name AS employer_name
  FROM jobs j
  JOIN applications a ON a.job_id=j.id
  JOIN users u ON j.employer_id=u.id
  WHERE a.worker_id=? AND a.status='accepted'
  ORDER BY j.created_at DESC
");
$stmt->execute([$provider_id]);
$jobs = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Jobs — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a class="btn btn-ghost text-xl">SkillBridge</a>
    <div class="flex-1"></div>
    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
    <a href="logout.php" class="btn btn-error ml-2">Logout</a>
  </div>

  <div class="max-w-5xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">My Jobs</h2>
    <div class="grid gap-4">
      <?php foreach ($jobs as $j): ?>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="card-title"><?=htmlspecialchars($j['title'])?></h3>
          <p><?=nl2br(htmlspecialchars($j['description']))?></p>
          <p><strong>Employer:</strong> <?=htmlspecialchars($j['employer_name'])?></p>
          <p><strong>Location:</strong> <?=htmlspecialchars($j['location'])?></p>
          <p><strong>Status:</strong> 
            <span class="badge <?= $j['status']=='completed'?'badge-success':($j['status']=='assigned'?'badge-info':'badge-warning')?>"><?=$j['status']?></span>
          </p>
          <?php if ($j['status']=='completed'): ?>
            <p class="text-sm text-gray-500 italic">Completed on <?=$j['completed_at']?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($jobs)): ?><p>No jobs yet.</p><?php endif; ?>
    </div>
  </div>
</body>
</html>
