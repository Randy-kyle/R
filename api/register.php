<?php
// Allow CORS (optional untuk testing lokal)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

// Connect ke database
$host = 'localhost';
$db_name = 'nutaku_clone';
$username = 'root'; // sesuaikan
$password = '';     // sesuaikan

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection error"]);
    exit;
}

// Ambil input JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validasi
if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "All fields are required"]);
    exit;
}

$username = trim($data['username']);
$email = trim($data['email']);
$password = $data['password'];

// Cek apakah username/email sudah ada
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(["message" => "Username or email already taken"]);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert ke database
$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
try {
    $stmt->execute([$username, $email, $hashed_password]);
    http_response_code(201);
    echo json_encode(["message" => "User registered successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Server error"]);
}
