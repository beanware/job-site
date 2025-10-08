<?php
// navbar.php
if (!isset($_SESSION)) session_start();
?>
<nav class="navbar bg-base-100 shadow sticky top-0 z-50">
  <div class="flex-1">
    <a href="dashboard.php" class="btn btn-ghost normal-case text-xl font-bold">SkillBridge</a>
  </div>
  <div class="flex-none">
    <ul class="menu menu-horizontal px-1 space-x-1">
      <?php if ($_SESSION['role'] === 'client'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="post_job.php">Post Job</a></li>
        <li><a href="view_applicants.php">Applicants</a></li>
        <li><a href="client_jobs.php">My Jobs</a></li>
        <li><a href="notifications.php">Notifications</a></li>
        <li><a href="client_profile.php">Profile</a></li>
      <?php elseif ($_SESSION['role'] === 'provider'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="find_jobs.php">Find Jobs</a></li>
        <li><a href="my_applications.php">My Applications</a></li>
        <li><a href="portfolio.php?id=<?=$_SESSION['user_id']?>">Portfolio</a></li>
        <li><a href="notifications.php">Notifications</a></li>
        <li><a href="provider_profile.php">Profile</a></li>
      <?php elseif ($_SESSION['role'] === 'admin'): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="verify_providers.php">Verify Providers</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="manage_jobs.php">Manage Jobs</a></li>
      <?php endif; ?>
      <li><a href="logout.php" class="btn btn-error btn-sm text-white ml-2">Logout</a></li>
    </ul>
  </div>
</nav>
