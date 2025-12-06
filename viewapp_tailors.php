<?php
session_start();
require_once 'databasecon.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- VALIDATE AND GET TAILOR ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: approvedtailors.php"); // Redirect if ID is missing or invalid
    exit();
}
$tailor_id = intval($_GET['id']);

// --- Removal Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_tailor'])) {
    $tailor_id_to_remove = $_POST['tid'];
    $tailor_email = $_POST['tailor_email'];
    $removal_reason = $_POST['removal_reason'];

    // Update tailor status in 'treg' table
    $update_treg_sql = "UPDATE treg SET status = 'Removed' WHERE tid = ?";
    $db->executeQuery($update_treg_sql, "i", $tailor_id_to_remove);

    // Update user type in 'login' table
    $update_login_sql = "UPDATE login SET utype = 'removed_tailor' WHERE uid = ? AND utype = 'tailor'";
    $db->executeQuery($update_login_sql, "i", $tailor_id_to_remove);
    
    // --- Removal Email Sending Logic ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com';
        $mail->Password   = 'cqdaqntjinoeclpr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Admin');
        $mail->addAddress($tailor_email);
        $mail->isHTML(true);
        $mail->Subject = 'Account Deactivated on StitchVerse';
        $mail->Body    = "
            <html><body>
                <h2>Account Deactivated</h2>
                <p>Hi there,</p>
                <p>Your tailor account on StitchVerse has been deactivated by the administration team.</p>
                <p><strong>Reason for removal:</strong></p>
                <p>" . htmlspecialchars($removal_reason) . "</p>
                <p>If you believe this is a mistake or wish to appeal, please contact our support team.</p>
                <br><p>Thanks,</p><p>The StitchVerse Team</p>
            </body></html>";
        $mail->AltBody = "Your tailor account on StitchVerse has been deactivated. Reason: " . $removal_reason;
        $mail->send();
        
        $_SESSION['action_success'] = 'Tailor has been removed and a notification email has been sent.';
        header("Location: approvedtailors.php");
        exit();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        $_SESSION['action_error'] = 'Tailor removed, but the notification email could not be sent.';
        header("Location: approvedtailors.php");
        exit();
    }
}

// --- Custom Email Sending Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_custom_email'])) {
    $tailor_email = $_POST['tailor_email'];
    $email_subject = $_POST['email_subject'];
    $email_body = $_POST['email_body'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com';
        $mail->Password   = 'cqdaqntjinoeclpr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Admin');
        $mail->addAddress($tailor_email);
        $mail->isHTML(true);
        $mail->Subject = htmlspecialchars($email_subject);
        $mail->Body    = "
            <html><body>
                <h2>Message from StitchVerse Admin</h2>
                <p>Hi there,</p>
                <p>" . nl2br(htmlspecialchars($email_body)) . "</p>
                <br><p>Thanks,</p><p>The StitchVerse Team</p>
            </body></html>";
        $mail->AltBody = htmlspecialchars($email_body);
        $mail->send();
        
        $_SESSION['action_success'] = 'Email has been sent successfully to the tailor.';
        header("Location: viewapp_tailors.php?id=$tailor_id");
        exit();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        $_SESSION['action_error'] = 'The email could not be sent. Please check system logs.';
        header("Location: viewapp_tailors.php?id=$tailor_id");
        exit();
    }
}

// --- FETCH ALL DATA RELATED TO THE TAILOR ---

// --- Main Tailor Details ---
$tailor = null;
$sql_tailor = "SELECT * FROM treg WHERE tid = ?";
$result_tailor = $db->selectData($sql_tailor, "i", $tailor_id);
if ($result_tailor && $result_tailor->num_rows > 0) {
    $tailor = $result_tailor->fetch_assoc();
} else {
    $_SESSION['action_error'] = "Tailor not found.";
    header("Location: approvedtailors.php");
    exit();
}

// --- Uploaded Designs ---
$designs = [];
$sql_designs = "SELECT did, dname, dtype, dprice, dimg FROM upload WHERE uid = ?";
$result_designs = $db->selectData($sql_designs, "i", $tailor_id);
if ($result_designs) {
    while ($row = $result_designs->fetch_assoc()) {
        $designs[] = $row;
    }
}

// --- Stitching Requests Received ---
$requests = [];
$sql_requests = "SELECT s.sdid, s.sdname, s.sprice, s.sstatus, c.cname 
                 FROM stitchreq s 
                 JOIN creg c ON s.uid = c.cid 
                 WHERE s.tid = ? ORDER BY s.sdid DESC";
$result_requests = $db->selectData($sql_requests, "i", $tailor_id);
if ($result_requests) {
    while ($row = $result_requests->fetch_assoc()) {
        $requests[] = $row;
    }
}

// --- Orders for Tailor's Designs ---
$orders = [];
$sql_orders = "SELECT od.orderdate, u.dname, c.cname, u.dprice, od.ostatus
               FROM orderdesign od 
               JOIN upload u ON od.did = u.did 
               JOIN creg c ON od.uid = c.cid 
               WHERE u.uid = ? ORDER BY od.oid DESC";
$result_orders = $db->selectData($sql_orders, "i", $tailor_id);
if ($result_orders) {
    while ($row = $result_orders->fetch_assoc()) {
        $orders[] = $row;
    }
}

// --- Customer Feedback ---
$feedback = [];
$sql_feedback = "SELECT 
                    f.feedbck, f.rate, f.feedtype, c.cname as customer_name,
                    CASE 
                        WHEN f.feedtype = 'design_purchase' THEN u.dname
                        WHEN f.feedtype = 'stitching_service' THEN sr.sdname
                        ELSE 'N/A'
                    END as item_name
                 FROM feedb f
                 JOIN creg c ON f.uid = c.cid
                 LEFT JOIN upload u ON f.itemid = u.did AND f.feedtype = 'design_purchase'
                 LEFT JOIN stitchreq sr ON f.itemid = sr.sdid AND f.feedtype = 'stitching_service'
                 WHERE f.tid = ? ORDER BY f.fid DESC";
$result_feedback = $db->selectData($sql_feedback, "i", $tailor_id);
if ($result_feedback) {
    while ($row = $result_feedback->fetch_assoc()) {
        $feedback[] = $row;
    }
}

// Function to render status badges
function getStatusBadge($status) {
    $status = strtolower($status ?? 'unknown');
    switch ($status) {
        case 'paid': case 'shipped': case 'approved': case 'accepted':
            return '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        case 'rejected': case 'cancelled':
            return '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        default:
            return '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
    }
}

// Function to render rating stars
function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= ($i <= $rating) ? '<i class="ri-star-fill text-yellow-400"></i>' : '<i class="ri-star-line text-gray-300"></i>';
    }
    return $stars;
}

// Display session messages
$success_message = '';
$error_message = '';
if (isset($_SESSION['action_success'])) {
    $success_message = $_SESSION['action_success'];
    unset($_SESSION['action_success']);
}
if (isset($_SESSION['action_error'])) {
    $error_message = $_SESSION['action_error'];
    unset($_SESSION['action_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Details - <?php echo htmlspecialchars($tailor['tname']); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
        .toast-message {
            transition: opacity 0.5s, transform 0.5s;
        }
    </style>
</head>
<body class="bg-gray-100">

   <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-8">
            <div class="relative">
                <button id="tailor-dropdown-button" data-dropdown-toggle="tailor-dropdown-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Tailors</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="tailor-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="approvedtailors.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Approved Tailors</a>
                    <a href="pendingtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Tailors</a>
                    <a href="rejectedtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Rejected Tailors</a>
                    <a href="removedtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Removed Tailors</a>
                </div>
            </div>
            <div class="relative">
                <button id="customer-dropdown-button" data-dropdown-toggle="customer-dropdown-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Customers</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="customer-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="activecustomers.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Active Customers</a>
                    <a href="removedcustomers.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Removed Customers</a>
                </div>
            </div>
            <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Designs</a>
          <div class="relative">
                 <button id="stitch-dropdown-button" data-dropdown-toggle="stitch-dropdown-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Stitch Requests</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="stitch-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="activerequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Active Requests</a>
                    <a href="shippedrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Shipped Requests</a>
                    <a href="paymentpendingrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Payment Pending</a>
                    <a href="paidrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Paid Requests</a>
                </div>
            </div>
            <div class="relative">
                <button id="report-dropdown-button" data-dropdown-toggle="report-dropdown-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Reports</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="report-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailor_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Tailor Reports</a>
                    <a href="customer_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Customer Reports</a>
                    <a href="design_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Design Reports</a>
                    <a href="request_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Request Reports</a>
                </div>
            </div>
          </nav>

          <div class="hidden md:flex items-center space-x-4">
            <a href="index.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold flex items-center gap-2">
                <i class="ri-logout-box-line"></i> Logout
            </a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <div class="mb-6">
            <a href="approvedtailors.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Approved Tailors</span>
            </a>
        </div>
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-10">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-grow">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($tailor['tname']); ?></h1>
                            <p class="text-purple-600 font-semibold mt-1"><?php echo htmlspecialchars($tailor['spect']); ?></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button onclick="openEmailModal()" class="bg-blue-100 text-blue-700 font-semibold px-4 py-2 rounded-lg hover:bg-blue-200 text-sm flex items-center gap-2">
                                <i class="ri-mail-send-line"></i> Email
                            </button>
                            <button onclick="openRemoveModal()" class="bg-red-100 text-red-700 font-semibold px-4 py-2 rounded-lg hover:bg-red-200 text-sm flex items-center gap-2">
                                <i class="ri-user-unfollow-line"></i> Remove
                            </button>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 my-6"></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div class="flex items-center gap-3"><i class="ri-mail-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['email']); ?></span></div>
                        <div class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['phone']); ?></span></div>
                        <div class="flex items-center gap-3"><i class="ri-map-pin-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['city'] . ', ' . $tailor['distri']); ?></span></div>
                        <div class="flex items-center gap-3"><i class="ri-award-line text-purple-500 text-xl"></i><span><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($tailor['quali']))); ?></span></div>
                        <div class="flex items-start gap-3 col-span-full"><i class="ri-building-line text-purple-500 text-xl pt-1"></i><span class="flex-1"><?php echo htmlspecialchars($tailor['address']); ?></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-12">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Customer Feedback & Ratings</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3">Customer</th>
                                   <th scope="col" class="px-6 py-3">Feedback On</th>
                                   <th scope="col" class="px-6 py-3">Comment</th>
                                   <th scope="col" class="px-6 py-3 text-center">Rating</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feedback)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No feedback has been received for this tailor yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($feedback as $fb): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($fb['customer_name']); ?></td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-800"><?php echo htmlspecialchars($fb['item_name']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($fb['feedtype']))); ?></div>
                                        </td>
                                        <td class="px-6 py-4 italic">"<?php echo htmlspecialchars($fb['feedbck']); ?>"</td>
                                        <td class="px-6 py-4 text-center text-lg"><?php echo renderStars($fb['rate']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Stitching Jobs Assigned</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3">Request</th>
                                   <th scope="col" class="px-6 py-3">Customer</th>
                                   <th scope="col" class="px-6 py-3 text-right">Price</th>
                                   <th scope="col" class="px-6 py-3 text-center">Status</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No stitching jobs have been assigned to this tailor.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $request): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4"><a href="view_request_details.php?id=<?php echo $request['sdid']; ?>" class="font-medium text-purple-600 hover:underline"><?php echo htmlspecialchars($request['sdname']); ?></a></td>
                                        <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($request['cname']); ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <?php 
                                                if (!empty($request['sprice']) && is_numeric($request['sprice'])) {
                                                    echo '₹' . number_format($request['sprice'], 2);
                                                } else {
                                                    echo '<span class="text-gray-400 italic">Not Quoted</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 text-center"><?php echo getStatusBadge($request['sstatus']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Uploaded Designs</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3">Design</th>
                                   <th scope="col" class="px-6 py-3">Category</th>
                                   <th scope="col" class="px-6 py-3 text-right">Price</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($designs)): ?>
                                    <tr><td colspan="3" class="px-6 py-10 text-center text-gray-500">This tailor has not uploaded any designs.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($designs as $design): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <img src="<?php echo htmlspecialchars($design['dimg']); ?>" class="w-12 h-16 object-cover rounded-lg border shrink-0" alt="Design Image">
                                                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($design['dname']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($design['dtype']); ?></td>
                                        <td class="px-6 py-4 text-right font-medium">₹<?php echo number_format($design['dprice'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Orders for Tailor's Designs</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                   <th scope="col" class="px-6 py-3">Order Date</th>
                                   <th scope="col" class="px-6 py-3">Design Name</th>
                                   <th scope="col" class="px-6 py-3">Customer</th>
                                   <th scope="col" class="px-6 py-3 text-center">Status</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No orders found for this tailor's designs.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4"><?php echo date("M d, Y", strtotime($order['orderdate'])); ?></td>
                                        <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($order['dname']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($order['cname']); ?></td>
                                        <td class="px-6 py-4 text-center"><?php echo getStatusBadge($order['ostatus']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[101] space-y-3">
        <?php if ($success_message): ?>
        <div class="toast-message max-w-xs bg-green-500 text-white text-sm rounded-lg shadow-lg p-4" role="alert">
            <div class="flex items-center">
                <i class="ri-checkbox-circle-fill text-xl mr-3"></i>
                <div class="flex-1"><?php echo htmlspecialchars($success_message); ?></div>
                <button type="button" class="ml-2 text-white opacity-70 hover:opacity-100" onclick="this.parentElement.parentElement.remove();">&times;</button>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="toast-message max-w-xs bg-red-500 text-white text-sm rounded-lg shadow-lg p-4" role="alert">
            <div class="flex items-center">
                <i class="ri-error-warning-fill text-xl mr-3"></i>
                <div class="flex-1"><?php echo htmlspecialchars($error_message); ?></div>
                <button type="button" class="ml-2 text-white opacity-70 hover:opacity-100" onclick="this.parentElement.parentElement.remove();">&times;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <!-- Removal Modal -->
    <div id="removal-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[100] opacity-0">
        <div class="modal-container bg-white w-full max-w-md p-6 rounded-2xl shadow-xl transform scale-95">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100"><i class="ri-error-warning-line text-2xl text-red-600"></i></div>
                <h3 class="mt-4 text-lg font-semibold leading-6 text-gray-900">Remove Tailor Account</h3>
                <p class="mt-2 text-sm text-gray-500">You are about to remove <strong class="font-bold"><?php echo htmlspecialchars($tailor['tname']); ?></strong>. Please provide a reason. This action will deactivate their account and notify them via email.</p>
            </div>
            <form method="POST" action="viewapp_tailors.php?id=<?php echo $tailor_id; ?>" class="mt-6">
                <input type="hidden" name="tid" value="<?php echo $tailor['tid']; ?>">
                <input type="hidden" name="tailor_email" value="<?php echo htmlspecialchars($tailor['email']); ?>">
                <div>
                    <label for="removal_reason" class="block text-sm font-medium text-gray-700">Reason for Removal (Required)</label>
                    <textarea id="removal_reason" name="removal_reason" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm" placeholder="e.g., Violation of terms of service."></textarea>
                </div>
                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:gap-3 sm:justify-center">
                    <button type="button" onclick="closeRemoveModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:w-auto sm:text-sm">Cancel</button>
                    <button type="submit" name="remove_tailor" class="w-full inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">Confirm & Send Email</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Email Modal -->
    <div id="email-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-[100] opacity-0">
        <div class="modal-container bg-white w-full max-w-lg p-6 rounded-2xl shadow-xl transform scale-95">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Send Email to <?php echo htmlspecialchars($tailor['tname']); ?></h3>
                <button onclick="closeEmailModal()" class="p-1 rounded-full hover:bg-gray-100"><i class="ri-close-line text-xl text-gray-500"></i></button>
            </div>
            <form method="POST" action="viewapp_tailors.php?id=<?php echo $tailor_id; ?>" class="mt-6 space-y-4">
                <input type="hidden" name="tailor_email" value="<?php echo htmlspecialchars($tailor['email']); ?>">
                <div>
                    <label for="email_subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" id="email_subject" name="email_subject" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm" placeholder="Enter email subject">
                </div>
                <div>
                    <label for="email_body" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="email_body" name="email_body" rows="6" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm" placeholder="Compose your message..."></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeEmailModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:w-auto sm:text-sm">Cancel</button>
                    <button type="submit" name="send_custom_email" class="w-full inline-flex justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:w-auto sm:text-sm">Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const modalContainer = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContainer.classList.remove('scale-95');
            }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const modalContainer = modal.querySelector('.modal-container');
            modal.classList.add('opacity-0');
            modalContainer.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }
        
        const openRemoveModal = () => openModal('removal-modal');
        const closeRemoveModal = () => closeModal('removal-modal');
        const openEmailModal = () => openModal('email-modal');
        const closeEmailModal = () => closeModal('email-modal');

        document.addEventListener('DOMContentLoaded', () => {
            // Dropdown menu logic
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => {
                        if (b !== button) {
                            document.getElementById(b.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                            b.setAttribute('aria-expanded', 'false');
                        }
                    });
                    dropdownMenu.classList.toggle('hidden');
                    button.setAttribute('aria-expanded', !dropdownMenu.classList.contains('hidden'));
                });
            });

            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    document.getElementById(button.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });

            // Auto-hide toast messages
            document.querySelectorAll('.toast-message').forEach(toast => {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-20px)';
                    setTimeout(() => toast.remove(), 500);
                }, 5000);
            });

            // Close modals on escape key press
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (!document.getElementById('removal-modal').classList.contains('hidden')) closeRemoveModal();
                    if (!document.getElementById('email-modal').classList.contains('hidden')) closeEmailModal();
                }
            });
        });
    </script>
</body>
</html>

