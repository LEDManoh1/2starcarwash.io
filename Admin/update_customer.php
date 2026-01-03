<?php
// Admin/update_customer.php
header('Content-Type: application/json');
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/../api/config.php';
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    // fallback to POST form-data
    $data = $_POST;
}

// Require session-based admin or fallback to admin_key (compat)
session_start();
$isAdmin = $_SESSION['is_admin'] ?? false;
$admin_key = $data['admin_key'] ?? ($data['adminKey'] ?? null);
if (!$isAdmin) {
    if (!$admin_key || $admin_key !== $ADMIN_KEY) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
}

$id = isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null;
$name = $data['name'] ?? null;
$phone = $data['phone'] ?? null;
$car_model = $data['car_model'] ?? ($data['carModel'] ?? null);
$notes = $data['notes'] ?? null;
$is_vip = isset($data['is_vip']) ? (int)$data['is_vip'] : (isset($data['isVip']) ? (int)$data['isVip'] : 0);
$is_deleted = isset($data['is_deleted']) ? (int)$data['is_deleted'] : (isset($data['isDeleted']) ? (int)$data['isDeleted'] : 0);

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $mysqli->set_charset('utf8mb4');

    if ($id) {
        $stmt = $mysqli->prepare('UPDATE customers SET name = ?, phone = ?, car_model = ?, notes = ?, is_vip = ?, is_deleted = ? WHERE id = ?');
        $stmt->bind_param('sssisii', $name, $phone, $car_model, $notes, $is_vip, $is_deleted, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'updated_id' => $id]);
        exit;
    } else {
        // Insert new customer (if phone unique constraint exists, handle gracefully)
        $stmt = $mysqli->prepare('INSERT INTO customers (name, phone, car_model, notes, is_vip, is_deleted) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssii', $name, $phone, $car_model, $notes, $is_vip, $is_deleted);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'inserted_id' => $newId]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>
