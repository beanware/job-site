<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $loc = trim($_POST['location']);
    $budget = floatval($_POST['budget']);

    if ($title === '' || $desc === '') $errors[] = "Title and description are required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO jobs (employer_id, title, description, location, budget) VALUES (?,?,?,?,?)");
        $stmt->execute([$user_id, $title, $desc, $loc, $budget]);
        $success = "Job posted successfully.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Post a Job — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex justify-center items-center">
  <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
    <div class="card-body">
      <h2 class="card-title">Post a Job</h2>

      <?php if ($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
      <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=$e?></div><?php endforeach; ?>

      <form method="post" class="space-y-4">
        <div>
          <label class="label"><span class="label-text">Title</span></label>
          <input type="text" name="title" class="input input-bordered w-full" required>
        </div>
        <div>
          <label class="label"><span class="label-text">Description</span></label>
          <textarea name="description" class="textarea textarea-bordered w-full" rows="4" required></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label"><span class="label-text">Location</span></label>
            <input type="text" name="location" class="input input-bordered w-full">
          </div>
          <div>
            <label class="label"><span class="label-text">Budget (KSh)</span></label>
            <input type="number" name="budget" class="input input-bordered w-full" step="0.01">
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-full">Post Job</button>
      </form>

      <a href="dashboard.php" class="btn btn-ghost mt-4 w-full">Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
