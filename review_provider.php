<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$client_id = $_SESSION['user_id'];
$job_id = intval($_GET['job_id'] ?? 0);

// Fetch job and provider details
$stmt = $pdo->prepare("
  SELECT j.*, p.id AS provider_id, u.full_name AS provider_name
  FROM jobs j
  JOIN applications a ON a.job_id=j.id
  JOIN providers p ON a.worker_id=p.id
  JOIN users u ON p.user_id=u.id
  WHERE j.id=? AND j.employer_id=? AND a.status='accepted'
");
$stmt->execute([$job_id, $client_id]);
$job = $stmt->fetch();
if (!$job) exit('Invalid job.');

$success = $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) $errors[] = "Invalid rating.";
    if (empty($comment)) $errors[] = "Please add a comment.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (job_id, provider_id, employer_id, rating, comment) VALUES (?,?,?,?,?)");
        $stmt->execute([$job_id, $job['provider_id'], $client_id, $rating, $comment]);

        // Update provider average rating
        $avgStmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE provider_id=?");
        $avgStmt->execute([$job['provider_id']]);
        $avg = round($avgStmt->fetchColumn(), 1);
        $pdo->prepare("UPDATE providers SET rating=? WHERE id=?")->execute([$avg, $job['provider_id']]);

        $success[] = "Review submitted successfully.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Leave Review — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex justify-center items-center">
  <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
    <div class="card-body">
      <h2 class="card-title">Leave a Review for <?=$job['provider_name']?></h2>

      <?php foreach ($success as $s): ?><div class="alert alert-success"><?=$s?></div><?php endforeach; ?>
      <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=$e?></div><?php endforeach; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="label"><span class="label-text">Rating (1–5)</span></label>
          <select name="rating" class="select select-bordered w-full">
            <option value="">Choose rating</option>
            <?php for ($i=5; $i>=1; $i--): ?>
              <option value="<?=$i?>"><?=$i?> ⭐</option>
            <?php endfor; ?>
          </select>
        </div>

        <div>
          <label class="label"><span class="label-text">Comment</span></label>
          <textarea name="comment" rows="4" class="textarea textarea-bordered w-full"></textarea>
        </div>

        <button class="btn btn-primary w-full">Submit Review</button>
        <a href="manage_jobs.php" class="btn btn-ghost w-full mt-2">Back</a>
      </form>
    </div>
  </div>
</body>
</html>
