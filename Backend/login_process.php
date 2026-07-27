<?php
session_start();

header('Content-Type: application/json');

// CSRF validation
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid CSRF token']));
}

// Include database config
require 'config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed']));
}

// Sanitize inputs
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    exit(json_encode(['error' => 'Email and password are required']));
}

// Fetch user
$stmt = $pdo->prepare('SELECT user_id, email, password_hash, status, role FROM users WHERE email = ? AND role IN ("admin", "officer")');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    if ($user['status'] !== 'active') {
        http_response_code(403);
        exit(json_encode(['error' => 'Account not active']));
    }

    // Store session data
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];

    // Regenerate CSRF token after login
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Send redirect info
    exit(json_encode([
        'redirect' => $user['role'] === 'admin'
            ? '/EvisaProject/Backend/Dashboard.php'
            : '/EvisaProject/Backend/officer.php'
    ]));
}

// Invalid credentials
http_response_code(401);
exit(json_encode(['error' => 'Invalid email or password']));
