<?php
session_start();
require_once 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check for success or error messages from a previous action
$success_message = '';
if (isset($_SESSION['action_success'])) {
    $success_message = $_SESSION['action_success'];
    unset($_SESSION['action_success']); // Clear the message after retrieving it
}
$error_message = '';
if (isset($_SESSION['action_error'])) {
    $error_message = $_SESSION['action_error'];
    unset($_SESSION['action_error']); // Clear the message
}


$db = new DatabaseCon();

// --- Data Fetching Logic ---
// We query the database to get ONLY tailors with the status 'Rejected'.
$rejected_tailors = [];
$sql = "SELECT tid, tname, email, city, distri, phone FROM treg WHERE status = 'Rejected' ORDER BY tid DESC";
$result = $db->selectData($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rejected_tailors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Applications - StitchVerse Admin</title>
    
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
        /* Styles for the notification pop-up */
        #action-notification {
            transform: translateX(100%);
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- ACTION NOTIFICATION POP-UP -->
    <div id="action-notification" class="hidden fixed top-20 right-5 z-[100] p-4 rounded-lg shadow-lg text-white max-w-sm">
        <div class="flex items-center">
            <i id="notification-icon" class="text-2xl mr-3"></i>
            <span id="notification-message" class="font-medium"></span>
            <button onclick="closeNotification()" class="ml-auto -mr-1 p-1"><i class="ri-close-line text-xl"></i></button>
        </div>
    </div>

    <!-- === HEADER WITH ACTIVE STATE FOR 'REJECTED' === -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-8">
            <!-- Tailors Dropdown -->
            <div class="relative">
                <button id="tailor-dropdown-button" data-dropdown-toggle="tailor-dropdown-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Tailors</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="tailor-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="approvedtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Approved Tailors</a>
                    <a href="pendingtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Tailors</a>
                    <a href="rejectedtailors.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Rejected Tailors</a>
                    <a href="removedtailors.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Removed Tailors</a>
                </div>
            </div>

            <!-- Customers Dropdown -->
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

            <!-- MODIFIED: Designs link (no dropdown) -->
            <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Designs</a>
            
            <!-- Stitch Requests Dropdown -->
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
        <!-- Page Title and Stat Card -->
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Rejected Tailor Applications</h1>
                <p class="text-gray-500 mt-1">A historical log of all rejected applications.</p>
            </div>
            <div class="bg-gradient-to-br from-red-100 to-red-200 p-4 rounded-xl text-center shrink-0">
                <div class="text-3xl font-bold text-red-700"><?php echo count($rejected_tailors); ?></div>
                <div class="text-red-700 text-sm font-medium">Rejected Applications</div>
            </div>
        </div>

        <!-- Rejected Tailors Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                   <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Applicant Name</th>
                            <th scope="col" class="px-6 py-3">Contact Information</th>
                            <th scope="col" class="px-6 py-3">Location</th>
                            <th scope="col" class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rejected_tailors)): ?>
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No rejected applications found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rejected_tailors as $tailor): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-900"><?php echo htmlspecialchars($tailor['tname']); ?></td>
                                <td class="px-6 py-4">
                                    <div><?php echo htmlspecialchars($tailor['email']); ?></div>
                                    <div class="text-gray-500 font-mono"><?php echo htmlspecialchars($tailor['phone']); ?></div>
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($tailor['city']) . ", " . htmlspecialchars($tailor['distri']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="viewrejectedtailor.php?id=<?php echo $tailor['tid']; ?>" class="font-medium text-purple-600 hover:text-purple-800 text-xs inline-flex items-center gap-1">
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
    </main>

    <footer class="bg-gray-800 text-white py-12 mt-20">
       <div class="container mx-auto px-6 text-center">
         <a href="adminhomepage.php" class="text-2xl font-bold text-purple-400 mb-2 inline-block font-pacifico">StitchVerse</a>
         <p class="text-gray-500 text-sm">Administration Panel | © <?php echo date("Y"); ?> All Rights Reserved.</p>
       </div>
    </footer>

    <script>
        // --- Notification Pop-up Script ---
        let notificationTimeout;

        function showNotification() {
            const notification = document.getElementById('action-notification');
            if (notification.classList.contains('hidden')) return;

            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Auto-hide after 5 seconds
            notificationTimeout = setTimeout(() => {
                closeNotification();
            }, 5000);
        }

        function closeNotification() {
            const notification = document.getElementById('action-notification');
            if (!notification) return;
            
            clearTimeout(notificationTimeout); // Prevent auto-hide if closed manually
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.classList.add('hidden');
            }, 500); // Wait for transition to finish
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Check for PHP-injected messages and show notification
            const notification = document.getElementById('action-notification');
            const notificationMessage = document.getElementById('notification-message');
            const notificationIcon = document.getElementById('notification-icon');
            
            const successMessage = "<?php echo $success_message; ?>";
            const errorMessage = "<?php echo $error_message; ?>";

            if (successMessage) {
                notificationMessage.textContent = successMessage;
                notificationIcon.className = 'ri-checkbox-circle-fill text-2xl mr-3';
                notification.classList.remove('hidden', 'bg-red-500');
                notification.classList.add('bg-green-500');
                showNotification();
            } else if (errorMessage) {
                notificationMessage.textContent = errorMessage;
                notificationIcon.className = 'ri-error-warning-fill text-2xl mr-3';
                notification.classList.remove('hidden', 'bg-green-500');
                notification.classList.add('bg-red-500');
                showNotification();
            }

            // --- Desktop Dropdown Menu Toggle Script ---
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    // Close other open dropdowns
                    document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                        if (otherButton !== button) {
                            const otherMenu = document.getElementById(otherButton.getAttribute('data-dropdown-toggle'));
                            if (otherMenu) {
                                otherMenu.classList.add('hidden');
                                otherButton.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                    // Toggle the clicked dropdown
                    dropdownMenu.classList.toggle('hidden');
                    const isExpanded = !dropdownMenu.classList.contains('hidden');
                    button.setAttribute('aria-expanded', isExpanded);
                });
            });

            // Hide dropdowns when clicking outside
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    const menu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                    if (menu) {
                        menu.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    </script>
</body>
</html>
