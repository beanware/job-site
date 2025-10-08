<?php
// test_admin_dashboard.php
session_start();
require_once '../db.php';

// Simulate admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

try {
    $counts = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'jobs' => $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn(),
        'verifications' => $pdo->query("SELECT COUNT(*) FROM verifications")->fetchColumn(),
    ];

    echo "✅ Database connection successful.<br>";
    echo "Users: {$counts['users']}<br>";
    echo "Jobs: {$counts['jobs']}<br>";
    echo "Verifications: {$counts['verifications']}<br>";

} catch (Exception $e) {
    echo "❌ Test failed: " . htmlspecialchars($e->getMessage());
}
?>
