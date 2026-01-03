<?php
// Return JSON list of services (id, name, price, duration)
header('Content-Type: application/json');

// Defensive error handling: log errors, but always return a JSON array to the client
ini_set('display_errors', 0);
error_reporting(E_ALL);

$host = '127.0.0.1';
$db   = 'carwash';
$user = 'staruser';
$pass = 'StrongPassword123';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');

    $sql = "SELECT id, name, price, duration FROM services ORDER BY id ASC";
    $result = $conn->query($sql);

    $services = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Cast numeric fields to ints
            $row['id'] = isset($row['id']) ? (int)$row['id'] : null;
            $row['price'] = isset($row['price']) ? (int)$row['price'] : 0;
            $row['duration'] = isset($row['duration']) ? (int)$row['duration'] : 0;
            $services[] = $row;
        }
        $result->free();
    }

    echo json_encode($services);
    $conn->close();
    exit;

} catch (Throwable $e) {
    // Log the error server-side for debugging
    error_log('get_services error: ' . $e->getMessage());
    // Return empty array so frontend doesn't crash
    echo json_encode([]);
    exit;
}

