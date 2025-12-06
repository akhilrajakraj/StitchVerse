<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- 1. Fetch Summary Statistics ---

// Total number of customers (active and removed)
$total_customers = $db->selectData("SELECT COUNT(c.cid) as count FROM creg c JOIN login l ON c.cid = l.uid WHERE l.utype IN ('customer', 'removed_customer')")->fetch_assoc()['count'];

// Active customers
$active_customers = $db->selectData("SELECT COUNT(c.cid) as count FROM creg c JOIN login l ON c.cid = l.uid WHERE l.utype = 'customer'")->fetch_assoc()['count'];

// Removed customers (assuming 'removed_customer' is a utype in login table)
$removed_customers = $db->selectData("SELECT COUNT(c.cid) as count FROM creg c JOIN login l ON c.cid = l.uid WHERE l.utype = 'removed_customer'")->fetch_assoc()['count'];

// Total stitch requests submitted by all customers
$total_stitch_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq")->fetch_assoc()['count'];

// Total designs ordered by all customers
$total_design_orders = $db->selectData("SELECT COUNT(oid) as count FROM orderdesign")->fetch_assoc()['count'];


// --- 2. Fetch Detailed Report Data with Filtering ---

// Get the current filter status from the URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql_detailed = "
    SELECT 
        c.cid,
        c.cname,
        c.email,
        l.utype as status,
        (SELECT COUNT(sdid) FROM stitchreq WHERE uid = c.cid) as stitch_requests,
        (SELECT COUNT(oid) FROM orderdesign WHERE uid = c.cid) as design_orders
    FROM 
        creg c
    JOIN 
        login l ON c.cid = l.uid
";

$types = "";
$params = [];

// Add a WHERE clause based on the filter
if ($filter_status !== 'all' && in_array($filter_status, ['customer', 'removed_customer'])) {
    $sql_detailed .= " WHERE l.utype = ?";
    $types = "s";
    $params = [$filter_status];
} else {
    $sql_detailed .= " WHERE l.utype IN ('customer', 'removed_customer')";
}

$sql_detailed .= " ORDER BY stitch_requests DESC, c.cname ASC";

$detailed_report_result = $db->selectData($sql_detailed, $types, ...$params);
$customer_details = [];
if ($detailed_report_result) {
    while ($row = $detailed_report_result->fetch_assoc()) {
        $customer_details[] = $row;
    }
}

// Helper function for status badge styling
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'customer': return 'bg-green-100 text-green-800';
        case 'removed_customer': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Report - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
        /* Styles for printing the report */
        @media print {
            body { background-color: white; }
            .no-print { display: none; }
            main { margin: 0; padding: 0; }
            .printable-area { box-shadow: none; border: 1px solid #e5e7eb; }
        }
    </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50 no-print">
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
                <button id="report-dropdown-button" data-dropdown-toggle="report-dropdown-menu" class="dropdown-button text-purple-600 font-semibold flex items-center gap-1">
                    <span>Reports</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="report-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailor_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Tailor Reports</a>
                    <a href="customer_report.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Customer Reports</a>
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
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8 no-print">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Customer Activity Report</h1>
                <p class="text-gray-500 mt-1">An overview of customer registrations and their platform engagement.</p>
                <p class="text-xs text-gray-400 mt-2">Report generated on: <?php echo date("F j, Y, g:i a"); ?></p>
            </div>
            <button onclick="window.print()" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold flex items-center gap-2">
                <i class="ri-printer-line"></i> Print Report
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12 no-print">
            <a href="customer_report.php" class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5 hover:border-blue-500 hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-team-fill text-3xl"></i></div>
                <div><p class="text-gray-500 text-sm font-medium">Total Customers</p><p class="text-3xl font-bold text-gray-800"><?php echo $total_customers; ?></p></div>
            </a>
            <a href="customer_report.php?status=customer" class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5 hover:border-green-500 hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-user-smile-fill text-3xl"></i></div>
                <div><p class="text-gray-500 text-sm font-medium">Active Customers</p><p class="text-3xl font-bold text-gray-800"><?php echo $active_customers; ?></p></div>
            </a>
            <a href="customer_report.php?status=removed_customer" class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5 hover:border-red-500 hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-user-unfollow-fill text-3xl"></i></div>
                <div><p class="text-gray-500 text-sm font-medium">Removed Customers</p><p class="text-3xl font-bold text-gray-800"><?php echo $removed_customers; ?></p></div>
            </a>
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-ruler-2-fill text-3xl"></i></div>
                <div><p class="text-gray-500 text-sm font-medium">Total Stitch Requests</p><p class="text-3xl font-bold text-gray-800"><?php echo $total_stitch_requests; ?></p></div>
            </div>
             <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200 flex items-center gap-5">
                <div class="w-16 h-16 bg-pink-100 text-pink-600 rounded-2xl flex items-center justify-center shrink-0"><i class="ri-shopping-cart-fill text-3xl"></i></div>
                <div><p class="text-gray-500 text-sm font-medium">Total Design Orders</p><p class="text-3xl font-bold text-gray-800"><?php echo $total_design_orders; ?></p></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden printable-area">
            <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Detailed Customer Breakdown</h2>
                    <?php if ($filter_status !== 'all'): ?>
                        <p class="text-sm text-purple-600 font-semibold mt-1">
                            Filtering by Status: "<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $filter_status))); ?>"
                        </p>
                    <?php endif; ?>
                </div>
                <?php if ($filter_status !== 'all'): ?>
                    <a href="customer_report.php" class="no-print text-sm font-semibold text-gray-600 hover:text-purple-600 flex items-center gap-2">
                        <i class="ri-close-circle-line"></i> Clear Filter
                    </a>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Customer Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                            <th scope="col" class="px-6 py-3 text-center">Stitch Requests</th>
                            <th scope="col" class="px-6 py-3 text-center">Design Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customer_details)): ?>
                            <tr><td colspan="5" class="px-6 py-16 text-center"><i class="ri-user-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Customers Found</h3><p class="text-gray-500 mt-1">No customers match the current filter.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($customer_details as $customer): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-900"><?php echo htmlspecialchars($customer['cname']); ?></td>
                                <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusClass($customer['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $customer['status']))); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-gray-800"><?php echo $customer['stitch_requests']; ?></td>
                                <td class="px-6 py-4 text-center font-medium text-gray-800"><?php echo $customer['design_orders']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const wasOpen = !dropdownMenu.classList.contains('hidden');
                    document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => {
                        document.getElementById(b.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                        b.setAttribute('aria-expanded', 'false');
                    });
                    if (!wasOpen) {
                        dropdownMenu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', !dropdownMenu.classList.contains('hidden'));
                    }
                });
            });
            window.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                    document.getElementById(button.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                    button.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
</body>
</html>
