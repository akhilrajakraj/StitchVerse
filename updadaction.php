<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in before processing any action.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    // If not a tailor, stop immediately.
    http_response_code(403); // Forbidden
    echo "Unauthorized access.";
    exit();
}

// Ensure the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $tailor_id = $_SESSION['user_id']; // Get the logged-in tailor's ID

    // Get all data from the form using $_POST
    $design_id = $_POST['id'];
    $dname = $_POST['dname'];
    $dtype = $_POST['dtype'];
    $ddesc = $_POST['ddesc'];
    $dprice = $_POST['dprice'];
    $old_image_path = $_POST['old_image'];
    
    $target_file_path = $old_image_path; // Default to the old image path

    // --- Check for and handle a new file upload (Your existing logic is good) ---
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
        $target_dir = "uploads/";
        $original_name = basename($_FILES["new_image"]["name"]);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $unique_filename = time() . '_' . uniqid() . '.' . $imageFileType;
        $new_target_file_path = $target_dir . $unique_filename;

        $check = getimagesize($_FILES["new_image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $new_target_file_path)) {
                if (file_exists($old_image_path) && is_writable($old_image_path)) {
                    unlink($old_image_path);
                }
                $target_file_path = $new_target_file_path;
            }
        }
    }

    // --- Use a Prepared Statement for the UPDATE ---
    $sql = "UPDATE upload SET 
                dname = ?, 
                dtype = ?, 
                ddesc = ?, 
                dprice = ?, 
                dimg = ? 
            WHERE did = ? AND uid = ?";

    // --- THIS IS THE CORRECTED LINE ---
    // The type string is now "sssisii" (7 characters for 7 variables)
    $db->executeQuery($sql, "sssisii", $dname, $dtype, $ddesc, $dprice, $target_file_path, $design_id, $tailor_id);

    // Set a session variable for the success message
    $_SESSION['update_success'] = "Design '" . htmlspecialchars($dname) . "' was updated successfully!";
    
    // Redirect back to the view page
    header("Location: viewmydesigns.php");
    exit();
} else {
    // If someone tries to access this page directly, redirect them away.
    header("Location: tailorhome.php");
    exit();
}
?>