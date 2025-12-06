<?php
session_start();
require_once 'databasecon.php';
require_once 'mailer2.php'; // We now use the central mailer

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

// Determine action from GET or POST for flexibility
$action = $_REQUEST['action'] ?? null; 
$db = new DatabaseCon();

try {
    switch ($action) {
        case 'approve':
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                header("Location: pendingtailors.php?error=invalid_request");
                exit();
            }
            $tailor_id = (int)$_GET['id'];
            
            // Your logic for fetching details and creating a login
            $sql_fetch = "SELECT tname, email, password FROM treg WHERE tid = ? AND status = 'pending'";
            $result = $db->selectData($sql_fetch, "i", $tailor_id);

            if ($result && $result->num_rows === 1) {
                $tailor = $result->fetch_assoc();
                
                // IMPORTANT: Assuming the password in `treg` is already securely hashed.
                $sql_insert_login = "INSERT INTO login (uid, uname, upass, utype) VALUES (?, ?, ?, 'tailor')";
                $db->executeQuery($sql_insert_login, "iss", $tailor_id, $tailor['email'], $tailor['password']);

                $db->executeQuery("UPDATE treg SET status = 'Approved' WHERE tid = ?", "i", $tailor_id);

                $subject = "Welcome to StitchVerse - Your Account is Approved!";
                $body = get_approval_email_template($tailor['tname']);
                sendStitchverseEmail($tailor['email'], $tailor['tname'], $subject, $body);
                
                $_SESSION['action_success'] = "Tailor '" . htmlspecialchars($tailor['tname']) . "' has been approved and notified.";
            } else {
                $_SESSION['action_error'] = "Tailor not found or already processed.";
            }
            header("Location: approvedtailors.php");
            exit();

        case 'reject':
            // This case now handles the form submission from the rejection modal
            if (!isset($_POST['tid']) || !is_numeric($_POST['tid'])) {
                 header("Location: pendingtailors.php?error=invalid_request");
                 exit();
            }
            $tailor_id = (int)$_POST['tid'];
            $reason = $_POST['reason'];
            $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

            $sql_fetch = "SELECT tname, email FROM treg WHERE tid = ? AND status = 'pending'";
            $result = $db->selectData($sql_fetch, "i", $tailor_id);

            if ($result && $result->num_rows === 1) {
                $tailor = $result->fetch_assoc();
                $db->executeQuery("UPDATE treg SET status = 'Rejected' WHERE tid = ?", "i", $tailor_id);
                $db->executeQuery("UPDATE login SET utype = 'rejected_tailor' WHERE uid = ? AND utype = 'tailor'", "i", $tailor_id);
                
                $subject = "Update on Your StitchVerse Tailor Application";
                $body = get_rejection_email_template($tailor['tname'], $reason, $custom_message);
                sendStitchverseEmail($tailor['email'], $tailor['tname'], $subject, $body);

                $_SESSION['action_success'] = "Tailor '" . htmlspecialchars($tailor['tname']) . "' has been rejected and notified.";
            } else {
                $_SESSION['action_error'] = "Tailor not found or already processed.";
            }
            header("Location: rejectedtailors.php");
            exit();

        case 'remove_with_reason':
            // This is our new, non-destructive removal logic from the modal
            if (!isset($_POST['tid']) || !is_numeric($_POST['tid'])) {
                 header("Location: approvedtailors.php?error=invalid_request");
                 exit();
            }
            $tailor_id = (int)$_POST['tid'];
            $reason = $_POST['reason'];
            $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

            $sql_fetch = "SELECT tname, email FROM treg WHERE tid = ?";
            $result = $db->selectData($sql_fetch, "i", $tailor_id);

            if ($result && $result->num_rows === 1) {
                $tailor = $result->fetch_assoc();
                $tailor_name = htmlspecialchars($tailor['tname']);

                // Update status to 'Removed' (non-destructive)
                $db->executeQuery("UPDATE treg SET status = 'Removed' WHERE tid = ?", "i", $tailor_id);
                $db->executeQuery("UPDATE login SET utype = 'removed_tailor' WHERE uid = ? AND utype = 'tailor'", "i", $tailor_id);

                $subject = "Notification: Your StitchVerse Account Has Been Removed";
                $body = get_removal_email_template($tailor_name, $reason, $custom_message);
                sendStitchverseEmail($tailor['email'], $tailor_name, $subject, $body);
                
                $_SESSION['action_success'] = "Tailor '{$tailor_name}' has been removed and notified.";
            } else {
                $_SESSION['action_error'] = "Could not find the tailor to remove.";
            }
            header("Location: approvedtailors.php");
            exit();

        default:
            // If no valid action is provided, redirect away.
            header("Location: adminhomepage.php");
            exit();
    }
} catch (Exception $e) {
    error_log("Admin Tailor Action Error: " . $e->getMessage());
    $_SESSION['action_error'] = "A server error occurred. Please try again.";
    // Redirect to a safe page
    header("Location: adminhomepage.php");
    exit();
}