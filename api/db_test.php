<?php
header('Content-Type: application/json');

$host = "127.0.0.1";
$db   = "carwash";
$user = "staruser";
$pass = "StrongPassword123";
$charset = "utf8mb4";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo json_encode(['success' => true, 'message' => 'Connected to DB', 'server_version' => $version]);
} catch (PDOException $e) {
    error_log('DB test error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'DB connection failed', 'error' => $e->getMessage()]);
}
