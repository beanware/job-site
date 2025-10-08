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
    $method = $_POST['method'];
    if (empty($_FILES['document']['name'])) $errors[] = "Please select a document.";

    if (empty($errors)) {
        $dir = "uploads/verifications/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $fileName = "verify_" . $provider_id . "_" . basename($_FILES['document']['name']);
        $target = $dir . $fileName;
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            move_uploaded_file($_FILES['document']['tmp_name'], $target);
            $stmt = $pdo->prepare("INSERT INTO verifications (provider_id, method, document_path) VALUES (?,?,?)");
            $stmt->execute([$provider_id, $method, $target]);
            $success[] = "Verification request submitted successfully.";
        } else {
            $errors[] = "Invalid file type.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Submit Verification — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex items-center justify-center">
  <div class="card w-full max-w-lg shadow-xl bg-base-100">
    <div class="card-body">
      <h2 class="card-title">Submit Verification</h2>
      <?php foreach ($success as $s): ?><div class="alert alert-success"><?=htmlspecialchars($s)?></div><?php endforeach; ?>
      <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="label"><span class="label-text">Verification Type</span></label>
          <select name="method" class="select select-bordered w-full">
            <option value="ID">National ID</option>
            <option value="Certificate">Certificate</option>
            <option value="Reference">Community Reference</option>
          </select>
        </div>

        <div>
          <label class="label"><span class="label-text">Upload Document (JPG, PNG, PDF)</span></label>
          <input type="file" name="document" class="file-input file-input-bordered w-full" required>
        </div>

        <button type="submit" class="btn btn-primary w-full">Submit for Review</button>
        <a href="provider_profile.php" class="btn btn-ghost w-full">Back to Profile</a>
      </form>
    </div>
  </div>
</body>
</html>
