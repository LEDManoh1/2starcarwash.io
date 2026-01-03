
<?php
$host = "127.0.0.1";      // ⚠️ Tumia 127.0.0.1 si localhost
$db   = "carwash";
$user = "staruser";
$pass = "StrongPassword123"; // password ile ile ya staruser

try {
	$pdo = new PDO(
		"mysql:host=$host;dbname=$db;charset=utf8mb4",
		$user,
		$pass,
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		]
	);
} catch (PDOException $e) {
	die(json_encode([
		"success" => false,
		"message" => "DB Connection failed: " . $e->getMessage()
	]));
}
