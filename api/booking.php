<?php
file_put_contents('debug.txt', "REQUEST \n" . print_r($_REQUEST, true) . "\nPOST\n" . print_r($_POST, true));
header('Content-Type: application/json');

// Be defensive: don't emit HTML errors to client — log them instead and return JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Convert uncaught exceptions to JSON responses
set_exception_handler(function($e){
    error_log('Uncaught exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

// Convert fatal shutdown errors to JSON
register_shutdown_function(function(){
    $err = error_get_last();
    if ($err && ($err['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) ) {
        error_log('Shutdown error: ' . json_encode($err));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server encountered a fatal error']);
        exit;
    }
});

// DB credentials
$host = '127.0.0.1';
$db   = 'carwash';
$user = 'staruser';
$pass = 'StrongPassword123';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    error_log('DB connect error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Collect POST data
$name         = $_POST['name'] ?? '';
$phone        = $_POST['phone'] ?? '';
$car_model    = $_POST['car_model'] ?? ($_POST['carModel'] ?? '');
$service      = $_POST['service'] ?? '';
$booking_date = $_POST['booking_date'] ?? '';
$time         = $_POST['time'] ?? '';
$status       = $_POST['status'] ?? 'pending';
$amount       = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;

// Validation
if (!$name || !$phone || !$car_model || !$service || !$booking_date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Validation failed: Please fill all fields']);
    exit;
}

// Prepare and execute insert
$stmt = $conn->prepare(
    "INSERT INTO bookings (name, phone, car_model, service, booking_date, time, status, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssssssd", $name, $phone, $car_model, $service, $booking_date, $time, $status, $amount);

if ($stmt->execute()) {
    $bookingData = compact('name','phone','car_model','service','booking_date','time','status','amount');
    echo json_encode(['success' => true, 'data' => $bookingData]);
    // fire notifications (best-effort)
    try {
        if (file_exists(__DIR__ . '/notifications.php')) {
            require_once __DIR__ . '/notifications.php';
            if (function_exists('send_notifications_for_booking')) {
                send_notifications_for_booking($bookingData);
            }
        }
    } catch (Throwable $e) {
        error_log('Notification error: ' . $e->getMessage());
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed']);
}

$stmt->close();
$conn->close();
