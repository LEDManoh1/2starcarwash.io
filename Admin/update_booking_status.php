<?php
header('Content-Type: application/json');

// load DB config
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    // Prefer secure config outside webroot if available
    $secure = '/var/www/secure/config.php';
    if (file_exists($secure)) {
        require_once $secure;
    } else {
        require_once __DIR__ . '/../api/config.php';
    }

    session_start();
    if (!($_SESSION['is_admin'] ?? false)) {
        // allow fallback via JSON admin_key for compatibility (not recommended)
        $body = json_decode(file_get_contents('php://input'), true);
        $provided = $body['admin_key'] ?? $_POST['admin_key'] ?? null;
        if (!$provided || $provided !== $ADMIN_KEY) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
    }
}

// read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$status = isset($data['status']) ? trim($data['status']) : '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Booking ID missing']);
    exit;
}

$allowed = ['Pending','Confirmed','Cancelled','Completed'];
// normalize status capitalization
$status = ucfirst(strtolower($status));
if (!in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

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
    $stmt = $conn->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('si', $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
    $stmt->close();
    $conn->close();
    exit;
} catch (Throwable $e) {
    error_log('update_booking_status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
