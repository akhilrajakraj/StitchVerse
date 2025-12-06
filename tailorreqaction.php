<?php
session_start();
require 'databasecon.php';

// --- PHPMailer Integration ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sdid'], $_POST['action'])) {
    $db = new DatabaseCon();
    $conn = $db->getConnection();
    
    $tailor_id = $_SESSION['user_id'];
    $sdid = (int)$_POST['sdid'];
    $action = $_POST['action'];
    $status = ($action == 'Accept') ? 'Accepted' : 'Rejected';

    // --- MODIFIED QUERY: Fetches extra details for the tailor's email ---
    // Now gets customer email, tailor email, original request tailor ID, and priority.
    $info_sql = "SELECT 
                    c.cname, c.email AS customer_email, 
                    sr.sdname, sr.spriority, sr.tid AS original_tid,
                    t.tname, t.email AS tailor_email
                 FROM stitchreq sr 
                 JOIN creg c ON sr.uid = c.cid
                 JOIN treg t ON t.tid = ?
                 WHERE sr.sdid = ?";
    $info_result = $db->selectData($info_sql, "ii", $tailor_id, $sdid);
    $details = $info_result->fetch_assoc();

    if ($details) {
        // Instantiate PHPMailer - ready for either action
        $mail = new PHPMailer(true);
        try {
            // Server settings (this part is unchanged)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com'; 
            $mail->Password   = 'cqdaqntjinoeclpr';      
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse');
            
            // --- Set initial recipient to the CUSTOMER ---
            $mail->addAddress($details['customer_email'], $details['cname']);
            $mail->isHTML(true);

            if ($status == 'Accepted') {
                // --- ACCEPT LOGIC (UNCHANGED) ---
                $sql = "UPDATE stitchreq SET sstatus = 'Accepted', tid = ? WHERE sdid = ?";
                $db->executeQuery($sql, "ii", $tailor_id, $sdid);

                $mail->Subject = "Your Stitch Request has been Accepted!";
                $mail->Body    = "
                    <html><body>
                        <h2>Great News, " . htmlspecialchars($details['cname']) . "!</h2>
                        <p>Your stitch request for the item '<b>" . htmlspecialchars($details['sdname']) . "</b>' (Request ID: #$sdid) has been accepted by our tailor, <b>" . htmlspecialchars($details['tname']) . "</b>.</p>
                        <p>The tailor will now review the details and provide you with a final price shortly. You will be able to make the payment from the 'My Orders' section of your account once the price is updated.</p>
                        <p>Thank you for using StitchVerse!</p>
                    </body></html>";
                
                $mail->send();
                $_SESSION['action_success'] = "Request accepted! A confirmation email has been sent to the customer.";
                
                // --- === NEW FEATURE: SEND NOTIFICATION EMAIL TO THE TAILOR === ---
                try {
                    // 1. Determine Request Type (Public or Private)
                    $request_type = ($details['original_tid'] == 0) ? 'Public Pool' : 'Private Request';

                    // 2. Set priority instructions
                    $priority_instructions = '';
                    switch ($details['spriority']) {
                        case 'Urgent':
                            $priority_instructions = '<p style="color: #D32F2F; font-weight: bold;">This is an URGENT request. Please prioritize processing and update the final price for the customer within 24 hours.</p>';
                            break;
                        case 'High':
                            $priority_instructions = '<p style="color: #F57C00; font-weight: bold;">This is a HIGH priority request. Please update the price for the customer within 48 hours.</p>';
                            break;
                        default:
                            $priority_instructions = '<p>Please review the details and update the final price at your earliest convenience.</p>';
                    }

                    // 3. Build the attractive HTML email for the tailor
                    $tailor_email_body = '
                        <html lang="en">
                        <body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr><td align="center">
                                    <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; padding: 40px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                        <tr><td align="center" style="padding-bottom: 20px;">
                                            <h1 style="color: #8a2be2; font-size: 24px;">Action Required: Request Accepted</h1>
                                        </td></tr>
                                        <tr><td style="color: #333333; font-size: 16px; line-height: 1.6;">
                                            <p>Hello ' . htmlspecialchars($details['tname']) . ',</p>
                                            <p>This is a confirmation that you have successfully accepted the following stitch request:</p>
                                            <div style="background-color: #f9f9f9; border-left: 4px solid #8a2be2; padding: 15px; margin: 20px 0;">
                                                <p><strong>Request ID:</strong> #' . $sdid . '</p>
                                                <p><strong>Item Name:</strong> ' . htmlspecialchars($details['sdname']) . '</p>
                                                <p><strong>Customer:</strong> ' . htmlspecialchars($details['cname']) . '</p>
                                                <p><strong>Source:</strong> ' . $request_type . '</p>
                                            </div>
                                            ' . $priority_instructions . '
                                            <p>You may now proceed to the "Accepted Requests" section of your dashboard to view full details and set the final price for the customer.</p>
                                            <p>Thank you for your prompt attention.</p>
                                        </td></tr>
                                        <tr><td align="center" style="padding-top: 30px;">
                                            <p style="color: #888888; font-size: 12px;">StitchVerse Platform</p>
                                        </td></tr>
                                    </table>
                                </td></tr>
                            </table>
                        </body></html>';
                    
                    // 4. Reconfigure mailer for the tailor and send
                    $mail->clearAddresses(); // IMPORTANT: Clear previous recipient
                    $mail->addAddress($details['tailor_email'], $details['tname']);
                    $mail->Subject = "Action Required: You have accepted Stitch Request #{$sdid}";
                    $mail->Body    = $tailor_email_body;
                    $mail->send();

                } catch (Exception $e) {
                    // Log if the second email fails, but don't interrupt the user
                    error_log("Failed to send acceptance notification to tailor {$tailor_id} for request {$sdid}: {$mail->ErrorInfo}");
                }
                // --- === END OF NEW FEATURE === ---


            } else {
                // --- REJECT LOGIC (UNCHANGED) ---
                $sql = "UPDATE stitchreq SET sstatus = 'Rejected' WHERE sdid = ?";
                $db->executeQuery($sql, "i", $sdid);

                $mail->Subject = "An Update on Your StitchVerse Request";
                $mail->Body    = "
                    <html><body>
                        <h2>Update on Your Request, " . htmlspecialchars($details['cname']) . "</h2>
                        <p>We're writing to inform you that your stitch request for the item '<b>" . htmlspecialchars($details['sdname']) . "</b>' (Request ID: #$sdid) could not be accepted by the tailor, <b>" . htmlspecialchars($details['tname']) . "</b>, at this time.</p>
                        <p>This can happen for various reasons, including current workload or specialty. We encourage you to submit your request again to the 'General Pool' for other talented tailors to view.</p>
                        <p>We apologize for any inconvenience.</p>
                        <p>Thank you for using StitchVerse!</p>
                    </body></html>";

                $mail->send();
                $_SESSION['action_success'] = "Request rejected. The customer has been notified.";
            }

        } catch (Exception $e) {
            error_log("Mailer Error on request action: {$mail->ErrorInfo}");
            // Set a generic error for the user if the primary email fails
            $_SESSION['action_error'] = "The action was completed, but the notification email could not be sent.";
        }
    }

    header("Location: tailorreq.php");
    exit();
}
?>