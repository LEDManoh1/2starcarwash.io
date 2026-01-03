<?php
require 'db.php';

    try {
        $stmt = $pdo->query("SELECT id, name, phone, car_model, service, booking_date, time, status, amount, created_at FROM bookings ORDER BY created_at DESC");
        $bookings = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'bookings' => $bookings
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
