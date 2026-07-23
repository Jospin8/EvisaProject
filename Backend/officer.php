<?php
session_start();

// Ensure only officers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'officer') {
    header("Location: /EvisaProject/adminlog.php");
    exit;
}

require __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Visa Applications dynamically
    $stmtVisa = $pdo->query("SELECT * FROM VisaApp");
    $visaApps = $stmtVisa->fetchAll(PDO::FETCH_ASSOC);
    $visaColumns = array_keys($visaApps[0] ?? []);

    // Fetch Passport Applications dynamically
    $stmtPass = $pdo->query("SELECT * FROM PassApp");
    $passApps = $stmtPass->fetchAll(PDO::FETCH_ASSOC);
    $passColumns = array_keys($passApps[0] ?? []);

} catch (PDOException $e) {
    die("Database Error: " . htmlspecialchars($e->getMessage()));
}

// CSRF token for logout
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officer Dashboard</title>
    <link rel="stylesheet" href="officer.css">
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            width: 70%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-header {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            gap: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-close { background: #aaa; color: white; }
        .btn-validate { background: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="dash-container">
        <!-- Header -->
        <header>
            <h1>Officer <span>Dashboard</span></h1>
            <form method="POST" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </header>

        <div class="main-content">
            <!-- Sidebar -->
            <nav class="side-bar">
                <a href="#visa-section">Visa Applications</a>
                <a href="#passport-section">Passport Applications</a>
            </nav>

            <!-- Main Content -->
            <div class="content">
                <!-- Visa Section -->
                <section id="visa-section" class="section active">
                    <h2>Visa Applications</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visaApps as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['application_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['first_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['last_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['status'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['created_at'] ?? '') ?></td>
                                    <td>
                                        <button class="btn review-btn" 
                                            data-type="Visa Application" 
                                            data-details='<?= json_encode($app) ?>'>Review</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Passport Section -->
                <section id="passport-section" class="section">
                    <h2>Passport Applications</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($passApps as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['application_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['first_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['last_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['status'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($app['created_at'] ?? '') ?></td>
                                    <td>
                                        <button class="btn review-btn" 
                                            data-type="Passport Application" 
                                            data-details='<?= json_encode($app) ?>'>Review</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Application Details</div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button class="btn btn-validate">Validate</button>
                <button class="btn btn-close">Close</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('reviewModal');
        const modalBody = modal.querySelector('.modal-body');
        const closeBtn = modal.querySelector('.btn-close');
        const validateBtn = modal.querySelector('.btn-validate');

        document.querySelectorAll('.review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const details = JSON.parse(btn.dataset.details);
                let html = `<table style="width:100%; border-collapse:collapse;">`;
                for (const [key, value] of Object.entries(details)) {
                    html += `<tr>
                                <th style="text-align:left; border:1px solid #ccc; padding:5px;">${key}</th>
                                <td style="border:1px solid #ccc; padding:5px;">${value}</td>
                             </tr>`;
                }
                html += `</table>`;
                modalBody.innerHTML = html;
                modal.style.display = 'flex';

                validateBtn.onclick = () => {
                    alert("Application validated!");
                    modal.style.display = 'none';
                };
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.onclick = (e) => {
            if (e.target === modal) modal.style.display = 'none';
        };
    </script>
</body>
</html>
