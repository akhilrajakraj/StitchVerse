
<?php
session_start();
require 'databasecon.php'; // Your database connection file

// PHPMailer Integration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


// --- 1. SECURITY AND SESSION CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Error: Invalid request method.");
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];

// --- 2. ROBUST SERVER-SIDE VALIDATION ---
$errors = [];
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
$card_name = trim($_POST['card_name'] ?? '');
$card_no_raw = str_replace(' ', '', $_POST['card_no'] ?? '');
$carexp_dt = trim($_POST['carexp_dt'] ?? '');
$cvv = trim($_POST['cvv'] ?? '');

if (empty($card_name)) { $errors[] = "Card holder name is required."; }
if (!ctype_digit($card_no_raw) || strlen($card_no_raw) !== 16) { $errors[] = "A valid 16-digit card number is required."; }
if (!preg_match('/^(0[1-9]|1[0-2])\s\/\s(\d{2})$/', $carexp_dt, $matches)) {
    $errors[] = "Expiry date must be in MM / YY format.";
} else {
    $exp_month = $matches[1];
    $exp_year = $matches[2];
    $expiryDate = new DateTime("20{$exp_year}-{$exp_month}-01");
    $expiryDate->modify('last day of this month');
    $currentDate = new DateTime('now');
    if ($expiryDate < $currentDate) { $errors[] = "The credit card has expired."; }
}

// --- MODIFICATION IS HERE ---
// The validation now strictly checks for exactly 3 digits for the CVV.
if (!ctype_digit($cvv) || strlen($cvv) !== 3) { 
    $errors[] = "A valid 3-digit CVV is required."; 
}

if (!empty($errors)) {
    $_SESSION['payment_error'] = implode('<br>', $errors);
    header("Location: payment1.php?order_id=" . urlencode($order_id));
    exit();
}


// --- 3. DATABASE TRANSACTION FOR DATA INTEGRITY ---
$connection = $db->getConnection();
$connection->begin_transaction();

try {
    // Step 1: Insert the payment record with ENHANCED SECURITY
    $card_no_masked = '**** **** **** ' . substr($card_no_raw, -4);
    
    $sql_payment = "INSERT INTO payment (uid, order_id, card_name, card_no, carexp_dt, pmode, pdate, pstatus) 
                    VALUES (?, ?, ?, ?, ?, 'Card', NOW(), 'Paid')";
    $stmt_payment = $connection->prepare($sql_payment);
    if ($stmt_payment === false) { throw new Exception("Prepare failed (payment): " . $connection->error); }
    $stmt_payment->bind_param("issss", $customer_id, $order_id, $card_name, $card_no_masked, $carexp_dt);
    $stmt_payment->execute();
    $payment_id = $stmt_payment->insert_id;
    $stmt_payment->close();

    // Step 2: Update the stitch request status to 'Paid'
    $sql_order_update = "UPDATE stitchreq SET sstatus = 'Paid' WHERE sdid = ? AND uid = ?";
    $stmt_order = $connection->prepare($sql_order_update);
    if ($stmt_order === false) { throw new Exception("Prepare failed (stitchreq update): " . $connection->error); }
    $stmt_order->bind_param("ii", $order_id, $customer_id);
    $stmt_order->execute();
    $stmt_order->close();
    
    // Step 3: Fetch all data needed for the emails
    $sql_email_data = "SELECT
                            c.cname AS customer_name, c.email AS customer_email, c.address AS customer_address,
                            t.tname AS tailor_name, t.email AS tailor_email,
                            sr.sdname AS request_name, sr.simg AS request_image, sr.sprice AS request_price
                        FROM stitchreq sr
                        JOIN creg c ON sr.uid = c.cid
                        JOIN treg t ON sr.tid = t.tid
                        WHERE sr.sdid = ?";
    
    $stmt_email = $connection->prepare($sql_email_data);
    if ($stmt_email === false) { throw new Exception("Prepare failed (email data): " . $connection->error); }
    $stmt_email->bind_param("i", $order_id);
    $stmt_email->execute();
    $email_data = $stmt_email->get_result()->fetch_assoc();
    $stmt_email->close();
    
    if (!$email_data) { throw new Exception("Could not retrieve order details for email notification."); }
    
    // Step 4: Send Emails
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host       = 'smtp.gmail.com';
    $mailer->SMTPAuth   = true;
    $mailer->Username   = 'stitchverrse@gmail.com';
    $mailer->Password   = 'cqdaqntjinoeclpr';
    $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port       = 587;
    $mailer->setFrom('no-reply@stitchverse.com', 'StitchVerse Orders');

    // Email to Customer
    $mailer->addAddress($email_data['customer_email'], $email_data['customer_name']);
    $mailer->isHTML(true);
    $mailer->Subject = "Payment Confirmed for Stitch Request (#{$order_id})";
    $mailer->Body    = buildCustomerEmailForStitchRequest($order_id, $payment_id, $email_data, $card_no_masked);
    $mailer->send();
    
    // Email to Tailor
    $mailer->clearAddresses();
    $mailer->addAddress($email_data['tailor_email'], $email_data['tailor_name']);
    $mailer->Subject = "Payment Received for Custom Request (#{$order_id})";
    $mailer->Body    = buildTailorEmailForStitchRequest($order_id, $email_data);
    $mailer->send();

    // --- 5. COMMIT TRANSACTION & REDIRECT ---
    $connection->commit();
    $_SESSION['stitch_request_success'] = "Payment successful for Stitch Request #{$order_id}! A confirmation has been sent to your email.";
    header("Location: stitchingorders.php");
    exit();

} catch (Exception $e) {
    // --- 6. ROLLBACK TRANSACTION ON ERROR ---
    $connection->rollback();
    error_log("Payment failed for stitch request #{$order_id}: " . $e->getMessage());
    $_SESSION['payment_error'] = "Your payment could not be processed due to a technical issue. Please try again.";
    header("Location: payment1.php?order_id=" . $order_id);
    exit();
}

// --- EMAIL FUNCTIONS (Unchanged) ---
function buildCustomerEmailForStitchRequest($order_id, $payment_id, $data, $card_no_masked) {
    $total_amount = $data['request_price'] + 40;
    $price_formatted = '₹' . number_format($data['request_price']);
    $total_amount_formatted = '₹' . number_format($total_amount);
    $date = date("F j, Y");
    $current_year = date("Y");

    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Payment Confirmation</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#9333ea,#ec4899);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.item{display:flex;align-items:center;gap:16px;padding:16px 0;border-bottom:1px solid #e5e7eb;}.summary-table td{padding:4px 0;}.button{display:inline-block;padding:12px 24px;background-color:#9333ea;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>Payment Successful!</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['customer_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">Your payment for a custom stitch request has been processed. The tailor will now begin working on your order.</p>
<h3 style="font-size:18px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:8px;margin-bottom:16px;">Request #{$order_id}</h3>
<div class="item">
    <img src="{$data['request_image']}" alt="Request Image" style="width:80px;height:100px;object-fit:cover;border-radius:6px;">
    <div>
        <p style="font-weight:bold;color:#111827;">{$data['request_name']}</p>
        <p style="font-size:14px;color:#6b7280;">Handled by: {$data['tailor_name']}</p>
        <p style="font-size:16px;font-weight:bold;color:#111827;margin-top:8px;">{$price_formatted}</p>
    </div>
</div>
<h3 style="font-size:18px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:8px;margin-bottom:16px;margin-top:24px;">Payment Receipt</h3>
<table class="summary-table" style="width:100%;font-size:14px;">
<tr><td>Stitching Price:</td><td style="text-align:right;">{$price_formatted}</td></tr>
<tr><td>Delivery Fee:</td><td style="text-align:right;">₹40.00</td></tr>
<tr style="font-weight:bold;border-top:1px solid #e5e7eb;font-size:16px;"><td style="padding-top:12px;">Total Paid:</td><td style="text-align:right;padding-top:12px;color:#9333ea;">{$total_amount_formatted}</td></tr>
</table>
<table class="summary-table" style="width:100%;font-size:14px;margin-top:16px;">
<tr><td>Payment ID:</td><td style="text-align:right;">#{$payment_id}</td></tr>
<tr><td>Payment Date:</td><td style="text-align:right;">{$date}</td></tr>
<tr><td>Paid With:</td><td style="text-align:right;">{$card_no_masked}</td></tr>
<tr><td>Shipping To:</td><td style="text-align:right;">{$data['customer_address']}</td></tr>
</table>
<div style="text-align:center;margin-top:32px;"><a href='#' class='button'>View My Stitching Orders</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}

function buildTailorEmailForStitchRequest($order_id, $data) {
    $price_formatted = '₹' . number_format($data['request_price']);
    $current_year = date("Y");
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Payment Received</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#16a34a,#15803d);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.item{display:flex;align-items:center;gap:16px;padding:16px;border:1px solid #e5e7eb;border-radius:8px;}.button{display:inline-block;padding:12px 24px;background-color:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>Payment Received!</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['tailor_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">The customer has completed the payment for the custom stitch request assigned to you. Please begin the work.</p>
<div class="item">
    <img src="{$data['request_image']}" alt="Request Image" style="width:80px;height:100px;object-fit:cover;border-radius:6px;">
    <div>
        <p style="font-weight:bold;color:#111827;">{$data['request_name']}</p>
        <p style="font-size:14px;color:#6b7280;">Request ID: #{$order_id}</p>
        <p style="font-size:16px;font-weight:bold;color:#111827;margin-top:8px;">Agreed Price: {$price_formatted}</p>
    </div>
</div>
<h3 style="font-size:18px;color:#111827;border-bottom:1px solid #e5e7eb;padding-bottom:8px;margin-bottom:16px;margin-top:24px;">Shipping Details</h3>
<p style="font-size:14px;color:#4b5563;line-height:1.6;">
<strong>Customer Name:</strong> {$data['customer_name']}<br>
<strong>Shipping Address:</strong> {$data['customer_address']}
</p>
<div style="text-align:center;margin-top:32px;"><a href='#' class='button'>View in Dashboard</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}
?>