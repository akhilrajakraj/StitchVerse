
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- Fetch Key Platform Metrics for a more detailed dashboard ---

// Total active customers
$sql_customers = "SELECT COUNT(cid) as count FROM creg WHERE cname != ''";
$customer_count = $db->selectData($sql_customers)->fetch_assoc()['count'];

// Total registered tailors
$sql_tailors = "SELECT COUNT(tid) as count FROM treg";
$tailor_count = $db->selectData($sql_tailors)->fetch_assoc()['count'];

// Pending stitch requests that need attention
$sql_pending = "SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus='Pending'";
$pending_requests = $db->selectData($sql_pending)->fetch_assoc()['count'];

// Total designs uploaded by tailors
$sql_designs = "SELECT COUNT(did) as count FROM upload";
$design_count = $db->selectData($sql_designs)->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        /* A little extra style for the dropdown arrow animation */
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-50">

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

        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="flex flex-col space-y-4 mt-4">
                <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Tailors</p>
                    <a href="approvedtailors.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Approved Tailors</a>
                    <a href="pendingtailors.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Pending Tailors</a>
                    <a href="rejectedtailors.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Rejected Tailors</a>
                    <a href="removedtailors.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Removed Tailors</a>
                </div>
                <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Customers</p>
                    <a href="activecustomers.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Active Customers</a>
                    <a href="removedcustomers.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Removed Customers</a>
                </div>

                <!-- MODIFIED: Mobile Menu Designs link -->
                <a href="managedesigns.php" class="px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Designs</a>

                <!-- Mobile Menu Stitch Requests -->
                 <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Stitch Requests</p>
                    <a href="activerequests.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Active Requests</a>
                    <a href="shippedrequests.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Shipped Requests</a>
                    <a href="paymentpendingrequests.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Payment Pending</a>
                    <a href="paidrequests.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Paid Requests</a>
                </div>
                
                <a href="index.php" class="bg-purple-100 text-purple-700 px-6 py-2 rounded-lg text-center font-semibold mt-2">Logout</a>
            </div>
        </div>
        </div>
    </header>

    <main>
        <section class="relative py-24 md:py-32 flex items-center bg-cover bg-center" style="background-image: linear-gradient(rgba(76, 29, 149, 0.8), rgba(107, 33, 168, 0.8)), url('images/admin-bg.jpg');">
            <div class="container mx-auto px-6 z-10 text-center">
              <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">StitchVerse Control Center</h1>
              <p class="text-lg md:text-xl text-purple-200 max-w-3xl mx-auto">An overview of platform activities and management tools.</p>
            </div>
        </section>

        <section class="py-20">
            <div class="container mx-auto px-6">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5">
                        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-group-2-line text-3xl"></i></div>
                        <div><p class="text-gray-500 text-sm font-medium">Total Customers</p><p class="text-3xl font-bold text-gray-800"><?php echo $customer_count; ?></p></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5">
                        <div class="w-16 h-16 bg-pink-100 text-pink-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-user-star-line text-3xl"></i></div>
                        <div><p class="text-gray-500 text-sm font-medium">Registered Tailors</p><p class="text-3xl font-bold text-gray-800"><?php echo $tailor_count; ?></p></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5">
                        <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-time-line text-3xl"></i></div>
                        <div><p class="text-gray-500 text-sm font-medium">Pending Requests</p><p class="text-3xl font-bold text-gray-800"><?php echo $pending_requests; ?></p></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col group">
                        <div class="p-8">
                            <i class="ri-user-settings-line text-5xl text-purple-600 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Tailor Management</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">Approve new tailor applications, view detailed profiles, and manage all registered artisans on the platform.</p>
                        </div>
                        <div class="mt-auto bg-gray-50 p-6 border-t border-gray-200">
                            <a href="approvedtailors.php" class="font-semibold text-purple-600 hover:text-purple-800 flex items-center justify-between">
                                <span>View All Tailors</span>
                                <i class="ri-arrow-right-s-line text-2xl transition-transform duration-300 group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col group">
                        <div class="p-8">
                            <i class="ri-team-line text-5xl text-purple-600 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Customer Management</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">Access a complete list of customer profiles, view their history, and manage accounts to ensure a great user experience.</p>
                        </div>
                        <div class="mt-auto bg-gray-50 p-6 border-t border-gray-200">
                            <a href="activecustomers.php" class="font-semibold text-purple-600 hover:text-purple-800 flex items-center justify-between">
                                <span>View All Customers</span>
                                <i class="ri-arrow-right-s-line text-2xl transition-transform duration-300 group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col group">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-4">
                                <i class="ri-gallery-line text-5xl text-purple-600"></i>
                                <span class="bg-purple-100 text-purple-800 text-sm font-bold px-3 py-1 rounded-full"><?php echo $design_count; ?> Designs</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Design Moderation</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">Review and manage all designs uploaded by tailors to the public gallery, ensuring quality and appropriateness.</p>
                        </div>
                        <div class="mt-auto bg-gray-50 p-6 border-t border-gray-200">
                             <a href="managedesigns.php" class="font-semibold text-purple-600 hover:text-purple-800 flex items-center justify-between">
                                <span>Go To Design Gallery</span>
                                <i class="ri-arrow-right-s-line text-2xl transition-transform duration-300 group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col group">
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-4">
                                <i class="ri-ruler-2-line text-5xl text-purple-600"></i>
                                <span class="bg-yellow-100 text-yellow-800 text-sm font-bold px-3 py-1 rounded-full"><?php echo $pending_requests; ?> Pending</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Stitch Requests Overview</h3>
                            <p class="text-gray-600 mb-6 leading-relaxed">Monitor the status of all custom tailoring requests between customers and tailors to ensure timely fulfillment.</p>
                        </div>
                        <div class="mt-auto bg-gray-50 p-6 border-t border-gray-200">
                            <a href="activerequests.php" class="font-semibold text-purple-600 hover:text-purple-800 flex items-center justify-between">
                                <span>Review All Requests</span>
                                <i class="ri-arrow-right-s-line text-2xl transition-transform duration-300 group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <footer class="bg-gray-900 text-white py-12">
      <div class="container mx-auto px-6 text-center">
        <a href="adminhomepage.php" class="text-2xl font-bold text-purple-400 mb-2 inline-block font-pacifico">StitchVerse</a>
        <p class="text-gray-500 text-sm">Administration Panel | © <?php echo date("Y"); ?> All Rights Reserved.</p>
      </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Menu Toggle Script
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // --- Desktop Dropdown Menu Toggle Script ---
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');

            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));

                button.addEventListener('click', (event) => {
                    event.stopPropagation(); // Prevents the window click event from firing immediately
                    
                    const wasOpen = !dropdownMenu.classList.contains('hidden');

                    // Hide all open dropdowns first
                    document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                        document.getElementById(otherButton.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                        otherButton.setAttribute('aria-expanded', 'false');
                    });

                    // If the current dropdown was not open, show it
                    if (!wasOpen) {
                        dropdownMenu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // --- Hide dropdowns when clicking outside ---
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button').forEach(button => {
                    document.getElementById(button.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
    </body>
</html>
