<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=carwash;charset=utf8mb4",
        "staruser",
        "StrongPassword123",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // Try selecting the modern column names first. If the DB still uses legacy
    // `date`/`time` columns we'll catch the PDOException and retry with aliases.
    // Select using `booking_date` and `time` columns
    $stmt = $pdo->query(
        "SELECT id, name, phone, car_model, service, booking_date, time, status, amount, created_at
         FROM bookings
         ORDER BY created_at DESC"
    );

    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($bookings);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
