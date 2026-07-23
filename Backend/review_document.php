<?php
session_start();
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
header('Content-Type: application/json');

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

require 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!in_array($stmt->fetchColumn(), ['officer', 'admin'])) {
        exit(json_encode(['error' => 'Unauthorized role']));
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error']));
}

$document_id = $_POST['document_id'] ?? '';
$application_id = filter_var($_POST['application_id'] ?? '', FILTER_VALIDATE_INT);
$application_type = in_array($_POST['application_type'] ?? '', ['visa', 'passport']) ? $_POST['application_type'] : null;
$status = in_array($_POST['status'] ?? '', ['valid', 'non_conforming']) ? $_POST['status'] : null;
$notes = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_STRING);
$request_reupload = isset($_POST['request_reupload']) && $_POST['request_reupload'] == '1';

if (!$document_id || !$application_id || !$application_type || !$status) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid input']));
}

try {
    $table = $application_type === 'visa' ? 'VisaApp' : 'PassApp';
    if (strpos($document_id, 'photo_') === 0) {
        $stmt = $pdo->prepare("UPDATE $table SET status = ?, notes = ? WHERE application_id = ?");
        $stmt->execute([$status, $notes, $application_id]);
    } else {
        $stmt = $pdo->prepare('UPDATE documents SET status = ?, notes = ? WHERE id = ?');
        $stmt->execute([$status, $notes, $document_id]);
    }

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        exit(json_encode(['error' => 'Document not found']));
    }

    if ($request_reupload) {
        $stmt = $pdo->prepare("UPDATE $table SET status = 'pending' WHERE application_id = ?");
        $stmt->execute([$application_id]);
        $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = (SELECT user_id FROM $table WHERE application_id = ?)");
        $stmt->execute([$application_id]);
        $email = $stmt->fetchColumn();
        // TODO: Implement notification
    }

    $stmt = $pdo->prepare("SELECT status FROM $table WHERE application_id = ?");
    $stmt->execute([$application_id]);
    $app_status = $stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT status FROM documents WHERE application_id = ? AND application_type = ?');
    $stmt->execute([$application_id, $application_type]);
    $doc_statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $new_app_status = 'pending';
    if ($app_status === 'valid' && !in_array('pending', $doc_statuses) && !in_array('non_conforming', $doc_statuses)) {
        $new_app_status = 'approved';
    }
    $stmt = $pdo->prepare("UPDATE $table SET status = ? WHERE application_id = ?");
    $stmt->execute([$new_app_status, $application_id]);

    exit(json_encode(['message' => $request_reupload ? 'Re-upload requested' : 'Document updated']));
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database error']));
}
?>