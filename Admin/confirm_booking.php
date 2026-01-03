<?php
header('Content-Type: application/json');

// Load shared config for DB credentials
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/../api/config.php';
}

// Read JSON body first, fall back to POST form-data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = null;
if (is_array($data) && isset($data['id'])) {
    $id = (int)$data['id'];
} else {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Booking ID missing']);
    exit;
}

// use DB settings from config.php
$host = $DB_HOST ?? '127.0.0.1';
$user = $DB_USER ?? 'staruser';
$pass = $DB_PASS ?? '';
$db   = $DB_NAME ?? 'carwash';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('DB connect error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking confirmed']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} catch (Throwable $e) {
    error_log('confirm_booking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

$conn->close();
