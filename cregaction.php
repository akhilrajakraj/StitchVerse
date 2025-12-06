<?php
session_start();
require 'databasecon.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $conn = $db->getConnection();

    
    $cname = trim($_POST['cname']);
    $email = trim(strtolower($_POST['email']));
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $distr = $_POST['distr'];
    $phone = trim($_POST['phone']);
    $pincode = trim($_POST['pincode']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    
    $errors = [];
    if (strlen($cname) < 3) { $errors[] = "Full name must be at least 3 characters."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    if (strlen($address) < 10) { $errors[] = "A more complete address is required."; }
    if (strlen($city) < 3) { $errors[] = "City name seems too short."; }
    if (empty($distr)) { $errors[] = "Please select a district."; }
    if (!preg_match('/^[0-9]{10}$/', $phone)) { $errors[] = "Phone number must be exactly 10 digits."; }
    if (!preg_match('/^[0-9]{6}$/', $pincode)) { $errors[] = "Pincode must be exactly 6 digits."; }
    if (strlen($password) < 8) { $errors[] = "Password must be at least 8 characters."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }
    if ($terms !== 'agreed') { $errors[] = "You must agree to the terms and conditions."; }

    
    $email_check_query = "SELECT uid FROM login WHERE uname = ?";
    $email_result = $db->selectData($email_check_query, "s", $email);
    if ($email_result && $email_result->num_rows > 0) {
        $errors[] = "This email address is already registered.";
    }

    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header("Location: creg.php");
        exit();
    }

   
    try {
        
        $conn->begin_transaction();

        
        $customer_query = "INSERT INTO creg (cname, email, address, city, distr, phone, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)";
       
        $stmt_customer = $conn->prepare($customer_query);
        $stmt_customer->bind_param("ssssssi", $cname, $email, $address, $city, $distr, $phone, $pincode);
        if (!$stmt_customer->execute()) {
            throw new Exception("Failed to create customer entry.");
        }
        $customer_id = $stmt_customer->insert_id; 
        $stmt_customer->close();

        
        $usertype = 'customer';

       
        $login_query = "INSERT INTO login (uid, uname, upass, utype) VALUES (?, ?, ?, ?)";
        $stmt_login = $conn->prepare($login_query);
        
        $stmt_login->bind_param("isss", $customer_id, $email, $password, $usertype);
        if (!$stmt_login->execute()) {
            throw new Exception("Failed to create login entry.");
        }
        $stmt_login->close();

        
        $conn->commit();

        //  Send the Welcome Email ---
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com';
            $mail->Password   = 'cqdaqntjinoeclpr'; // Use your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse');
            $mail->addAddress($email, $cname);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to StitchVerse!';
            $mail->Body    = "
                <html><body>
                    <h1 style='color: #6d28d9;'>Welcome to StitchVerse, {$cname}!</h1>
                    <p>We're thrilled to have you join our community of fashion lovers and talented tailors.</p>
                    <p>Your account has been created successfully. You can now log in to explore custom designs, connect with skilled tailors, and bring your perfect outfit to life.</p>
                    <p>Thank you for registering!</p>
                    <br>
                    <p>Best regards,</p>
                    <p><strong>The StitchVerse Team</strong></p>
                </body></html>";
            $mail->AltBody = "Welcome to StitchVerse, {$cname}! We're thrilled to have you. Your account has been created successfully. Thank you for registering!";

            $mail->send();
        } catch (Exception $e) {
            
            error_log("Welcome email failed to send to {$email}: {$mail->ErrorInfo}");
        }

       
        header("Location: creg.php?success=true&name=" . urlencode($cname));
        exit();

    } catch (Exception $e) {
        
        $conn->rollback();
        
        error_log("Registration Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Registration failed due to a server error. Please try again.";
        header("Location: creg.php");
        exit();
    }
}
?>
