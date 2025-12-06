
<?php
session_start();
require_once 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. --- VALIDATE AND GET CUSTOMER ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: activecustomers.php"); // Redirect if ID is missing or invalid
    exit();
}
$customer_id = intval($_GET['id']);
$db = new DatabaseCon();

// 2. --- FETCH ALL DATA RELATED TO THE CUSTOMER ---

// --- Main Customer Details ---
$customer = null;
$sql_customer = "SELECT * FROM creg WHERE cid = ?";
$result_customer = $db->selectData($sql_customer, "i", $customer_id);
if ($result_customer && $result_customer->num_rows > 0) {
    $customer = $result_customer->fetch_assoc();
} else {
    $_SESSION['action_error'] = "Customer not found.";
    header("Location: activecustomers.php");
    exit();
}

// --- Customer Measurements ---
$measurements = null;
$sql_measurements = "SELECT * FROM measurements WHERE uid = ?";
$result_measurements = $db->selectData($sql_measurements, "i", $customer_id);
if ($result_measurements && $result_measurements->num_rows > 0) {
    $measurements = $result_measurements->fetch_assoc();
}

// --- Customer's Design Orders ---
$design_orders = [];
$sql_orders = "SELECT od.orderdate, u.dname, u.dprice, p.pstatus 
               FROM orderdesign od 
               JOIN upload u ON od.did = u.did
               LEFT JOIN payment p ON od.oid = p.order_id AND p.uid = od.uid
               WHERE od.uid = ? ORDER BY od.orderdate DESC";
$result_orders = $db->selectData($sql_orders, "i", $customer_id);
if ($result_orders) {
    while ($row = $result_orders->fetch_assoc()) {
        $design_orders[] = $row;
    }
}

// --- Customer's Stitching Requests ---
$stitch_requests = [];
// CORRECTED QUERY: Use LEFT JOIN to include requests that are not yet assigned to a tailor
$sql_requests = "SELECT sr.sdid, sr.sdname, sr.sprice, sr.sstatus, t.tname 
                 FROM stitchreq sr
                 LEFT JOIN treg t ON sr.tid = t.tid
                 WHERE sr.uid = ? ORDER BY sr.sdid DESC";
$result_requests = $db->selectData($sql_requests, "i", $customer_id);
if ($result_requests) {
    while ($row = $result_requests->fetch_assoc()) {
        $stitch_requests[] = $row;
    }
}

// Function to render status badges
function getStatusBadge($status) {
    $status = strtolower($status ?? 'unknown');
    switch ($status) {
        case 'paid':
        case 'shipped':
        case 'approved':
        case 'accepted':
            return '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        case 'rejected':
        case 'cancelled':
            return '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
        default:
            return '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">' . ucfirst($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Details - <?php echo htmlspecialchars($customer['cname']); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
        #remove-modal.hidden { display: none; }
        #remove-modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- REMOVAL MODAL -->
    <div id="remove-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div id="remove-modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto p-8 transform scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Remove Customer Account</h3>
                <button onclick="closeRemoveModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <p class="text-gray-600 mb-6">You are about to remove <strong class="font-bold"><?php echo htmlspecialchars($customer['cname']); ?></strong>. This will prevent them from logging in. An email will be sent to the customer with the reason for removal.</p>
            
            <form action="customer_action.php" method="POST">
                <input type="hidden" name="action" value="remove_with_reason">
                <input type="hidden" name="cid" value="<?php echo $customer['cid']; ?>">
                <input type="hidden" name="cname" value="<?php echo htmlspecialchars($customer['cname']); ?>">
                <input type="hidden" name="cemail" value="<?php echo htmlspecialchars($customer['email']); ?>">

                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Removal</label>
                    <select name="reason" id="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="Violation of Terms of Service">Violation of Terms of Service</option>
                        <option value="Spam or fraudulent activity">Spam or fraudulent activity</option>
                        <option value="Account security concerns">Account security concerns</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="custom_message" class="block text-sm font-medium text-gray-700 mb-1">Additional Details (Optional)</label>
                    <textarea name="custom_message" id="custom_message" rows="3" placeholder="Provide more details here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeRemoveModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2">
                        <i class="ri-delete-bin-line"></i> Confirm Removal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
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
                <button id="customer-dropdown-button" data-dropdown-toggle="customer-dropdown-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Customers</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="customer-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="activecustomers.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Active Customers</a>
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
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Previous Page</span>
            </a>
        </div>

        <!-- CUSTOMER PROFILE SECTION -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-10">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-grow">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($customer['cname']); ?></h1>
                            <p class="text-gray-500 mt-1">Customer ID: <?php echo $customer['cid']; ?></p>
                        </div>
                        <button onclick="openRemoveModal()" class="bg-red-100 text-red-700 font-semibold px-4 py-2 rounded-lg hover:bg-red-200 text-sm flex items-center gap-2">
                            <i class="ri-user-unfollow-line"></i> Remove Customer
                        </button>
                    </div>
                    
                    <div class="border-t border-gray-200 my-6"></div>
                    
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm mb-8">
                        <div class="flex items-center gap-3"><i class="ri-mail-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($customer['email']); ?></span></div>
                        <div class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($customer['phone']); ?></span></div>
                        <div class="flex items-start gap-3 col-span-full"><i class="ri-map-pin-line text-purple-500 text-xl pt-1"></i><span class="flex-1"><?php echo htmlspecialchars($customer['address'] . ', ' . $customer['city'] . ', ' . $customer['distr'] . ' - ' . $customer['pincode']); ?></span></div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Saved Measurements</h3>
                    <?php if ($measurements): ?>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-x-6 gap-y-4 text-sm">
                            <?php foreach ($measurements as $key => $value): ?>
                                <?php if ($key !== 'mid' && $key !== 'uid' && !empty($value)): ?>
                                    <div>
                                        <p class="text-gray-500 capitalize"><?php echo str_replace('_', ' ', $key); ?></p>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($value); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">This customer has not saved any measurements yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- NEW: Vertically Stacked Layout for Tables -->
        <div class="space-y-12">
            <!-- DESIGN ORDERS -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Design Orders</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                    <th scope="col" class="px-6 py-3">Order Date</th>
                                    <th scope="col" class="px-6 py-3">Design Name</th>
                                    <th scope="col" class="px-6 py-3 text-right">Price</th>
                                    <th scope="col" class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($design_orders)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No design orders found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($design_orders as $order): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4"><?php echo date("M d, Y", strtotime($order['orderdate'])); ?></td>
                                        <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($order['dname']); ?></td>
                                        <td class="px-6 py-4 text-right font-medium">₹<?php echo number_format($order['dprice'], 2); ?></td>
                                        <td class="px-6 py-4 text-center"><?php echo getStatusBadge($order['pstatus'] ?? 'Unpaid'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- STITCHING REQUESTS -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Custom Stitching Requests</h2>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                           <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                               <tr>
                                    <th scope="col" class="px-6 py-3">Request</th>
                                    <th scope="col" class="px-6 py-3">Assigned Tailor</th>
                                    <th scope="col" class="px-6 py-3 text-right">Quoted Price</th>
                                    <th scope="col" class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($stitch_requests)): ?>
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No stitching requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($stitch_requests as $request): ?>
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4">
                                            <a href="view_request_details.php?id=<?php echo $request['sdid']; ?>" class="font-medium text-purple-600 hover:underline"><?php echo htmlspecialchars($request['sdname']); ?></a>
                                        </td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($request['tname'] ?? 'Unassigned'); ?></td>
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
        </div>
    </main>

    <script>
        function openRemoveModal() {
            const modal = document.getElementById('remove-modal');
            const modalContent = document.getElementById('remove-modal-content');
            modal.classList.remove('hidden');
            setTimeout(() => { modalContent.classList.remove('scale-95', 'opacity-0'); }, 50);
        }

        function closeRemoveModal() {
            const modal = document.getElementById('remove-modal');
            const modalContent = document.getElementById('remove-modal-content');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
        const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                        if (otherButton !== button) {
                            const otherMenu = document.getElementById(otherButton.getAttribute('data-dropdown-toggle'));
                            if (otherMenu) {
                                otherMenu.classList.add('hidden');
                                otherButton.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                    dropdownMenu.classList.toggle('hidden');
                    const isExpanded = !dropdownMenu.classList.contains('hidden');
                    button.setAttribute('aria-expanded', isExpanded);
                });
            });

            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    const menu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                    if (menu) {
                        menu.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
    </script>
</body>
</html>
