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
$worker_id = $stmt->fetchColumn();

// Fetch jobs
$stmt = $pdo->query("
    SELECT j.*, u.full_name AS employer_name 
    FROM jobs j 
    JOIN users u ON j.employer_id = u.id 
    WHERE j.status='open'
    ORDER BY j.created_at DESC
");

// Apply to job
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $job_id = intval($_POST['apply']);
    // Check if already applied
    $chk = $pdo->prepare("SELECT id FROM applications WHERE job_id=? AND worker_id=?");
    $chk->execute([$job_id, $worker_id]);
    if ($chk->fetch()) {
        $success = "Already applied for this job.";
    } else {
        $pdo->prepare("INSERT INTO applications (job_id, worker_id) VALUES (?, ?)")->execute([$job_id, $worker_id]);
        $success = "Application sent successfully.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Browse Jobs — SkillBridge</title>
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

  <div class="max-w-5xl mx-auto p-6">
    <?php if ($success): ?><div class="alert alert-info"><?=$success?></div><?php endif; ?>

    <h2 class="text-2xl font-bold mb-4">Available Jobs</h2>
    <div class="grid gap-4">
    <?php foreach ($stmt as $job): ?>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="card-title"><?=htmlspecialchars($job['title'])?></h3>
          <p><?=nl2br(htmlspecialchars($job['description']))?></p>
          <p><strong>Location:</strong> <?=htmlspecialchars($job['location'])?> | <strong>Budget:</strong> KSh <?=number_format($job['budget'])?></p>
          <p><em>Posted by <?=htmlspecialchars($job['employer_name'])?></em></p>

          <form method="post">
            <button name="apply" value="<?=$job['id']?>" class="btn btn-primary btn-sm">Apply</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
