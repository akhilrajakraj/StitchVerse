
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if a Request ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: personalrequest.php");
    exit();
}

$db = new DatabaseCon();
$request_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

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
            sr.sdid = ? AND sr.tid = ? AND sr.sstatus = 'Pending'";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
    if (!empty($request_details['sdesign_details'])) {
        $design_specifics = json_decode($request_details['sdesign_details'], true);
    }
} else {
    $_SESSION['action_error'] = "This request is no longer available or does not exist.";
    header("Location: personalrequest.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Request Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
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

    <!-- Accept & Email Modal -->
    <div id="accept-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Accept Request & Notify Customer</h3><button onclick="closeModal('accept-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="personal_request_action.php" method="POST" class="p-6">
                <input type="hidden" name="action" value="accept_request">
                <input type="hidden" name="sdid" value="<?php echo htmlspecialchars($request_details['sdid']); ?>">
                <p class="text-sm text-gray-600 mb-4">You can send an optional message to the customer along with the standard acceptance notification.</p>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">To:</label><input type="text" readonly value="<?php echo htmlspecialchars($request_details['cname']); ?> (<?php echo htmlspecialchars($request_details['customer_email']); ?>)" class="w-full mt-1 p-2 border bg-gray-100 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Subject:</label><input type="text" name="subject" value="Regarding your Stitch Request #<?php echo htmlspecialchars($request_details['sdid']); ?>" class="w-full mt-1 p-2 border border-gray-300 rounded-lg"></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700">Message (Optional):</label><textarea name="message" rows="5" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" placeholder="e.g., I have reviewed the details and am excited to start working on your outfit! I will send an update soon."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeModal('accept-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Cancel</button><button type="submit" class="px-8 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 flex items-center gap-2"><i class="ri-send-plane-fill"></i> Accept & Send</button></div>
            </form>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejection-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 opacity-0">
        <div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform -translate-y-10">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <h2 class="text-xl font-bold text-gray-800">Reject Request</h2>
                    <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
                </div>
                <p class="text-sm text-gray-600 mt-2">The customer will be notified of this rejection and your reason.</p>
                <form action="personal_request_action.php" method="POST" class="mt-4">
                    <input type="hidden" name="action" value="reject_request">
                    <input type="hidden" name="sdid" value="<?php echo htmlspecialchars($request_details['sdid']); ?>">
                    <div>
                        <label for="rejection_reason" class="sr-only">Rejection Reason</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., I am currently unavailable, I don't have the required fabric, etc." required></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" id="cancel-rejection-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Send Rejection</button>
                    </div>
                </form>
            </div>
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
                        <a href="personalrequest.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Personal Requests</a>
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
            <a href="personalrequest.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Personal Requests</span>
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
                        Awaiting Your Response
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
                            <p class="text-sm text-gray-500">This customer has not saved their measurements yet. You may need to contact them directly.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <p class="text-sm text-gray-600 mr-auto">Please respond to the customer's request.</p>
                
                <button id="reject-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-close-circle-line text-lg"></i> Reject
                </button>

                <button type="button" onclick="openModal('accept-modal')" class="w-full md:w-auto font-semibold text-white bg-green-600 hover:bg-green-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-checkbox-circle-line text-lg"></i> Accept
                </button>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        window.openModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                const container = modal.querySelector('.modal-container');
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    if (container) container.classList.remove('-translate-y-10');
                }, 10);
            }
        }
        
        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                const container = modal.querySelector('.modal-container');
                modal.classList.add('opacity-0');
                if (container) container.classList.add('-translate-y-10');
                setTimeout(() => { modal.classList.add('hidden'); }, 300);
            }
        }

        const rejectBtn = document.getElementById('reject-btn');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const cancelRejectionBtn = document.getElementById('cancel-rejection-btn');
        if (rejectBtn) rejectBtn.addEventListener('click', () => openModal('rejection-modal'));
        if (closeModalBtn) closeModalBtn.addEventListener('click', () => closeModal('rejection-modal'));
        if (cancelRejectionBtn) cancelRejectionBtn.addEventListener('click', () => closeModal('rejection-modal'));
        document.getElementById('rejection-modal').addEventListener('click', (e) => {
            if (e.target.id === 'rejection-modal') {
                closeModal('rejection-modal');
            }
        });
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
    });
    </script>
</body>
</html>

