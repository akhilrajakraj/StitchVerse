<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $customer_id = $_SESSION['user_id'];

    $order_id = (int)$_POST['order_id'];
    $amount = (float)$_POST['amount'];
    
    $pdate = date("Y-m-d H:i:s");
    $pstatus = "Unpaid"; // Status is 'Unpaid' for COD until delivery
    $pmode = "COD";      // Payment mode is 'COD'

    // --- Insert a record into the payment table for tracking ---
    $sql_insert = "INSERT INTO payment (uid, order_id, pdate, pstatus, pmode, card_name, card_no, carexp_dt, cvv) 
                   VALUES (?, ?, ?, ?, ?, '', '', '1000-01-01', '')"; // Empty/default values for card details
    $db->executeQuery($sql_insert, "issss", $customer_id, $order_id, $pdate, $pstatus, $pmode);

    // --- Update the stitchreq table status to 'Processing' ---
    // This new status indicates the order is confirmed but awaiting payment on delivery.
    $sql_update = "UPDATE stitchreq SET sstatus = 'Processing' WHERE sdid = ?";
    $db->executeQuery($sql_update, "i", $order_id);

    // --- Set success message for the pop-up and redirect ---
    $_SESSION['order_success'] = "Your Cash on Delivery order (#$order_id) has been confirmed!";
    header("Location: vieworders.php");
    exit();

} else {
    header("Location: customerhome.php");
    exit();
}
?>