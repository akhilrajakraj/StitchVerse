<?php
session_start();
require_once 'databasecon.php';
// --- FIX: Changed to require_once ---
require_once 'mailer.php'; 

// Security: Ensure an admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

if (!isset($_GET['tid']) || !is_numeric($_GET['tid'])) {
    header("Location: viewt.php?error=invalid_id");
    exit();
}

$tailor_id_to_approve = $_GET['tid'];
$db = new DatabaseCon();
$conn = $db->getConnection();

// --- Main Approval Logic ---
try {
    // 1. Fetch tailor details from treg table
    $query_fetch = "SELECT tname, email, password FROM treg WHERE tid = ?";
    $stmt_fetch = $conn->prepare($query_fetch);
    $stmt_fetch->bind_param("i", $tailor_id_to_approve);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows === 1) {
        $tailor = $result->fetch_assoc();
        $tailor_email = $tailor['email'];
        $tailor_name = $tailor['tname'];
        $tailor_password = $tailor['password']; 

        // 2. Check if tailor is already in login table
        $query_check = "SELECT uid FROM login WHERE uid = ? AND utype = 'tailor'";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("i", $tailor_id_to_approve);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $_SESSION['action_error'] = "This tailor has already been approved.";
            header("Location: viewt.php");
            exit();
        }

        // 3. Insert the tailor into the login table with their chosen password
        $query_insert = "INSERT INTO login (uid, uname, upass, utype) VALUES (?, ?, ?, 'tailor')";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iss", $tailor_id_to_approve, $tailor_email, $tailor_password);
        if (!$stmt_insert->execute()) {
            throw new Exception("Failed to create login entry for the tailor.");
        }

        // 4. Update the status in the treg table to 'Approved'
        $query_update = "UPDATE treg SET status = 'Approved' WHERE tid = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("i", $tailor_id_to_approve);
        $stmt_update->execute();

        // 5. Send the "Approval Granted" email
        $subject = "Welcome to StitchVerse - Your Account is Approved!";
        $body = get_approval_email_template($tailor_name, $tailor_email);
        send_email_with_phpmailer($tailor_email, $tailor_name, $subject, $body);
        
        $_SESSION['action_success'] = "Tailor '" . htmlspecialchars($tailor_name) . "' has been successfully approved and notified.";
        header("Location: viewt.php");
        exit();

    } else {
        throw new Exception("Tailor with the specified ID was not found.");
    }
} catch (Exception $e) {
    error_log("Tailor Approval Error: " . $e->getMessage());
    $_SESSION['action_error'] = "An error occurred during the approval process. Please try again.";
    header("Location: viewt.php");
    exit();
}
?>