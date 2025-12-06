<?php
session_start();
require 'databasecon.php';

// --- Security Checks ---
// 1. Ensure a tailor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// 2. Ensure an ID was passed in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: upd.php");
    exit();
}

$db = new DatabaseCon();
$design_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- First, find the image path and verify ownership ---
$sql_select = "SELECT dimg, uid FROM upload WHERE did='$design_id'";
$rs = $db->selectData($sql_select);

if ($row = mysqli_fetch_array($rs)) {
    // 3. Check if the design belongs to the logged-in tailor
    if ($row['uid'] == $tailor_id) {
        $image_path = $row['dimg'];

        // --- Now, delete the database record ---
        $sql_delete = "DELETE FROM upload WHERE did='$design_id'";
        $db->executeQuery($sql_delete); // Using the correct 'query' method

        // --- Finally, delete the actual image file from the server ---
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Set the success message for the red pop-up
        $_SESSION['delete_success'] = "Design has been removed successfully.";

    } else {
        // Set an error message if they try to delete someone else's design
        $_SESSION['delete_error'] = "You do not have permission to remove this design.";
    }
}

// Redirect back to the designs page
header("Location: upd.php");
exit();
?>





