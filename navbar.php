<?php
// navbar.php
if (!isset($_SESSION)) session_start();
require_once 'db.php';

// Fetch unread notification count
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id']]);
    $notif_count = (int)$stmt->fetchColumn();
}
?>
<nav class="navbar bg-base-100 shadow sticky top-0 z-50">
  <div class="flex-1">
    <a href="dashboard.php" class="btn btn-ghost normal-case text-xl font-bold">SkillConnect</a>
  </div>

  <div class="flex-none">
    <ul class="menu menu-horizontal px-1 space-x-1 items-center">
      <?php if ($_SESSION['role'] === 'client'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="post_job.php">Post Job</a></li>
        <li><a href="view_applicants.php">Applicants</a></li>
        <li><a href="client_jobs.php">My Jobs</a></li>
        <li>
          <a href="notifications.php" class="relative">
            <span class="mr-1">Notifications</span>
            <?php if ($notif_count > 0): ?>
              <span class="badge badge-error badge-sm absolute -top-2 -right-2"><?=$notif_count?></span>
            <?php endif; ?>
          </a>
        </li>
        <li><a href="client_profile.php">Profile</a></li>

      <?php elseif ($_SESSION['role'] === 'provider'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="find_jobs.php">Find Jobs</a></li>
        <li><a href="my_applications.php">My Applications</a></li>
        <li><a href="portfolio.php?id=<?=$_SESSION['user_id']?>">Portfolio</a></li>
        <li>
          <a href="notifications.php" class="relative">
            <span class="mr-1">Notifications</span>
            <?php if ($notif_count > 0): ?>
              <span class="badge badge-error badge-sm absolute -top-2 -right-2"><?=$notif_count?></span>
            <?php endif; ?>
          </a>
        </li>
        <li><a href="provider_profile.php">Profile</a></li>

      <?php elseif ($_SESSION['role'] === 'admin'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="verify_providers.php">Verify Providers</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="admin_manage_jobs.php">Manage Jobs</a></li>
        <li>
          <a href="notifications.php" class="relative">
            <span class="mr-1">Notifications</span>
            <?php if ($notif_count > 0): ?>
              <span class="badge badge-error badge-sm absolute -top-2 -right-2"><?=$notif_count?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endif; ?>

      <li><a href="logout.php" class="btn btn-error btn-sm text-white ml-2">Logout</a></li>
    </ul>
  </div>
</nav>
