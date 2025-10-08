<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success = $errors = [];

// Ensure status column exists
try {
    $pdo->query("ALTER TABLE users ADD COLUMN status ENUM('active','inactive') DEFAULT 'active'");
} catch (Exception $e) {
    // ignore if column exists
}

// --- Handle Admin Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $status = $_POST['status'];

        if ($name === '' || $email === '') {
            $errors[] = "Name and Email cannot be empty.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $status, $user_id]);
            $success[] = "User details updated successfully.";
        }
    } else {
        // Handle Activate / Deactivate / Delete
        $stmt = $pdo->prepare("SELECT id, full_name, email, role, status FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = "User not found.";
        } else {
            switch ($action) {
                case 'deactivate':
                    $pdo->prepare("UPDATE users SET status='inactive' WHERE id=?")->execute([$user_id]);
                    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$user_id, 'Your account has been deactivated by admin.']);
                    $success[] = "Deactivated account for " . htmlspecialchars($user['full_name']);
                    break;

                case 'activate':
                    $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$user_id]);
                    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$user_id, 'Your account has been reactivated by admin.']);
                    $success[] = "Reactivated account for " . htmlspecialchars($user['full_name']);
                    break;

                case 'delete':
                    $pdo->prepare("DELETE FROM providers WHERE user_id=?")->execute([$user_id]);
                    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$user_id]);
                    $success[] = "Deleted user " . htmlspecialchars($user['full_name']);
                    break;
            }
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Users — SkillConnect Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="navbar bg-base-100 shadow">
    <a href="admin_dashboard.php" class="btn btn-ghost text-xl">SkillConnect Admin</a>
    <div class="flex-1"></div>
    <a href="logout.php" class="btn btn-error">Logout</a>
  </div>

  <div class="max-w-7xl mx-auto mt-8">
    <h2 class="text-2xl font-bold mb-6">Manage Users</h2>

    <?php foreach ($success as $s): ?><div class="alert alert-success"><?=htmlspecialchars($s)?></div><?php endforeach; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?=htmlspecialchars($e)?></div><?php endforeach; ?>

    <div class="overflow-x-auto">
      <table class="table table-zebra w-full">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?=$u['id']?></td>
              <td><?=htmlspecialchars($u['full_name'])?></td>
              <td><?=htmlspecialchars($u['email'])?></td>
              <td><span class="badge <?= $u['role']=='admin'?'badge-neutral':($u['role']=='provider'?'badge-warning':'badge-info')?>"><?=ucfirst($u['role'])?></span></td>
              <td><span class="badge <?= $u['status']=='active'?'badge-success':'badge-error'?>"><?=ucfirst($u['status'])?></span></td>
              <td><?=htmlspecialchars($u['created_at'])?></td>
              <td class="flex gap-2">
                <!-- Edit Button triggers modal -->
                <label for="edit-<?=$u['id']?>" class="btn btn-sm btn-info">Edit</label>
                <form method="post" onsubmit="return confirm('Delete this user permanently? This cannot be undone.')">
                  <input type="hidden" name="user_id" value="<?=$u['id']?>">
                  <button name="action" value="delete" class="btn btn-error btn-sm">Delete</button>
                </form>
              </td>
            </tr>

            <!-- EDIT MODAL -->
            <input type="checkbox" id="edit-<?=$u['id']?>" class="modal-toggle" />
            <div class="modal">
              <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Edit User</h3>
                <form method="post" class="space-y-4">
                  <input type="hidden" name="user_id" value="<?=$u['id']?>">
                  <input type="hidden" name="action" value="edit">

                  <div>
                    <label class="label"><span class="label-text">Full Name</span></label>
                    <input type="text" name="full_name" value="<?=htmlspecialchars($u['full_name'])?>" class="input input-bordered w-full" required>
                  </div>
                  <div>
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" value="<?=htmlspecialchars($u['email'])?>" class="input input-bordered w-full" required>
                  </div>
                  <div>
                    <label class="label"><span class="label-text">Role</span></label>
                    <select name="role" class="select select-bordered w-full">
                      <option value="client" <?=$u['role']=='client'?'selected':''?>>Client</option>
                      <option value="provider" <?=$u['role']=='provider'?'selected':''?>>Provider</option>
                      <option value="admin" <?=$u['role']=='admin'?'selected':''?>>Admin</option>
                    </select>
                  </div>
                  <div>
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" class="select select-bordered w-full">
                      <option value="active" <?=$u['status']=='active'?'selected':''?>>Active</option>
                      <option value="inactive" <?=$u['status']=='inactive'?'selected':''?>>Inactive</option>
                    </select>
                  </div>
                  <div class="modal-action">
                    <label for="edit-<?=$u['id']?>" class="btn btn-ghost">Cancel</label>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
