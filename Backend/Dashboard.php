<?php
session_start();
ini_set('session.cookie_secure', defined('ENV') && ENV === 'production' ? 1 : 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /adminlog.php');
    exit;
}

require 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetchColumn() !== 'admin') {
        header('Location: /officer.php');
        exit;
    }

    // Fetch applications & users
    $visaApps = $pdo->query("SELECT * FROM VisaApp")->fetchAll(PDO::FETCH_ASSOC);
    $passApps = $pdo->query("SELECT * FROM PassApp")->fetchAll(PDO::FETCH_ASSOC);
    $users = $pdo->query("SELECT user_id, email, role, status FROM users")->fetchAll(PDO::FETCH_ASSOC);

    // Handle POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if (in_array($action, ['add_user', 'add_admin', 'add_officer'])) {
            $data = [
                filter_var($_POST['id_number'], FILTER_SANITIZE_STRING),
                filter_var($_POST['first_name'], FILTER_SANITIZE_STRING),
                filter_var($_POST['last_name'], FILTER_SANITIZE_STRING),
                filter_var($_POST['phone'], FILTER_SANITIZE_STRING),
                filter_var($_POST['dob'], FILTER_SANITIZE_STRING),
                filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                filter_var($_POST['address'], FILTER_SANITIZE_STRING),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $action === 'add_admin' ? 'admin' : ($action === 'add_officer' ? 'officer' : 'applicant')
            ];

            $pdo->prepare("INSERT INTO users (id_number, first_name, last_name, phone, dob, email, address, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute($data);
        } elseif ($action === 'delete_user' && filter_var($_POST['user_id'], FILTER_VALIDATE_INT)) {
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$_POST['user_id']]);
        }

        header("Location: Dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Visa Admin</title>
    <link rel="stylesheet" href="Dash.css">
    <style>
        .modal-actions { margin-top:20px; text-align:right; }
        .modal-actions button { margin-left:10px; padding:8px 16px; cursor:pointer; }
        .user-form { margin:20px 0; padding:10px; border:1px solid #ccc; border-radius:8px; }
        .user-form input, .user-form select { margin:5px; padding:5px; }
    </style>
</head>
<body>
    <div class="dash-container">
        <header>
            <h1>E-VISA <span>ADMIN</span></h1>
            <div class="search-bar"><input type="text" placeholder="Search"></div>
        </header>
        <div class="main-content">
            <nav class="side-bar">
                <a href="#" data-section="visa-applications">Visa Applications</a>
                <a href="#" data-section="passport-applications">Passport Applications</a>
                <a href="#" data-section="users">Manage Users</a>
                <a href="#" data-section="logout">Logout</a>
            </nav>
            <main class="content">

                <!-- VISA APPLICATIONS -->
                <section class="section active" id="visa-applications">
                    <h2>Visa Applications</h2>
                    <table>
                        <tr><th>ID</th><th>Name</th><th>DOB</th><th>Visa Type</th><th>Action</th></tr>
                        <?php foreach ($visaApps as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['application_id']) ?></td>
                            <td><?= htmlspecialchars($app['first_name'].' '.$app['last_name']) ?></td>
                            <td><?= htmlspecialchars($app['dob']) ?></td>
                            <td><?= htmlspecialchars($app['visa_type'] ?? 'N/A') ?></td>
                            <td><button class="btn review-btn" data-id="<?= $app['application_id'] ?>" data-type="visa">Review</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </section>

                <!-- PASSPORT APPLICATIONS -->
                <section class="section" id="passport-applications">
                    <h2>Passport Applications</h2>
                    <table>
                        <tr><th>ID</th><th>Name</th><th>DOB</th><th>Action</th></tr>
                        <?php foreach ($passApps as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['application_id']) ?></td>
                            <td><?= htmlspecialchars($app['first_name'].' '.$app['last_name']) ?></td>
                            <td><?= htmlspecialchars($app['dob']) ?></td>
                            <td><button class="btn review-btn" data-id="<?= $app['application_id'] ?>" data-type="passport">Review</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </section>

                <!-- MANAGE USERS -->
                <section class="section" id="users">
                    <h2>Manage Users</h2>

                    <!-- Add User Form -->
                    <form method="post" class="user-form">
                        <h3>Add New User</h3>
                        <input type="text" name="id_number" placeholder="ID Number" required>
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                        <input type="text" name="phone" placeholder="Phone" required>
                        <input type="date" name="dob" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="address" placeholder="Address" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <select name="action" required>
                            <option value="add_user">Applicant</option>
                            <option value="add_officer">Officer</option>
                            <option value="add_admin">Admin</option>
                        </select>
                        <button type="submit">Add User</button>
                    </form>

                    <!-- User Table -->
                    <table>
                        <tr><th>ID</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['user_id']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td><?= htmlspecialchars($u['status']) ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <button type="submit" class="btn" onclick="return confirm('Delete user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </section>

                <!-- LOGOUT -->
                <section class="section" id="logout">
                    <form action="logout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </section>
            </main>
        </div>

        <!-- MODAL -->
        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Application Details</h2>
                <div id="modal-content"></div>
                <div class="modal-actions">
                    <button id="validate-btn">Validate</button>
                    <button id="close-btn">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const visaApps = <?= json_encode($visaApps) ?>;
        const passApps = <?= json_encode($passApps) ?>;

        const modal = document.getElementById('modal');
        const modalContent = document.getElementById('modal-content');

        // Sidebar navigation
        document.querySelectorAll('.side-bar a').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
                const target = link.dataset.section;
                if (target) document.getElementById(target).classList.add('active');
            });
        });

        // Review modal
        document.querySelectorAll('.review-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const appId = btn.dataset.id;
                const type = btn.dataset.type;
                const apps = type === 'visa' ? visaApps : passApps;
                const app = apps.find(a => a.application_id == appId);

                if (app) {
                    modalContent.innerHTML = Object.entries(app)
                        .map(([k, v]) => `<p><strong>${k}:</strong> ${v ?? 'N/A'}</p>`)
                        .join('');
                    modal.style.display = 'flex';
                    document.getElementById('validate-btn').onclick = () => {
                        alert(`Application ${app.application_id} validated!`);
                        modal.style.display = 'none';
                    };
                }
            });
        });

        // Close modal
        document.querySelector('.close').onclick = () => modal.style.display = 'none';
        document.getElementById('close-btn').onclick = () => modal.style.display = 'none';
        window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };
    </script>
</body>
</html>
