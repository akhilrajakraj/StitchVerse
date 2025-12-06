<?php
session_start();

require 'databasecon.php';

// Only proceed if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();

    // 1. Get the data from the form
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 2. Basic Validation
    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        die('Please fill out all fields.');
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['error_message'] = "Password must be at least 8 characters long.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // 3. Verify the token is valid and not expired
    // Using NOW() for a reliable time comparison, assuming the column type is DATETIME
    $query = "SELECT uid FROM login WHERE reset_token = ? AND token_expiry > NOW()";
    $result = $db->selectData($query, "s", $token);

    if ($result && $result->num_rows === 1) {
        // Token is valid and not expired! Let's update the password.
        
        // ####################### INSECURE MODIFICATION #######################
        // The password_hash() function has been removed.
        // The plain text password from the form ($new_password) will now be saved directly to the database.
        // This is STRONGLY DISCOURAGED for security reasons.
        // ##################### END OF INSECURE MODIFICATION ##################
        
        // 5. Update the password and clear the reset token
        $update_query = "UPDATE login SET upass = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
        
        // We pass the plain text $new_password directly into the query execution.
        $db->executeQuery($update_query, "ss", $new_password, $token);

        // 6. Set a success message and redirect to the login page
        $_SESSION['success_message'] = "Your password has been updated successfully! You can now log in.";
        header("Location: login.php");
        exit();

    } else {
        // Token is invalid or has expired
        $_SESSION['status_message'] = "This password reset link is invalid or has expired. Please try again.";
        header("Location: forgot_password.php");
        exit();
    }
} else {
    // If someone tries to access this page directly without POSTing
    header("Location: login.php");
    exit();
}
?>