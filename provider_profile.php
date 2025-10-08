<?php
// provider_profile.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'provider') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $errors = [];

// Fetch provider data
$stmt = $pdo->prepare("SELECT * FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();

// Update profile logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $skill = trim($_POST['skill_category']);
    $exp = intval($_POST['experience_years']);
    $bio = trim($_POST['bio']);

    // Handle image upload
    $imagePath = $provider['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = "worker_" . $user_id . "_" . basename($_FILES['profile_image']['name']);
        $targetFile = $targetDir . $fileName;
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile);
            $imagePath = $targetFile;
        } else {
            $errors[] = "Invalid image format (jpg, jpeg, png only).";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE providers SET skill_category=?, experience_years=?, bio=?, profile_image=? WHERE user_id=?");
        $stmt->execute([$skill, $exp, $bio, $imagePath, $user_id]);
        $success[] = "Profile updated successfully.";
    }
}

// Refresh provider data properly
$stmt = $pdo->prepare("SELECT * FROM providers WHERE user_id = ?");
$stmt->execute([$user_id]);
$provider = $stmt->fetch();

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>SkillBridge — Provider Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a class="btn btn-ghost text-xl">SkillBridge</a>
    <div class="flex-1"></div>
    <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
    <a class="btn btn-error ml-2" href="logout.php">Logout</a>
  </div>

  <div class="max-w-3xl mx-auto mt-8 card bg-base-100 shadow-xl">
    <div class="card-body">
      <h2 class="card-title">My Profile</h2>

      <?php foreach ($success as $s): ?>
        <div class="alert alert-success"><?=htmlspecialchars($s)?></div>
      <?php endforeach; ?>
      <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?=htmlspecialchars($e)?></div>
      <?php endforeach; ?>
<?php if ($provider['verification_status'] !== 'verified'): ?>
<div class="alert alert-warning">
  <strong>Unverified account:</strong> Your profile is visible to fewer employers. 
  <a href="submit_verification.php" class="link link-primary">Verify now</a>.
</div>
<?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          
          <div>
            <label class="label"><span class="label-text">Skill Category</span></label>
            <input type="text" name="skill_category" value="<?=htmlspecialchars($provider['skill_category'])?>" class="input input-bordered w-full" required>
          </div>
          <div>
            <label class="label"><span class="label-text">Years of Experience</span></label>
            <input type="number" name="experience_years" value="<?=htmlspecialchars($provider['experience_years'])?>" class="input input-bordered w-full" min="0">
          </div>
        </div>

        <div>
          <label class="label"><span class="label-text">Bio / Description</span></label>
          <textarea name="bio" class="textarea textarea-bordered w-full" rows="4"><?=htmlspecialchars($provider['bio'])?></textarea>
        </div>

        <div>
          <label class="label"><span class="label-text">Profile Picture</span></label>
          <?php if ($provider['profile_image']): ?>
            <img src="<?=htmlspecialchars($provider['profile_image'])?>" alt="Profile" class="w-24 h-24 rounded-full mb-2">
          <?php endif; ?>
          <input type="file" name="profile_image" accept="image/*" class="file-input file-input-bordered w-full">
        </div>

        <button name="update_profile" class="btn btn-primary w-full">Update Profile</button>
      </form>

      <hr class="my-6">

      <a href="submit_verification.php" class="btn btn-outline btn-success w-full">Submit Verification</a>
    </div>
  </div>
</body>
</html>
