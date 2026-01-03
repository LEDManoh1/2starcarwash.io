<?php
// Admin/check_auth.php
session_start();
header('Content-Type: application/json');
echo json_encode(['is_admin' => !!($_SESSION['is_admin'] ?? false)]);
exit;

?>
