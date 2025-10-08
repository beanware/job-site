<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = [];
if (isset($_POST['approve'])) {
    $id = intval($_POST['approve']);
    $pdo->prepare("UPDATE verifications SET status='approved', reviewed_at=NOW() WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE providers SET verification_status='verified' WHERE id=(SELECT provider_id FROM verifications WHERE id=?)")->execute([$id]);
    $success[] = "Verification #$id approved.";
}
if (isset($_POST['reject'])) {
    $id = intval($_POST['reject']);
    $pdo->prepare("UPDATE verifications SET status='rejected', reviewed_at=NOW() WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE providers SET verification_status='rejected' WHERE id=(SELECT provider_id FROM verifications WHERE id=?)")->execute([$id]);
    $success[] = "Verification #$id rejected.";
}

$stmt = $pdo->query("
    SELECT v.id, v.method, v.status, v.document_path, u.full_name, v.created_at
    FROM verifications v
    JOIN providers p ON v.provider_id=p.id
    JOIN users u ON p.user_id=u.id
    ORDER BY v.created_at DESC
");
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Verification Panel — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a class="btn btn-ghost text-xl">SkillBridge Admin</a>
    <div class="flex-1"></div>
    <a class="btn btn-outline" href="logout.php">Logout</a>
  </div>

  <div class="max-w-5xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-4">Verification Requests</h2>

    <?php foreach ($success as $s): ?>
      <div class="alert alert-success mb-2"><?=htmlspecialchars($s)?></div>
    <?php endforeach; ?>

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th>ID</th>
            <th>Provider</th>
            <th>Method</th>
            <th>Status</th>
            <th>Document</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['id'])?></td>
            <td><?=htmlspecialchars($r['full_name'])?></td>
            <td><?=htmlspecialchars($r['method'])?></td>
            <td><span class="badge <?= $r['status']=='approved'?'badge-success':($r['status']=='rejected'?'badge-error':'badge-warning')?>"><?=htmlspecialchars($r['status'])?></span></td>
            <td><a href="<?=htmlspecialchars($r['document_path'])?>" target="_blank" class="link link-primary">View</a></td>
            <td>
              <form method="post" class="flex gap-2">
                <button name="approve" value="<?=$r['id']?>" class="btn btn-sm btn-success">Approve</button>
                <button name="reject" value="<?=$r['id']?>" class="btn btn-sm btn-error">Reject</button>
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
