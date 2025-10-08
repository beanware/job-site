<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$role = $_SESSION['role'];
include 'navbar.php';
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard — SkillBridge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">

<div class="max-w-6xl mx-auto mt-10">

  <h2 class="text-3xl font-bold mb-6">Welcome, <?=htmlspecialchars($_SESSION['user_name'])?>!</h2>

  <?php if ($role === 'client'): ?>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Post New Job</h3>
          <p>Quickly create a new job listing and find qualified workers.</p>
          <a href="post_job.php" class="btn btn-primary mt-2">Post Job</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">View Applicants</h3>
          <p>Review applications and assign jobs to the best candidates.</p>
          <a href="view_applicants.php" class="btn btn-primary mt-2">View Applicants</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">My Jobs</h3>
          <p>Track active and completed projects in one place.</p>
          <a href="manage_jobs.php" class="btn btn-primary mt-2">View My Jobs</a>
        </div>
      </div>
    </div>

  <?php elseif ($role === 'provider'): ?>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Find Jobs</h3>
          <p>Browse available opportunities that match your skills.</p>
          <a href="browse_jobs.php" class="btn btn-primary mt-2">Explore</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">My Applications</h3>
          <p>View the jobs you’ve applied for and track progress.</p>
          <a href="provider_jobs.php" class="btn btn-primary mt-2">Check Status</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Portfolio</h3>
          <p>Showcase your work, photos, and client reviews.</p>
          <a href="manage_portfolio.php?id=<?=$_SESSION['user_id']?>" class="btn btn-primary mt-2">Manage Portfolio</a>
        </div>
      </div>
    </div>

  <?php elseif ($role === 'admin'): ?>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Verify Providers</h3>
          <p>Approve or decline pending verification requests.</p>
          <a href="verify_providers.php" class="btn btn-primary mt-2">Manage Verifications</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Manage Users</h3>
          <p>Oversee platform users, roles, and activity logs.</p>
          <a href="manage_users.php" class="btn btn-primary mt-2">Manage Users</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Manage Jobs</h3>
          <p>Review, archive, or remove job listings.</p>
          <a href="manage_jobs.php" class="btn btn-primary mt-2">Manage Jobs</a>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>
</body>
</html>
