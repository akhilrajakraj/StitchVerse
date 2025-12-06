
<?php
session_start();
require_once 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- Data Fetching Logic ---
$active_customers = [];
$sql = "SELECT c.cid, c.cname, c.email, c.phone, c.city, c.distr 
FROM creg c 
JOIN login l ON c.cid = l.uid 
WHERE l.utype = 'customer' 
ORDER BY c.cname ASC";
$result = $db->selectData($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $active_customers[] = $row;
    }
}

// --- Prepare Flash Messages for JavaScript ---
$successMessage = $_SESSION['action_success'] ?? null;
$errorMessage = $_SESSION['action_error'] ?? null;

if ($successMessage) {
    unset($_SESSION['action_success']);
}
if ($errorMessage) {
    unset($_SESSION['action_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Customers - StitchVerse Admin</title>
    
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
<body class="bg-gray-100">

    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full z-[100]"><i class="ri-checkbox-circle-line text-2xl"></i><span></span></div>
    <div id="error-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full z-[100]"><i class="ri-error-warning-line text-2xl"></i><span></span></div>

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
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Active Customers</h1>
                <p class="text-gray-500 mt-1">A list of all registered and active customers on the platform.</p>
            </div>
            <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-4 rounded-xl text-center shrink-0">
                <div class="text-3xl font-bold text-blue-700"><?php echo count($active_customers); ?></div>
                <div class="text-blue-700 text-sm font-medium">Active Customers</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                   <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Customer Name</th>
                            <th scope="col" class="px-6 py-3">Contact Information</th>
                            <th scope="col" class="px-6 py-3">Location</th>
                            <th scope="col" class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($active_customers)): ?>
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No active customers found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($active_customers as $customer): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-900"><?php echo htmlspecialchars($customer['cname']); ?></td>
                                <td class="px-6 py-4">
                                    <div><?php echo htmlspecialchars($customer['email']); ?></div>
                                    <div class="text-gray-500 font-mono"><?php echo htmlspecialchars($customer['phone']); ?></div>
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($customer['city']) . ", " . htmlspecialchars($customer['distr']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="activecustomdetails.php?id=<?php echo $customer['cid']; ?>" class="font-medium text-white bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg text-xs inline-flex items-center gap-1">
                                        <i class="ri-eye-line"></i>
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
        document.addEventListener('DOMContentLoaded', function () {
            // --- FLASH MESSAGE HANDLING ---
            const successMessage = <?php echo json_encode($successMessage); ?>;
            const errorMessage = <?php echo json_encode($errorMessage); ?>;

            if (successMessage) {
                const popup = document.getElementById('success-popup');
                const messageSpan = popup.querySelector('span');
                messageSpan.textContent = successMessage;
                
                popup.classList.remove('hidden');
                setTimeout(() => popup.classList.remove('translate-x-full'), 50);

                setTimeout(() => {
                    popup.classList.add('translate-x-full');
                    setTimeout(() => popup.classList.add('hidden'), 300);
                }, 4000);
            }

            if (errorMessage) {
                const popup = document.getElementById('error-popup');
                const messageSpan = popup.querySelector('span');
                messageSpan.textContent = errorMessage;
                
                popup.classList.remove('hidden');
                setTimeout(() => popup.classList.remove('translate-x-full'), 50);

                setTimeout(() => {
                    popup.classList.add('translate-x-full');
                    setTimeout(() => popup.classList.add('hidden'), 300);
                }, 4000);
            }

            // --- DROPDOWN SCRIPT ---
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
        });
    </script>
</body>
</html>