<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Check if all required data is present in the URL
if (isset($_GET['tid'], $_GET['fd'], $_GET['rt'])) {
    
    $db = new DatabaseCon();
    
    // --- Collect and Sanitize Data ---
    $customer_id = $_SESSION['user_id'];
    $tailor_id = (int)$_GET['tid']; // Ensure it's an integer
    $feedback = trim($_GET['fd']); // Trim whitespace
    $rating = (int)$_GET['rt']; // Ensure it's an integer

    // Basic validation
    if (!empty($feedback) && $rating > 0 && $rating <= 5) {

        // --- Insert into feedb table using a secure prepared statement ---
        $sql_insert = "INSERT INTO feedb (tid, uid, feedbck, rate) VALUES (?, ?, ?, ?)";
        
        // Use the insertData method we've established for consistency and security
        // Data types: i = integer, s = string
        $db->executeQuery($sql_insert, "iisi", $tailor_id, $customer_id, $feedback, $rating);

        // --- Set success message for the pop-up and redirect ---
        $_SESSION['feedback_success'] = "Thank you! Your feedback has been submitted successfully.";
        header("Location: cviewt.php");
        exit();
    }
}

// Redirect back if data is missing or invalid
header("Location: cviewt.php");
exit();

?>