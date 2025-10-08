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

$success = $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = trim($_POST['caption']);
    if (!empty($_FILES['image']['name'])) {
        $dir = "uploads/portfolio/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $fileName = "portfolio_" . $provider_id . "_" . basename($_FILES['image']['name']);
        $target = $dir . $fileName;
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
            $stmt = $pdo->prepare("INSERT INTO portfolio_images (provider_id, image_path, caption) VALUES (?,?,?)");
            $stmt->execute([$provider_id, $target, $caption]);
            $success[] = "Image uploaded.";
        } else {
            $errors[] = "Only JPG or PNG allowed.";
        }
    } else {
        $errors[] = "No file selected.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM portfolio_images WHERE provider_id=? ORDER BY uploaded_at DESC");
$stmt->execute([$provider_id]);
$images = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Portfolio — SkillBridge</title>
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

  <div class="max-w-5xl mx-auto mt-8 card bg-base-100 shadow-xl">
    <div class="card-body">
      <h2 class="card-title">My Portfolio</h2>

      <?php foreach ($success as $s): ?><div class="alert alert-success"><?=$s?></div><?php endforeach; ?>
      <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=$e?></div><?php endforeach; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="image" class="file-input file-input-bordered w-full" accept="image/*" required>
        <input type="text" name="caption" placeholder="Short caption (optional)" class="input input-bordered w-full">
        <button class="btn btn-primary w-full">Upload</button>
      </form>

      <div class="grid md:grid-cols-3 gap-4 mt-6">
        <?php foreach ($images as $img): ?>
        <div class="card shadow">
          <figure><img src="<?=$img['image_path']?>" alt="Work sample" class="h-48 w-full object-cover"></figure>
          <div class="card-body">
            <p><?=$img['caption']?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
