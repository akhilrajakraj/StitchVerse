
<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

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
    header("Location: pendingrequest.php");
    exit();
}

// --- [NEW] Get and clear any action messages from the session ---
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

$db = new DatabaseCon();
$request_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

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
            $mail->Username   = 'stitchverrse@gmail.com'; // Your Gmail address
            $mail->Password   = 'cqdaqntjinoeclpr';       // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Tailor');
            $mail->addAddress($customer_email, $customer_name);
            $mail->isHTML(true);

            if ($action === 'remove_request') {
                $removal_reason = $_POST['removal_reason'] ?? 'No reason provided.';
                $sql = "UPDATE stitchreq SET sstatus = 'Removed' WHERE sdid = ? AND tid = ?";
                $db->executeQuery($sql, "ii", $sdid, $tailor_id);

                $mail->Subject = 'Your Stitching Request has been Removed';
                $mail->Body    = "
                    <html><body>
                        <h2>Dear {$customer_name},</h2>
                        <p>We are writing to inform you that your stitching request for '<strong>{$request_name}</strong>' has been removed by the tailor.</p>
                        <p>Reason for removal:</p>
                        <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 20px; font-style: italic;'>
                            <p>" . htmlspecialchars($removal_reason) . "</p>
                        </blockquote>
                        <p>We apologize for any inconvenience. If you believe this was a mistake, please contact us. You can always create a new request on StitchVerse.</p>
                        <br><p>Sincerely,</p><p>The StitchVerse Team</p>
                    </body></html>";
                
                $mail->send();
                // [MODIFIED] Use 'action_success' for the notification system
                $_SESSION['action_success'] = "Request removed successfully! An email has been sent to the customer.";
                header("Location: viewpendpay.php?id=$request_id");
                exit();

            } elseif ($action === 'send_email') {
                $email_subject = $_POST['email_subject'] ?? 'A message regarding your order';
                $email_message = $_POST['email_message'] ?? 'The tailor has sent you a message.';

                $mail->Subject = $email_subject;
                $mail->Body    = "
                    <html><body>
                        <h2>A Message from Your Tailor</h2>
                        <p>Dear {$customer_name},</p>
                        <p>You have received the following message regarding your stitching request for '<strong>{$request_name}</strong>':</p>
                        <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 20px; font-style: italic;'>
                            <p>" . nl2br(htmlspecialchars($email_message)) . "</p>
                        </blockquote>
                        <p>You can reply to this email to communicate directly with your tailor.</p>
                        <br><p>Best regards,</p><p>The StitchVerse Team</p>
                    </body></html>";
                
                $mail->send();
                // [MODIFIED] Use 'action_success' for the notification system
                $_SESSION['action_success'] = "Email sent to the customer successfully!";
                header("Location: viewpendpay.php?id=$request_id");
                exit();
            }

        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            // [MODIFIED] Use 'action_error' for the notification system
            $_SESSION['action_error'] = "The action was processed, but we couldn't send the notification email.";
            // Redirect based on original action
            if ($action === 'remove_request') {
                header("Location: pendingrequest.php");
            } else {
                header("Location: viewpendpay.php?id=$request_id");
            }
            exit();
        }
    }
}

// --- Data Fetching Logic ---
$request_details = null;
$design_specifics = [];
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
            sr.sdid = ? AND sr.tid = ? AND sr.sstatus = 'Accepted'";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
    if (!empty($request_details['sdesign_details'])) {
        $design_specifics = json_decode($request_details['sdesign_details'], true);
    }
} else {
    // [MODIFIED] Use 'action_error' for consistency
    $_SESSION['action_error'] = "This request is no longer available or has already been paid for.";
    header("Location: pendingrequest.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payment Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
        .arrow-rotated { transform: rotate(180deg); }
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
                      <span>Designs</span>
                      <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                  </button>
                  <div id="designs-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                      <a href="upd.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Upload</a>
                      <a href="viewmydesigns.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">My Designs</a>
                  </div>
              </div>

              <div class="relative">
                  <button data-dropdown-toggle="design-orders-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                      <span>Design Orders</span>
                      <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                  </button>
                  <div id="design-orders-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                      <a href="pendingorderpay.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Payments</a>
                      <a href="paidorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Paid Orders</a>
                  </div>
              </div>

              <div class="relative">
                  <button data-dropdown-toggle="custom-orders-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                      <span>Custom Orders</span>
                      <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                  </button>
                  <div id="custom-orders-menu" class="hidden absolute mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                      <a href="tailorreq.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">General Requests</a>
                      <a href="personalrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Personal Requests</a>
                      <div class="my-1 border-t border-gray-100"></div>
                      <a href="pendingrequest.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Pending Requests</a>
                      <a href="acceptedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Accepted Requests</a>
                      <a href="shipped_request.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Shipped Requests</a>
                      <a href="rejectedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Rejected Requests</a>
                      <a href="cancelledrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Cancelled Requests</a>
                  </div>
              </div>
          </nav>
          
          <div class="hidden md:flex items-center space-x-4">
              <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
              <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>
    <main class="container mx-auto px-6 py-12">
        <div class="mb-6">
            <a href="pendingrequest.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Pending Requests</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($request_details['sdname']); ?></h1>
                        <p class="text-purple-200 font-mono">Request ID: #<?php echo htmlspecialchars($request_details['sdid']); ?></p>
                    </div>
                    <div class="text-center bg-orange-400 text-orange-900 font-bold px-4 py-2 rounded-lg">
                        Awaiting Customer Payment
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
                                
                                <?php if (!empty($design_specifics)): ?>
                                    <?php foreach ($design_specifics as $key => $value): ?>
                                        <?php if (!empty($value) && !is_numeric($key)): ?>
                                            <p><strong class="text-gray-500"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $key))); ?>:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($value); ?></span></p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <p><strong class="text-gray-500">Desired Delivery Date:</strong><br><span class="font-bold text-red-600"><?php echo date("F j, Y", strtotime($request_details['sddate'])); ?></span></p>
                                <p><strong class="text-gray-500">Quoted Price:</strong><br><span class="font-bold text-green-600 text-lg">₹<?php echo htmlspecialchars(number_format($request_details['sprice'], 2)); ?></span></p>

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
                                <div class="flex items-start gap-3 pt-2">
                                    <i class="ri-map-pin-line text-purple-500 w-4 text-center mt-1"></i>
                                    <div class="text-gray-600">
                                        <p><?php echo htmlspecialchars($request_details['customer_address']); ?></p>
                                        <p><?php echo htmlspecialchars($request_details['customer_city']); ?>, <?php echo htmlspecialchars($request_details['customer_distr']); ?></p>
                                        <p><?php echo htmlspecialchars($request_details['customer_pincode']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Measurements</h2>
                        <?php if (isset($request_details['mid'])): ?>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <?php
                                $measurements_to_display = ['height', 'weight', 'neck', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'arm_length', 'sleeve_length', 'bicep', 'wrist', 'thigh', 'knee', 'calf', 'inseam', 'outseam', 'ankle'];
                                foreach ($measurements_to_display as $key) {
                                    $value = $design_specifics[ucfirst($key)] ?? $request_details[$key] ?? null;
                                    if ($value) {
                                        echo '<div><p class="text-gray-500 capitalize">' . str_replace('_', ' ', $key) . '</p><p class="font-semibold text-gray-800">' . htmlspecialchars($value) . '</p></div>';
                                    }
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">This customer has not saved their measurements yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <p class="text-sm text-gray-600 mr-auto">Contact customer or remove if payment is delayed.</p>
                <button id="remove-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-delete-bin-line text-lg"></i> Remove Request
                </button>
                <button id="email-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-purple-600 hover:bg-purple-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-mail-send-line text-lg"></i> Email Customer
                </button>
            </div>
        </div>
    </main>
    
    <div id="removal-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0">
        <div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform -translate-y-10">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Remove Request</h2>
                    <button id="close-removal-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Provide a reason for removing this request. This will be sent to the customer.</p>
                <form action="viewpendpay.php?id=<?php echo $request_id; ?>" method="POST" class="mt-4">
                    <input type="hidden" name="action" value="remove_request">
                    <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
                    <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
                    <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">
                    <div>
                        <label for="removal_reason" class="sr-only">Reason for Removal</label>
                        <textarea id="removal_reason" name="removal_reason" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g., Payment was not received within the specified timeframe." required></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="cancel-removal-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Confirm Removal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="email-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0">
        <div class="modal-container bg-white w-full max-w-lg rounded-lg shadow-xl transform -translate-y-10">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Send Email to Customer</h2>
                    <button id="close-email-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">Compose your message to <?php echo htmlspecialchars($request_details['cname']); ?>.</p>
                <form action="viewpendpay.php?id=<?php echo $request_id; ?>" method="POST" class="mt-4 space-y-4">
                    <input type="hidden" name="action" value="send_email">
                    <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                    <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>">
                    <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>">
                    <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>">
                    <div>
                        <label for="email_subject" class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" id="email_subject" name="email_subject" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" value="Regarding your Stitching Request #<?php echo htmlspecialchars($request_details['sdid']); ?>" required>
                    </div>
                    <div>
                        <label for="email_message" class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea id="email_message" name="email_message" rows="6" class="mt-1 w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g., Hello, just a friendly reminder about the pending payment for your order..." required></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="cancel-email-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700">Send Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- Logic for Modals (Remove and Email) ---
            const setupModal = (modalId, openBtnId, closeBtnId, cancelBtnId) => {
                const modal = document.getElementById(modalId);
                const openBtn = document.getElementById(openBtnId);
                const closeBtn = document.getElementById(closeBtnId);
                const cancelBtn = document.getElementById(cancelBtnId);
                
                if (!modal || !openBtn || !closeBtn || !cancelBtn) return;

                const modalContainer = modal.querySelector('.modal-container');

                const openModal = () => {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        modalContainer.classList.remove('-translate-y-10');
                    }, 10);
                };

                const closeModal = () => {
                    modal.classList.add('opacity-0');
                    modalContainer.classList.add('-translate-y-10');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                };

                openBtn.addEventListener('click', openModal);
                closeBtn.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', closeModal);

                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            };
            setupModal('removal-modal', 'remove-btn', 'close-removal-modal-btn', 'cancel-removal-btn');
            setupModal('email-modal', 'email-btn', 'close-email-modal-btn', 'cancel-email-btn');

            
            // --- Logic for Dropdown Menus in Header ---
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                const dropdownMenu = document.getElementById(dropdownMenuId);
                const arrowIcon = button.querySelector('.arrow-icon');
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                        if (otherButton !== button) {
                            const otherMenuId = otherButton.getAttribute('data-dropdown-toggle');
                            document.getElementById(otherMenuId).classList.add('hidden');
                            otherButton.setAttribute('aria-expanded', 'false');
                            const otherArrowIcon = otherButton.querySelector('.arrow-icon');
                            if (otherArrowIcon) otherArrowIcon.classList.remove('arrow-rotated');
                        }
                    });
                    dropdownMenu.classList.toggle('hidden');
                    if (arrowIcon) arrowIcon.classList.toggle('arrow-rotated');
                    button.setAttribute('aria-expanded', !dropdownMenu.classList.contains('hidden'));
                });
            });
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                    document.getElementById(dropdownMenuId).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                    const arrowIcon = button.querySelector('.arrow-icon');
                    if (arrowIcon) arrowIcon.classList.remove('arrow-rotated');
                });
            });

            // --- [NEW] Toast Notification Javascript ---
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
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 5000); // Hide after 5 seconds
            }
            // --- End Toast Notification Javascript ---

        });
    </script>
</body>
</html>