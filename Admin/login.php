<?php
// Admin/login.php - simple session login endpoint
session_start();
header('Content-Type: application/json');
$secure = '/var/www/secure/config.php';
if (file_exists($secure)) {
    require_once $secure;
} else {
    require_once __DIR__ . '/../api/config.php';
}

// Accept POST JSON or form
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) $body = $_POST;

$user = $body['username'] ?? ($body['user'] ?? null);
$pass = $body['password'] ?? null;

if (!$user || !$pass) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Missing credentials']);
    exit;
}

if (!isset($ADMIN_USER) || !isset($ADMIN_PASS)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Admin not configured']);
    exit;
}

if ($user === $ADMIN_USER && password_verify($pass, password_needs_rehash($ADMIN_PASS,PASSWORD_DEFAULT) ? password_hash($ADMIN_PASS,PASSWORD_DEFAULT) : $ADMIN_PASS) || $pass === $ADMIN_PASS) {
    // Note: If secure config stores plaintext ADMIN_PASS, allow exact match.
    $_SESSION['is_admin'] = true;
    echo json_encode(['success'=>true]);
    exit;
}

http_response_code(403);
echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
exit;

?>
