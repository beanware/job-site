<?php
// db.php
// Edit these for your environment
$db_host = 'localhost';
$db_name = 'jobsite';
$db_user = 'root';
$db_pass = ''; // XAMPP default

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production, do NOT echo errors
    exit('Database connection failed: ' . $e->getMessage());
}
