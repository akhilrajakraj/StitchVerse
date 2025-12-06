
<?php
session_start();
require_once 'databasecon.php'; 

// PHPMailer imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if a Request ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: acceptedrequest.php");
    exit();
}

$db = new DatabaseCon();
$request_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- Get and clear any action messages from the session ---
$success_message = '';
if (isset($_SESSION['action_success'])) {
    $success_message = $_SESSION['action_success'];
    unset($_SESSION['action_success']);
}
$error_message = '';
if (isset($_SESSION['action_error'])) {
    $error_message = $_SESSION['action_error'];
    unset($_SESSION['action_error']);
}
// --- End message handling ---

// --- Form Handling for All Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $sdid = $_POST['sdid'] ?? 0;
    
    if ($sdid == $request_id) {
        $customer_email = $_POST['customer_email'] ?? '';
        $customer_name = $_POST['customer_name'] ?? '';
        $request_name = $_POST['request_name'] ?? '';

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com';
            $mail->Password   = 'cqdaqntjinoeclpr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Tailor');
            $mail->addAddress($customer_email, $customer_name);
            $mail->isHTML(true);

            if ($action === 'set_price') {
                $price = $_POST['sprice'] ?? 0;
                $sql = "UPDATE stitchreq SET sprice = ? WHERE sdid = ? AND tid = ?";
                $db->executeQuery($sql, "dii", $price, $sdid, $tailor_id);

                $mail->Subject = "Price Quote for Your Stitching Request: {$request_name}";
                $mail->Body    = "
                    <html><body>
                        <h2>Hello {$customer_name},</h2>
                        <p>Good news! We have a price quote for your stitching request, '<strong>{$request_name}</strong>'.</p>
                        <p><strong>Quoted Price: ₹" . number_format($price, 2) . "</strong></p>
                        <p>Please log in to your StitchVerse account to review the details and complete the payment to proceed with your order.</p>
                        <br><p>Thank you,</p><p>The StitchVerse Team</p>
                    </body></html>";
                
                $mail->send();
                $_SESSION['action_success'] = "Price set successfully! The customer has been notified.";

            } elseif ($action === 'send_email') {
                $email_subject = $_POST['email_subject'] ?? 'A message regarding your order';
                $email_message = $_POST['email_message'] ?? 'The tailor has sent you a message.';

                $mail->Subject = $email_subject;
                $mail->Body    = "
                    <html><body>
                        <h2>A Message from Your Tailor</h2>
                        <p>Dear {$customer_name},</p>
                        <p>You have received the following message regarding your stitching request for '<strong>{$request_name}</strong>':</p>
                        <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 20px; font-style: italic;'>" . nl2br(htmlspecialchars($email_message)) . "</blockquote>
                        <p>You can reply to this email to communicate directly with your tailor.</p>
                        <br><p>Best regards,</p><p>The StitchVerse Team</p>
                    </body></html>";
                
                $mail->send();
                $_SESSION['action_success'] = "Email sent to the customer successfully!";

            } elseif ($action === 'cancel_request') {
                $cancellation_reason = $_POST['cancellation_reason'] ?? 'No reason provided.';
                $sql = "UPDATE stitchreq SET sstatus = 'Cancelled' WHERE sdid = ? AND tid = ?";
                $db->executeQuery($sql, "ii", $sdid, $tailor_id);

                $mail->Subject = "Update on Your Stitching Request: {$request_name}";
                $mail->Body    = "
                    <html><body>
                        <h2>Dear {$customer_name},</h2>
                        <p>We are writing to inform you that your stitching request for '<strong>{$request_name}</strong>' has unfortunately been cancelled.</p>
                        <p>Reason for cancellation:</p>
                        <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 20px; font-style: italic;'>" . htmlspecialchars($cancellation_reason) . "</blockquote>
                        <p>We apologize for any inconvenience. Any payment made for this request will be processed for a refund. Please feel free to create a new request on StitchVerse.</p>
                        <br><p>Sincerely,</p><p>The StitchVerse Team</p>
                    </body></html>";

                $mail->send();
                $_SESSION['action_success'] = "Request cancelled successfully. The customer has been notified.";
                header("Location: acceptedrequest.php");
                exit();

            } elseif ($action === 'send_shipping_info_custom') {
                $shipping_provider = trim($_POST['shipping_provider']);
                $tracking_number = trim($_POST['tracking_number']);

                $updateSql = "UPDATE stitchreq SET sstatus = 'Shipped' WHERE sdid = ? AND tid = ?";
                $db->executeQuery($updateSql, "ii", $sdid, $tailor_id);

                $mail->Subject = "Your StitchVerse Order Has Shipped! (#{$sdid})";
                $mail->Body    = "
                    <html><body>
                        <h2>Great News, {$customer_name}!</h2>
                        <p>Your custom stitching order for '<strong>{$request_name}</strong>' has been shipped.</p>
                        <p>Here are your tracking details:</p>
                        <ul>
                            <li><strong>Shipping Provider:</strong> " . htmlspecialchars($shipping_provider) . "</li>
                            <li><strong>Tracking Number:</strong> " . htmlspecialchars($tracking_number) . "</li>
                        </ul>
                        <p>You can use this information to track your package. Thank you for choosing StitchVerse!</p>
                        <br><p>Best regards,</p><p>The StitchVerse Team</p>
                    </body></html>";
                $mail->send();
                $_SESSION['action_success'] = "Shipping information sent and order marked as 'Shipped'.";
            }

        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            $_SESSION['action_error'] = "Action processed, but the notification email failed to send.";
        }
        
        header("Location: viewacceptedreqdetails.php?id=$request_id");
        exit();
    }
}

// --- Data Fetching Logic ---
$request_details = null;
$customer_measurements_json = "{}";
$design_details_parts = ['design' => '', 'measurements' => ''];
$sql = "SELECT
            sr.*,
            c.cname, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.city AS customer_city, c.distr AS customer_distr, c.pincode AS customer_pincode,
            m.*
        FROM
            stitchreq sr
        JOIN
            creg c ON sr.uid = c.cid
        LEFT JOIN
            measurements m ON sr.uid = m.uid
        WHERE
            sr.sdid = ? AND sr.tid = ?";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
    $customer_measurements_json = json_encode($request_details);
    
    if (!empty($request_details['sdesign_details'])) {
        $parts = explode('--- Measurements ---', $request_details['sdesign_details']);
        $design_details_parts['design'] = trim($parts[0]);
        if (isset($parts[1])) {
            $design_details_parts['measurements'] = trim($parts[1]);
        }
    }
} else {
    $_SESSION['action_error'] = "The requested job could not be found or is not assigned to you.";
    header("Location: acceptedrequest.php");
    exit();
}

$can_cancel = false;
if ($request_details['sstatus'] === 'Accepted') {
    $submitted_at = new DateTime($request_details['submitted_at']);
    $now = new DateTime();
    $interval_seconds = $now->getTimestamp() - $submitted_at->getTimestamp();
    $hours_passed = $interval_seconds / 3600;
    if ($hours_passed < 28) {
        $can_cancel = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <div id="toast-notification" class="hidden fixed top-5 right-5 z-[150] w-full max-w-xs p-4 text-gray-700 bg-white rounded-2xl shadow-xl" role="alert">
        <div class="flex items-center">
            <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"></div>
            <div id="toast-message" class="ms-3 text-sm font-semibold"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100" data-dismiss-target="#toast-notification" aria-label="Close">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
              <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
              <nav class="hidden md:flex items-center space-x-6">
                <div class="relative">
                    <button data-dropdown-toggle="designs-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                        <span>Designs</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                    </button>
                    <div id="designs-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                        <a href="upd.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Upload</a>
                        <a href="viewmydesigns.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">My Designs</a>
                    </div>
                </div>
                <div class="relative">
                    <button data-dropdown-toggle="design-orders-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                        <span>Design Orders</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                    </button>
                    <div id="design-orders-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                        <a href="pendingorderpay.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Payments</a>
                        <a href="paidorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Paid Orders</a>
                    </div>
                </div>
                <div class="relative">
                    <button data-dropdown-toggle="custom-orders-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                        <span>Custom Orders</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                    </button>
                    <div id="custom-orders-menu" class="hidden absolute mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                        <a href="tailorreq.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">General Requests</a>
                        <a href="personalrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Personal Requests</a>
                        <div class="my-1 border-t border-gray-100"></div>
                        <a href="pendingrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Requests</a>
                        <a href="acceptedrequest.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Accepted Requests</a>
                        <a href="shipped_request.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Shipped Requests</a>
                        <a href="rejectedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Rejected Requests</a>
                        <a href="cancelledrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Cancelled Requests</a>
                    </div>
                </div>
              </nav>
              
              <div class="hidden md:flex items-center space-x-4">
                <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
                <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
              </div>
              <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-6">
            <a href="acceptedrequest.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Active Jobs</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($request_details['sdname']); ?></h1>
                        <p class="text-purple-200 font-mono">Request ID: #<?php echo htmlspecialchars($request_details['sdid']); ?></p>
                    </div>
                    <?php
                        $status = $request_details['sstatus'];
                        $status_color = 'bg-gray-400 text-gray-900';
                        $status_text = $status;
                        if ($status == 'Paid') { $status_color = 'bg-green-400 text-green-900'; } 
                        elseif ($status == 'Accepted') { $status_color = 'bg-yellow-400 text-yellow-900'; $status_text = 'Awaiting Payment'; } 
                        elseif ($status == 'Shipped') { $status_color = 'bg-blue-400 text-blue-900'; }
                    ?>
                    <div class="text-center <?php echo $status_color; ?> font-bold px-4 py-2 rounded-lg">
                        <?php echo htmlspecialchars($status_text); ?>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Stitching Specifications</h2>
                            <div class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                                <p><strong class="text-gray-500">Dress Type:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sdtype']); ?></span></p>
                                <p><strong class="text-gray-500">Fabric:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sfabric']); ?></span></p>
                                <p><strong class="text-gray-500">Color:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['scolor'] ?? 'N/A'); ?></span></p>
                                <p><strong class="text-gray-500">Pattern:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['spattern'] ?? 'N/A'); ?></span></p>
                                
                                <?php if (!empty($design_details_parts['design'])): ?>
                                    <?php foreach (explode("\n", $design_details_parts['design']) as $line): ?>
                                        <?php if(strpos($line, ':') !== false): list($key, $value) = array_map('trim', explode(':', $line, 2)); ?>
                                        <p><strong class="text-gray-500"><?php echo htmlspecialchars($key); ?>:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($value); ?></span></p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <p><strong class="text-gray-500">Desired Delivery Date:</strong><br><span class="font-bold text-red-600"><?php echo date("F j, Y", strtotime($request_details['sddate'])); ?></span></p>
                                
                                <div class="sm:col-span-2">
                                    <strong class="text-gray-500">Special Instructions:</strong>
                                    <p class="font-medium text-gray-800 mt-1 p-3 bg-gray-50 rounded-lg"><?php echo nl2br(htmlspecialchars($request_details['sinstructions'])); ?></p>
                                </div>
                                <?php if(!empty($request_details['simg'])): ?>
                                <div class="sm:col-span-2">
                                    <strong class="text-gray-500">Reference Image:</strong>
                                    <div class="mt-2"><img src="<?php echo htmlspecialchars($request_details['simg']); ?>" class="max-w-xs w-full rounded-lg border"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Customer Details</h2>
                            <div class="space-y-2 text-sm">
                                <p class="flex items-center gap-3"><i class="ri-user-line text-purple-500 w-4 text-center"></i><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['cname']); ?></span></p>
                                <p class="flex items-center gap-3"><i class="ri-at-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_email']); ?></span></p>
                                <p class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_phone']); ?></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Required Measurements</h2>
                        <?php if (isset($request_details['mid'])): ?>
                            <div id="relevant-measurements-container" class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm"></div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">This customer has not saved their measurements yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <?php if ($can_cancel): ?>
                <button id="cancel-btn" type="button" class="w-full md:w-auto font-semibold text-red-600 hover:text-red-800 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2 mr-auto">
                    <i class="ri-close-circle-line text-lg"></i> Cancel Request
                </button>
                <?php endif; ?>

                <button id="email-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-purple-600 hover:bg-purple-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-mail-send-line text-lg"></i> Email Customer
                </button>
                
                <?php if (empty($request_details['sprice']) || $request_details['sprice'] <= 0): ?>
                <button id="price-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-blue-600 hover:bg-blue-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-price-tag-3-line text-lg"></i> Set Price & Notify
                </button>
                <?php elseif ($request_details['sstatus'] === 'Paid'): ?>
                <button id="ship-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-truck-line text-lg"></i> Send Shipping Details
                </button>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '_accepted_req_modals.php'; ?>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center border-t border-gray-800 pt-8">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const customerMeasurements = <?php echo $customer_measurements_json; ?>;
        const requestName = <?php echo json_encode($request_details['sdname']); ?>;
        const outfitConfigs = {
            'womens_anarkali_festive': { name: "Anarkali Suit – Festive Wear", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length', 'inseam', 'outseam'] },
            'womens_kurti_office': { name: "Office Kurti – Formal Wear", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length'] },
            'womens_anarkali_semi': { name: "Anarkali Kurti – Semi-Formal", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length'] },
            'womens_shirt_palazzo': { name: "Mandarin Collar Shirt with Palazzo", measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'wrist', 'inseam', 'outseam'] },
            'womens_trousers_formal': { name: "Formal Linen Trousers – Women", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam', 'ankle'] },
            'womens_blouse_bridal': { name: "Silk Bridal Blouse", measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'arm_length', 'sleeve_length'] },
            'womens_skirt_aline': { name: "A-Line Midi Skirt – Casual Chic", measurements: ['waist', 'hip', 'height'] },
            'womens_skirt_pleated': { name: "Pleated Maxi Skirt – Formal Occasions", measurements: ['waist', 'hip', 'height'] },
            'womens_skirt_denim': { name: "Denim Mini Skirt – Street Style", measurements: ['waist', 'hip'] },
            'womens_skirt_mermaid': { name: "Mermaid Skirt – Elegant Fit", measurements: ['waist', 'hip', 'knee', 'height'] },
            'unisex_skirt_wrap': { name: "Wrap Skirt – Bohemian Style", measurements: ['waist', 'hip', 'height'] },
            'mens_shirt_formal': { name: "Classic Formal Shirt", measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length', 'wrist'] },
            'mens_shirt_linen': { name: "Casual Linen Shirt", measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length'] },
            'mens_chinos_slim': { name: "Slim Fit Chinos", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'ankle'] },
            'mens_trousers_wool': { name: "Tailored Wool Trousers", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam'] },
            'mens_cargo': { name: "Cargo Pants – Functional Casual", measurements: ['waist', 'hip', 'thigh', 'inseam', 'outseam'] },
            'mens_polo': { name: "Polo T-Shirt – Smart Casual", measurements: ['neck', 'shoulder', 'chest', 'sleeve_length'] },
            'mens_kurta': { name: "Mandarin Collar Kurta – Ethnic Formal", measurements: ['neck', 'shoulder', 'chest', 'waist', 'hip', 'sleeve_length'] },
            'unisex_hoodie': { name: "Oversized Hoodie", measurements: ['shoulder', 'chest', 'sleeve_length', 'height'] },
            'unisex_joggers': { name: "Athletic Joggers", measurements: ['waist', 'hip', 'thigh', 'inseam', 'ankle'] }
        };
        const allMeasurementsLabels = { height: 'Height', weight: 'Weight', neck: 'Neck', shoulder: 'Shoulder', chest: 'Chest', bust: 'Bust', waist: 'Waist', hip: 'Hip', arm_length: 'Arm Length', sleeve_length: 'Sleeve Length', bicep: 'Bicep', wrist: 'Wrist', thigh: 'Thigh', knee: 'Knee', calf: 'Calf', inseam: 'Inseam', outseam: 'Outseam', ankle: 'Ankle' };

        function displayRelevantMeasurements() {
            const container = document.getElementById('relevant-measurements-container');
            if (!container) return;
            let outfitKey = Object.keys(outfitConfigs).find(key => outfitConfigs[key].name === requestName);
            if (outfitKey) {
                const requiredMeasurements = outfitConfigs[outfitKey].measurements;
                let html = '';
                requiredMeasurements.forEach(key => {
                    const label = allMeasurementsLabels[key] || key.replace('_', ' ');
                    const value = customerMeasurements[key];
                    if (value) html += `<div><p class="text-gray-500 capitalize">${label}</p><p class="font-semibold text-gray-800">${value} cm</p></div>`;
                });
                container.innerHTML = html || '<p class="col-span-2 text-sm text-gray-500">No relevant measurements found.</p>';
            } else {
                 container.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Could not determine required measurements.</p>';
            }
        }
        displayRelevantMeasurements();

        const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                const dropdownMenu = document.getElementById(dropdownMenuId);
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                        if (otherButton !== button) {
                            const otherMenuId = otherButton.getAttribute('data-dropdown-toggle');
                            document.getElementById(otherMenuId).classList.add('hidden');
                            otherButton.setAttribute('aria-expanded', 'false');
                        }
                    });
                    dropdownMenu.classList.toggle('hidden');
                    const isExpanded = !dropdownMenu.classList.contains('hidden');
                    button.setAttribute('aria-expanded', isExpanded);
                });
            });
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                    document.getElementById(dropdownMenuId).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });
        
        const setupModal = (modalId, openBtnId, closeBtnIds = []) => {
            const modal = document.getElementById(modalId), openBtn = document.getElementById(openBtnId);
            if (!modal) return;
            const openModal = () => { modal.classList.remove('hidden'); setTimeout(() => modal.classList.remove('opacity-0'), 10); };
            const closeModal = () => { modal.classList.add('opacity-0'); setTimeout(() => modal.classList.add('hidden'), 300); };
            if (openBtn) openBtn.addEventListener('click', openModal);
            closeBtnIds.forEach(id => { const btn = document.getElementById(id); if (btn) btn.addEventListener('click', closeModal); });
            modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
        };

        setupModal('price-modal', 'price-btn', ['close-price-modal-btn', 'cancel-price-btn']);
        setupModal('email-modal', 'email-btn', ['close-email-modal-btn', 'cancel-email-btn']);
        setupModal('cancel-modal', 'cancel-btn', ['close-cancel-modal-btn', 'cancel-cancel-btn']);
        setupModal('shipping-modal', 'ship-btn', ['close-shipping-modal-btn', 'cancel-shipping-btn']);
        
        const successMessage = '<?php echo $success_message; ?>';
        const errorMessage = '<?php echo $error_message; ?>';
        const toast = document.getElementById('toast-notification');
        if (toast && (successMessage || errorMessage)) {
            const toastMessageEl = document.getElementById('toast-message');
            const toastIconContainer = document.getElementById('toast-icon-container');
            const type = successMessage ? 'success' : 'error';
            toastMessageEl.textContent = successMessage || errorMessage;
            toastIconContainer.className = `inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg ${type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'}`;
            toastIconContainer.innerHTML = `<i class="ri-${type === 'success' ? 'check-double' : 'error-warning'}-line text-xl"></i>`;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }
        
    });
    </script>
</body>
</html>
