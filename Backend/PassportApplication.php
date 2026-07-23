<?php
ob_start();
try {
    require 'config.php';

    // Allow local requests (CORS)
    header('Access-Control-Allow-Origin: http://localhost');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        ob_end_flush();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        ob_end_flush();
        exit;
    }

    session_start();
    if (!isset($_SESSION['id_number'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please log in']);
        ob_end_flush();
        exit;
    }

    $id_number = $_SESSION['id_number'];
    $application_id = bin2hex(random_bytes(16));

    // Required fields
    $fields = [
        'first_name', 'last_name', 'dob', 'place_of_birth',
        'nationality', 'gender', 'marital_status',
        'occupation', 'address', 'phone', 'email', 'emergency_contact'
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? trim($_POST[$field]) : '';
        if (empty($data[$field]) && $field !== 'emergency_contact') {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            ob_end_flush();
            exit;
        }
    }

    // Handle file uploads
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $documents = [
        'photo', 'id_scan', 'birth_certificate', 'citizenship_proof',
        'old_passport', 'payment_receipt', 'police_clearance', 'recommandation_letter'
    ];

    $fileData = [];
    foreach ($documents as $doc) {
        if (isset($_FILES[$doc]) && $_FILES[$doc]['error'] === UPLOAD_ERR_OK) {
            $fileExt = pathinfo($_FILES[$doc]['name'], PATHINFO_EXTENSION);
            $fileName = $application_id . '_' . $doc . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES[$doc]['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['error' => "Failed to upload $doc"]);
                ob_end_flush();
                exit;
            }
            $fileData[$doc] = $fileName;
        } elseif ($doc === 'payment_receipt' && !isset($_FILES[$doc])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required document: $doc"]);
            ob_end_flush();
            exit;
        }
    }

    // Passport type + duration
    $passportType = isset($_POST['passport_type']) ? trim($_POST['passport_type']) : '';
    $duration = isset($_POST['duration']) ? (int)trim($_POST['duration']) : 0;

    if (empty($passportType) || $duration <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid passport type or duration']);
        ob_end_flush();
        exit;
    }

    // Insert application
    $stmt = $pdo->prepare("
        INSERT INTO VisaApp (
            application_id, first_name, last_name, dob, place_of_birth,
            nationality, genre, marital_status, occupation, address,
            phone, email, Emergency_contact, Passport_type, duration, status
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $application_id,
        $data['first_name'],
        $data['last_name'],
        $data['dob'],
        $data['place_of_birth'],
        $data['nationality'],
        $data['gender'],
        $data['marital_status'],
        $data['occupation'],
        $data['address'],
        $data['phone'],
        $data['email'],
        $data['emergency_contact'],
        $passportType,
        $duration,
        'submitted'
    ]);

    echo json_encode([
        'message' => 'Application submitted successfully',
        'application_id' => $application_id
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    $error_msg = 'Application submission failed: ' . $e->getMessage();
    echo json_encode(['error' => $error_msg]);
    file_put_contents(__DIR__ . '/../debug.log', "$error_msg\n", FILE_APPEND);
} finally {
    ob_end_flush();
}
