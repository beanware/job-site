<?php
session_start();
require_once 'db.php';

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = $errors = [];

// --- Handle admin actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = intval($_POST['job_id']);
    $action = $_POST['action'] ?? '';

    // Check job existence
    $stmt = $pdo->prepare("SELECT id, title, status FROM jobs WHERE id=?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job) {
        $errors[] = "Job not found.";
    } else {
        switch ($action) {
            case 'close':
                $pdo->prepare("UPDATE jobs SET status='completed', completed_at=NOW() WHERE id=?")->execute([$job_id]);
                $success[] = "Job '{$job['title']}' marked as completed.";
                break;

            case 'reopen':
                $pdo->prepare("UPDATE jobs SET status='open', completed_at=NULL WHERE id=?")->execute([$job_id]);
                $success[] = "Job '{$job['title']}' reopened.";
                break;

            case 'delete':
                $pdo->prepare("DELETE FROM applications WHERE job_id=?")->execute([$job_id]);
                $pdo->prepare("DELETE FROM jobs WHERE id=?")->execute([$job_id]);
                $success[] = "Job '{$job['title']}' deleted permanently.";
                break;

            default:
                $errors[] = "Invalid action.";
        }
    }
}

// Fetch jobs
$stmt = $pdo->query("
    SELECT j.*, 
           u.full_name AS employer_name,
           (SELECT u2.full_name 
            FROM applications a
            JOIN providers p ON a.worker_id=p.id
            JOIN users u2 ON p.user_id=u2.id
            WHERE a.job_id=j.id AND a.status='accepted' LIMIT 1) AS provider_name
    FROM jobs j
    JOIN users u ON j.employer_id = u.id
    ORDER BY j.created_at DESC
");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Jobs — SkillConnect Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a href="admin_dashboard.php" class="btn btn-ghost text-xl">SkillConnect Admin</a>
    <div class="flex-1"></div>
    <a href="logout.php" class="btn btn-error">Logout</a>
  </div>

  <div class="max-w-7xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Manage Jobs</h2>

    <?php foreach ($success as $s): ?><div class="alert alert-success"><?=htmlspecialchars($s)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <div class="overflow-x-auto">
      <table class="table table-zebra w-full">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Employer</th>
            <th>Provider</th>
            <th>Status</th>
            <th>Budget</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><?=$j['id']?></td>
              <td><?=htmlspecialchars($j['title'])?></td>
              <td><?=htmlspecialchars($j['employer_name'])?></td>
              <td><?=htmlspecialchars($j['provider_name'] ?? '—')?></td>
              <td>
                <span class="badge 
                  <?= $j['status']=='open'?'badge-warning':($j['status']=='assigned'?'badge-info':'badge-success')?>">
                  <?=ucfirst($j['status'])?>
                </span>
              </td>
              <td><?=number_format($j['budget'], 2)?></td>
              <td><?=htmlspecialchars($j['created_at'])?></td>
              <td>
                <form method="post" class="flex gap-2">
                  <input type="hidden" name="job_id" value="<?=$j['id']?>">
                  <?php if ($j['status'] !== 'completed'): ?>
                    <button name="action" value="close" class="btn btn-success btn-xs">Mark Completed</button>
                  <?php endif; ?>
                  <?php if ($j['status'] === 'completed'): ?>
                    <button name="action" value="reopen" class="btn btn-info btn-xs">Reopen</button>
                  <?php endif; ?>
                  <button name="action" value="delete" class="btn btn-error btn-xs" 
                          onclick="return confirm('Delete this job permanently?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
