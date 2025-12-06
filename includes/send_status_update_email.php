<?php
/**
 * Email Sending Function for Order/Request Status Updates using PHPMailer
 *
 * Include this file in admin scripts where status updates need email notifications.
 * Assumes PHPMailer library is available in a 'PHPMailer' directory within the same 'includes' folder.
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if needed
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// --- PHPMailer Configuration (Use consistent settings) ---
// IMPORTANT: Ensure these are correctly configured for your environment.
// You might want to create a central config file for these in the future.
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'threadhub104@gmail.com'); // CHANGE THIS
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'ecplcvvdopquribv');       // CHANGE THIS (App Password recommended)
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE')) define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);
if (!defined('EMAIL_FROM')) define('EMAIL_FROM', 'threadhub104@gmail.com');       // CHANGE THIS
if (!defined('EMAIL_FROM_NAME')) define('EMAIL_FROM_NAME', 'ThreadHub Support');   // CHANGE THIS

/**
 * Sends an order/request status update email to the customer.
 *
 * @param string $toEmail The customer's email address.
 * @param string $customerName The customer's name.
 * @param string $orderType "Order" or "Request".
 * @param int    $orderId The ID of the order or request.
 * @param string $newStatus The new status being set.
 * @param string $trackingInfo Optional tracking number or details.
 * @param string $adminNotes Optional notes from the admin.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendStatusUpdateEmail($toEmail, $customerName, $orderType, $orderId, $newStatus, $trackingInfo = null, $adminNotes = null) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for debugging
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($toEmail, $customerName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Update on your ThreadHub {$orderType} #{$orderId}";

        // Construct HTML email body
        $message_body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>ThreadHub {$orderType} Update</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { padding: 20px; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 5px; }
                p { margin-bottom: 15px; }
                .status { font-weight: bold; padding: 3px 8px; border-radius: 4px; display: inline-block; }
                .status-shipped { background-color: #dbeafe; color: #1e40af; }
                .status-delivered { background-color: #dcfce7; color: #166534; }
                .status-processing { background-color: #fef9c3; color: #854d0e; }
                .status-completed { background-color: #dcfce7; color: #166534; }
                .status-cancelled { background-color: #fee2e2; color: #991b1b; }
                .status-inprogress { background-color: #e0f2fe; color: #0369a1; }
                .notes-box { background-color: #f8f8f8; padding: 15px; border-left: 4px solid #ddd; margin-top: 15px; font-size: 0.9em; }
                 /* Basic dark mode styles */
                 @media (prefers-color-scheme: dark) {
                     body { background-color: #1f2937; color: #f3f4f6; }
                     .container { border-color: #4b5563; background-color: #374151; }
                     .notes-box { background-color: #4b5563; border-left-color: #6b7280; color: #d1d5db;}
                     /* Adjust status colors for dark mode */
                     .status-shipped { background-color: #1e3a8a; color: #bfdbfe; }
                     .status-delivered, .status-completed { background-color: #14532d; color: #bbf7d0; }
                     .status-processing { background-color: #713f12; color: #fef08a; }
                     .status-cancelled { background-color: #7f1d1d; color: #fecaca; }
                     .status-inprogress { background-color: #0c4a6e; color: #bae6fd; }
                 }
            </style>
        </head>
        <body>
            <div class='container'>
                <h3>Hello " . htmlspecialchars($customerName) . ",</h3>
                <p>We have an update regarding your ThreadHub {$orderType} #{$orderId}.</p>
                <p>The status has been updated to:
                   <span class='status status-" . strtolower(str_replace(' ', '', $newStatus)) . "'>" . htmlspecialchars(ucfirst($newStatus)) . "</span>
                </p>";

        // Add tracking info if provided and status is relevant (e.g., Shipped)
        if (!empty($trackingInfo) && strtolower($newStatus) === 'shipped') {
            $message_body .= "<p><strong>Tracking Information:</strong> " . htmlspecialchars($trackingInfo) . "</p>";
        }

        // Add admin notes if provided
        if (!empty($adminNotes)) {
            $message_body .= "<div class='notes-box'><p><strong>Admin Notes:</strong></p><p>" . nl2br(htmlspecialchars($adminNotes)) . "</p></div>";
        }

        $message_body .= "<p>You can view your {$orderType} details here:</p>";
        // Generate appropriate link based on type
        $detailsLink = (strtolower($orderType) === 'order')
            ? "http://{$_SERVER['HTTP_HOST']}/threadhub1/customers/order_details.php?order_id={$orderId}" // Adjust domain/path if needed
            : "http://{$_SERVER['HTTP_HOST']}/threadhub1/customers/custom_req_dtls.php?request_id={$orderId}"; // Adjust domain/path if needed
        $message_body .= "<p><a href='" . htmlspecialchars($detailsLink) . "'>View {$orderType} Details</a></p>";


        $message_body .= "<hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>Thank you for choosing ThreadHub!<br>The ThreadHub Team</p>
            </div>
        </body>
        </html>";

        $mail->Body = $message_body;

        // Plain text alternative
        $alt_message_body = "Hello " . $customerName . ",\n\n";
        $alt_message_body .= "Update on your ThreadHub {$orderType} #{$orderId}.\n";
        $alt_message_body .= "New Status: " . ucfirst($newStatus) . "\n\n";
        if (!empty($trackingInfo) && strtolower($newStatus) === 'shipped') {
             $alt_message_body .= "Tracking Information: " . $trackingInfo . "\n\n";
        }
         if (!empty($adminNotes)) {
             $alt_message_body .= "Admin Notes:\n" . $adminNotes . "\n\n";
         }
        $alt_message_body .= "View Details: " . $detailsLink . "\n\n";
        $alt_message_body .= "Thanks,\nThe ThreadHub Team";
        $mail->AltBody = $alt_message_body;


        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the detailed error
        error_log("Status update email could not be sent to " . $toEmail . " for {$orderType} #{$orderId}. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>