
<?php
session_start();
require 'databasecon.php'; 

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

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if a Request ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tailorreq.php");
    exit();
}

$db = new DatabaseCon();
$request_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- Data Fetching Logic ---
$request_details = null;
$sql = "SELECT
            sr.*,
            c.cname, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.city AS customer_city, c.distr AS customer_distr, c.pincode AS customer_pincode
        FROM
            stitchreq sr
        JOIN
            creg c ON sr.uid = c.cid
        WHERE
            sr.sdid = ? AND (sr.tid = 0 OR sr.tid = ?) AND sr.sstatus = 'Pending'";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
} else {
    $_SESSION['action_error'] = "This request is no longer available.";
    header("Location: tailorreq.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- ===== Email & Accept Modal ===== -->
    <div id="email-accept-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Accept Request & Notify Customer</h3><button onclick="closeModal('email-accept-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="request_action.php" method="POST" class="p-6">
                <input type="hidden" name="action" value="accept_request">
                <input type="hidden" name="sdid" value="<?php echo htmlspecialchars($request_details['sdid']); ?>">
                <p class="text-sm text-gray-600 mb-4">You can send an optional message to the customer along with the standard acceptance notification.</p>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">To:</label><input type="text" readonly value="<?php echo htmlspecialchars($request_details['cname']); ?> (<?php echo htmlspecialchars($request_details['customer_email']); ?>)" class="w-full mt-1 p-2 border bg-gray-100 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Subject:</label><input type="text" name="subject" value="Regarding your Stitch Request #<?php echo htmlspecialchars($request_details['sdid']); ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Message (Optional):</label><textarea name="message" rows="5" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" placeholder="e.g., I have reviewed the details and am excited to start working on your outfit! I will send an update soon."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeModal('email-accept-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Cancel</button><button type="submit" class="px-8 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 flex items-center gap-2"><i class="ri-send-plane-fill"></i> Accept & Send</button></div>
            </form>
        </div>
    </div>
    <!-- ===== End Modal ===== -->

    <!-- ===== Pop-up Notification Structure ===== -->
    <div id="toast-notification" class="hidden fixed top-5 right-5 z-[150] w-full max-w-xs p-4 text-gray-700 bg-white rounded-2xl shadow-xl" role="alert">
        <div class="flex items-center">
            <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"></div>
            <div id="toast-message" class="ms-3 text-sm font-semibold"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100" data-dismiss-target="#toast-notification" aria-label="Close">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
    </div>
    <!-- ===== End Notification ===== -->


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
                          <a href="tailorreq.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">General Requests</a>
                          <a href="personalrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Personal Requests</a>
                          <div class="my-1 border-t border-gray-100"></div>
                          <a href="pendingrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Requests</a>
                          <a href="acceptedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Accepted Requests</a>
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
            <a href="tailorreq.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to General Requests</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($request_details['sdname']); ?></h1>
                        <p class="text-purple-200 font-mono">Request ID: #<?php echo htmlspecialchars($request_details['sdid']); ?></p>
                    </div>
                    <div class="text-center bg-yellow-400 text-yellow-900 font-bold px-4 py-2 rounded-lg">
                        Awaiting Acceptance
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-t-shirt-line text-purple-600"></i>Core Specifications</h2>
                        <div class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm bg-gray-50 p-4 rounded-lg border">
                            <p><strong class="text-gray-500">Dress Type:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sdtype']); ?></span></p>
                            <p><strong class="text-gray-500">Fabric:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sfabric']); ?></span></p>
                            <p><strong class="text-gray-500">Color:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['scolor'] ?? 'N/A'); ?></span></p>
                            <p><strong class="text-gray-500">Pattern:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['spattern'] ?? 'N/A'); ?></span></p>
                            <p class="sm:col-span-2"><strong class="text-gray-500">Desired Delivery Date:</strong><br><span class="font-bold text-red-600"><?php echo date("F j, Y", strtotime($request_details['sddate'])); ?></span></p>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-ruler-2-line text-purple-600"></i>Customer's Custom Choices & Measurements</h2>
                        <div class="space-y-2 text-sm">
                            <?php
                            $details_lines = explode("\n", trim($request_details['sdesign_details']));
                            foreach ($details_lines as $line):
                                $line = trim($line);
                                if (strpos($line, '---') !== false):
                            ?>
                                    <h3 class="text-md font-bold text-gray-700 pt-4 mt-4 border-t"><?php echo htmlspecialchars(str_replace('-', '', $line)); ?></h3>
                            <?php
                                elseif (strpos($line, ':') !== false):
                                    list($key, $value) = array_map('trim', explode(':', $line, 2));
                            ?>
                                    <div class="grid grid-cols-3 gap-4">
                                        <strong class="text-gray-500 col-span-1"><?php echo htmlspecialchars($key); ?>:</strong>
                                        <span class="font-medium text-gray-800 col-span-2"><?php echo htmlspecialchars($value); ?></span>
                                    </div>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                        <?php if(!empty($request_details['sinstructions']) || !empty($request_details['simg'])): ?>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-edit-2-line text-purple-600"></i>Additional Notes & Inspiration</h2>
                             <?php if(!empty($request_details['sinstructions'])): ?>
                                <div class="mb-4">
                                    <strong class="text-gray-500 text-sm">Special Instructions:</strong>
                                    <p class="font-medium text-gray-800 mt-1 p-3 bg-yellow-50 rounded-lg border border-yellow-200 text-sm"><?php echo nl2br(htmlspecialchars($request_details['sinstructions'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($request_details['simg'])): ?>
                                <div>
                                    <strong class="text-gray-500 text-sm">Reference Image:</strong>
                                    <div class="mt-2"><img src="<?php echo htmlspecialchars($request_details['simg']); ?>" class="max-w-xs w-full rounded-lg border shadow-sm"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Details</h2>
                        <div class="space-y-3 text-sm">
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
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <p class="text-sm text-gray-600 mr-auto">Review the details carefully before accepting this request.</p>
                <button type="button" onclick="openModal('email-accept-modal')" class="w-full md:w-auto font-semibold text-white bg-green-600 hover:bg-green-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-checkbox-circle-line text-lg"></i> Accept This Request
                </button>
            </div>
        </div>
    </main>

    <script>
    // ===== Modal and Notification Javascript =====
    function openModal(modalId) { document.getElementById(modalId).classList.remove('hidden'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

    document.addEventListener('DOMContentLoaded', function () {
        // Dropdown Menu Logic
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

        // Toast Notification Logic
        const successMessage = '<?php echo $success_message; ?>';
        const errorMessage = '<?php echo $error_message; ?>';
        const toast = document.getElementById('toast-notification');
        
        if (toast) {
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
                setTimeout(() => { toast.classList.add('hidden'); }, 5000);
            }

            if (successMessage) showToast(successMessage, 'success');
            if (errorMessage) showToast(errorMessage, 'error');
            
            const dismissButton = toast.querySelector('[data-dismiss-target]');
            if(dismissButton) {
                dismissButton.addEventListener('click', () => { toast.classList.add('hidden'); });
            }
        }
    });
    </script>
</body>
</html>

