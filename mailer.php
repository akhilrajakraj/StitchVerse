<?php
// mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Make sure this path is correct for your project folder structure
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

if (!function_exists('sendStitchverseEmail')) {
    function sendStitchverseEmail($recipient_email, $recipient_name, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com'; // Your SMTP username
            $mail->Password   = 'cqdaqntjinoeclpr';     // Your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Team');
            $mail->addAddress($recipient_email, $recipient_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Stitchverse Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}


if (!function_exists('get_tailor_notification_email_template')) {
    /**
     * --- THIS IS THE NEW, UPDATED FUNCTION ---
     * Creates a clean HTML table for the new stitch request email sent to the tailor.
     * It's designed to work with the new dynamic form data.
     *
     * @param string $tailor_name
     * @param string $customer_name
     * @param int $request_id
     * @param string $dress_name
     * @param array $details_array The array of dynamic measurements and designs.
     * @param string $instructions
     * @param string $request_type "Private Request" or "Public Request"
     * @return string The full HTML body of the email.
     */
    function get_tailor_notification_email_template($tailor_name, $customer_name, $request_id, $dress_name, $details_array, $instructions, $request_type) {
        // Start building the HTML table for the details
        $details_html = "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;'>";
        $details_html .= "<tr><td colspan='2' style='background-color: #f2f2f2;'><strong>Request Details for: " . htmlspecialchars($dress_name) . "</strong></td></tr>";
        
        // Loop through the associative array of details and add a row for each
        foreach ($details_array as $key => $value) {
            $details_html .= "<tr><td style='width: 30%;'><strong>" . htmlspecialchars($key) . "</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
        }

        // Add a separate row for special instructions if they exist
        if (!empty($instructions)) {
             $details_html .= "<tr><td><strong>Special Instructions</strong></td><td>" . nl2br(htmlspecialchars($instructions)) . "</td></tr>";
        }

        $details_html .= "</table>";

        // Construct the full email body with the details table
        $body = "<div style='font-family: Arial, sans-serif; line-height: 1.6;'>";
        $body .= "<h2>New Stitch Request Received!</h2>";
        $body .= "<p>Hello " . htmlspecialchars($tailor_name) . ",</p>";
        $body .= "<p>You have received a new <strong>" . htmlspecialchars($request_type) . "</strong> (ID: #{$request_id}) from customer " . htmlspecialchars($customer_name) . ".</p>";
        $body .= "<br>" . $details_html . "<br>";
        $body .= '<p style="text-align: center; margin-top: 20px;"><a href="http://localhost/stitchverse1/login.php" style="padding: 12px 25px; background-color: #8a2be2; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Go to Your Dashboard</a></p>';
        $body .= "<p>Please log in to review, accept, or decline this request.</p>";
        $body .= "<br><p>Thank you,<br>The StitchVerse Team</p>";
        $body .= "</div>";
        
        return $body;
    }
}

if (!function_exists('get_pending_email_template')) {
    /**
     * Returns the HTML template for a PENDING registration email.
     */
    function get_pending_email_template($tailor_name) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Hello " . htmlspecialchars($tailor_name) . ",</h2>
            <p>Thank you for registering as a tailor with StitchVerse! Your application has been successfully received and is now pending review from our admin team.</p>
            <p>You will receive another email from us once your account has been approved. This process usually takes 1-2 business days.</p>
            <p>We look forward to having you on our platform!</p>
            <br>
            <p>Best Regards,</p>
            <p><strong>The StitchVerse Team</strong></p>
        </div>";
    }
}

if (!function_exists('get_approval_email_template')) {
    /**
     * Returns the HTML template for an APPROVAL email.
     */
    function get_approval_email_template($tailor_name, $tailor_email) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Congratulations, " . htmlspecialchars($tailor_name) . "! Your StitchVerse account is now active.</h2>
            <p>We are thrilled to welcome you to our community of professional tailors. Your application has been reviewed and approved.</p>
            <p>You can now log in to your dashboard using your email: <strong>{$tailor_email}</strong></p>
            <p>We are excited to see your creations and help you grow your business.</p>
            <br>
            <p>Best Regards,</p>
            <p><strong>The StitchVerse Team</strong></p>
        </div>";
    }
}

if (!function_exists('get_rejection_email_template')) {
    /**
     * Returns the HTML template for a REJECTION email.
     */
    function get_rejection_email_template($tailor_name) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Hello " . htmlspecialchars($tailor_name) . ",</h2>
            <p>Thank you for your interest in joining the StitchVerse platform as a professional tailor.</p>
            <p>After careful review, we regret to inform you that we are unable to approve your registration at this time as it did not meet our verification criteria.</p>
            <p>For further clarification, please contact our support team at: <strong>contact@stitchverse.com</strong></p>
            <p>We appreciate your understanding and wish you the best in your endeavors.</p>
            <br>
            <p>Sincerely,</p>
            <p><strong>The StitchVerse Team</strong></p>
        </div>";
    }
}

if (!function_exists('get_removal_email_template')) {
    function get_removal_email_template($tailor_name) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Account Notification from StitchVerse</h2>
            <p>Hello " . htmlspecialchars($tailor_name) . ",</p>
            <p>This email is to confirm that your tailor account and all associated data have been permanently removed from the StitchVerse platform by an administrator.</p>
            <p>This action is irreversible. You will no longer be able to log in or access the tailor dashboard.</p>
            <p>If you believe this was done in error or have any questions, please contact our support team at: <strong>contact@stitchverse.com</strong></p>
            <br>
            <p>Sincerely,</p>
            <p><strong>The StitchVerse Team</strong></p>
        </div>";
    }
}

if (!function_exists('get_customer_public_email_template')) {
    function get_customer_public_email_template($customer_name, $dress_name, $request_id) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Your Stitch Request (#{$request_id}) has been submitted!</h2>
            <p>Hello " . htmlspecialchars($customer_name) . ",</p>
            <p>Your request for '<b>" . htmlspecialchars($dress_name) . "</b>' has been successfully sent to our general pool of talented tailors. It is now visible to all available artisans on our platform.</p>
            <p>You will be notified as soon as a tailor accepts your request. You can track its status in the 'My Orders' section of your account.</p>
            <p>Thank you for using StitchVerse!</p>
        </div>";
    }
}

if (!function_exists('get_customer_private_email_template')) {
    function get_customer_private_email_template($customer_name, $tailor_name, $dress_name, $request_id) {
        return "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2>Your Stitch Request (#{$request_id}) has been sent!</h2>
            <p>Hello " . htmlspecialchars($customer_name) . ",</p>
            <p>Your request for '<b>" . htmlspecialchars($dress_name) . "</b>' has been sent directly to the tailor, <b>" . htmlspecialchars($tailor_name) . "</b>.</p>
            <p>They have been notified and will review your request shortly. You will receive another email once they accept or reject it. You can track its status in the 'My Orders' section of your account.</p>
            <p>Thank you for choosing a specific tailor on StitchVerse!</p>
        </div>";
    }
}

// Add this new function to your mailer.php file

function get_order_cancellation_email_template($customer_name, $order_id, $design_name, $reason, $custom_message) {
    return "
        <html><body>
            <h2>Regarding Your StitchVerse Order #{$order_id}</h2>
            <p>Hi {$customer_name},</p>
            <p>We're writing to inform you that your recent order for the design '<strong>{$design_name}</strong>' has been cancelled by the tailor.</p>
            <p>The reason provided for the cancellation is:</p>
            <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                <strong>Reason:</strong> {$reason}<br>
                <strong>Details:</strong> {$custom_message}
            </p>
            <p>No payment was processed for this order. We apologize for any inconvenience this may cause. You are welcome to browse other designs on our platform.</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}
// Add these two new functions to your mailer.php file

function get_paid_order_cancellation_email_template($customer_name, $order_id, $design_name, $reason, $custom_message) {
    return "
        <html><body>
            <h2>Regarding Your StitchVerse Order #{$order_id}</h2>
            <p>Hi {$customer_name},</p>
            <p>We're writing to inform you that your recent paid order for the design '<strong>{$design_name}</strong>' has been cancelled by the tailor.</p>
            <p>The reason provided for the cancellation is:</p>
            <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                <strong>Reason:</strong> {$reason}<br>
                <strong>Details:</strong> {$custom_message}
            </p>
            <p>A full refund for this order will be processed and should reflect in your account within 5-7 business days. We sincerely apologize for any inconvenience this may cause.</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}

function get_shipping_details_email_template($customer_name, $order_id, $design_name, $provider, $tracking_number) {
    // A generic tracking URL that works for many providers by searching Google
    $tracking_link = 'https://www.google.com/search?q=' . urlencode($provider . ' tracking ' . $tracking_number);
    return "
        <html><body>
            <h2>Great News! Your StitchVerse Order #{$order_id} is on its way!</h2>
            <p>Hi {$customer_name},</p>
            <p>Your order for the design '<strong>{$design_name}</strong>' has been shipped by the tailor.</p>
            <p style='padding: 15px; background-color: #f3e8ff; border-left: 4px solid #9333ea;'>
                <strong>Shipping Provider:</strong> {$provider}<br>
                <strong>Tracking Number:</strong> {$tracking_number}
            </p>
            <p>You can track your package using the link below:</p>
            <p><a href='{$tracking_link}' style='padding: 10px 15px; background-color: #7c3aed; color: white; text-decoration: none; border-radius: 5px;'>Track Your Order</a></p>
            <p>Thank you for your purchase!</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}
function get_tailor_notification_email_template($tailor_name, $customer_name, $request_id, $dress_name, $details_array, $instructions, $request_type) {
    $details_html = "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: sans-serif;'>";
    $details_html .= "<thead><tr><th colspan='2' style='background-color: #f0e6ff; padding: 12px; text-align: left;'>Request Details: " . htmlspecialchars($dress_name) . "</th></tr></thead>";
    $details_html .= "<tbody>";
    
    foreach ($details_array as $key => $value) {
        $details_html .= "<tr><td style='width: 30%; background-color: #f9f9f9;'><strong>" . htmlspecialchars($key) . "</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
    }

    if (!empty($instructions)) {
         $details_html .= "<tr><td style='background-color: #f9f9f9;'><strong>Special Instructions</strong></td><td>" . nl2br(htmlspecialchars($instructions)) . "</td></tr>";
    }

    $details_html .= "</tbody></table>";

    $body = "<p>Hi " . htmlspecialchars($tailor_name) . ",</p>";
    $body .= "<p>You have received a new <strong>" . htmlspecialchars($request_type) . "</strong> (ID: #{$request_id}) from customer " . htmlspecialchars($customer_name) . ".</p>";
    $body .= $details_html;
    $body .= "<p>Please log in to your StitchVerse dashboard to review, accept, or decline this request.</p><p>Thank you!</p>";
    
    return $body;
}
// Add these two new functions to your mailer.php file

/**
 * Generates the email body for a customer when their request is accepted.
 */
function get_request_acceptance_email_for_customer($customer_name, $request_id, $tailor_name) {
    return "
        <html><body>
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Great News Regarding Your Stitching Request #{$request_id}</h2>
                <p>Hi {$customer_name},</p>
                <p>We're excited to let you know that your custom stitching request has been accepted by one of our talented tailors!</p>
                <div style='padding: 15px; background-color: #f3e8ff; border-left: 4px solid #9333ea; margin: 20px 0;'>
                    <strong>Assigned Tailor:</strong> {$tailor_name}
                </div>
                <p>The tailor will now review your request in detail and will contact you shortly if they have any questions. You can view the status of your request in your dashboard.</p>
                <p>Thank you for using StitchVerse!</p>
                <br><p>Regards,</p><p>The StitchVerse Team</p>
            </div>
        </body></html>";
}

/**
 * Generates the email body for a tailor confirming they accepted a request.
 */
function get_request_acceptance_email_for_tailor($tailor_name, $request_id, $customer_name, $request_name) {
    return "
        <html><body>
            <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <h2>Confirmation: You have accepted Request #{$request_id}</h2>
                <p>Hi {$tailor_name},</p>
                <p>This email confirms that you have successfully accepted a new custom stitching job from the general pool. The request is now assigned to you and has been moved to your 'Pending Requests' list.</p>
                <div style='padding: 15px; background-color: #f3e8ff; border-left: 4px solid #9333ea; margin: 20px 0;'>
                    <strong>Request Name:</strong> {$request_name}<br>
                    <strong>Customer:</strong> {$customer_name}
                </div>
                <p>Please review the full details in your dashboard and contact the customer if you have any questions. The customer has been notified that you are their assigned tailor.</p>
                <p>Happy stitching!</p>
                <br><p>Regards,</p><p>The StitchVerse Team</p>
            </div>
        </body></html>";
}
?>
