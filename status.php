<?php
// status.php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - Evisa Portal</title>
    <link rel="stylesheet" href="status.css">
</head>
<body>
    <div class="flag">
        <img src="./assets/poils.png" alt="Flag">
    </div>

    <header class="navbar">
        <div class="logo">
            <ion-icon class="menu-toggle" name="menu-outline"></ion-icon>
            <div class="logo-img">
                <img src="./assets/images.png" alt="Logo">
            </div>
            Evisa and Passport
        </div>
        <div class="navbar-links">
            <a href="index.php"><ion-icon name="home-outline"></ion-icon>Home</a>
            <a href="index.php#hero-section"><ion-icon name="documents-outline"></ion-icon>Apply</a>
            <a href="status.php"><ion-icon name="checkmark-done-outline"></ion-icon>Status</a>
            <a href="#faq"><ion-icon name="help-circle-outline"></ion-icon>FAQ</a>
            <a href="#contact"><ion-icon name="mail-outline"></ion-icon>Contact</a>
            <?php if (isset($_SESSION['user_email'])): ?>
                <a href="Backend/logout.php"><ion-icon name="log-out-outline"></ion-icon>Logout</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="status-section">
        <div class="status-box">
            <?php if (!isset($_SESSION['user_email'])): ?>
                <h2>Login to Check Status</h2>
                <form id="statusLoginForm" action="Backend/status.php" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit">Login</button>
                    <p class="toggle-text">Don't have an account? <a href="index.php#hero-section">Sign up</a></p>
                </form>
            <?php else: ?>
                <h2>Your Application Status</h2>
                <div id="statusResult" class="status-result">
                    <!-- Statuses will be loaded here via JavaScript -->
                </div>
                <button id="refreshStatus">Refresh Status</button>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="socials">
            <a href="#home"><ion-icon name="logo-instagram"></ion-icon></a>
            <a href="#status"><ion-icon name="logo-twitter"></ion-icon></a>
            <a href="#faq"><ion-icon name="logo-linkedin"></ion-icon></a>
            <a href="#contact"><ion-icon name="mail-outline"></ion-icon></a>
        </div>
        <div class="divider"></div>
        <div class="navbar-links">
            <a href="index.php"><ion-icon name="home-outline"></ion-icon>Home</a>
            <a href="status.php"><ion-icon name="checkmark-done-outline"></ion-icon>Status</a>
            <a href="#faq"><ion-icon name="help-circle-outline"></ion-icon>FAQ</a>
            <a href="#contact"><ion-icon name="mail-outline"></ion-icon>Contact</a>
        </div>
        <div class="copyR">
            <p>© 2025 Evisa Portal. All rights reserved.</p>
        </div>
    </footer>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        <?php if (!isset($_SESSION['user_email'])): ?>
        // Handle login form submission
        document.getElementById('statusLoginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const statusResult = document.getElementById('statusResult');

            fetch('Backend/status.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    // Reload page after successful login to show status
                    window.location.reload();
                })
                .catch(error => {
                    statusResult.innerHTML = `Error: ${error.message}`;
                    statusResult.style.color = 'red';
                });
        });
        <?php else: ?>
        // Fetch status for logged-in user
        function fetchStatus() {
            const statusResult = document.getElementById('statusResult');
            fetch('Backend/status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ fetch_status: true })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    if (data.applications.length === 0) {
                        statusResult.innerHTML = '<p>No applications found.</p>';
                    } else {
                        statusResult.innerHTML = data.applications.map(app => `
                            <div class="status-item">
                                <p><strong>Application ID:</strong> ${app.application_id}</p>
                                <p><strong>Type:</strong> ${app.application_type}</p>
                                <p><strong>Status:</strong> ${app.status}</p>
                                <p><strong>Details:</strong> ${app.details}</p>
                            </div>
                        `).join('');
                    }
                    statusResult.style.color = '#333';
                })
                .catch(error => {
                    statusResult.innerHTML = `Error: ${error.message}`;
                    statusResult.style.color = 'red';
                });
        }

        // Initial status fetch
        fetchStatus();

        // Refresh status button
        document.getElementById('refreshStatus').addEventListener('click', fetchStatus);
        <?php endif; ?>
    </script>
</body>
</html>