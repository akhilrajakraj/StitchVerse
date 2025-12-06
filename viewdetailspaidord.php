
<?php
session_start();
require_once 'databasecon.php'; // Ensure this path is correct and included once

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if an Order ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: paidorders.php");
    exit();
}

$db = new DatabaseCon();
$order_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- Data Fetching Logic ---
// Fetch the order details, joining all necessary tables for a complete view.
// **MODIFICATION**: Added od.ostatus to the SELECT query to get the order status.
$order_details = null;
$sql = "SELECT
            od.oid, od.orderdate, od.ostatus,
            c.cname, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.city AS customer_city, c.distr AS customer_distr, c.pincode AS customer_pincode,
            u.dname, u.dtype, u.ddesc, u.dprice, u.dimg,
            p.pdate AS payment_date, p.pmode, p.card_name, p.card_no
        FROM
            orderdesign od
        JOIN
            upload u ON od.did = u.did
        JOIN
            creg c ON od.uid = c.cid
        JOIN
            payment p ON od.oid = p.order_id
        WHERE
            od.oid = ? AND u.uid = ? AND p.pstatus = 'Paid'";

$result = $db->selectData($sql, "ii", $order_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $order_details = $result->fetch_assoc();
} else {
    // If no order is found, or it doesn't belong to this tailor, redirect.
    $_SESSION['error_message'] = "Paid order not found or you do not have permission to view it.";
    header("Location: paidorders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paid Order Details #<?php echo htmlspecialchars($order_details['oid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <!-- CANCELLATION MODAL -->
    <div id="cancel-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div id="cancel-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto p-8 transform scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Cancel Paid Order #<?php echo htmlspecialchars($order_details['oid']); ?></h3>
                <button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <p class="text-gray-600 mb-6">You are about to cancel a paid order. This will notify the customer that their order has been cancelled and a refund will be processed. This action cannot be undone.</p>
            
            <form action="order_action.php" method="POST">
                <input type="hidden" name="action" value="cancel_paid_order">
                <input type="hidden" name="oid" value="<?php echo $order_details['oid']; ?>">
                <input type="hidden" name="cname" value="<?php echo htmlspecialchars($order_details['cname']); ?>">
                <input type="hidden" name="cemail" value="<?php echo htmlspecialchars($order_details['customer_email']); ?>">
                <input type="hidden" name="dname" value="<?php echo htmlspecialchars($order_details['dname']); ?>">

                <div class="mb-4">
                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Cancellation</label>
                    <select name="reason" id="cancel_reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="Item is out of stock">Item is out of stock</option>
                        <option value="Unable to fulfill the order at this time">Unable to fulfill the order at this time</option>
                        <option value="Customer requested cancellation">Customer requested cancellation</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="cancel_custom_message" class="block text-sm font-medium text-gray-700 mb-1">Additional Details (Optional)</label>
                    <textarea name="custom_message" id="cancel_custom_message" rows="3" placeholder="Provide more details for the customer..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeCancelModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Back</button>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2">
                        <i class="ri-close-circle-line"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SHIPPING DETAILS MODAL -->
    <div id="shipping-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div id="shipping-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto p-8 transform scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Send Shipping Details</h3>
                <button onclick="closeShippingModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <p class="text-gray-600 mb-6">Enter the shipping information for order #<?php echo htmlspecialchars($order_details['oid']); ?>. An email will be sent to the customer.</p>
            
            <form action="order_action.php" method="POST">
                <input type="hidden" name="action" value="send_shipping_info">
                <input type="hidden" name="oid" value="<?php echo $order_details['oid']; ?>">
                <input type="hidden" name="cname" value="<?php echo htmlspecialchars($order_details['cname']); ?>">
                <input type="hidden" name="cemail" value="<?php echo htmlspecialchars($order_details['customer_email']); ?>">
                <input type="hidden" name="dname" value="<?php echo htmlspecialchars($order_details['dname']); ?>">

                <div class="mb-4">
                    <label for="shipping_provider" class="block text-sm font-medium text-gray-700 mb-1">Shipping Provider *</label>
                    <input type="text" name="shipping_provider" id="shipping_provider" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g., FedEx, UPS, Delhivery">
                </div>

                <div class="mb-6">
                    <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-1">Tracking Number *</label>
                    <input type="text" name="tracking_number" id="tracking_number" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g., 1Z999AA10123456784">
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeShippingModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 flex items-center gap-2">
                        <i class="ri-send-plane-fill"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- === NEW CONSISTENT HEADER === -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-6">
            <!-- Designs Dropdown -->
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

            <!-- Design Orders Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="design-orders-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Design Orders</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="design-orders-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="pendingorderpay.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Payments</a>
                    <a href="paidorders.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Paid Orders</a>
                </div>
            </div>

            <!-- Custom Orders Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="custom-orders-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Custom Orders</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="custom-orders-menu" class="hidden absolute mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailorreq.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">General Requests</a>
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
            <a href="paidorders.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Paid Orders</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-5xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold">Order Details</h1>
                        <p class="text-purple-200 font-mono">#<?php echo htmlspecialchars($order_details['oid']); ?></p>
                    </div>
                    <?php
                        $status = $order_details['ostatus'];
                        $status_color = 'bg-green-500 text-white';
                        $status_text = 'Payment Received';
                        if ($status == 'Shipped') {
                             $status_color = 'bg-blue-500 text-white';
                             $status_text = 'Shipped';
                        }
                    ?>
                    <div class="text-center <?php echo $status_color; ?> font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="ri-checkbox-circle-fill"></i>
                        <span><?php echo $status_text; ?></span>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2">
                <!-- Left Side -->
                <div class="p-8 space-y-8">
                    <!-- Design Details -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Design Details</h2>
                        <div class="flex items-center gap-4">
                            <img src="<?php echo htmlspecialchars($order_details['dimg']); ?>" class="w-24 h-24 rounded-lg object-cover border">
                            <div>
                                <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($order_details['dname']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($order_details['dtype']); ?></p>
                                <p class="text-xl font-bold text-purple-600 mt-2">₹ <?php echo number_format($order_details['dprice']); ?></p>
                            </div>
                        </div>
                    </div>
                    <!-- Customer Details -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Details</h2>
                        <div class="space-y-2 text-sm">
                            <p class="flex items-center gap-3"><i class="ri-user-line text-purple-500 w-4 text-center"></i><span class="font-medium text-gray-800"><?php echo htmlspecialchars($order_details['cname']); ?></span></p>
                            <p class="flex items-center gap-3"><i class="ri-at-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($order_details['customer_email']); ?></span></p>
                            <p class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($order_details['customer_phone']); ?></span></p>
                        </div>
                    </div>
                </div>
                <!-- Right Side -->
                <div class="p-8 bg-gray-50 border-l border-gray-200 flex flex-col">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Shipping Address</h2>
                    <div class="text-sm text-gray-600 leading-relaxed">
                        <p><?php echo htmlspecialchars($order_details['customer_address']); ?></p>
                        <p><?php echo htmlspecialchars($order_details['customer_city']); ?>, <?php echo htmlspecialchars($order_details['customer_distr']); ?></p>
                        <p><?php echo htmlspecialchars($order_details['customer_pincode']); ?></p>
                    </div>
                    <div class="border-t my-8"></div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Summary</h2>
                    <div class="space-y-2 text-sm flex-grow">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Date:</span>
                            <span class="font-medium text-gray-800"><?php echo date("F j, Y, g:i a", strtotime($order_details['payment_date'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium text-gray-800"><?php echo htmlspecialchars($order_details['pmode']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Status:</span>
                            <span class="font-medium text-green-600">Paid</span>
                        </div>
                        <div class="flex justify-between pt-4 border-t mt-4">
                            <span class="text-lg font-bold text-gray-800">Total Amount:</span>
                            <span class="text-lg font-bold text-purple-600">₹ <?php echo number_format($order_details['dprice']); ?></span>
                        </div>
                    </div>
                    <div class="mt-8 pt-6 border-t flex flex-col sm:flex-row gap-4">
                        <!-- **MODIFICATION**: This button will only show if the order is NOT shipped -->
                        <?php if ($order_details['ostatus'] !== 'Shipped'): ?>
                        <button onclick="openShippingModal()" class="w-full font-semibold text-white bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                            <i class="ri-truck-line text-lg"></i> Send Shipping Details
                        </button>
                        <button onclick="openCancelModal()" class="w-full sm:w-auto font-semibold text-red-700 bg-red-100 hover:bg-red-200 px-6 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                            <i class="ri-close-circle-line text-lg"></i> Cancel
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center border-t border-gray-800 pt-8">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // JavaScript for Modals
        function openCancelModal() {
            document.getElementById('cancel-modal').classList.remove('hidden');
            setTimeout(() => { document.getElementById('cancel-modal-content').classList.remove('scale-95', 'opacity-0'); }, 50);
        }
        function closeCancelModal() {
            document.getElementById('cancel-modal-content').classList.add('scale-95', 'opacity-0');
            setTimeout(() => { document.getElementById('cancel-modal').classList.add('hidden'); }, 300);
        }
        function openShippingModal() {
            document.getElementById('shipping-modal').classList.remove('hidden');
            setTimeout(() => { document.getElementById('shipping-modal-content').classList.remove('scale-95', 'opacity-0'); }, 50);
        }
        function closeShippingModal() {
            document.getElementById('shipping-modal-content').classList.add('scale-95', 'opacity-0');
            setTimeout(() => { document.getElementById('shipping-modal').classList.add('hidden'); }, 300);
        }
        
        // Dropdown JS
        document.addEventListener('DOMContentLoaded', function () {
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
