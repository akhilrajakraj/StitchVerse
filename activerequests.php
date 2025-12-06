
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- Get filter and search parameters ---
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- Define active statuses ---
$active_statuses = ['Pending', 'Accepted', 'Paid'];
$active_statuses_placeholder = implode(',', array_fill(0, count($active_statuses), '?'));

// --- Fetch stats for active requests ---
$total_active = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus IN ($active_statuses_placeholder)", str_repeat('s', count($active_statuses)), ...$active_statuses)->fetch_assoc()['count'];
$pending_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus='Pending'")->fetch_assoc()['count'];
$in_progress_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus IN ('Accepted', 'Paid')")->fetch_assoc()['count'];

// --- Build the main query securely ---
$sql_main = "SELECT sr.sdid, sr.sdname, sr.sstatus, sr.simg, c.cname, c.email as cemail, t.tname, t.email as temail 
             FROM stitchreq sr
             JOIN creg c ON sr.uid = c.cid
             LEFT JOIN treg t ON sr.tid = t.tid
             WHERE sr.sstatus IN ($active_statuses_placeholder)";
$types = str_repeat('s', count($active_statuses));
$params = $active_statuses;

if ($filter_status !== 'all' && in_array($filter_status, $active_statuses)) {
    $sql_main = str_replace("WHERE sr.sstatus IN ($active_statuses_placeholder)", "WHERE sr.sstatus = ?", $sql_main);
    $types = "s";
    $params = [$filter_status];
}

if (!empty($search_term)) {
    $searchTermLike = "%" . $search_term . "%";
    $sql_main .= " AND (sr.sdname LIKE ? OR c.cname LIKE ? OR t.tname LIKE ?)";
    $types .= "sss";
    array_push($params, $searchTermLike, $searchTermLike, $searchTermLike);
}
$sql_main .= " ORDER BY sr.sdid DESC";

$rs_main = $db->selectData($sql_main, $types, ...$params);

$requests = [];
if ($rs_main) {
    while ($row = $rs_main->fetch_assoc()) {
        $requests[] = $row;
    }
}

// --- Prepare Flash Messages for JavaScript and Unset Them ---
$successMessage = $_SESSION['action_success'] ?? null;
$errorMessage = $_SESSION['action_error'] ?? null;
if ($successMessage) unset($_SESSION['action_success']);
if ($errorMessage) unset($_SESSION['action_error']);


// Function to determine status badge color
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'accepted':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Stitch Requests - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-gray-100">

    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full z-[100]"><i class="ri-checkbox-circle-line text-2xl"></i><span></span></div>
    <div id="error-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full z-[100]"><i class="ri-error-warning-line text-2xl"></i><span></span></div>


    <div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Cancel Request & Notify Parties</h3><button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="managestraction.php" method="POST" class="p-6">
                <input type="hidden" name="sdid" id="modal-cancel-sdid">
                <input type="hidden" name="customer_email" id="modal-cancel-cemail">
                <input type="hidden" name="tailor_email" id="modal-cancel-temail">
                <input type="hidden" name="request_name" id="modal-cancel-rname">
                <input type="hidden" name="action" value="cancel">
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation*</label><textarea name="reason" required rows="4" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Provide a clear reason for cancelling this request. This will be sent to both the customer and the tailor."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeCancelModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Close</button><button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2"><i class="ri-close-circle-line"></i> Confirm Cancellation</button></div>
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
                    <span>Tailors</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
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
                    <span>Customers</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="customer-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="activecustomers.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Active Customers</a>
                    <a href="removedcustomers.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Removed Customers</a>
                </div>
            </div>
            <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Designs</a>
            <div class="relative">
                 <button id="stitch-dropdown-button" data-dropdown-toggle="stitch-dropdown-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Stitch Requests</span><i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="stitch-dropdown-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="activerequests.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Active Requests</a>
                    <a href="shippedrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:text-purple-600 ">Shipped Requests</a>
                    <a href="paymentpendingrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:text-purple-600 ">Payment Pending</a>
                    <a href="paidrequests.php" class="block px-4 py-2 text-sm text-gray-700 hover:text-purple-600 ">Paid Requests</a>
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
                <h1 class="text-3xl font-bold text-gray-800">Active Stitch Requests</h1>
                <p class="text-gray-500 mt-1">Monitor all ongoing requests from assignment to pre-shipment.</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-4 rounded-xl text-center"><div class="text-3xl font-bold text-blue-700"><?php echo $total_active; ?></div><div class="text-blue-700 text-sm font-medium">Total Active</div></div>
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-4 rounded-xl text-center"><div class="text-3xl font-bold text-yellow-700"><?php echo $pending_requests; ?></div><div class="text-yellow-700 text-sm font-medium">Pending</div></div>
                <div class="bg-gradient-to-br from-green-100 to-green-200 p-4 rounded-xl text-center col-span-2 lg:col-span-1"><div class="text-3xl font-bold text-green-700"><?php echo $in_progress_requests; ?></div><div class="text-green-700 text-sm font-medium">In Progress</div></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <form method="get" action="activerequests.php" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <select name="status" onchange="this.form.submit()" class="w-full md:w-auto pr-8 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                            <option value="all">All Active Statuses</option>
                            <option value="Pending" <?php if($filter_status == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Accepted" <?php if($filter_status == 'Accepted') echo 'selected'; ?>>Accepted</option>
                            <option value="Paid" <?php if($filter_status == 'Paid') echo 'selected'; ?>>Paid</option>
                        </select>
                    </div>
                    <div class="relative">
                        <i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by request, customer, tailor..." class="pl-10 pr-4 py-2 w-full md:w-72 border border-gray-300 rounded-lg text-sm"/>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Request Info</th>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Assigned Tailor</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="5" class="px-6 py-16 text-center"><i class="ri-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Active Requests Found</h3><p class="text-gray-500 mt-1">Try adjusting your search or filter criteria.</p></td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $row): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="<?php echo htmlspecialchars(explode(',', $row['simg'])[0]); ?>" class="w-12 h-16 object-cover rounded-lg border shrink-0">
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($row['sdname']); ?></div>
                                            <div class="text-gray-500 text-xs">ID: <?php echo $row['sdid']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($row['cname']); ?></td>
                                <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($row['tname'] ?? 'Unassigned'); ?></td>
                                <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusClass($row['sstatus']); ?>"><?php echo htmlspecialchars($row['sstatus']); ?></span></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-4">
                                        <a href="view_request_details.php?id=<?php echo $row['sdid']; ?>" class="font-medium text-purple-600 hover:text-purple-800">View</a>
                                        <button onclick="openCancelModal('<?php echo $row['sdid']; ?>', '<?php echo htmlspecialchars(addslashes($row['sdname'])); ?>', '<?php echo htmlspecialchars($row['cemail']); ?>', '<?php echo htmlspecialchars($row['temail'] ?? ''); ?>')" class="font-medium text-red-600 hover:text-red-800">Cancel</button>
                                    </div>
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
        function openCancelModal(sdid, rname, cemail, temail) {
            document.getElementById('modal-cancel-sdid').value = sdid;
            document.getElementById('modal-cancel-rname').value = rname;
            document.getElementById('modal-cancel-cemail').value = cemail;
            document.getElementById('modal-cancel-temail').value = temail;
            document.getElementById('cancel-modal').classList.remove('hidden');
        }
        function closeCancelModal() { document.getElementById('cancel-modal').classList.add('hidden'); }
        
        document.addEventListener('DOMContentLoaded', () => {
            // Flash message handling
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