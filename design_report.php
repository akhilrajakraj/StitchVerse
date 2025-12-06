
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- 1. Fetch Key Performance Indicators (KPIs) for the Summary ---

$total_designs = $db->selectData("SELECT COUNT(did) as count FROM upload")->fetch_assoc()['count'];
$paid_orders_query = "SELECT COUNT(oid) as count FROM orderdesign WHERE ostatus = 'Paid'";
$total_paid_orders = $db->selectData($paid_orders_query)->fetch_assoc()['count'];
$shipped_orders_query = "SELECT COUNT(oid) as count FROM orderdesign WHERE ostatus = 'Shipped'";
$total_shipped_orders = $db->selectData($shipped_orders_query)->fetch_assoc()['count'];
// --- ADDED: Query for Cancelled Orders ---
$cancelled_orders_query = "SELECT COUNT(oid) as count FROM orderdesign WHERE ostatus = 'Cancelled'";
$total_cancelled_orders = $db->selectData($cancelled_orders_query)->fetch_assoc()['count'];
$total_revenue_query = "SELECT SUM(u.dprice) as total FROM orderdesign o JOIN upload u ON o.did = u.did WHERE o.ostatus IN ('Paid', 'Shipped')";
$total_revenue = $db->selectData($total_revenue_query)->fetch_assoc()['total'] ?? 0;

// --- EFFICIENT WAY TO FIND A RANKED LIST OF BEST-SELLING DESIGNS ---
$best_selling_designs = [];
$most_ordered_design_query = "
    SELECT u.dname, COUNT(o.oid) as order_count 
    FROM orderdesign o 
    JOIN upload u ON o.did = u.did 
    WHERE o.ostatus IN ('Paid', 'Shipped')
    GROUP BY o.did 
    ORDER BY order_count DESC, u.dname ASC
    LIMIT 5";
$most_ordered_design_result = $db->selectData($most_ordered_design_query);
if ($most_ordered_design_result) {
    while($row = $most_ordered_design_result->fetch_assoc()) {
        $best_selling_designs[] = $row;
    }
}

// --- EFFICIENT WAY TO FIND A RANKED LIST OF TOP DESIGNERS (with contact details) ---
$top_designers = [];
$top_designer_query = "
    SELECT t.tid, t.tname, t.email, t.phone, COUNT(u.did) as design_count 
    FROM upload u 
    JOIN treg t ON u.uid = t.tid 
    GROUP BY u.uid 
    ORDER BY design_count DESC, t.tname ASC
    LIMIT 5";
$top_designer_result = $db->selectData($top_designer_query);
if ($top_designer_result) {
    while($row = $top_designer_result->fetch_assoc()) {
        $top_designers[] = $row;
    }
}

// --- 2. Fetch Detailed Report Data for Each Design (with tailor contact details and filtering) ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql_detailed = "
    SELECT 
        u.did,
        u.dname,
        u.dimg,
        u.dtype,
        u.dprice,
        t.tid as designer_id,
        t.tname as designer_name,
        t.email as designer_email,
        t.phone as designer_phone,
        (SELECT COUNT(oid) FROM orderdesign WHERE did = u.did AND ostatus IN ('Paid', 'Shipped')) as order_count
    FROM 
        upload u
    JOIN 
        treg t ON u.uid = t.tid
";

$types = "";
$params = [];

// Add a WHERE clause if a specific status is selected from the clickable cards
if ($filter_status !== 'all' && in_array($filter_status, ['Paid', 'Shipped', 'Cancelled'])) {
    // This subquery finds all design IDs that have at least one order with the specified status.
    $sql_detailed .= " WHERE u.did IN (SELECT DISTINCT did FROM orderdesign WHERE ostatus = ?)";
    $types = "s";
    $params = [$filter_status];
}

$sql_detailed .= " ORDER BY order_count DESC, u.dname ASC";

$detailed_report_result = $db->selectData($sql_detailed, $types, ...$params);
$design_details = [];
if ($detailed_report_result) {
    while ($row = $detailed_report_result->fetch_assoc()) {
        $design_details[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design Performance Report - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
        .tooltip {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .has-tooltip:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
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
                <button id="report-dropdown-button" data-dropdown-toggle="report-dropdown-menu" class="dropdown-button text-purple-600 font-semibold flex items-center gap-1">
                    <span>Reports</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="report-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailor_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Tailor Reports</a>
                    <a href="customer_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Customer Reports</a>
                    <a href="design_report.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Design Reports</a>
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
                <h1 class="text-3xl font-bold text-gray-800">Design Marketplace Report</h1>
                <p class="text-gray-500 mt-1">An analysis of design popularity, sales, and top contributors.</p>
                <p class="text-xs text-gray-400 mt-2">Report generated on: <?php echo date("F j, Y, g:i a"); ?></p>
            </div>
            <button onclick="window.print()" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold flex items-center gap-2">
                <i class="ri-printer-line"></i> Print Report
            </button>
        </div>

        <!-- MODIFIED: Summary Section with Clickable Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12 no-print">
            <a href="design_report.php" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-purple-500 hover:shadow-md transition-all">
                <p class="text-gray-500 text-sm font-medium">Total Designs</p>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_designs; ?></p>
            </a>
            <a href="design_report.php?status=Paid" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-green-500 hover:shadow-md transition-all">
                <p class="text-gray-500 text-sm font-medium">Paid Orders</p>
                <p class="text-3xl font-bold text-green-600 mt-1"><?php echo $total_paid_orders; ?></p>
            </a>
            <a href="design_report.php?status=Shipped" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-blue-500 hover:shadow-md transition-all">
                <p class="text-gray-500 text-sm font-medium">Shipped Orders</p>
                <p class="text-3xl font-bold text-blue-600 mt-1"><?php echo $total_shipped_orders; ?></p>
            </a>
            <a href="design_report.php?status=Cancelled" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-red-500 hover:shadow-md transition-all">
                <p class="text-gray-500 text-sm font-medium">Cancelled Orders</p>
                <p class="text-3xl font-bold text-red-600 mt-1"><?php echo $total_cancelled_orders; ?></p>
            </a>
            <div class="bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center">
                <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                <p class="text-3xl font-bold text-purple-600 mt-1">₹<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>
        
        <!-- Top Performers Highlights Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12 no-print">
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2"><i class="ri-trophy-line text-yellow-500"></i> Top 5 Best Selling Designs</h3>
                <div class="space-y-2">
                    <?php if (empty($best_selling_designs)): ?>
                        <p class="text-sm text-gray-500">No confirmed orders have been placed yet.</p>
                    <?php else: ?>
                        <?php foreach($best_selling_designs as $index => $design): ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-gray-400 w-5 text-center">#<?php echo $index + 1; ?></span>
                                <span class="font-semibold text-purple-700 truncate"><?php echo htmlspecialchars($design['dname']); ?></span>
                            </div>
                            <span class="font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md"><?php echo $design['order_count']; ?> orders</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2"><i class="ri-user-star-line text-blue-500"></i> Top 5 Designers</h3>
                <div class="space-y-2">
                     <?php if (empty($top_designers)): ?>
                        <p class="text-sm text-gray-500">No designs have been uploaded yet.</p>
                    <?php else: ?>
                        <?php foreach($top_designers as $index => $designer): ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3 relative has-tooltip">
                                <span class="font-bold text-gray-400 w-5 text-center">#<?php echo $index + 1; ?></span>
                                <span class="font-semibold text-purple-700 truncate"><?php echo htmlspecialchars($designer['tname']); ?></span>
                                <div class="tooltip absolute z-10 w-64 p-3 -mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                    <p class="font-bold"><?php echo htmlspecialchars($designer['tname']); ?></p>
                                    <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($designer['email']); ?></p>
                                    <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($designer['phone']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md"><?php echo $designer['design_count']; ?> designs</span>
                                <a href="viewapp_tailors.php?id=<?php echo $designer['tid']; ?>" class="text-purple-600 hover:text-purple-800"><i class="ri-eye-line"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <!-- Detailed Report Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden printable-area">
            <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Detailed Design Performance</h2>
                    <?php if ($filter_status !== 'all'): ?>
                        <p class="text-sm text-purple-600 font-semibold mt-1">
                            Filtering by Order Status: "<?php echo htmlspecialchars($filter_status); ?>"
                        </p>
                    <?php endif; ?>
                </div>
                 <?php if ($filter_status !== 'all'): ?>
                    <a href="design_report.php" class="no-print text-sm font-semibold text-gray-600 hover:text-purple-600 flex items-center gap-2">
                        <i class="ri-close-circle-line"></i> Clear Filter
                    </a>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Design</th>
                            <th scope="col" class="px-6 py-3">Designer</th>
                            <th scope="col" class="px-6 py-3">Category</th>
                            <th scope="col" class="px-6 py-3 text-right">Price</th>
                            <th scope="col" class="px-6 py-3 text-center">Confirmed Orders</th>
                            <th scope="col" class="px-6 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($design_details)): ?>
                            <tr><td colspan="6" class="px-6 py-16 text-center"><i class="ri-palette-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Designs Found</h3><p class="text-gray-500 mt-1">No designs match the current filter.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($design_details as $design): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="<?php echo htmlspecialchars($design['dimg']); ?>" class="w-12 h-16 object-cover rounded-lg border shrink-0">
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($design['dname']); ?></div>
                                            <div class="text-gray-500 text-xs">ID: <?php echo $design['did']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <div class="relative has-tooltip">
                                        <a href="viewapp_tailors.php?id=<?php echo $design['designer_id']; ?>" class="text-purple-600 hover:underline"><?php echo htmlspecialchars($design['designer_name']); ?></a>
                                        <div class="tooltip absolute z-10 w-64 p-3 -mt-2 -ml-28 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                            <p class="font-bold"><?php echo htmlspecialchars($design['designer_name']); ?></p>
                                            <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($design['designer_email']); ?></p>
                                            <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($design['designer_phone']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($design['dtype']); ?></td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800">₹<?php echo number_format($design['dprice'], 2); ?></td>
                                <td class="px-6 py-4 text-center font-bold text-lg text-purple-700"><?php echo $design['order_count']; ?></td>
                                <td class="px-6 py-4 text-right font-bold text-green-600">₹<?php echo number_format($design['order_count'] * $design['dprice'], 2); ?></td>
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
