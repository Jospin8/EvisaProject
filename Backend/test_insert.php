// backend/test_insert.php
<?php
require 'config.php';

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (id_number, first_name, last_name, phone, dob, email, address, password_hash, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        '123XYZ',
        'Test',
        'User',
        '+9876543210',
        '1980-01-01',
        'test@example.com',
        '456 Test St',
        password_hash('TestPass123', PASSWORD_DEFAULT)
    ]);

    echo "Inserted!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
