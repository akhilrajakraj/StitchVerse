
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

$total_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq")->fetch_assoc()['count'];
$pending_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus = 'Pending'")->fetch_assoc()['count'];
$paid_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus = 'Paid'")->fetch_assoc()['count'];
$shipped_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus = 'Shipped'")->fetch_assoc()['count'];
$cancelled_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus = 'Cancelled'")->fetch_assoc()['count'];
$rejected_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus = 'Rejected'")->fetch_assoc()['count'];

// --- 2. Fetch Top Performer Data with Contact Details ---

$top_customers_query = "SELECT c.cid, c.cname, c.email, c.phone, COUNT(sr.sdid) as request_count FROM stitchreq sr JOIN creg c ON sr.uid = c.cid GROUP BY sr.uid ORDER BY request_count DESC, c.cname ASC LIMIT 5";
$top_customers = $db->selectData($top_customers_query)->fetch_all(MYSQLI_ASSOC);

$top_tailors_query = "SELECT t.tid, t.tname, t.email, t.phone, COUNT(sr.sdid) as request_count FROM stitchreq sr JOIN treg t ON sr.tid = t.tid WHERE sr.tid IS NOT NULL AND sr.tid > 0 GROUP BY sr.tid ORDER BY request_count DESC, t.tname ASC LIMIT 5";
$top_tailors = $db->selectData($top_tailors_query)->fetch_all(MYSQLI_ASSOC);

// --- 3. Fetch Detailed Report Data with Filtering ---

// Get the current filter status from the URL
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql_detailed = "
    SELECT 
        sr.sdid,
        sr.sdname,
        sr.sstatus,
        sr.sprice,
        c.cid as customer_id,
        c.cname as customer_name,
        c.email as customer_email,
        c.phone as customer_phone,
        t.tid as tailor_id,
        t.tname as tailor_name,
        t.email as tailor_email,
        t.phone as tailor_phone
    FROM 
        stitchreq sr
    JOIN 
        creg c ON sr.uid = c.cid
    LEFT JOIN
        treg t ON sr.tid = t.tid
";

$types = "";
$params = [];

// Add a WHERE clause if a specific status is selected from the clickable boxes
if ($filter_status !== 'all' && in_array($filter_status, ['Pending', 'Paid', 'Shipped', 'Cancelled', 'Rejected'])) {
    $sql_detailed .= " WHERE sr.sstatus = ?";
    $types = "s";
    $params = [$filter_status];
}

$sql_detailed .= " ORDER BY sr.sdid DESC";

$detailed_report_result = $db->selectData($sql_detailed, $types, ...$params);
$request_details = $detailed_report_result->fetch_all(MYSQLI_ASSOC);

// Helper function for status badge styling
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'paid': return 'bg-green-100 text-green-800';
        case 'shipped': return 'bg-blue-100 text-blue-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'accepted': return 'bg-indigo-100 text-indigo-800';
        case 'cancelled':
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stitch Request Report - StitchVerse Admin</title>
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
                    <a href="customer_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Customer Reports</a>
                    <a href="design_report.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Design Reports</a>
                    <a href="request_report.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Request Reports</a>
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
                <h1 class="text-3xl font-bold text-gray-800">Stitch Request Report</h1>
                <p class="text-gray-500 mt-1">A complete overview of the custom tailoring service pipeline.</p>
                <p class="text-xs text-gray-400 mt-2">Report generated on: <?php echo date("F j, Y, g:i a"); ?></p>
            </div>
            <button onclick="window.print()" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold flex items-center gap-2">
                <i class="ri-printer-line"></i> Print Report
            </button>
        </div>

        <!-- Summary Section -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-12 no-print">
            <a href="request_report.php" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-purple-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Total Requests</p><p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_requests; ?></p></a>
            <a href="request_report.php?status=Pending" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-yellow-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Pending</p><p class="text-3xl font-bold text-yellow-600 mt-1"><?php echo $pending_requests; ?></p></a>
            <a href="request_report.php?status=Paid" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-green-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Paid</p><p class="text-3xl font-bold text-green-600 mt-1"><?php echo $paid_requests; ?></p></a>
            <a href="request_report.php?status=Shipped" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-blue-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Shipped</p><p class="text-3xl font-bold text-blue-600 mt-1"><?php echo $shipped_requests; ?></p></a>
            <a href="request_report.php?status=Cancelled" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-red-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Cancelled</p><p class="text-3xl font-bold text-red-600 mt-1"><?php echo $cancelled_requests; ?></p></a>
            <a href="request_report.php?status=Rejected" class="block bg-white p-4 rounded-2xl shadow-lg border border-gray-200 text-center hover:border-gray-500 hover:shadow-md transition-all"><p class="text-gray-500 text-sm font-medium">Rejected</p><p class="text-3xl font-bold text-gray-500 mt-1"><?php echo $rejected_requests; ?></p></a>
        </div>
        
        <!-- Top Performers Highlights Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12 no-print">
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2"><i class="ri-user-heart-line text-pink-500"></i> Top 5 Customers (by Requests)</h3>
                <div class="space-y-2">
                    <?php if (empty($top_customers)): ?>
                        <p class="text-sm text-gray-500">No stitch requests have been made yet.</p>
                    <?php else: ?>
                        <?php foreach($top_customers as $index => $customer): ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3 relative has-tooltip">
                                <span class="font-bold text-gray-400 w-5 text-center">#<?php echo $index + 1; ?></span>
                                <span class="font-semibold text-purple-700 truncate"><?php echo htmlspecialchars($customer['cname']); ?></span>
                                <div class="tooltip absolute z-10 w-64 p-3 -mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                    <p class="font-bold"><?php echo htmlspecialchars($customer['cname']); ?></p>
                                    <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($customer['email']); ?></p>
                                    <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($customer['phone']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md"><?php echo $customer['request_count']; ?> requests</span>
                                <a href="activecustomdetails.php?id=<?php echo $customer['cid']; ?>" class="text-purple-600 hover:text-purple-800"><i class="ri-eye-line"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-200">
                <h3 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2"><i class="ri-user-star-line text-indigo-500"></i> Top 5 Tailors (by Jobs)</h3>
                <div class="space-y-2">
                     <?php if (empty($top_tailors)): ?>
                        <p class="text-sm text-gray-500">No requests have been assigned to tailors yet.</p>
                    <?php else: ?>
                        <?php foreach($top_tailors as $index => $tailor): ?>
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex items-center gap-3 relative has-tooltip">
                                <span class="font-bold text-gray-400 w-5 text-center">#<?php echo $index + 1; ?></span>
                                <span class="font-semibold text-purple-700 truncate"><?php echo htmlspecialchars($tailor['tname']); ?></span>
                                <div class="tooltip absolute z-10 w-64 p-3 -mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                    <p class="font-bold"><?php echo htmlspecialchars($tailor['tname']); ?></p>
                                    <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($tailor['email']); ?></p>
                                    <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($tailor['phone']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md"><?php echo $tailor['request_count']; ?> jobs</span>
                                <a href="viewapp_tailors.php?id=<?php echo $tailor['tid']; ?>" class="text-purple-600 hover:text-purple-800"><i class="ri-eye-line"></i></a>
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
                    <h2 class="text-xl font-bold text-gray-800">Complete Stitch Request Log</h2>
                    <?php if ($filter_status !== 'all'): ?>
                        <p class="text-sm text-purple-600 font-semibold mt-1">
                            Filtering by Status: "<?php echo htmlspecialchars($filter_status); ?>"
                        </p>
                    <?php endif; ?>
                </div>
                <?php if ($filter_status !== 'all'): ?>
                    <a href="request_report.php" class="no-print text-sm font-semibold text-gray-600 hover:text-purple-600 flex items-center gap-2">
                        <i class="ri-close-circle-line"></i> Clear Filter
                    </a>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Request</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Assigned Tailor</th>
                            <th scope="col" class="px-6 py-3 text-right">Price</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($request_details)): ?>
                            <tr><td colspan="5" class="px-6 py-16 text-center"><i class="ri-file-text-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Stitch Requests Found</h3><p class="text-gray-500 mt-1">No requests match the current filter.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($request_details as $request): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="view_request_details.php?id=<?php echo $request['sdid']; ?>" class="font-bold text-gray-900 hover:text-purple-600"><?php echo htmlspecialchars($request['sdname']); ?></a>
                                    <div class="font-mono text-xs text-gray-500">#<?php echo $request['sdid']; ?></div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800">
                                    <div class="relative has-tooltip">
                                        <a href="activecustomdetails.php?id=<?php echo $request['customer_id']; ?>" class="text-purple-600 hover:underline"><?php echo htmlspecialchars($request['customer_name']); ?></a>
                                        <div class="tooltip absolute z-10 w-64 p-3 -mt-2 -ml-28 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                            <p class="font-bold"><?php echo htmlspecialchars($request['customer_name']); ?></p>
                                            <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($request['customer_email']); ?></p>
                                            <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($request['customer_phone']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    <?php if ($request['tailor_name']): ?>
                                    <div class="relative has-tooltip">
                                        <a href="viewapp_tailors.php?id=<?php echo $request['tailor_id']; ?>" class="text-purple-600 hover:underline"><?php echo htmlspecialchars($request['tailor_name']); ?></a>
                                        <div class="tooltip absolute z-10 w-64 p-3 -mt-2 -ml-28 text-sm text-white bg-gray-800 rounded-lg shadow-lg bottom-full">
                                            <p class="font-bold"><?php echo htmlspecialchars($request['tailor_name']); ?></p>
                                            <p><i class="ri-mail-line"></i> <?php echo htmlspecialchars($request['tailor_email']); ?></p>
                                            <p><i class="ri-phone-line"></i> <?php echo htmlspecialchars($request['tailor_phone']); ?></p>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                        <span class="italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-800"><?php echo $request['sprice'] ? '₹' . number_format($request['sprice'], 2) : 'N/A'; ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusClass($request['sstatus']); ?>">
                                        <?php echo htmlspecialchars($request['sstatus']); ?>
                                    </span>
                                </td>
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
