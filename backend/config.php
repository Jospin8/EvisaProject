<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root13@');
define('DB_NAME', 'EVisa');

define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_USER', 'jospin88kasereka@gmail.com');
define('EMAIL_PASS', 'Email123456');
define('EMAIL_PORT', 587);

try{
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed:" . $e->getMessage());

}

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USER;
        $mail->password = EMAIL_PASS;
        $mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port= EMAIL_PORT;

        $mail->setFrom(EMAIL_USER, 'Evisa');
        $mail->addAddress($to);
        $mail->subject=$subject;
        $mail->body=$body;
        $mail->isHTML(false);

        $mail->send();
        return true;
    }catch (Exception $e) {
        error_log("Email not found: {$mail->ErrorInfo}");
        return;false;
    }
}
?>