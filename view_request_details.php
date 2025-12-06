
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
// Validate the request ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: activerequests.php");
    exit();
}

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

$db = new DatabaseCon();
$request_id = $_GET['id'];

// Fetch all request details, including customer and tailor information
$sql = "SELECT sr.*, c.cname, c.email as cemail, c.phone as cphone, t.tname, t.email as temail 
        FROM stitchreq sr 
        JOIN creg c ON sr.uid = c.cid 
        LEFT JOIN treg t ON sr.tid = t.tid 
        WHERE sr.sdid = ?";
$result = $db->selectData($sql, "i", $request_id);
if (!$result || $result->num_rows === 0) {
    die("Request not found.");
}
$details = $result->fetch_assoc();

// Check if this request is linked to an original design based on the image
$original_design = null;
if (!empty($details['simg'])) {
    // We assume the first image is the primary design image
    $first_image = trim(explode(',', $details['simg'])[0]);
    
    // Query to find a matching design in the 'upload' table and get the designer's name
    $design_sql = "SELECT u.*, t.tname as designer_name 
                   FROM upload u
                   JOIN treg t ON u.uid = t.tid
                   WHERE u.dimg = ?";
    $design_result = $db->selectData($design_sql, "s", $first_image);
    if ($design_result && $design_result->num_rows > 0) {
        $original_design = $design_result->fetch_assoc();
    }
}

// Parse the sdesign_details text field instead of JSON
$design_options = [];
$measurements = [];
if (!empty($details['sdesign_details'])) {
    $parts = explode('--- Measurements ---', $details['sdesign_details']);
    
    // Process the design options part
    $design_lines = explode("\n", trim($parts[0]));
    foreach ($design_lines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = array_map('trim', explode(':', $line, 2));
            if(!empty($key) && !empty($value)) $design_options[$key] = $value;
        }
    }

    // Process the measurements part
    if (isset($parts[1])) {
        $measurement_lines = explode("\n", trim($parts[1]));
        foreach ($measurement_lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = array_map('trim', explode(':', $line, 2));
                if(!empty($key) && !empty($value)) $measurements[$key] = $value;
            }
        }
    }
}

// Helper function for styling status badges
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'paid':
        case 'shipped': return 'bg-green-100 text-green-800';
        case 'accepted': return 'bg-blue-100 text-blue-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
        case 'cancelled': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details #<?php echo htmlspecialchars($details['sdid']); ?> - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-gray-100">

    <!-- ===== Success/Error Toast Notification ===== -->
    <div id="toast-notification" class="hidden fixed top-5 right-5 z-[150] w-full max-w-xs p-4 text-gray-700 bg-white rounded-2xl shadow-xl" role="alert">
        <div class="flex items-center">
            <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
                <!-- Icon will be dynamically inserted here -->
            </div>
            <div id="toast-message" class="ms-3 text-sm font-semibold"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-notification" aria-label="Close">
                <span class="sr-only">Close</span>
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
    </div>
    <!-- ===== End Notification ===== -->

    <!-- Email Customer Modal -->
    <div id="email-customer-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Email Customer</h3><button onclick="closeModal('email-customer-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="send_admin_email.php" method="POST" class="p-6">
                <input type="hidden" name="recipient_email" value="<?php echo htmlspecialchars($details['cemail']); ?>">
                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($details['sdid']); ?>">
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">To:</label><input type="text" readonly value="<?php echo htmlspecialchars($details['cname']); ?> (<?php echo htmlspecialchars($details['cemail']); ?>)" class="w-full mt-1 p-2 border bg-gray-100 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Subject:</label><input type="text" name="subject" required value="Update on your StitchVerse Request #<?php echo htmlspecialchars($details['sdid']); ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Message:</label><textarea name="message" required rows="6" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" placeholder="Write your message to the customer here..."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeModal('email-customer-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Cancel</button><button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 flex items-center gap-2"><i class="ri-send-plane-fill"></i> Send Email</button></div>
            </form>
        </div>
    </div>

    <!-- Email Tailor Modal -->
    <div id="email-tailor-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Email Tailor</h3><button onclick="closeModal('email-tailor-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="send_admin_email.php" method="POST" class="p-6">
                <input type="hidden" name="recipient_email" value="<?php echo htmlspecialchars($details['temail']); ?>">
                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($details['sdid']); ?>">
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">To:</label><input type="text" readonly value="<?php echo htmlspecialchars($details['tname']); ?> (<?php echo htmlspecialchars($details['temail']); ?>)" class="w-full mt-1 p-2 border bg-gray-100 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Subject:</label><input type="text" name="subject" required value="Regarding Stitch Request #<?php echo htmlspecialchars($details['sdid']); ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Message:</label><textarea name="message" required rows="6" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" placeholder="Write your message to the tailor here..."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeModal('email-tailor-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Cancel</button><button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 flex items-center gap-2"><i class="ri-send-plane-fill"></i> Send Email</button></div>
            </form>
        </div>
    </div>

    <!-- Cancel Request Modal -->
    <div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Cancel Request</h3><button onclick="closeModal('cancel-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="cancel_request_action.php" method="POST" class="p-6">
                <input type="hidden" name="sdid" value="<?php echo htmlspecialchars($details['sdid']); ?>">
                <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($details['cemail']); ?>">
                <input type="hidden" name="tailor_email" value="<?php echo htmlspecialchars($details['temail'] ?? ''); ?>">
                <input type="hidden" name="request_name" value="<?php echo htmlspecialchars($details['sdname']); ?>">
                <p class="text-gray-600 mb-4">You are about to cancel this request. A notification will be sent to the customer and the assigned tailor (if any).</p>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation*</label><textarea name="reason" required rows="4" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Provide a clear reason for cancelling this request..."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeModal('cancel-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Close</button><button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2"><i class="ri-close-circle-line"></i> Confirm & Notify</button></div>
            </form>
        </div>
    </div>

    <!-- ===== FULLY CORRECTED HEADER ===== -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-8">
            <div class="relative">
                <button id="tailor-dropdown-button" data-dropdown-toggle="tailor-dropdown-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Tailors</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="tailor-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="approvedtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Approved Tailors</a>
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
                <button id="stitch-dropdown-button" data-dropdown-toggle="stitch-dropdown-menu" class="dropdown-button text-purple-600 font-semibold flex items-center gap-1">
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
            
            <!-- Reports Dropdown Re-integrated -->
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

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <!-- Mobile nav items would go here -->
        </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-6xl mx-auto mb-4">
            <a href="javascript:history.back()" class="text-sm text-purple-700 font-semibold hover:underline flex items-center gap-2">
                <i class="ri-arrow-left-line"></i> Back to Previous Page
            </a>
        </div>
        
        <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 md:p-8 md:flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-white"><?php echo htmlspecialchars($details['sdname']); ?></h1>
                    <p class="text-purple-100 mt-1">Request #<?php echo htmlspecialchars($details['sdid']); ?></p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="px-4 py-2 text-sm font-bold rounded-full <?php echo getStatusClass($details['sstatus']); ?>">
                        <?php echo htmlspecialchars($details['sstatus']); ?>
                    </span>
                </div>
            </div>

            <div class="p-4 border-b bg-gray-50/50 flex flex-wrap items-center gap-3">
                <button onclick="openModal('email-customer-modal')" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700"><i class="ri-mail-send-line"></i> Email Customer</button>
                <?php if (!empty($details['temail'])): ?>
                    <button onclick="openModal('email-tailor-modal')" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gray-700 rounded-lg hover:bg-gray-800"><i class="ri-mail-send-line"></i> Email Tailor</button>
                <?php endif; ?>
                
                <?php if (!in_array(strtolower($details['sstatus']), ['cancelled', 'rejected', 'shipped'])): ?>
                <button onclick="openModal('cancel-modal')" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-red-600 bg-red-100 rounded-lg hover:bg-red-200 ml-auto"><i class="ri-close-circle-line"></i> Cancel Request</button>
                <?php endif; ?>
            </div>
            
            <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <section>
                        <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Participants</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2"><i class="ri-user-line"></i> Customer Details</h4>
                                <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($details['cname']); ?></p>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($details['cemail']); ?></p>
                                <p class="text-gray-600 text-sm font-mono"><?php echo htmlspecialchars($details['cphone']); ?></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2"><i class="ri-user-star-line"></i> Assigned Tailor</h4>
                                <?php if (!empty($details['tname'])): ?>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($details['tname']); ?></p>
                                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($details['temail']); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">Not yet assigned to a tailor.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    
                    <?php if ($original_design): ?>
                    <section>
                        <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Based on Original Design</h3>
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                            <h4 class="font-bold text-purple-800 text-lg"><?php echo htmlspecialchars($original_design['dname']); ?></h4>
                            <p class="text-sm text-purple-600 mb-2">Designed by: <?php echo htmlspecialchars($original_design['designer_name']); ?></p>
                            <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($original_design['ddesc'])); ?></p>
                            <p class="text-lg font-bold text-purple-800 mt-3">Original Price: ₹<?php echo number_format($original_design['dprice'], 2); ?></p>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($design_options)): ?>
                    <section>
                        <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Custom Design Options</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm bg-gray-50 p-4 rounded-lg border">
                            <?php foreach ($design_options as $key => $value): ?>
                            <div>
                                <label class="block text-gray-500"><?php echo htmlspecialchars($key); ?></label>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($value); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($measurements)): ?>
                    <section>
                        <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Provided Measurements</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm bg-gray-50 p-4 rounded-lg border">
                             <?php foreach ($measurements as $key => $value): ?>
                            <div>
                                <label class="block text-gray-500"><?php echo htmlspecialchars($key); ?></label>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($value); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <section>
                        <h3 class="text-xl font-bold text-gray-800 border-b pb-2 mb-4">Special Instructions</h3>
                        <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg border prose max-w-none">
                            <p><?php echo !empty($details['sinstructions']) ? nl2br(htmlspecialchars($details['sinstructions'])) : '<span class="italic text-gray-500">No special instructions provided.</span>'; ?></p>
                        </div>
                    </section>
                </div>
                
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg border sticky top-24">
                        <h3 class="font-semibold text-gray-700 mb-3 text-lg border-b pb-2">Logistics</h3>
                        <div class="space-y-4 text-sm">
                            <div><label class="block text-gray-500">Requested Delivery Date</label><p class="font-medium text-gray-800 text-base"><?php echo date("d M, Y", strtotime($details['sddate'])); ?></p></div>
                            <div>
                                <label class="block text-gray-500">Quoted Price</label>
                                <?php if (!empty($details['sprice']) && $details['sprice'] > 0): ?>
                                    <p class="font-bold text-purple-600 text-2xl">₹<?php echo number_format($details['sprice'], 2); ?></p>
                                <?php else: ?>
                                    <p class="font-medium text-gray-500 text-base italic">Not yet quoted</p>
                                <?php endif; ?>
                            </div>
                             <div>
                                <label class="block text-gray-500">Submitted On</label>
                                <p class="font-medium text-gray-800 text-base"><?php echo date("d M, Y, h:i A", strtotime($details['submitted_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-3 text-lg">Reference Images</h3>
                        <div class="grid grid-cols-3 gap-2">
                        <?php 
                        $images = !empty($details['simg']) ? explode(',', $details['simg']) : [];
                        if (!empty($images[0])):
                            foreach($images as $img): ?>
                                <a href="<?php echo htmlspecialchars(trim($img)); ?>" target="_blank"><img src="<?php echo htmlspecialchars(trim($img)); ?>" class="w-full h-24 object-cover rounded-lg border hover:opacity-80 transition-opacity"></a>
                            <?php endforeach; 
                        else: ?>
                            <p class="text-sm text-gray-500 col-span-3">No reference images provided.</p>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Modal handling functions
        function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

        document.addEventListener('DOMContentLoaded', () => {
            // Mobile Menu Toggle
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Dropdown Menu Logic for multiple dropdowns
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const wasOpen = !dropdownMenu.classList.contains('hidden');
                    // Hide all other open dropdowns
                    document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => {
                        if (b !== button) {
                            document.getElementById(b.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                            b.setAttribute('aria-expanded', 'false');
                        }
                    });
                    // Toggle the current one
                    if (!wasOpen) {
                        dropdownMenu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        dropdownMenu.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
            // Hide dropdowns when clicking anywhere else on the window
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    document.getElementById(button.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });

            // ===== Toast Notification Logic =====
            const successMessage = '<?php echo $success_message; ?>';
            const errorMessage = '<?php echo $error_message; ?>';
            const toast = document.getElementById('toast-notification');
            const toastMessageEl = document.getElementById('toast-message');
            const toastIconContainer = document.getElementById('toast-icon-container');

            function showToast(message, type) {
                toastMessageEl.textContent = message;
                
                toastIconContainer.innerHTML = '';
                toastIconContainer.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg';

                if (type === 'success') {
                    toastIconContainer.classList.add('text-green-500', 'bg-green-100');
                    toastIconContainer.innerHTML = `<i class="ri-check-double-line text-xl"></i>`;
                } else { // error
                    toastIconContainer.classList.add('text-red-500', 'bg-red-100');
                    toastIconContainer.innerHTML = `<i class="ri-error-warning-line text-xl"></i>`;
                }

                toast.classList.remove('hidden');
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 5000);
            }

            if (successMessage) {
                showToast(successMessage, 'success');
            }

            if (errorMessage) {
                showToast(errorMessage, 'error');
            }
            
            // Manual dismiss
            const dismissButton = toast.querySelector('[data-dismiss-target]');
            if(dismissButton) {
                dismissButton.addEventListener('click', () => {
                    toast.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
