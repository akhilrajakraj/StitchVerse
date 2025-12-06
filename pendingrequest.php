<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

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

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];

// --- Data Fetching Logic ---
// MODIFIED: This query now correctly fetches requests that are 'Accepted' 
// AND have a price set, which are the ones truly awaiting payment.
$pending_payment_requests = [];
$sql = "SELECT
            sr.sdid,
            sr.sddate,
            sr.sdname,
            sr.sdtype,
            sr.sprice,
            c.cname,
            c.phone
        FROM
            stitchreq sr
        JOIN
            creg c ON sr.uid = c.cid
        WHERE
            sr.tid = ? 
            AND sr.sstatus = 'Accepted' 
            AND sr.sprice IS NOT NULL AND sr.sprice > 0
        ORDER BY
            sr.sddate ASC";

$result = $db->selectData($sql, "i", $tailor_id);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pending_payment_requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests Awaiting Payment - StitchVerse</title>
    
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
        /* [NEW] A class for the arrow rotation to be controlled by JS */
        .arrow-rotated {
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
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                                <i class="ri-time-line text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Requests Awaiting Payment</h1>
                                <p class="text-purple-100 mt-1">Jobs you've accepted and quoted a price for.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                       <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                           <tr>
                               <th scope="col" class="px-6 py-3">Request ID</th>
                               <th scope="col" class="px-6 py-3">Desired Date</th>
                               <th scope="col" class="px-6 py-3">Customer</th>
                               <th scope="col" class="px-6 py-3">Request Name</th>
                               <th scope="col" class="px-6 py-3">Type</th>
                               <th scope="col" class="px-6 py-3">Quoted Price</th>
                               <th scope="col" class="px-6 py-3 text-center">Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php if (empty($pending_payment_requests)): ?>
                               <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">You have no requests awaiting payment at this time.</td></tr>
                           <?php else: ?>
                               <?php foreach ($pending_payment_requests as $request): ?>
                               <tr class="bg-white border-b hover:bg-gray-50">
                                   <td class="px-6 py-4 font-mono text-gray-500">#<?php echo htmlspecialchars($request['sdid']); ?></td>
                                   <td class="px-6 py-4 font-medium text-red-600"><?php echo date("M d, Y", strtotime($request['sddate'])); ?></td>
                                   <td class="px-6 py-4">
                                       <div class="font-medium text-gray-800"><?php echo htmlspecialchars($request['cname']); ?></div>
                                       <div class="text-gray-500"><?php echo htmlspecialchars($request['phone']); ?></div>
                                   </td>
                                   <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($request['sdname']); ?></td>
                                   <td class="px-6 py-4"><?php echo htmlspecialchars($request['sdtype']); ?></td>
                                   <td class="px-6 py-4 font-semibold text-gray-800">
                                       <?php echo '₹' . htmlspecialchars(number_format($request['sprice'], 2)); ?>
                                   </td>
                                   <td class="px-6 py-4 text-center">
                                       <a href="viewpendpay.php?id=<?php echo $request['sdid']; ?>" class="font-medium text-purple-600 hover:text-purple-800 text-xs inline-flex items-center gap-1">
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
            
            // --- Logic for Dropdown Menus in Header (with working arrow) ---
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

                const closeButton = toast.querySelector('[data-dismiss-target]');
                if(closeButton) {
                    closeButton.addEventListener('click', () => {
                        toast.classList.add('hidden');
                    });
                }
            }
            // --- End Toast Notification Javascript ---
        });
    </script>
</body>
</html>