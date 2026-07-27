<?php
require 'config.php'; // Assumes $pdo and APP_URL are defined

// Error handling and logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

// CORS headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Content-Type: application/json');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// CSRF validation
session_start();
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Validate request method
$required = array('id_number', 'first_name', 'last_name', 'phone', 'dob', 'email', 'address', 'password', 'role');
$data = array();
foreach ($required as $field) {
    $value = trim($_POST[$field] ?? '');
    if ($value === '') {
        http_response_code(400);
        echo json_encode(array('error' => "Missing: $field"));
        exit;
    }
    $data[$field] = htmlspecialchars($value); // Sanitize input
}

// Validate fields
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]{8,}$/', $data['password'])) {

    http_response_code(400);
    echo json_encode(['error' => 'Password must be 8+ characters with letters and numbers']);
    exit;
}

if (!DateTime::createFromFormat('Y-m-d', $data['dob']) || (new DateTime($data['dob'])) > new DateTime()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date of birth']);
    exit;
}

if (!preg_match('/^\+?\d{10,15}$/', $data['phone'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid phone number']);
    exit;
}

if (!in_array(strtolower($data['role']), ['applicant', 'officer', 'admin'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Password hashing
$data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

// Database operations
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR id_number = ?");
    $stmt->execute([$data['email'], $data['id_number']]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Email or ID number already exists']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (id_number, first_name, last_name, phone, dob, email, address, password_hash, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([
        $data['id_number'], $data['first_name'], $data['last_name'],
        $data['phone'], $data['dob'], $data['email'],
        $data['address'], $data['password_hash'], strtolower($data['role'])
    ]);

    // Optional email
    if (function_exists('sendEmail')) {
        $emailBody = "Welcome to Evisa Portal. Log in at " . APP_URL;
        if (sendEmail($data['email'], 'Welcome', $emailBody)) {
            error_log("Email sent to: {$data['email']}");
        }
    }

    echo json_encode(['message' => 'Registration successful']);
} catch (PDOException $e) {
    error_log("DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed']);
}
?>