<?php
// Central DB connection for ProcrastiTrack
$host = '127.0.0.1';
$db   = 'procrastitrack';
$user = 'root';
$pass = ''; // default XAMPP mysql password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed. Ensure XAMPP is running and database imported.']);
    exit;
}
?>
