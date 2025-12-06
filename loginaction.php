<?php
// Start the session to store user data upon successful login
session_start();
require 'databasecon.php';

$db = new DatabaseCon();

// Ensure the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Server-side validation: Check for empty fields
    if (empty($_POST['email']) || empty($_POST['password'])) {
        header("Location: login.php?error=emptyfields");
        exit();
    }

    // Clean the email input to prevent errors from whitespace or case differences
    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];

    // Server-side validation: Check for valid email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=invalidemail");
        exit();
    }

    // Securely check if the user exists using the class method
    $query = "SELECT * FROM login WHERE uname = ?";
    $result = $db->selectData($query, "s", $email);

    // Process the result
    if ($result && $result->num_rows === 1) {
        // User found, fetch their data
        $user = $result->fetch_assoc();

        // --- MENTOR'S NOTE: Plain-text password check as requested. ---
        // For a real-world application, this should be replaced with password_verify().
        if ($password === $user['upass']) {
            
            // --- LOGIN SUCCESS ---
            // Set session variables to remember the user
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['user_email'] = $user['uname'];
            $_SESSION['user_type'] = $user['utype'];

            // Set a flag to show the welcome pop-up on the next page
            $_SESSION['show_welcome_popup'] = true;

            // Redirect user to the correct dashboard based on their user type
            switch ($user['utype']) {
                case 'admin':
                    header("Location: adminhomepage.php");
                    break;
                case 'customer':
                    // Assuming you have a customer dashboard page
                    header("Location: customerhome.php"); 
                    break;
                case 'tailor':
                    header("Location: tailorhome.php");
                    break;
                default:
                    // Fallback redirect to the main page
                    header("Location: index.php");
            }
            exit(); // Stop script execution after redirect

        } else {
            // Password did not match
            header("Location: login.php?error=incorrectpassword");
            exit();
        }

    } else {
        // User's email was not found in the database
        header("Location: login.php?error=emailnotfound");
        exit();
    }
} else {
    // If someone tries to access this page directly without submitting the form
    header("Location: login.php");
    exit();
}
?>