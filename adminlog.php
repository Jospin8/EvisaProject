<?php
session_start();
ini_set('session.cookie_secure', defined('ENV') && ENV === 'production' ? 1 : 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Clear session if logout=success is present
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="adminlog.css">
</head>
<body>
    <section class="login">
        <div class="title"><h1>System Login</h1></div>
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div id="success-message">Successfully logged out.</div>
        <?php endif; ?>
        <div id="error-message" style="display: none;">
            <span id="error-text"></span>
            <span>Please contact <a href="mailto:support@example.com">support@example.com</a>.</span>
        </div>
        <form class="logform" method="POST" action="Backend/login_process.php">
            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-row">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit">Login</button>
        </form>
    </section>
    <script>
        document.querySelector('.logform')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                console.log('Login response:', data);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    errorText.textContent = data.error || 'An error occurred';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                console.log('Login error:', error);
                errorText.textContent = 'Network error occurred';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>
