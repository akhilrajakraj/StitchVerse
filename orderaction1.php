
<?php
session_start();
require 'databasecon.php'; 

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
    header("Location: customerhome.php");
    exit();
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$customer_id = $_SESSION['user_id'];

// --- 2. DATA COLLECTION & VALIDATION ---
$design_id = isset($_POST['did']) ? (int)$_POST['did'] : 0;

if ($customer_id <= 0 || $design_id <= 0) {
    $_SESSION['action_error'] = "There was a problem with your order details."; // MODIFIED
    header("Location: viewdesigns.php");
    exit();
}

// --- 3. DATABASE TRANSACTION ---
$conn->begin_transaction();
try {
    // Step 1: Insert the new order
    $order_status = "Payment Pending"; // A more descriptive initial status
    $order_date = date("Y-m-d");
    $sql_insert = "INSERT INTO orderdesign (uid, did, ostatus, orderdate) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert === false) { throw new Exception("Prepare failed (insert): " . $conn->error); }
    $stmt_insert->bind_param("iiss", $customer_id, $design_id, $order_status, $order_date);
    $stmt_insert->execute();
    $new_order_id = $stmt_insert->insert_id;
    $stmt_insert->close();

    if ($new_order_id <= 0) { throw new Exception("Failed to create the order."); }

    // Step 2: Fetch all data needed for the emails
    $sql_email_data = "SELECT
                            c.cname AS customer_name, c.email AS customer_email,
                            t.tname AS tailor_name, t.email AS tailor_email,
                            u.dname AS design_name, u.dimg AS design_image, u.dprice
                        FROM orderdesign o
                        JOIN creg c ON o.uid = c.cid
                        JOIN upload u ON o.did = u.did
                        JOIN treg t ON u.uid = t.tid
                        WHERE o.oid = ?";
    $stmt_email = $conn->prepare($sql_email_data);
    if ($stmt_email === false) { throw new Exception("Prepare failed (email data): " . $conn->error); }
    $stmt_email->bind_param("i", $new_order_id);
    $stmt_email->execute();
    $email_data = $stmt_email->get_result()->fetch_assoc();
    $stmt_email->close();

    if (!$email_data) { throw new Exception("Could not retrieve order details for email notification."); }
    $email_data['order_id'] = $new_order_id;

    // --- 4. SEND EMAIL NOTIFICATIONS ---
    // Email to Customer
    sendStitchverseEmail($email_data['customer_email'], $email_data['customer_name'], "Your StitchVerse Order #{$new_order_id} has been placed!", buildCustomerOrderEmail($email_data));
    // Email to Tailor
    sendStitchverseEmail($email_data['tailor_email'], $email_data['tailor_name'], "New Order! Your design '{$email_data['design_name']}' has been sold.", buildTailorOrderEmail($email_data));

    // --- 5. COMMIT & REDIRECT ---
    $conn->commit();
    // MODIFIED: Use 'action_success' for consistency
    $_SESSION['action_success'] = "Order #{$new_order_id} placed! Please complete the payment.";
    header("Location: designorders.php"); // Corrected redirect to designorder.php for tailor view
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Order placement failed: " . $e->getMessage());
    // MODIFIED: Use 'action_error' for consistency
    $_SESSION['action_error'] = "We could not place your order. Please try again.";
    header("Location: cvieworders2.php?id=" . $design_id);
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
        error_log("Mailer Error for {$to_email}: " . $mailer->ErrorInfo);
    }
}

function buildCustomerOrderEmail($data) {
    $current_year = date("Y");
    $price_formatted = '₹' . number_format($data['dprice']);
    // Note: The URLs in the email body '#' should be replaced with actual links when that page exists.
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Order Placed</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#9333ea,#ec4899);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.item{display:flex;align-items:center;gap:16px;padding:16px;border:1px solid #e5e7eb;border-radius:8px;}.button{display:inline-block;padding:12px 24px;background-color:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>Your Order is Placed!</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['customer_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">Thank you for your order! The next step is to complete the payment. Please note, you can cancel this order within 12 hours of placement if needed.</p>
<div class="item">
    <img src="{$data['design_image']}" alt="Design" style="width:80px;height:100px;object-fit:cover;border-radius:6px;">
    <div>
        <p style="font-weight:bold;color:#111827;">{$data['design_name']}</p>
        <p style="font-size:14px;color:#6b7280;">Sold by: {$data['tailor_name']}</p>
        <p style="font-size:16px;font-weight:bold;color:#111827;margin-top:8px;">{$price_formatted}</p>
    </div>
</div>
<p style="font-size:14px;color:#4b5563;margin-top:24px;">You can view the full details, complete your payment, or cancel the order from your account.</p>
<div style="text-align:center;margin-top:24px;"><a href='#' class='button'>Go to My Orders</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}

function buildTailorOrderEmail($data) {
    $current_year = date("Y");
    $price_formatted = '₹' . number_format($data['dprice']);
    // Note: The URLs in the email body '#' should be replaced with actual links when that page exists.
    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>New Order!</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#16a34a,#15803d);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.item{display:flex;align-items:center;gap:16px;padding:16px;border:1px solid #e5e7eb;border-radius:8px;}.button{display:inline-block;padding:12px 24px;background-color:#16a34a;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>You've Made a Sale!</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">Hi {$data['tailor_name']},</h2>
<p style="color:#4b5563;margin-bottom:24px;">Great news! Your design has been ordered by a customer. Please prepare the item for shipping once payment is confirmed.</p>
<div class="item">
    <img src="{$data['design_image']}" alt="Design" style="width:80px;height:100px;object-fit:cover;border-radius:6px;">
    <div>
        <p style="font-weight:bold;color:#111827;">{$data['design_name']}</p>
        <p style="font-size:14px;color:#6b7280;">Order ID: #{$data['order_id']}</p>
        <p style="font-size:16px;font-weight:bold;color:#111827;margin-top:8px;">Sale Price: {$price_formatted}</p>
    </div>
</div>
<p style="font-size:14px;color:#4b5563;margin-top:24px;">You will be notified again once the customer completes the payment. Please remember to update the shipping details on the website once the item is dispatched.</p>
<div style="text-align:center;margin-top:24px;"><a href='#' class='button'>View in Dashboard</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}
?>