<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$client_id = $_SESSION['user_id'];
$success = [];

// Mark job as completed
if (isset($_POST['complete'])) {
    $job_id = intval($_POST['complete']);
    $pdo->prepare("UPDATE jobs SET status='completed', completed_at=NOW() WHERE id=? AND employer_id=?")->execute([$job_id, $client_id]);
    $success[] = "Job marked as completed.";
}

$stmt = $pdo->prepare("
  SELECT j.*, 
         (SELECT full_name FROM users u JOIN providers p ON p.user_id=u.id 
          WHERE p.id=(SELECT worker_id FROM applications WHERE job_id=j.id AND status='accepted' LIMIT 1)
         ) AS provider_name
  FROM jobs j 
  WHERE j.employer_id=? 
  ORDER BY j.created_at DESC
");
$stmt->execute([$client_id]);
$jobs = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Jobs — SkillBridge</title>
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
    <h2 class="text-2xl font-bold mb-4">My Posted Jobs</h2>
    <?php foreach ($success as $s): ?><div class="alert alert-success"><?=$s?></div><?php endforeach; ?>

    <div class="grid gap-4">
      <?php foreach ($jobs as $job): ?>
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <h3 class="card-title"><?=htmlspecialchars($job['title'])?></h3>
            <p><?=nl2br(htmlspecialchars($job['description']))?></p>
            <p><strong>Status:</strong> <span class="badge <?= $job['status']=='completed'?'badge-success':($job['status']=='assigned'?'badge-info':'badge-warning')?>"><?=$job['status']?></span></p>
            <?php if ($job['provider_name']): ?><p><strong>Assigned to:</strong> <?=$job['provider_name']?></p><?php endif; ?>

            <div class="flex gap-2 mt-4">
              <?php if ($job['status']=='assigned'): ?>
                <form method="post">
                  <button name="complete" value="<?=$job['id']?>" class="btn btn-success btn-sm">Mark Completed</button>
                </form>
              <?php elseif ($job['status']=='completed'): ?>
                <a href="review_provider.php?job_id=<?=$job['id']?>" class="btn btn-primary btn-sm">Leave Review</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
