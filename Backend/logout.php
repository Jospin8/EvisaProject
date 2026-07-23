<?php
session_start();
ini_set('session.cookie_secure', defined('ENV') && ENV === 'production' ? 1 : 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
header('Content-Type: application/json');

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF token validation failed");
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Clear all session data
$_SESSION = [];
// Invalidate the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// Destroy the session
session_destroy();

error_log("Logout executed, redirecting to /adminlog.php?logout=success");
echo json_encode(['redirect' => '/adminlog.php?logout=success']);
exit;