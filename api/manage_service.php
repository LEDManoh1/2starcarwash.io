<?php
// Admin endpoint to add or update services
// Protected via simple admin key passed as POST field `admin_key` (keep this secret).

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Load centralized config (admin key, DB credentials)
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/config.php';
}

// allow overrides from config file variables
$host = $DB_HOST ?? '127.0.0.1';
$db   = $DB_NAME ?? 'carwash';
$user = $DB_USER ?? 'staruser';
$pass = $DB_PASS ?? 'StrongPassword123';

// Use strict mysqli reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Auth: prefer session-based admin auth
    session_start();
    $isAdmin = $_SESSION['is_admin'] ?? false;
    // fallback to admin_key POST for compatibility (not recommended)
    $provided = $_POST['admin_key'] ?? '';
    if (!$isAdmin) {
        if (!$provided || !isset($ADMIN_KEY) || $provided !== $ADMIN_KEY) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
    }

    // Action: add or update
    $action = $_POST['action'] ?? 'add'; // 'add' or 'update'

    // Common fields
    $name = trim((string)($_POST['name'] ?? ''));
    $price = isset($_POST['price']) ? (int)$_POST['price'] : null;
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : null;

    // Basic validation
    if ($name === '' || $price === null || $duration === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validation failed: name, price and duration are required']);
        exit;
    }

    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO services (name, price, duration) VALUES (?, ?, ?)");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('sii', $name, $price, $duration);
        $ok = $stmt->execute();
        if ($ok) {
            $id = $stmt->insert_id;
            echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name, 'price' => $price, 'duration' => $duration]]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Insert failed']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    if ($action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Validation failed: id is required for update']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE services SET name = ?, price = ?, duration = ? WHERE id = ?");
        if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('siii', $name, $price, $duration, $id);
        $ok = $stmt->execute();
        if ($ok) {
            echo json_encode(['success' => true, 'data' => ['id' => $id, 'name' => $name, 'price' => $price, 'duration' => $duration]]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;

} catch (Throwable $e) {
    error_log('manage_service error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
