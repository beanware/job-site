<?php
session_start();
require_once 'db.php';

// Ensure admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = $errors = [];

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    $comment = trim($_POST['comment'] ?? '');

    // Fetch verification details
    $stmt = $pdo->prepare("
        SELECT v.id, v.provider_id, p.user_id, u.full_name
        FROM verifications v
        JOIN providers p ON v.provider_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE v.id = ?
    ");
    $stmt->execute([$id]);
    $ver = $stmt->fetch();

    if ($ver) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE verifications SET status='approved', admin_comment=NULL WHERE id=?")->execute([$id]);
            $pdo->prepare("UPDATE providers SET verification_status='verified' WHERE id=?")->execute([$ver['provider_id']]);

            // Create notification
            $msg = "Your verification request has been approved.";
            $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$ver['user_id'], $msg]);
            $success[] = "Approved verification for " . htmlspecialchars($ver['full_name']);

        } elseif ($action === 'reject') {
            if ($comment === '') {
                $errors[] = "Rejection comment is required.";
            } else {
                $pdo->prepare("UPDATE verifications SET status='rejected', admin_comment=? WHERE id=?")->execute([$comment, $id]);
                $pdo->prepare("UPDATE providers SET verification_status='unverified' WHERE id=?")->execute([$ver['provider_id']]);

                $msg = "Your verification request was rejected. Admin comment: " . $comment;
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$ver['user_id'], $msg]);
                $success[] = "Rejected verification for " . htmlspecialchars($ver['full_name']);
            }
        }
    } else {
        $errors[] = "Invalid verification request.";
    }
}

// Fetch pending verifications
$stmt = $pdo->query("
    SELECT v.id, v.method, v.document_path, v.status, v.created_at, 
           p.id AS provider_id, p.verification_status, u.full_name
    FROM verifications v
    JOIN providers p ON v.provider_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE v.status = 'pending'
    ORDER BY v.created_at DESC
");
$verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify Providers — SkillConnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a href="admin_dashboard.php" class="btn btn-ghost text-xl">SkillConnect Admin</a>
    <div class="flex-1"></div>
    <a href="logout.php" class="btn btn-error">Logout</a>
  </div>

  <div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Provider Verification Requests</h2>

    <?php foreach ($success as $s): ?><div class="alert alert-success"><?=htmlspecialchars($s)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <?php if (empty($verifications)): ?>
      <p class="text-gray-500 italic">No pending verification requests.</p>
    <?php endif; ?>

    <div class="grid gap-6">
    <?php foreach ($verifications as $v): ?>
      <div class="card bg-base-100 shadow-md border-l-4 border-yellow-500">
        <div class="card-body">
          <h3 class="text-lg font-bold"><?=htmlspecialchars($v['full_name'])?></h3>
          <p>Verification Type: <strong><?=htmlspecialchars($v['method'])?></strong></p>
          <p>Submitted: <?=htmlspecialchars($v['created_at'])?></p>
          <p>Current Status: 
            <span class="badge <?= $v['verification_status']=='verified'?'badge-success':'badge-warning' ?>">
              <?=ucfirst($v['verification_status'])?>
            </span>
          </p>

          <?php if (file_exists($v['document_path'])): ?>
            <?php if (preg_match('/\\.(jpg|jpeg|png)$/i', $v['document_path'])): ?>
              <img src="<?=htmlspecialchars($v['document_path'])?>" class="w-48 h-auto rounded border mt-2" alt="Document">
            <?php elseif (preg_match('/\\.pdf$/i', $v['document_path'])): ?>
              <a href="<?=htmlspecialchars($v['document_path'])?>" target="_blank" class="link link-primary mt-2">View PDF Document</a>
            <?php endif; ?>
          <?php else: ?>
            <p class="text-red-500 text-sm mt-2">Document missing on server.</p>
          <?php endif; ?>

          <form method="post" class="mt-4">
            <input type="hidden" name="id" value="<?=$v['id']?>">
            <textarea name="comment" class="textarea textarea-bordered w-full mb-2" placeholder="Optional comment (required if rejecting)"></textarea>
            <div class="flex gap-2">
              <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              <button name="action" value="reject" class="btn btn-error btn-sm">Reject</button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
