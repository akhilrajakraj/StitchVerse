<?php
session_start();
require 'databasecon.php'; 

// PHPMailer Integration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// --- 1. SECURITY & PRE-FLIGHT CHECKS ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: customerhome.php");
    exit();
}


// --- 2. DATA COLLECTION & PROCESSING ---
$db = new DatabaseCon();
$conn = $db->getConnection();
$customer_id = $_SESSION['user_id'];

// Collect static form fields
$sdname = trim($_POST['sdname'] ?? 'Untitled Request');
$sdtype = trim($_POST['sdtype'] ?? 'Unknown');
$sfabric = trim($_POST['sfabric'] ?? 'Not Specified');
$scolor = trim($_POST['scolor'] ?? 'Not Specified');
$spattern = trim($_POST['spattern'] ?? 'Not Specified');
$sinstructions = trim($_POST['sinstructions'] ?? '');
$sddate = $_POST['sddate'] ?? date('Y-m-d');
$tid = !empty($_POST['tid']) ? (int)$_POST['tid'] : 0; // Use 0 for general pool

// ** NEW DATA HANDLING **
// Collect the pre-formatted string of design and measurement details from the hidden input.
$sdesign_details = trim($_POST['sdesign_details'] ?? 'No specific design details provided.');

// Handle file upload
$simg_path = '';
if (isset($_FILES['simg']) && $_FILES['simg']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    $unique_filename = time() . '_' . uniqid() . '_' . basename($_FILES['simg']['name']);
    $target_file = $target_dir . $unique_filename;
    if (move_uploaded_file($_FILES['simg']['tmp_name'], $target_file)) {
        $simg_path = $target_file;
    }
}

// --- 3. DATABASE INSERTION WITH TRANSACTION ---
$conn->begin_transaction();
try {
    $sql_insert = "INSERT INTO stitchreq (uid, tid, sdname, sdtype, sfabric, scolor, spattern, sdesign_details, sinstructions, sddate, simg, sstatus) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) { throw new Exception("SQL Prepare Error: " . $conn->error); }
    $stmt->bind_param("iisssssssss", $customer_id, $tid, $sdname, $sdtype, $sfabric, $scolor, $spattern, $sdesign_details, $sinstructions, $sddate, $simg_path);
    $stmt->execute();
    $new_request_id = $stmt->insert_id;
    $stmt->close();

    if ($new_request_id <= 0) { throw new Exception("Failed to insert the stitch request."); }

    // --- 4. EMAIL NOTIFICATIONS ---
    // Fetch customer's details for the email
    $customer_info_stmt = $conn->prepare("SELECT cname, email FROM creg WHERE cid = ?");
    $customer_info_stmt->bind_param("i", $customer_id);
    $customer_info_stmt->execute();
    $customer_info = $customer_info_stmt->get_result()->fetch_assoc();
    $customer_info_stmt->close();

    $email_data = [
        'request_id' => $new_request_id,
        'customer_name' => $customer_info['cname'],
        'request_name' => $sdname,
        'details' => $sdesign_details,
        'instructions' => $sinstructions,
        'delivery_date' => $sddate
    ];

    if ($tid !== 0) {
        // --- PRIVATE REQUEST: A specific tailor was assigned ---
        $tailor_info_stmt = $conn->prepare("SELECT tname, email FROM treg WHERE tid = ?");
        $tailor_info_stmt->bind_param("i", $tid);
        $tailor_info_stmt->execute();
        $tailor_info = $tailor_info_stmt->get_result()->fetch_assoc();
        $tailor_info_stmt->close();
        
        $email_data['tailor_name'] = $tailor_info['tname'];

        // Email to Customer
        sendStitchverseEmail($customer_info['email'], $customer_info['cname'], "Your Stitch Request (#{$new_request_id}) has been sent!", buildCustomerStitchRequestEmail($email_data, true));
        // Email to Tailor
        sendStitchverseEmail($tailor_info['email'], $tailor_info['tname'], "New Private Stitch Request (#{$new_request_id})", buildTailorStitchRequestEmail($email_data, true));

    } else {
        // --- PUBLIC REQUEST: Goes to the general pool ---
        // Email to Customer
        sendStitchverseEmail($customer_info['email'], $customer_info['cname'], "Your Stitch Request (#{$new_request_id}) is in the General Pool", buildCustomerStitchRequestEmail($email_data, false));
        
        // Email to ALL Approved Tailors
        $all_tailors_stmt = $conn->prepare("SELECT tname, email FROM treg WHERE status = 'Approved'");
        $all_tailors_stmt->execute();
        $all_tailors_result = $all_tailors_stmt->get_result();
        while ($tailor = $all_tailors_result->fetch_assoc()) {
            $email_data['tailor_name'] = $tailor['tname'];
            sendStitchverseEmail($tailor['email'], $tailor['tname'], "New Stitch Request Available in the General Pool", buildTailorStitchRequestEmail($email_data, false));
        }
        $all_tailors_stmt->close();
    }
    
    // --- 5. COMMIT & REDIRECT ---
    $conn->commit();
    $_SESSION['stitch_request_success'] = "Your stitch request (#{$new_request_id}) has been submitted successfully!";
    header("Location: stitchingorders.php"); 
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Stitch Request Failed: " . $e->getMessage());
    $_SESSION['stitch_request_error'] = "We could not submit your request due to a technical issue. Please try again.";
    header("Location: customreq1.php");
    exit();
}


// --- EMAIL FUNCTIONS ---

function sendStitchverseEmail($to_email, $to_name, $subject, $body) {
    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host       = 'smtp.gmail.com';
        $mailer->SMTPAuth   = true;
        $mailer->Username   = 'stitchverrse@gmail.com';
        $mailer->Password   = 'cqdaqntjinoeclpr';
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port       = 587;
        $mailer->setFrom('no-reply@stitchverse.com', 'StitchVerse');
        
        $mailer->addAddress($to_email, $to_name);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body    = $body;
        
        $mailer->send();
    } catch (Exception $e) {
        // Log the error, but don't stop the script. The main process was successful.
        error_log("Mailer Error for {$to_email}: " . $mailer->ErrorInfo);
    }
}

function buildCustomerStitchRequestEmail($data, $is_private) {
    $current_year = date("Y");
    $greeting = $is_private ? 
        "Your request has been successfully sent to <strong>{$data['tailor_name']}</strong>. They will review it and get back to you shortly." : 
        "Your request has been successfully submitted to our general pool. One of our talented tailors will claim it and begin working soon.";

    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Stitch Request Confirmation</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#9333ea,#ec4899);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.details-box{padding:16px;background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;white-space:pre-wrap;font-family:monospace;}.button{display:inline-block;padding:12px 24px;background-color:#9333ea;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>Request Submitted!</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['customer_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">{$greeting}</p>
<h3 style="font-size:16px;color:#111827;margin-bottom:8px;">Request Summary (ID: #{$data['request_id']})</h3>
<div class="details-box"><strong>Request Name:</strong> {$data['request_name']}
<strong>Delivery Date:</strong> {$data['delivery_date']}

<strong>Your Specifications:</strong>
{$data['details']}

<strong>Special Instructions:</strong>
{$data['instructions']}</div>
<div style="text-align:center;margin-top:32px;"><a href='#' class='button'>View My Stitching Orders</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}

function buildTailorStitchRequestEmail($data, $is_private) {
    $current_year = date("Y");
    $subject_line = $is_private ? "You have a new private stitch request!" : "A new stitch request is available in the general pool!";
    $greeting = "A new request has been submitted. Please review the details below.";

    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>New Stitch Request</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#16a34a,#15803d);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.details-box{padding:16px;background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;white-space:pre-wrap;font-family:monospace;}.button{display:inline-block;padding:12px 24px;background-color:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>{$subject_line}</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['tailor_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">{$greeting}</p>
<h3 style="font-size:16px;color:#111827;margin-bottom:8px;">Request Summary (ID: #{$data['request_id']})</h3>
<div class="details-box"><strong>From Customer:</strong> {$data['customer_name']}
<strong>Request Name:</strong> {$data['request_name']}
<strong>Desired Delivery:</strong> {$data['delivery_date']}

<strong>Specifications & Measurements:</strong>
{$data['details']}

<strong>Special Instructions from Customer:</strong>
{$data['instructions']}</div>
<div style="text-align:center;margin-top:32px;"><a href='#' class='button'>View in Dashboard</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}
?>