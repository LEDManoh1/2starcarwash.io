<?php
// Admin/customer_details.php
header('Content-Type: application/json');
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/../api/config.php';
}

// Read input (GET or JSON body)
$id = null;
$phone = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $phone = isset($_GET['phone']) ? $_GET['phone'] : null;
} else {
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body)) {
        $id = $body['id'] ?? null;
        $phone = $body['phone'] ?? null;
    }
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $mysqli->set_charset('utf8mb4');

    $customer = null;

    if ($id !== null && is_numeric($id)) {
        $stmt = $mysqli->prepare('SELECT id, name, phone, car_model, notes, is_vip, is_deleted, created_at, updated_at FROM customers WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $customer = $res->fetch_assoc();
        $stmt->close();
    }

    // If not found by id, try by phone if provided
    if (!$customer && $phone) {
        $stmt = $mysqli->prepare('SELECT id, name, phone, car_model, notes, is_vip, is_deleted, created_at, updated_at FROM customers WHERE phone = ? LIMIT 1');
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $res = $stmt->get_result();
        $customer = $res->fetch_assoc();
        $stmt->close();
    }

    // Build booking history by phone (either from customer or provided phone)
    $lookupPhone = $phone;
    if (!$lookupPhone && $customer && isset($customer['phone'])) {
        $lookupPhone = $customer['phone'];
    }

    $bookings = [];
    $totals = ['last_visit' => null, 'total_spent' => 0, 'count' => 0];

    if ($lookupPhone) {
        // Join with services table if available to get price
        $sql = "SELECT b.id, b.name as customer_name, b.phone, b.car_model, b.service, b.date, b.time, b.created_at, IFNULL(s.price, 0) AS price, b.status
                FROM bookings b
                LEFT JOIN services s ON b.service = s.name
                WHERE b.phone = ?
                ORDER BY b.date DESC, b.time DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $lookupPhone);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $bookings[] = $row;
            $totals['total_spent'] += (float)$row['price'];
            $totals['count']++;
            if (!$totals['last_visit'] && !empty($row['date'])) {
                $totals['last_visit'] = $row['date'];
            }
        }
        $stmt->close();
    }

    // If no explicit customer record but bookings exist, infer basic customer info
    if (!$customer && count($bookings) > 0) {
        $first = $bookings[0];
        $customer = [
            'id' => null,
            'name' => $first['customer_name'] ?? null,
            'phone' => $first['phone'] ?? $lookupPhone,
            'car_model' => $first['car_model'] ?? null,
            'notes' => null,
            'is_vip' => 0,
            'is_deleted' => 0,
        ];
    }

    echo json_encode(['success' => true, 'customer' => $customer, 'bookings' => $bookings, 'totals' => $totals]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>
