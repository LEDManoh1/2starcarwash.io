<?php
// api/booking_test.php - Minimal test endpoint for local dev

// Allow CORS from anywhere for local testing
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header("Content-Type: application/json; charset=utf-8");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "OK (preflight)"]);
    exit;
}

// Only allow POST for booking creation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Accept form-encoded body (FormData) or JSON
$name = $_POST['name'] ?? null;
$service = $_POST['service'] ?? null;
$date = $_POST['date'] ?? null;

// If the body is JSON, try to parse it
if (!$name && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $name = $name ?? ($json['name'] ?? null);
        $service = $service ?? ($json['service'] ?? null);
        $date = $date ?? ($json['date'] ?? null);
    }
}

if (!$name || !$service || !$date) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please fill all fields",
        "received" => ["name" => $name, "service" => $service, "date" => $date]
    ]);
    exit;
}

// Return success (do not touch DB) — quick testing endpoint
http_response_code(201);
echo json_encode([
    "success" => true,
    "message" => "Booking successful (test endpoint)",
    "data" => ["name" => $name, "service" => $service, "date" => $date]
]);

?>