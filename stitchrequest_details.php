
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// --- Get and clear any action messages from the session for pop-up notifications ---
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

// Ensure a valid Request ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Request ID.");
}

$db = new DatabaseCon();
$request_id = $_GET['id'];
$customer_id = $_SESSION['user_id'];

// --- Main Query: Fetch the request and verify it belongs to the logged-in customer ---
$sql_request = "SELECT 
                    sr.*, 
                    t.tname, t.email as tailor_email,
                    c.cname, c.email as customer_email
                FROM stitchreq sr 
                JOIN creg c ON sr.uid = c.cid
                LEFT JOIN treg t ON sr.tid = t.tid 
                WHERE sr.sdid = ? AND sr.uid = ?";
$result_request = $db->selectData($sql_request, "ii", $request_id, $customer_id);

if ($result_request->num_rows === 0) {
    die("Stitch request not found or you do not have permission to view it.");
}
$request = $result_request->fetch_assoc();

// --- Check if feedback has already been submitted for this request ---
$sql_check_feedback = "SELECT fid FROM feedb WHERE uid = ? AND itemid = ? AND feedtype = 'stitch_request'";
$feedback_result = $db->selectData($sql_check_feedback, "ii", $customer_id, $request_id);
$has_submitted_feedback = ($feedback_result && $feedback_result->num_rows > 0);


// --- CANCELLATION LOGIC ---
$show_cancel_button = false;
if (isset($request['submitted_at'])) {
    $submitted_time = new DateTime($request['submitted_at']);
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $interval = $submitted_time->diff($current_time);
    $hours_passed = ($interval->days * 24) + $interval->h;

    // The button only shows if the status is Pending AND it's been less than 12 hours
    if (strtolower($request['sstatus']) === 'pending' && $hours_passed < 12) {
        $show_cancel_button = true;
    }
}

// Fetch customer's name for the header
$customer_name = explode(' ', trim($request['cname']))[0];

// Status timeline configuration
$statuses = ['Pending', 'Accepted', 'Paid', 'Shipped'];
$current_status_text = 'Pending'; // Default
if (in_array(ucfirst(strtolower($request['sstatus'])), $statuses)) {
    $current_status_text = ucfirst(strtolower($request['sstatus']));
}
$current_status_index = array_search($current_status_text, $statuses);
if ($current_status_index === false) $current_status_index = -1; // Not in the main flow (e.g., cancelled)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details #<?php echo htmlspecialchars($request['sdid']); ?> - StitchVerse</title>
    
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
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Cancel Stitch Request</h3>
                <button onclick="closeModal('cancel-modal')" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <form action="cancel_request.php" method="POST" class="p-6">
                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['sdid']); ?>">
                <p class="text-gray-600 mb-4">You are about to cancel your stitch request for "<?php echo htmlspecialchars($request['sdname']); ?>". Please provide a reason.</p>
                <div class="mb-4">
                    <label for="reason_select" class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation*</label>
                    <select id="reason_select" name="reason_preset" class="w-full p-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                        <option value="Submitted by mistake">Submitted by mistake</option>
                        <option value="Found a different tailor">Found a different tailor</option>
                        <option value="Changed my mind about the design">Changed my mind about the design</option>
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
            <a href="stitchingorders.php" class="inline-block mb-4 text-sm font-medium text-purple-600 hover:text-purple-800 transition-colors">
                &larr; Back to All Stitching Orders
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Stitch Request Details</h1>
            <p class="text-gray-600 mt-1">Request ID: #<?php echo htmlspecialchars($request['sdid']); ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Request Summary</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div><p class="text-sm text-gray-500">Request Name</p><p class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['sdname']); ?></p></div>
                    <div><p class="text-sm text-gray-500">Assigned Tailor</p><p class="font-semibold text-gray-800"><?php echo htmlspecialchars($request['tname'] ?? 'Pending Assignment'); ?></p></div>
                    <div><p class="text-sm text-gray-500">Desired by</p><p class="font-semibold text-gray-800"><?php echo date("d F, Y", strtotime($request['sddate'])); ?></p></div>
                    <div><p class="text-sm text-gray-500">Submitted On</p><p class="font-semibold text-gray-800"><?php echo date("d M, Y, g:i a", strtotime($request['submitted_at'])); ?></p></div>
                </div>

                <?php if(!empty($request['simg'])): ?>
                <div class="mt-6"><p class="text-sm font-medium text-gray-800 mb-2">Inspiration Image</p><img src="<?php echo htmlspecialchars($request['simg']); ?>" class="max-w-xs w-full rounded-lg border shadow-sm"></div>
                <?php endif; ?>

                <div class="mt-6"><p class="text-sm font-medium text-gray-800 mb-2">Specifications & Measurements</p><div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-md border whitespace-pre-wrap font-mono"><?php echo htmlspecialchars($request['sdesign_details']); ?></div></div>
                
                <?php if(!empty($request['sinstructions'])): ?>
                <div class="mt-6"><p class="text-sm font-medium text-gray-800 mb-2">Special Instructions</p><div class="text-sm text-gray-700 bg-yellow-50 p-4 rounded-md border border-yellow-200"><?php echo nl2br(htmlspecialchars($request['sinstructions'])); ?></div></div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-md sticky top-28">
                     <h3 class="text-lg font-bold text-gray-800 mb-4">Request Status</h3>
                     <?php if (strtolower($request['sstatus']) == 'cancelled'): ?>
                        <div class="flex items-center gap-4 bg-red-50 p-4 rounded-lg">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-red-100 text-red-600"><i class="ri-close-circle-line text-2xl"></i></div>
                            <p class="font-semibold text-red-700">Request Cancelled</p>
                        </div>
                    <?php elseif (strtolower($request['sstatus']) == 'rejected'): ?>
                        <div class="flex items-center gap-4 bg-yellow-50 p-4 rounded-lg">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 bg-yellow-100 text-yellow-600"><i class="ri-error-warning-line text-2xl"></i></div>
                            <p class="font-semibold text-yellow-700">Request Rejected</p>
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
                    <?php endif; ?>
                     
                     <div class="mt-8 border-t pt-6 text-center">
                        <?php
                        $status = strtolower($request['sstatus']);
                        $price = $request['sprice'];
                        $request_id_action = $request['sdid'];

                        if ($status == 'accepted' && !empty($price)) {
                            echo "<a href='payment1.php?order_id={$request_id_action}' class='w-full block bg-green-600 text-white px-4 py-3 rounded-lg font-bold hover:bg-green-700 transition-colors'>Proceed to Payment (₹" . number_format($price) . ")</a>";
                        } elseif ($status == 'paid' || $status == 'shipped') {
                            echo "<a href='paystr4.php?id={$request_id_action}' class='w-full block bg-blue-600 text-white px-4 py-3 rounded-lg font-bold hover:bg-blue-700 transition-colors'>View Payment Details</a>";
                        } elseif ($status == 'pending') {
                             echo "<div class='text-sm text-center text-gray-600 bg-gray-100 p-3 rounded-md mb-3'>Awaiting tailor's response.</div>";
                             if ($show_cancel_button) {
                                echo "<button onclick=\"openModal('cancel-modal')\" class='w-full block bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition-colors'>Cancel Request</button>";
                                echo "<p class='text-xs text-gray-500 mt-2'>You can cancel within 12 hours of submission.</p>";
                            }
                        }
                        ?>
                     </div>
                </div>
            </div>
        </div>

        <?php if (strtolower($request['sstatus']) === 'shipped'): ?>
        <div class="mt-8">
            <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-md">
                <?php if ($has_submitted_feedback): ?>
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-green-100 mx-auto flex items-center justify-center mb-4"><i class="ri-heart-line text-4xl text-green-600"></i></div>
                        <h2 class="text-2xl font-bold text-gray-800">Thank you!</h2>
                        <p class="text-gray-600 mt-2">You have already submitted feedback for this request.</p>
                    </div>
                <?php else: ?>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Leave Feedback</h2>
                    <p class="text-gray-600 mb-6">Share your thoughts on this custom stitch request. Your feedback helps the tailor and other customers!</p>
                    <form action="feedback_action.php" method="POST">
                        <input type="hidden" name="tid" value="<?php echo htmlspecialchars($request['tid']); ?>">
                        <input type="hidden" name="itemid" value="<?php echo htmlspecialchars($request['sdid']); ?>">
                        <input type="hidden" name="feedtype" value="stitch_request">
                        
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

