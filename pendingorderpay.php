
<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

// --- NEW: Get and clear any action messages from the session for the notification ---
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

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];

// --- Data Fetching Logic ---
// This query finds all orders for the logged-in tailor's designs
// that DO NOT have a corresponding 'Paid' entry in the payment table.
$pending_orders = [];
$sql = "SELECT
            od.oid,
            od.orderdate,
            c.cname,
            c.phone,
            u.dname,
            u.dprice
        FROM
            orderdesign od
        JOIN
            upload u ON od.did = u.did
        JOIN
            creg c ON od.uid = c.cid
        LEFT JOIN
            payment p ON od.oid = p.order_id AND p.pstatus = 'Paid'
        WHERE
            u.uid = ? AND p.pid IS NULL
        ORDER BY
            od.orderdate DESC";

$result = $db->selectData($sql, "i", $tailor_id);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pending_orders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments - StitchVerse</title>
    
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
      <!-- === HEADER: WITH ACTIVE STATE FOR 'PAID ORDERS' === -->
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
                    <a href="pendingorderpay.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Pending Payments</a>
                    <a href="paidorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Paid Orders</a>
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
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                                <i class="ri-time-line text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Pending Payments</h1>
                                <p class="text-purple-100 mt-1">Orders awaiting payment from customers.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                       <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                           <tr>
                                <th scope="col" class="px-6 py-3">Order ID</th>
                                <th scope="col" class="px-6 py-3">Order Date</th>
                                <th scope="col" class="px-6 py-3">Customer</th>
                                <th scope="col" class="px-6 py-3">Design Name</th>
                                <th scope="col" class="px-6 py-3">Amount</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pending_orders)): ?>
                                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No orders are currently awaiting payment.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pending_orders as $order): ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-mono text-gray-500">#<?php echo htmlspecialchars($order['oid']); ?></td>
                                    <td class="px-6 py-4"><?php echo date("M d, Y", strtotime($order['orderdate'])); ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($order['cname']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($order['phone']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($order['dname']); ?></td>
                                    <td class="px-6 py-4 font-bold text-purple-600">₹ <?php echo number_format($order['dprice']); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="viewdetailspendingord.php?id=<?php echo $order['oid']; ?>" class="font-medium text-purple-600 hover:text-purple-800 text-xs inline-flex items-center gap-1">
                                            <i class="ri-search-eye-line"></i>
                                            <span>View Details</span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Menu Toggle
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if(menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
            }

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

            // NEW: Toast Notification Logic (from personalrequest.php)
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