<?php
// admin_dashboard.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch system statistics
$stats = [];

// Count total users
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['providers'] = $pdo->query("SELECT COUNT(*) FROM providers")->fetchColumn();
$stats['jobs'] = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$stats['applications'] = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$stats['pending_verifications'] = $pdo->query("SELECT COUNT(*) FROM verifications WHERE status='pending'")->fetchColumn();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard — SkillConnect</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">

  <?php include 'navbar.php'; ?>

  <div class="max-w-6xl mx-auto mt-8">
    <h2 class="text-3xl font-bold mb-6">Admin Dashboard</h2>

    <!-- STAT CARDS -->
    <div class="grid md:grid-cols-3 gap-6 mb-10">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Total Users</h3>
          <p class="text-4xl font-bold"><?=$stats['users']?></p>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Providers</h3>
          <p class="text-4xl font-bold"><?=$stats['providers']?></p>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Jobs</h3>
          <p class="text-4xl font-bold"><?=$stats['jobs']?></p>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Applications</h3>
          <p class="text-4xl font-bold"><?=$stats['applications']?></p>
        </div>
      </div>
      <div class="card bg-base-100 shadow border-l-4 border-yellow-500">
        <div class="card-body">
          <h3 class="text-xl font-semibold">Pending Verifications</h3>
          <p class="text-4xl font-bold"><?=$stats['pending_verifications']?></p>
        </div>
      </div>
    </div>

    <!-- NAVIGATION LINKS -->
    <h3 class="text-2xl font-semibold mb-4">Management</h3>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-lg font-bold">Manage Jobs</h3>
          <p>View, edit, or close job listings.</p>
          <a href="admin_manage_jobs.php" class="btn btn-primary mt-2">Go</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-lg font-bold">Manage Users</h3>
          <p>View, activate, or deactivate accounts.</p>
          <a href="manage_users.php" class="btn btn-primary mt-2">Go</a>
        </div>
      </div>
      <div class="card bg-base-100 shadow">
        <div class="card-body">
          <h3 class="text-lg font-bold">Verify Providers</h3>
          <p>Review verification requests and approve or reject.</p>
          <a href="verify_providers.php" class="btn btn-primary mt-2">Go</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
