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
    $conn = $db->getConnection();

    // --- Collect and Sanitize Data ---
    $uid = $_POST['uid'];
    $tid = $_POST['tid'];
    $sdname = $conn->real_escape_string($_POST['sdname']);
    $sdtype = $conn->real_escape_string($_POST['sdtype']);
    $sinstructions = $conn->real_escape_string($_POST['sinstructions']);
    $sddate = $_POST['sddate'];
    $spriority = $_POST['spriority'];
    
    // --- Handle File Uploads ---
    $uploaded_images = [];
    $target_dir = "uploads/";
    
    if (isset($_FILES['simg']) && !empty($_FILES['simg']['name'][0])) {
        foreach ($_FILES['simg']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['simg']['name'][$key]);
            $unique_filename = time() . '_' . uniqid() . '_' . $file_name;
            $target_file = $target_dir . $unique_filename;
            
            // Check if it's a real image and move it
            $check = getimagesize($tmp_name);
            if ($check !== false) {
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploaded_images[] = $target_file;
                }
            }
        }
    }
    $simg_paths = implode(',', $uploaded_images);

    // --- Insert into stitchreq table ---
    // Note: Other fields like fabric, color, etc., are left NULL as they are part of the original design.
    // The tailor will see the instructions and discuss these details with the customer.
    $sql_insert = "INSERT INTO stitchreq 
                   (uid, tid, sdname, sdtype, sinstructions, sddate, spriority, simg, sstatus) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $db->executeQuery($sql_insert, "iissssss", $uid, $tid, $sdname, $sdtype, $sinstructions, $sddate, $spriority, $simg_paths);

    // --- Set success message and redirect ---
    $_SESSION['request_success'] = "Your customization request for '".htmlspecialchars($sdname)."' has been sent successfully!";
    header("Location: vieworders.php"); // Redirecting to 'My Orders' is a good user experience
    exit();

} else {
    // Redirect if accessed directly
    header("Location: customerhome.php");
    exit();
}
?>