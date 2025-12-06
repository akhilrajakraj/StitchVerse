<?php
session_start();
require_once 'databasecon.php';
require_once 'mailer.php'; // For sending the pending email

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();

    // 1. Sanitize and retrieve form data
    $tname = trim($_POST['tname']);
    $email = trim(strtolower($_POST['email']));
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $distri = $_POST['distri'];
    $pinc = trim($_POST['pinc']);
    $phone = trim($_POST['phone']);
    $spect = $_POST['spect'];
    $quali = $_POST['quali'];
    $password = $_POST['password']; // Plain text password from form
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    // 2. Perform Server-Side Validation
    $errors = [];
    if (strlen($tname) < 3) { $errors[] = "Full name must be at least 3 characters."; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }
    if ($terms !== 'agreed') { $errors[] = "You must agree to the terms and conditions."; }
    
    // Check if email or phone is already registered
    $email_result = $db->selectData("(SELECT email FROM creg WHERE email = ?) UNION ALL (SELECT email FROM treg WHERE email = ?)", "ss", $email, $email);
    if ($email_result && $email_result->num_rows > 0) {
        $errors[] = "This email address is already registered.";
    }

    $phone_result = $db->selectData("(SELECT phone FROM creg WHERE phone = ?) UNION ALL (SELECT phone FROM treg WHERE phone = ?)", "ss", $phone, $phone);
    if ($phone_result && $phone_result->num_rows > 0) {
        $errors[] = "This phone number is already registered.";
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header("Location: treg.php");
        exit();
    }

    // 3. Insert into treg table, including the plain password
    try {
        // IMPORTANT: The password field in your `treg` table must be able to store the password.
        // It is recommended to have a `password` VARCHAR(255) column.
        $tailor_query = "INSERT INTO treg (tname, email, address, city, distri, pinc, phone, spect, quali, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        // This uses the plain $password variable directly, as requested.
        $db->executeQuery($tailor_query, "ssssssssss", $tname, $email, $address, $city, $distri, $pinc, $phone, $spect, $quali, $password);
        
        // 4. Send the "Pending Approval" Email
        $subject = "Your StitchVerse Application is Awaiting Approval";
        $body = get_pending_email_template($tname);
        sendStitchverseEmail($email, $tname, $subject, $body);

        // 5. Redirect with success status
        header("Location: treg.php?status=pending");
        exit();

    } catch (Exception $e) {
        error_log("Tailor Registration Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Registration failed due to a server error.";
        header("Location: treg.php");
        exit();
    }
} else {
    header("Location: treg.php");
    exit();
}
?>