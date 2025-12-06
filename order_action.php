
<?php
session_start();
require_once 'databasecon.php';
require_once 'mailer.php'; // We use the central mailer

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    http_response_code(403);
    exit("Unauthorized access.");
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

// --- ACTION: CANCEL A PENDING ORDER ---
if ($action === 'cancel_order') {
    $oid = $_POST['oid'];
    $cname = $_POST['cname'];
    $cemail = $_POST['cemail'];
    $dname = $_POST['dname'];
    $reason = $_POST['reason'];
    $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

    // --- Security Check: Verify this order belongs to the tailor before deleting ---
    $verify_sql = "SELECT od.oid FROM orderdesign od JOIN upload u ON od.did = u.did WHERE od.oid = ? AND u.uid = ?";
    $verify_result = $db->selectData($verify_sql, "ii", $oid, $tailor_id);

    if ($verify_result && $verify_result->num_rows > 0) {
        // If verification passes, delete the order. This is a "hard cancel" since no payment was made.
        $delete_sql = "DELETE FROM orderdesign WHERE oid = ?";
        $db->executeQuery($delete_sql, "i", $oid);

        // Send a cancellation email notification
        $subject = "Your StitchVerse Order #{$oid} has been cancelled";
        $body = get_order_cancellation_email_template($cname, $oid, $dname, $reason, $custom_message);
        sendStitchverseEmail($cemail, $cname, $subject, $body);

        // MODIFIED: Set the session message using the key expected by the notification script.
        $_SESSION['action_success'] = "Order #{$oid} has been successfully cancelled and the customer notified.";
    } else {
        // If verification fails
        // MODIFIED: Set the error message using the key expected by the notification script.
        $_SESSION['action_error'] = "Could not cancel order. It may have already been processed or does not belong to you.";
    }

    header("Location: pendingorderpay.php");
    exit();
}
// --- NEW ACTION: CANCEL A PAID ORDER ---
if ($action === 'cancel_paid_order') {
    $oid = $_POST['oid'];
    $cname = $_POST['cname'];
    $cemail = $_POST['cemail'];
    $dname = $_POST['dname'];
    $reason = $_POST['reason'];
    $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

    // Security Check: Verify this order belongs to the tailor
    $verify_sql = "SELECT od.oid FROM orderdesign od JOIN upload u ON od.did = u.did WHERE od.oid = ? AND u.uid = ?";
    $verify_result = $db->selectData($verify_sql, "ii", $oid, $tailor_id);

    if ($verify_result && $verify_result->num_rows > 0) {
        // Update the order status to 'Cancelled' instead of deleting
        $update_sql = "UPDATE orderdesign SET ostatus = 'Cancelled' WHERE oid = ?";
        $db->executeQuery($update_sql, "i", $oid);
        
        // Also update the payment status
        $update_payment_sql = "UPDATE payment SET pstatus = 'Cancelled' WHERE order_id = ?";
        $db->executeQuery($update_payment_sql, "i", $oid);

        // Send a cancellation email notification
        $subject = "Your StitchVerse Order #{$oid} has been cancelled";
        $body = get_paid_order_cancellation_email_template($cname, $oid, $dname, $reason, $custom_message);
        sendStitchverseEmail($cemail, $cname, $subject, $body);

        $_SESSION['action_success'] = "Paid Order #{$oid} has been cancelled and customer notified.";
    } else {
        $_SESSION['action_error'] = "Could not cancel order #{$oid}. Verification failed.";
    }
    header("Location: paidorders.php");
    exit();
}

// --- NEW ACTION: SEND SHIPPING INFO ---
if ($action === 'send_shipping_info') {
    $oid = $_POST['oid'];
    $cname = $_POST['cname'];
    $cemail = $_POST['cemail'];
    $dname = $_POST['dname'];
    $shipping_provider = $_POST['shipping_provider'];
    $tracking_number = $_POST['tracking_number'];

    // Security Check is still important
    $verify_sql = "SELECT od.oid FROM orderdesign od JOIN upload u ON od.did = u.did WHERE od.oid = ? AND u.uid = ?";
    $verify_result = $db->selectData($verify_sql, "ii", $oid, $tailor_id);

    if ($verify_result && $verify_result->num_rows > 0) {
        // Update the order status to 'Shipped'
        $update_sql = "UPDATE orderdesign SET ostatus = 'Shipped' WHERE oid = ?";
        $db->executeQuery($update_sql, "i", $oid);

        // Send the shipping details email
        $subject = "Your StitchVerse Order #{$oid} has been shipped!";
        $body = get_shipping_details_email_template($cname, $oid, $dname, $shipping_provider, $tracking_number);
        sendStitchverseEmail($cemail, $cname, $subject, $body);

        $_SESSION['action_success'] = "Shipping details for order #{$oid} have been sent.";
    } else {
         $_SESSION['action_error'] = "Could not send details for order #{$oid}. Verification failed.";
    }
    header("Location: paidorders.php");
    exit();
}


// Redirect if the action is not recognized
header("Location: tailorhome.php");
exit();