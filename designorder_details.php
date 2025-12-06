
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
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


// Ensure a valid Order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID.");
}

$db = new DatabaseCon();
$order_id = $_GET['id'];
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// --- Main Query: Fetch all details for the page ---
$sql_order = "SELECT 
                    o.oid, o.orderdate, o.ostatus, o.ordered_at,
                    u.did, u.dname, u.dimg, u.dprice, u.ddesc,
                    t.tid, t.tname, t.email AS tailor_email,
                    c.cname, c.email AS customer_email, c.address, c.city, c.distr, c.pincode
                FROM orderdesign o
                JOIN upload u ON o.did = u.did
                JOIN treg t ON u.uid = t.tid
                JOIN creg c ON o.uid = c.cid
                WHERE o.oid = ? AND o.uid = ?";
$result_order = $db->selectData($sql_order, "ii", $order_id, $customer_id);

if ($result_order->num_rows === 0) {
    die("Order not found or you do not have permission to view it.");
}
$order = $result_order->fetch_assoc();

// --- MODIFIED: Check feedback based on Design ID (did) and 'design_purchase' type to match feedback_action.php ---
$sql_check_feedback = "SELECT fid FROM feedb WHERE uid = ? AND itemid = ? AND feedtype = 'design_purchase'";
$feedback_result = $db->selectData($sql_check_feedback, "ii", $customer_id, $order['did']);
$has_submitted_feedback = ($feedback_result && $feedback_result->num_rows > 0);


// --- CANCELLATION LOGIC ---
$show_cancel_button = false;
$is_cancellable = !in_array(strtolower($order['ostatus']), ['paid', 'shipped', 'cancelled']);
if (isset($order['ordered_at'])) {
    $submitted_time = new DateTime($order['ordered_at']);
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $interval = $submitted_time->diff($current_time);
    $hours_passed = ($interval->days * 24) + $interval->h;
    if ($is_cancellable && $hours_passed < 12) {
        $show_cancel_button = true;
    }
}

// Fetch the logged-in customer's name for the header
$customer_name = explode(' ', trim($order['cname']))[0];

// Status timeline configuration
$statuses = ['Payment Pending', 'Paid', 'Shipped'];
$current_status_text = 'Payment Pending'; // Default
if (in_array(strtolower($order['ostatus']), ['paid', 'shipped', 'cancelled'])) {
    $current_status_text = ucfirst(strtolower($order['ostatus']));
}
$current_status_index = array_search($current_status_text, $statuses);
if ($current_status_index === false) $current_status_index = -1; // Not in the main flow

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo htmlspecialchars($order['oid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .star-rating label { cursor: pointer; color: #d1d5db; transition: color 0.2s; }
        .star-rating input:checked ~ label,
        .star-rating:not(:checked) > label:hover,
        .star-rating:not(:checked) > label:hover ~ label { color: #f59e0b; }
        .star-rating input { display: none; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- ===== Cancellation Modal ===== -->
    <div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto transform transition-all" id="cancel-modal-content">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Cancel Order</h3><button onclick="closeModal('cancel-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="cancel_order.php" method="POST" class="p-6">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['oid']); ?>">
                <p class="text-gray-600 mb-4">You are about to cancel your order for "<?php echo htmlspecialchars($order['dname']); ?>". Please provide a reason.</p>
                <div class="mb-4">
                    <label for="reason_select" class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation*</label>
                    <select id="reason_select" name="reason_preset" class="w-full p-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                        <option value="Ordered by mistake">Ordered by mistake</option>
                        <option value="Found a different item">Found a different item</option>
                        <option value="Changed my mind">Changed my mind</option>
                        <option value="Other">Other (please specify below)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="reason_text" class="block text-sm font-medium text-gray-700 mb-2">Additional Details (Optional)</label>
                    <textarea name="reason_text" id="reason_text" rows="3" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="If you selected 'Other', please explain here..."></textarea>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button" onclick="closeModal('cancel-modal')" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Close</button>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2"><i class="ri-close-circle-line"></i> Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ===== End Cancellation Modal ===== -->

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
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600 transition-colors">Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600 transition-colors">Tailors</a>
            <a href="customreq1.php" class="text-gray-700 hover:text-purple-600 transition-colors">Stitch Request</a>
          </nav>
          <div class="hidden md:flex items-center space-x-6">
            <div class="relative" id="profile-dropdown-container">
              <button id="profile-dropdown-button" class="flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                 <div class="px-4 py-3 border-b border-gray-100"><p class="text-sm text-gray-500">Signed in as</p><p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p></div>
                 <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a></div>
                 <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-8">
            <a href="designorders.php" class="inline-block mb-4 text-sm font-medium text-purple-600 hover:text-purple-800 transition-colors">
                &larr; Back to All Design Orders
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Design Order Details</h1>
            <p class="text-gray-600 mt-1">Order ID: #<?php echo htmlspecialchars($order['oid']); ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Order Summary</h2>
                <div class="flex flex-col sm:flex-row gap-6">
                    <img src="<?php echo htmlspecialchars($order['dimg']); ?>" alt="<?php echo htmlspecialchars($order['dname']); ?>" class="w-full sm:w-1/3 h-auto object-cover rounded-lg border shadow-sm">
                    <div class="flex-grow">
                        <p class="text-sm text-gray-500">Sold by</p><p class="font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($order['tname']); ?></p>
                        <p class="text-sm text-gray-500">Design</p><p class="font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($order['dname']); ?></p>
                        <p class="text-sm text-gray-500">Order Date</p><p class="font-semibold text-gray-800"><?php echo date("d F, Y, g:i a", strtotime($order['ordered_at'])); ?></p>
                    </div>
                </div>
                <div class="mt-6 border-t pt-6"><h3 class="text-lg font-bold text-gray-800 mb-4">Shipping Address</h3><p class="text-gray-700 leading-relaxed"><?php echo htmlspecialchars($order['address']); ?><br><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['distr']); ?><br>Pincode: <?php echo htmlspecialchars($order['pincode']); ?></p></div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-md sticky top-28">
                     <h3 class="text-lg font-bold text-gray-800 mb-4">Order Status</h3>
                     <?php if (strtolower($order['ostatus']) == 'cancelled'): ?>
                        <div class="flex items-center gap-4 bg-red-50 p-4 rounded-lg">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100 text-red-600"><i class="ri-close-circle-line text-2xl"></i></div>
                            <p class="font-semibold text-red-700">Order Cancelled</p>
                        </div>
                    <?php else: ?>
                     <div class="space-y-4">
                        <?php foreach($statuses as $index => $status): ?>
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 <?php echo ($index <= $current_status_index) ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-500'; ?>"><i class="ri-<?php echo ($index <= $current_status_index) ? 'check-line' : 'loader-5-line'; ?>"></i></div>
                            <p class="font-semibold <?php echo ($index <= $current_status_index) ? 'text-gray-800' : 'text-gray-400'; ?>"><?php echo $status; ?></p>
                        </div>
                        <?php if ($index < count($statuses) - 1): ?><div class="h-6 w-px bg-gray-200 ml-4"></div><?php endif; ?>
                        <?php endforeach; ?>
                     </div>
                     <div class="mt-8 border-t pt-6 text-center">
                        <?php if (in_array(strtolower($order['ostatus']), ['paid', 'shipped'])): ?>
                             <a href="paystr2.php?id=<?php echo $order['oid']; ?>" class="w-full block bg-blue-600 text-white px-4 py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors">View Payment Details</a>
                        <?php else: ?>
                            <a href="payment2.php?order_id=<?php echo $order['oid']; ?>" class="w-full block bg-green-600 text-white px-4 py-3 rounded-lg font-bold hover:bg-green-700 transition-colors">Proceed to Payment (₹<?php echo number_format($order['dprice']); ?>)</a>
                            <?php if ($show_cancel_button): ?>
                                <button onclick="openModal('cancel-modal')" class="block w-full mt-3 text-sm text-red-600 hover:text-red-800 font-semibold">Cancel Order</button>
                                <p class="text-xs text-gray-500 mt-1">You can cancel within 12 hours.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                     </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Feedback Section -->
        <?php if (strtolower($order['ostatus']) === 'shipped'): ?>
        <div class="mt-8">
            <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md">
                <?php if ($has_submitted_feedback): ?>
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-green-100 mx-auto flex items-center justify-center mb-4"><i class="ri-heart-line text-4xl text-green-600"></i></div>
                        <h2 class="text-2xl font-bold text-gray-800">Thank you!</h2>
                        <p class="text-gray-600 mt-2">You have already submitted feedback for this design.</p>
                    </div>
                <?php else: ?>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Leave Feedback</h2>
                    <p class="text-gray-600 mb-6">Share your thoughts on this design. Your feedback helps the tailor and other customers!</p>
                    <form action="feedback_action.php" method="POST">
                        <!-- MODIFIED: Inputs now match feedback_action.php requirements -->
                        <input type="hidden" name="tid" value="<?php echo htmlspecialchars($order['tid']); ?>">
                        <input type="hidden" name="oid" value="<?php echo htmlspecialchars($order['oid']); ?>">
                        <input type="hidden" name="did" value="<?php echo htmlspecialchars($order['did']); ?>">
                        <input type="hidden" name="feedtype" value="design_purchase">
                        
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Your Rating</label>
                            <div class="star-rating flex flex-row-reverse justify-end text-3xl mb-4">
                                <input type="radio" id="star5" name="rate" value="5" required/><label for="star5" title="5 stars"><i class="ri-star-fill"></i></label>
                                <input type="radio" id="star4" name="rate" value="4" /><label for="star4" title="4 stars"><i class="ri-star-fill"></i></label>
                                <input type="radio" id="star3" name="rate" value="3" /><label for="star3" title="3 stars"><i class="ri-star-fill"></i></label>
                                <input type="radio" id="star2" name="rate" value="2" /><label for="star2" title="2 stars"><i class="ri-star-fill"></i></label>
                                <input type="radio" id="star1" name="rate" value="1" /><label for="star1" title="1 star"><i class="ri-star-fill"></i></label>
                            </div>
                        </div>

                        <div>
                            <label for="feedback_text" class="block mb-2 text-sm font-medium text-gray-700">Your Review</label>
                            <textarea name="feedbck" id="feedback_text" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Describe your experience with the tailor and the final product..." required></textarea>
                        </div>

                        <div class="mt-6 text-right">
                            <button type="submit" class="px-8 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow-md transform hover:-translate-y-0.5 flex items-center gap-2 ml-auto">
                                <i class="ri-send-plane-2-line"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>
    <footer class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <a href="customerhome.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
                    <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide for custom clothing that fits perfectly.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Customers</h4>
                    <ul class="space-y-2">
                        <li><a href="viewdesigns.php" class="text-gray-400 hover:text-white">Browse Gallery</a></li>
                        <li><a href="cviewt.php" class="text-gray-400 hover:text-white">Find Tailors</a></li>
                        <li><a href="customreq1.php" class="text-gray-400 hover:text-white">Place Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">My Account</h4>
                    <ul class="space-y-2">
                        <li><a href="cupdate.php" class="text-gray-400 hover:text-white">My Profile</a></li>
                        <li><a href="meas.php" class="text-gray-400 hover:text-white">Measurements</a></li>
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Logout</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
    // ===== Modal and Notification Javascript =====
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const profileDropdownButton = document.getElementById('profile-dropdown-button');
        const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
        
        // Header Dropdown Logic
        if (profileDropdownButton && profileDropdownMenu) {
            profileDropdownButton.addEventListener('click', (event) => {
                event.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
            });
            window.addEventListener('click', (event) => {
                if (!profileDropdownButton.contains(event.target)) {
                    profileDropdownMenu.classList.add('hidden');
                }
            });
        }
        
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

