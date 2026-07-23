<?php
// Backend/status.php
header('Content-Type: application/json');
session_start();

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Mock user database (replace with actual database query)
$mock_users = [
    'user@example.com' => [
        'password' => password_hash('password123', PASSWORD_DEFAULT), // Hashed password
        'email' => 'user@example.com'
    ]
];

// Mock application data (replace with actual database query)
$mock_applications = [
    'user@example.com' => [
        [
            'application_id' => 'APP123',
            'application_type' => 'Visa',
            'status' => 'Approved',
            'details' => 'Your visa is ready for collection.'
        ],
        [
            'application_id' => 'APP789',
            'application_type' => 'Passport',
            'status' => 'Processing',
            'details' => 'Documents are being verified.'
        ]
    ]
];

// Handle login or status fetch
if (isset($_POST['email']) && isset($_POST['password'])) {
    // Login request
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo json_encode(['error' => 'Email and password are required']);
        exit;
    }

    // Verify user (replace with database query)
    if (isset($mock_users[$email]) && password_verify($password, $mock_users[$email]['password'])) {
        $_SESSION['user_email'] = $email;
        echo json_encode(['success' => 'Login successful']);
    } else {
        echo json_encode(['error' => 'Invalid email or password']);
    }
} elseif (isset($_POST['fetch_status']) && isset($_SESSION['user_email'])) {
    // Fetch status for logged-in user
    $email = $_SESSION['user_email'];

    // Fetch applications (replace with database query)
    $applications = $mock_applications[$email] ?? [];
    echo json_encode(['applications' => $applications]);
} else {
    echo json_encode(['error' => 'Unauthorized access']);
}

exit;
?>