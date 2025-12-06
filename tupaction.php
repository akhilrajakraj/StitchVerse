<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in before processing
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Ensure the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();

    // --- Retrieve and Sanitize Form Data ---
    $tailor_id = $_POST['id'];

    // Security Check: Ensure the ID from the form matches the one in the session
    if ($tailor_id != $_SESSION['user_id']) {
        // Use session flash message for a cleaner user experience
        $_SESSION['update_error'] = "Unauthorized action detected.";
        header("Location: tupdate.php");
        exit();
    }

    $tname = trim($_POST['tname']);
    $address = trim($_POST['tadd']);
    $city = trim($_POST['tcity']);
    $distri = $_POST['tdis'];
    $pinc = trim($_POST['pinc']);
    $phone = trim($_POST['ph']);
    $spect = $_POST['sp'];
    $quali = $_POST['qf'];

    // --- Server-Side Validation ---
    $errors = [];
    if (strlen($tname) < 3) $errors[] = "Name must be at least 3 characters.";
    if (strlen($address) < 10) $errors[] = "Please enter a complete address.";
    if (empty($city)) $errors[] = "City cannot be empty.";
    if (empty($spect)) $errors[] = "Please select a speciality.";
    if (empty($quali)) $errors[] = "Please select a qualification.";
    if (!preg_match('/^[0-9]{6}$/', $pinc)) $errors[] = "Pincode must be 6 digits.";
    
    // Validate phone number format first
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be 10 digits.";
    } else {
        // *** NEW: If phone format is valid, check for uniqueness in the database ***
        // This is the crucial server-side check that mirrors the JavaScript validation.
        $query_check_phone = "SELECT tid FROM treg WHERE phone = ? AND tid != ?";
        $result_check = $db->selectData($query_check_phone, "si", $phone, $tailor_id);
        if ($result_check && $result_check->num_rows > 0) {
            $errors[] = "This phone number is already registered by another user.";
        }
    }

    if (!empty($errors)) {
        // If there are any errors, store them and redirect back
        $_SESSION['update_error'] = implode("<br>", $errors);
        header("Location: tupdate.php?update=error"); // Add error flag for clarity
        exit();
    }
    
    // --- Database Update ---
    // If validation passes, proceed with the update.
    $sql = "UPDATE treg SET tname=?, address=?, city=?, distri=?, pinc=?, phone=?, spect=?, quali=? WHERE tid=?";
    
    // Using a new DatabaseCon method for updates makes the code cleaner
    // The bind parameter signature is 'ssssssssi' because pincode and phone are best handled as strings.
    $update_success = $db->executeQuery($sql, "ssssssssi", $tname, $address, $city, $distri, $pinc, $phone, $spect, $quali, $tailor_id);
    
    if ($update_success) {
        // Redirect back to the profile page with a success flag
        header("Location: tupdate.php?update=success");
        exit();
    } else {
        // If the update fails for some database reason
        $_SESSION['update_error'] = "Profile update failed due to a server error. Please try again.";
        header("Location: tupdate.php?update=error");
        exit();
    }
} else {
    // Redirect if this page is accessed directly without POST data
    header("Location: tupdate.php");
    exit();
}
?>