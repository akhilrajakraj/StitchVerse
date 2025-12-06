
<?php
session_start();
require 'databasecon.php'; // Make sure this path is correct

// Check if a tailor is logged in and the form was submitted
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $conn = $db->getConnection(); 

    // Get tailor ID from the session
    $tailor_id = $_SESSION['user_id'];

    // Get data from the POST request. 
    // No need for real_escape_string with prepared statements.
    $dname = $_POST['dname'];
    $dtype = $_POST['dtype'];
    $ddesc = $_POST['ddesc'];
    $dprice = $_POST['dprice'];

    // --- File Upload Logic ---
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $target_dir = "uploads/";
        
        // --- START: FILENAME SANITIZING LOGIC ---
        $original_name = basename($_FILES["file"]["name"]);
        
        // Get the filename parts
        $filename_without_ext = pathinfo($original_name, PATHINFO_FILENAME);
        $imageFileType = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // 1. Replace spaces and underscores with a hyphen
        $safe_filename = str_replace([' ', '_'], '-', $filename_without_ext);
        // 2. Remove all characters that are not letters, numbers, or hyphens
        $safe_filename = preg_replace('/[^A-Za-z0-9\-]/', '', $safe_filename);
        // 3. Create the final unique filename
        $unique_filename = time() . '-' . $safe_filename . '.' . $imageFileType;
        
        $target_file = $target_dir . $unique_filename;
        // --- END: FILENAME SANITIZING LOGIC ---

        // Check if image file is an actual image
        $check = getimagesize($_FILES["file"]["tmp_name"]);
        if($check === false) {
            $_SESSION['upload_error'] = "File is not a valid image.";
            header("Location: upd.php");
            exit();
        }

        // Allow certain file formats
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if(!in_array($imageFileType, $allowed_types)) {
            $_SESSION['upload_error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: upd.php");
            exit();
        }

        // Try to move the uploaded file
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // --- SECURE DATABASE INSERTION USING PREPARED STATEMENTS ---
            $sql = "INSERT INTO upload (uid, dname, dtype, ddesc, dprice, dimg) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // Bind the variables to the placeholders (i=integer, s=string)
                $stmt->bind_param("isssis", $tailor_id, $dname, $dtype, $ddesc, $dprice, $target_file);

                // Execute the statement
                if ($stmt->execute()) {
                    // Set the descriptive success message
                    $_SESSION['upload_success'] = "Design uploaded successfully!";
                } else {
                    $_SESSION['upload_error'] = "Database insertion failed.";
                    unlink($target_file); // Delete the uploaded file if DB insert fails
                }
                $stmt->close();
            } else {
                $_SESSION['upload_error'] = "Failed to prepare the database statement.";
                unlink($target_file);
            }
        } else {
            $_SESSION['upload_error'] = "Sorry, there was an error uploading your file.";
        }
    } else {
        $_SESSION['upload_error'] = "No file was uploaded or there was an upload error.";
    }

    // Redirect back to the upload page
    header("Location: upd.php");
    exit();
}
?>