<?php
require_once 'db.php';
$provider_id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
  SELECT p.*, u.full_name, u.location
  FROM providers p
  JOIN users u ON p.user_id=u.id
  WHERE p.id=?
");
$stmt->execute([$provider_id]);
$provider = $stmt->fetch();

if (!$provider) exit("Provider not found.");

$stmt = $pdo->prepare("SELECT * FROM portfolio_images WHERE provider_id=? ORDER BY uploaded_at DESC");
$stmt->execute([$provider_id]);
$images = $stmt->fetchAll();

$stmt = $pdo->prepare("
  SELECT r.rating, r.comment, u.full_name
  FROM reviews r
  JOIN users u ON r.employer_id=u.id
  WHERE r.provider_id=?
  ORDER BY r.created_at DESC
");
$stmt->execute([$provider_id]);
$reviews = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Provider Portfolio — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="max-w-5xl mx-auto mt-8 card bg-base-100 shadow-xl">
    <div class="card-body">
      <div class="flex items-center gap-4">
        <img src="<?=$provider['profile_image'] ?: 'https://via.placeholder.com/100'?>" class="w-24 h-24 rounded-full object-cover">
        <div>
          <h2 class="card-title"><?=htmlspecialchars($provider['full_name'])?></h2>
          <p><?=htmlspecialchars($provider['skill_category'])?> — <?=intval($provider['experience_years'])?> years experience</p>
          <span class="badge <?= $provider['verification_status']=='verified'?'badge-success':'badge-warning' ?>">
            <?=ucfirst($provider['verification_status'])?>
          </span>
          <p class="mt-1 text-sm text-gray-600"><?=htmlspecialchars($provider['location'])?></p>
        </div>
      </div>

      <hr class="my-6">

      <h3 class="text-xl font-semibold mb-3">Portfolio</h3>
      <div class="grid md:grid-cols-3 gap-4">
        <?php foreach ($images as $img): ?>
          <div class="card shadow">
            <figure><img src="<?=$img['image_path']?>" alt="Work sample" class="h-48 w-full object-cover"></figure>
            <div class="card-body"><p><?=$img['caption']?></p></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($images)): ?><p>No portfolio images yet.</p><?php endif; ?>
      </div>

      <hr class="my-6">

      <h3 class="text-xl font-semibold mb-3">Client Reviews</h3>
      <div class="space-y-3">
        <?php foreach ($reviews as $r): ?>
          <div class="card shadow-sm bg-base-200">
            <div class="card-body">
              <p class="text-yellow-500">⭐ <?=intval($r['rating'])?> / 5</p>
              <p><?=htmlspecialchars($r['comment'])?></p>
              <small class="text-gray-500">— <?=htmlspecialchars($r['full_name'])?></small>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><p>No reviews yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
