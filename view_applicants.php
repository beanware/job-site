<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$client_id = $_SESSION['user_id'];
$success = [];

// --- Handle Accept / Reject Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = intval($_POST['id']);

    // Fetch job + application info for validation
    $stmt = $pdo->prepare("
        SELECT a.status AS app_status, p.user_id, j.id AS job_id, j.status AS job_status, j.title
        FROM applications a
        JOIN providers p ON a.worker_id = p.id
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id=?
    ");
    $stmt->execute([$app_id]);
    $data = $stmt->fetch();

    if ($data && $data['app_status'] === 'pending' && $data['job_status'] === 'open') {

        // --- ACCEPT APPLICATION ---
        if (isset($_POST['accept'])) {
            // Update statuses
            $pdo->prepare("UPDATE applications SET status='accepted' WHERE id=?")->execute([$app_id]);
            $pdo->prepare("
                UPDATE jobs 
                SET status='assigned' 
                WHERE id=(SELECT job_id FROM applications WHERE id=?)
            ")->execute([$app_id]);

            // Notify provider
            $infoStmt = $pdo->prepare("
                SELECT p.user_id AS provider_user_id, j.title 
                FROM applications a
                JOIN providers p ON a.worker_id=p.id
                JOIN jobs j ON a.job_id=j.id
                WHERE a.id=? LIMIT 1
            ");
            $infoStmt->execute([$app_id]);
            $info = $infoStmt->fetch();

            if ($info) {
                $msg = "Your application for '{$info['title']}' has been accepted.";
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$info['provider_user_id'], $msg]);
            }

            $success[] = "Application accepted.";
        }

        // --- REJECT APPLICATION ---
        elseif (isset($_POST['reject'])) {
            // Update application
            $pdo->prepare("UPDATE applications SET status='rejected' WHERE id=?")->execute([$app_id]);

            // Notify provider
            $infoStmt = $pdo->prepare("
                SELECT p.user_id AS provider_user_id, j.title 
                FROM applications a
                JOIN providers p ON a.worker_id=p.id
                JOIN jobs j ON a.job_id=j.id
                WHERE a.id=? LIMIT 1
            ");
            $infoStmt->execute([$app_id]);
            $info = $infoStmt->fetch();

            if ($info) {
                $msg = "Your application for '{$info['title']}' was rejected.";
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$info['provider_user_id'], $msg]);
            }

            $success[] = "Application rejected.";
        }

    } else {
        $success[] = "This application can no longer be modified.";
    }
}

// --- Retrieve Applicants for Active Jobs ---
$activeStmt = $pdo->prepare("
  SELECT a.id, a.status AS app_status, j.status AS job_status, j.title, j.location AS job_location,
         p.id AS provider_id, p.skill_category, p.experience_years, p.bio, p.verification_status, p.profile_image,
         u.full_name
  FROM applications a
  JOIN jobs j ON a.job_id=j.id
  JOIN providers p ON a.worker_id=p.id
  JOIN users u ON p.user_id=u.id
  WHERE j.employer_id=? 
    AND j.status IN ('open','assigned')
  ORDER BY j.created_at DESC, a.applied_at DESC
");
$activeStmt->execute([$client_id]);
$activeApplicants = $activeStmt->fetchAll();

// --- Retrieve Applicants for Completed Jobs ---
$completedStmt = $pdo->prepare("
  SELECT a.id, a.status AS app_status, j.status AS job_status, j.title, j.location AS job_location,
         p.id AS provider_id, p.skill_category, p.experience_years, p.bio, p.verification_status, p.profile_image,
         u.full_name
  FROM applications a
  JOIN jobs j ON a.job_id=j.id
  JOIN providers p ON a.worker_id=p.id
  JOIN users u ON p.user_id=u.id
  WHERE j.employer_id=? 
    AND j.status = 'completed'
  ORDER BY j.completed_at DESC
");
$completedStmt->execute([$client_id]);
$completedApplicants = $completedStmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Applicants — SkillConnect</title>
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

  <div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Applicants for Your Jobs</h2>
    <?php foreach ($success as $s): ?><div class="alert alert-success"><?=$s?></div><?php endforeach; ?>

    <!-- ACTIVE JOBS SECTION -->
    <div class="mb-12">
      <h3 class="text-xl font-semibold mb-4">Active Jobs</h3>
      <?php if (empty($activeApplicants)): ?>
        <p class="text-gray-500 italic">No applicants yet for your current open or assigned jobs.</p>
      <?php endif; ?>
      <div class="grid gap-6">
      <?php foreach ($activeApplicants as $a): ?>
        <div class="card bg-base-100 shadow-md">
          <div class="card-body flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <img src="<?= $a['profile_image'] ?: 'https://via.placeholder.com/100' ?>" 
                   class="w-24 h-24 rounded-full object-cover" alt="Profile">
            </div>

            <div class="flex-1">
              <h3 class="text-lg font-bold"><?=htmlspecialchars($a['full_name'])?></h3>
              <p class="text-sm text-gray-500">
                <?=htmlspecialchars($a['skill_category'])?> — <?=intval($a['experience_years'])?> years
              </p>
              <p class="mt-2"><?=nl2br(htmlspecialchars($a['bio']))?></p>
              <div class="mt-2">
                <?php
                $verify_label = $a['verification_status'];
                if ($verify_label === 'pending') {
                    $verify_label = 'unverified';
                }
                $verify_badge = ($a['verification_status'] === 'verified') ? 'badge-success' : 'badge-warning';
                ?>
                <span class="badge <?=$verify_badge?>"><?=ucfirst($verify_label)?></span>
              </div>
              <a href="portfolio.php?id=<?=$a['provider_id']?>" class="link link-primary mt-2 inline-block">View Portfolio</a>
            </div>

            <div class="flex flex-col gap-2">
              <p class="text-sm font-semibold text-gray-600">
                Job: <span class="text-gray-900"><?=htmlspecialchars($a['title'])?></span><br>
                <small>(<?=htmlspecialchars($a['job_location'])?>)</small>
              </p>
              <p class="text-xs text-gray-500 uppercase font-semibold mt-1">Application Status:</p>
              <span class="badge 
                <?= $a['app_status']=='accepted'?'badge-success':
                    ($a['app_status']=='rejected'?'badge-error':'badge-warning')?>">
                <?=ucfirst($a['app_status'])?>
              </span>

              <?php if ($a['app_status'] === 'pending' && $a['job_status'] === 'open'): ?>
                <form method="post">
                  <input type="hidden" name="id" value="<?=$a['id']?>">
                  <button name="accept" class="btn btn-success btn-sm">Accept</button>
                  <button name="reject" class="btn btn-error btn-sm">Reject</button>
                </form>
              <?php else: ?>
                <p class="text-sm text-gray-500 italic">Action already taken or job in progress.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>

    <!-- COMPLETED JOBS SECTION -->
    <div>
      <h3 class="text-xl font-semibold mb-4">Completed Jobs</h3>
      <?php if (empty($completedApplicants)): ?>
        <p class="text-gray-500 italic">No completed jobs yet.</p>
      <?php endif; ?>
      <div class="grid gap-6">
      <?php foreach ($completedApplicants as $a): ?>
        <div class="card bg-base-100 shadow-md border-l-4 border-green-500">
          <div class="card-body flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <img src="<?= $a['profile_image'] ?: 'https://via.placeholder.com/100' ?>" 
                   class="w-24 h-24 rounded-full object-cover" alt="Profile">
            </div>

            <div class="flex-1">
              <h3 class="text-lg font-bold"><?=htmlspecialchars($a['full_name'])?></h3>
              <p class="text-sm text-gray-500">
                <?=htmlspecialchars($a['skill_category'])?> — <?=intval($a['experience_years'])?> years
              </p>
              <p class="mt-2"><?=nl2br(htmlspecialchars($a['bio']))?></p>
              <div class="mt-2">
                <?php
                $verify_label = $a['verification_status'];
                if ($verify_label === 'pending') {
                    $verify_label = 'unverified';
                }
                $verify_badge = ($a['verification_status'] === 'verified') ? 'badge-success' : 'badge-warning';
                ?>
                <span class="badge <?=$verify_badge?>"><?=ucfirst($verify_label)?></span>
              </div>
              <a href="portfolio.php?id=<?=$a['provider_id']?>" class="link link-primary mt-2 inline-block">View Portfolio</a>
            </div>

            <div class="flex flex-col gap-2">
              <p class="text-sm font-semibold text-gray-600">
                Job: <span class="text-gray-900"><?=htmlspecialchars($a['title'])?></span><br>
                <small>(<?=htmlspecialchars($a['job_location'])?>)</small>
              </p>
              <span class="badge badge-success">Completed</span>
              <p class="text-gray-500 italic">No further actions available.</p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>

  </div>
</body>
</html>
