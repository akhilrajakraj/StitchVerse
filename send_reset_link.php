<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require 'databasecon.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $email = trim(strtolower($_POST['email']));

    $query = "SELECT uid FROM login WHERE uname = ?";
    $result = $db->selectData($query, "s", $email);

    if ($result && $result->num_rows === 1) {
        $token = bin2hex(random_bytes(50));
        $expiry_time = date("Y-m-d H:i:s", time() + 3600); 

        $update_query = "UPDATE login SET reset_token = ?, token_expiry = ? WHERE uname = ?";
        $db->executeQuery($update_query, "sss", $token, $expiry_time, $email);

        $mail = new PHPMailer(true);

        try {
           
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com'; 
            $mail->Password   = 'cqdaqntjinoeclpr';      
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse');
            $mail->addAddress($email);

           
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request for StitchVerse';
            
            $reset_link = "http://localhost/stitchverse1/reset_password.php?token=" . $token;
            
            $mail->Body    = "
                <html>
                <body>
                    <h2>Password Reset Request</h2>
                    <p>Hi there,</p>
                    <p>We received a request to reset the password for your StitchVerse account. Click the link below to set a new password:</p>
                    <p><a href='{$reset_link}' style='padding: 10px 15px; background-color: #7c3aed; color: white; text-decoration: none; border-radius: 5px;'>Reset Your Password</a></p>
                    <p>This link will expire in one hour.</p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <br>
                    <p>Thanks,</p>
                    <p>The StitchVerse Team</p>
                </body>
                </html>";
            
            $mail->AltBody = "To reset your password, please visit the following link: {$reset_link}";

            $mail->send();

        } catch (Exception $e) {
           
            error_log("Mailer Error: {$mail->ErrorInfo}");
        }
    }

    
    $_SESSION['status_message'] = "If an account with that email address exists, we have sent a password reset link to it.";
    header("Location: forgot_password.php");
    exit();
}
?>


