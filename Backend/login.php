<?php
// backend/login.php
require 'config.php';
session_start();

header('Content-Type: application/json');

file_put_contents('debug.log', "Login POST data received:\n" . print_r($_POST, true) . "\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    file_put_contents('debug.log', "Method not allowed\n", FILE_APPEND);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$application_type = $_POST['applicationType'] ?? '';

if (!$email || !$password || !$application_type) {
    http_response_code(400);
    $msg = 'Missing required fields';
    echo json_encode(['error' => $msg]);
    file_put_contents('debug.log', "$msg\n", FILE_APPEND);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_number, password_hash, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        $msg = 'Invalid email or password';
        echo json_encode(['error' => $msg]);
        file_put_contents('debug.log', "$msg\n", FILE_APPEND);
        exit;
    }

    if ($user['status'] !== 'pending') {
        http_response_code(403);
        $msg = 'Account not active. Please verify your email.';
        echo json_encode(['error' => $msg]);
        file_put_contents('debug.log', "$msg\n", FILE_APPEND);
        exit;
    }

    $_SESSION['id_number'] = $user['id_number'];
    $_SESSION['application_type'] = $application_type;

    $redirect_url = match ($application_type){
     'passport' =>'PassportApplication.html',
     'visa' =>'VisaApplication.html',
     default => 'index.php',
    };
     echo json_encode(['message' => 'Login successful', 'redirect' => $redirect_url]);
} catch (PDOException $e) {
    http_response_code(500);
    $error_msg = 'Login failed: ' . $e->getMessage();
    echo json_encode(['error' => $error_msg]);
    file_put_contents('../debug.log', "$error_msg\n", FILE_APPEND);
}
?>