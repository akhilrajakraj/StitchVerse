<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure the form was submitted via POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $conn = $db->getConnection(); 

    $customer_id_from_form = $_POST['id'];
    $customer_id_from_session = $_SESSION['user_id'];
    
    // Security check to ensure users can only update their own profile
    if ($customer_id_from_form != $customer_id_from_session) {
        die("Unauthorized action.");
    }
    
    $cname = $_POST['cn'];
    $address = $_POST['ad'];
    $city = $_POST['city'];
    $district = $_POST['dis'];
    $phone = $_POST['ph'];
    $pincode = $_POST['pin'];

    // Update the database using a secure prepared statement (email field removed)
    $sql = "UPDATE creg SET cname=?, address=?, city=?, distr=?, phone=?, pincode=? WHERE cid=?";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: s = string, i = integer
    $stmt->bind_param("sssssis", $cname, $address, $city, $district, $phone, $pincode, $customer_id_from_session);

    if ($stmt->execute()) {
        header("Location: cupdate.php?update=success");
        exit();
    } else {
        $_SESSION['update_error'] = "Database error: Could not update profile.";
        header("Location: cupdate.php");
        exit();
    }

} else {
    header("Location: cupdate.php");
    exit();
}
?>