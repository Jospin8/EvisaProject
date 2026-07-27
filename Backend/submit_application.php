<?php
require 'config.php';

header('Content-Type: application/json');

// Database configuration
global $pdo;

// CSRF protection with debugging
session_start();
$csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
file_put_contents('debug.txt', 'POST CSRF: ' . $csrf_token . ', Session CSRF: ' . ($_SESSION['csrf_token'] ?? 'not set') . ', Session ID: ' . session_id() . "\n", FILE_APPEND);
if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Generate unique application ID
$application_id = bin2hex(random_bytes(16));

// Define form fields
$fields = ['firstName', 'lastName', 'passportNumber', 'dob', 'nationality', 'phone', 'email', 'address', 'visaType', 'duration'];

// Collect and validate form data
$data = [];
foreach ($fields as $field) {
    $data[$field] = $_POST[$field] ?? '';
    if (empty($data[$field])) {
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Validate critical fields
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}
if (!DateTime::createFromFormat('Y-m-d', $data['dob'])) {
    echo json_encode(['error' => 'Invalid DOB format (use YYYY-MM-DD)']);
    exit;
}
if (!preg_match('/^\+?[1-9]\d{1,14}$/', $data['phone'])) {
    echo json_encode(['error' => 'Invalid phone number']);
    exit;
}

// Handle file uploads
$upload_dir = __DIR__ . '/../Uploads/'; // Moved outside web root
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$maxFileSize = 5 * 1024 * 1024; // 5MB
$photo_path = $upload_dir . $application_id . '_photo.jpg';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
    if ($mime !== 'image/jpeg' || $_FILES['photo']['size'] > $maxFileSize) {
        finfo_close($finfo);
        echo json_encode(['error' => 'Invalid photo: must be JPEG and under 5MB']);
        exit;
    }
    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
} else {
    echo json_encode(['error' => 'Photo is required']);
    exit;
}

$passport_path = $upload_dir . $application_id . '_passport.pdf';
if (isset($_FILES['passportScan']) && $_FILES['passportScan']['error'] == UPLOAD_ERR_OK) {
    $mime = finfo_file($finfo, $_FILES['passportScan']['tmp_name']);
    if ($mime !== 'application/pdf' || $_FILES['passportScan']['size'] > $maxFileSize) {
        finfo_close($finfo);
        echo json_encode(['error' => 'Invalid passport scan: must be PDF and under 5MB']);
        exit;
    }
    move_uploaded_file($_FILES['passportScan']['tmp_name'], $passport_path);
} else {
    finfo_close($finfo);
    echo json_encode(['error' => 'Passport scan is required']);
    exit;
}

$support_path = null;
if (isset($_FILES['supportDoc']) && $_FILES['supportDoc']['error'] == UPLOAD_ERR_OK) {
    $mime = finfo_file($finfo, $_FILES['supportDoc']['tmp_name']);
    if ($mime !== 'application/pdf' || $_FILES['supportDoc']['size'] > $maxFileSize) {
        finfo_close($finfo);
        echo json_encode(['error' => 'Invalid support document: must be PDF and under 5MB']);
        exit;
    }
    $support_path = $upload_dir . $application_id . '_support.pdf';
    move_uploaded_file($_FILES['supportDoc']['tmp_name'], $support_path);
}
finfo_close($finfo);

// Store in database
try {
    $sql = "INSERT INTO users (
        application_id, first_name, last_name, passport_number, dob, nationality, phone,
        email, address, visa_type, duration, photo_path, passport_scan_path, support_doc_path, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $application_id, $data['firstName'], $data['lastName'], $data['passportNumber'],
        $data['dob'], $data['nationality'], $data['phone'], $data['email'],
        $data['address'], $data['visaType'], $data['duration'],
        $photo_path, $passport_path, $support_path
    ]);

    echo json_encode([
        'message' => 'Visa application submitted',
        'application_id' => $application_id
    ]);
} catch (PDOException $e) {
    @unlink($photo_path);
    @unlink($passport_path);
    if ($support_path) @unlink($support_path);
    echo json_encode(['error' => 'Failed to save application']);
}
?>