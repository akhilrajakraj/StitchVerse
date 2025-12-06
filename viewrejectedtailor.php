<?php
session_start();
require_once 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Validate and get tailor ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: rejectedtailors.php");
    exit();
}
$tailor_id = intval($_GET['id']);
$db = new DatabaseCon();

// Fetch Rejected Tailor Details from the database
$tailor = null;
$sql_tailor = "SELECT * FROM treg WHERE tid = ? AND status = 'Rejected'";
$result_tailor = $db->selectData($sql_tailor, "i", $tailor_id);

if ($result_tailor && $result_tailor->num_rows > 0) {
    $tailor = $result_tailor->fetch_assoc();
} else {
    // If no rejected tailor is found, redirect back with an error message
    $_SESSION['action_error'] = "Rejected tailor application not found.";
    header("Location: rejectedtailors.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Application - <?php echo htmlspecialchars($tailor['tname']); ?></title>
    
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
    
    <!-- HEADER (Consistent with rejectedtailors.php) -->
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
        
        <div class="mb-6">
            <a href="rejectedtailors.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Rejected Applications</span>
            </a>
        </div>

        <!-- REJECTED TAILOR PROFILE SECTION -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <div>
                    <p class="text-sm font-semibold text-red-600 bg-red-100 inline-block px-3 py-1 rounded-full mb-2">Application Rejected</p>
                    <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($tailor['tname']); ?></h1>
                    <p class="text-gray-500 mt-1">Original Speciality: <?php echo htmlspecialchars($tailor['spect']); ?></p>
                </div>
                <div class="border-t border-gray-200 my-6"></div>
                
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Submitted Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div class="flex items-center gap-3"><i class="ri-mail-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['email']); ?></span></div>
                    <div class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['phone']); ?></span></div>
                    <div class="flex items-center gap-3"><i class="ri-map-pin-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['city'] . ', ' . $tailor['distri']); ?></span></div>
                    <div class="flex items-center gap-3"><i class="ri-award-line text-purple-500 text-xl"></i><span>Qualification: <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($tailor['quali']))); ?></span></div>
                    <div class="flex items-start gap-3 col-span-full"><i class="ri-building-line text-purple-500 text-xl pt-1"></i><span class="flex-1"><?php echo htmlspecialchars($tailor['address']); ?></span></div>
                </div>
            </div>
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">This application was rejected. The data is retained for historical purposes.</p>
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
    // JS for dropdowns
    document.addEventListener('DOMContentLoaded', function () {
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
