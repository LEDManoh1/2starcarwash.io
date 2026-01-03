<?php
// api/customer_details.php
// Returns JSON array of customers with totals: id, name, phone, car_model, total_bookings, last_visit, is_vip
header('Content-Type: application/json');
// Prefer secure config outside webroot if available
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/config.php';
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $mysqli->set_charset('utf8mb4');

    // Use subqueries to compute total_bookings and last_visit using booking_date
    $sql = "SELECT c.id,
                   c.name,
                   c.phone,
                   c.car_model,
                   c.notes,
                   COALESCE(c.is_vip,0) AS is_vip,
                   COALESCE(c.is_deleted,0) AS is_deleted,
                   (SELECT COUNT(*) FROM bookings WHERE bookings.phone = c.phone) AS total_bookings,
                   (SELECT MAX(booking_date) FROM bookings WHERE bookings.phone = c.phone) AS last_visit
            FROM customers c
            WHERE COALESCE(c.is_deleted,0) = 0
            ORDER BY last_visit DESC, name ASC";

    $res = $mysqli->query($sql);
    $out = [];
    while ($row = $res->fetch_assoc()) {
        // Normalize numeric types
        $row['id'] = (int)$row['id'];
        $row['total_bookings'] = (int)$row['total_bookings'];
        $row['is_vip'] = (int)$row['is_vip'];
        $row['is_deleted'] = (int)$row['is_deleted'];
        // Trim whitespace/newlines from last_visit if present
        if (!empty($row['last_visit'])) {
            // replace any whitespace sequences (including newlines) with a single space and trim
            $row['last_visit'] = preg_replace('/\s+/', ' ', trim($row['last_visit']));
        }
        $out[] = $row;
    }

    echo json_encode($out);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

?>
