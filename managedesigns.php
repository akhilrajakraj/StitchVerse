
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
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';

// --- Fetch stats and filter options ---
$total_designs = $db->selectData("SELECT COUNT(did) as count FROM upload")->fetch_assoc()['count'];
$design_types_result = $db->selectData("SELECT DISTINCT dtype FROM upload ORDER BY dtype ASC");

// --- Build the main query securely ---
$sql_main = "SELECT u.*, t.tname, t.email, 
              (SELECT COUNT(oid) FROM orderdesign WHERE did = u.did) as order_count
             FROM upload u
             JOIN treg t ON u.uid = t.tid
             WHERE 1=1";
$types = "";
$params = [];

if ($filter_type !== 'all') {
    $sql_main .= " AND u.dtype = ?";
    $types .= "s";
    $params[] = $filter_type;
}
if (!empty($search_term)) {
    $searchTermLike = "%" . $search_term . "%";
    $sql_main .= " AND (u.dname LIKE ? OR t.tname LIKE ?)";
    $types .= "ss";
    $params[] = $searchTermLike; 
    $params[] = $searchTermLike;
}
$sql_main .= " ORDER BY u.did DESC";

if (!empty($types)) {
    $rs_main = $db->selectData($sql_main, $types, ...$params);
} else {
    $rs_main = $db->selectData($sql_main);
}

// Store fetched designs in an array to avoid re-querying
$designs = [];
if ($rs_main) {
    while ($row = $rs_main->fetch_assoc()) {
        $designs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Designs - StitchVerse Admin</title>
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
        #success-popup, #error-popup {
            z-index: 100;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full"><i class="ri-checkbox-circle-line text-2xl"></i><span></span></div>
    <div id="error-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl flex items-center gap-3 transition-transform duration-300 translate-x-full"><i class="ri-error-warning-line text-2xl"></i><span></span></div>
    
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Remove Design & Notify Tailor</h3><button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="ddelete.php" method="POST" class="p-6">
                <input type="hidden" name="did" id="modal-delete-did">
                <input type="hidden" name="tailor_email" id="modal-delete-email">
                <input type="hidden" name="design_name" id="modal-delete-dname">
                <div class="flex items-start gap-4 mb-4"><img id="modal-delete-img" src="" class="w-16 h-20 object-cover rounded-lg border"><div><p class="text-gray-600">You are about to delete the design:</p><p class="font-bold text-gray-800" id="modal-delete-dname-display"></p></div></div>
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Reason for Removal*</label><textarea name="reason" required rows="4" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Provide a clear reason for removing this design. This will be sent to the tailor."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeDeleteModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Cancel</button><button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2"><i class="ri-delete-bin-line"></i> Confirm & Send Notification</button></div>
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

            <a href="managedesigns.php" class="text-purple-600 font-semibold">Designs</a>
            
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
                <h1 class="text-3xl font-bold text-gray-800">Manage Designs</h1>
                <p class="text-gray-500 mt-1">Review, filter, and manage all designs uploaded by tailors.</p>
            </div>
            <div class="bg-gradient-to-br from-purple-100 to-pink-200 p-4 rounded-xl text-center shrink-0">
                <div class="text-3xl font-bold text-purple-700"><?php echo $total_designs; ?></div>
                <div class="text-purple-700 text-sm font-medium">Total Designs</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <form method="get" action="managedesigns.php" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <label for="type-filter" class="sr-only">Filter by Type</label>
                        <select id="type-filter" name="type" onchange="this.form.submit()" class="w-full md:w-auto pr-8 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-purple-500 focus:border-purple-500">
                            <option value="all">All Design Types</option>
                            <?php 
                            if ($design_types_result) {
                                while($row_type = $design_types_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($row_type['dtype']); ?>" <?php if($filter_type == $row_type['dtype']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($row_type['dtype']); ?>
                                </option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div class="relative">
                        <label for="search-input" class="sr-only">Search</label>
                        <i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input id="search-input" type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by design or tailor name..." class="pl-10 pr-4 py-2 w-full md:w-72 border border-gray-300 rounded-lg text-sm focus:ring-purple-500 focus:border-purple-500"/>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                       <tr>
                            <th scope="col" class="px-6 py-3">Design Info</th>
                            <th scope="col" class="px-6 py-3">Uploaded By</th>
                            <th scope="col" class="px-6 py-3">Price</th>
                            <th scope="col" class="px-6 py-3">Orders</th>
                            <th scope="col" class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($designs)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <i class="ri-search-line text-5xl text-gray-300"></i>
                                    <h3 class="text-xl font-semibold text-gray-800 mt-4">No Designs Found</h3>
                                    <p class="text-gray-500 mt-1">Your search or filter returned no results. Try adjusting your criteria.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($designs as $design): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img src="<?php echo htmlspecialchars($design['dimg']); ?>" class="w-12 h-16 object-cover rounded-lg border flex-shrink-0" alt="Design Image">
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo htmlspecialchars($design['dname']); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars($design['dtype']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div><?php echo htmlspecialchars($design['tname']); ?></div>
                                    <div class="text-gray-500 font-mono text-xs"><?php echo htmlspecialchars($design['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-purple-600">₹<?php echo number_format($design['dprice']); ?></td>
                                <td class="px-6 py-4 font-medium text-gray-800"><?php echo htmlspecialchars($design['order_count']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="openDeleteModal('<?php echo $design['did']; ?>', '<?php echo htmlspecialchars(addslashes($design['dname'])); ?>', '<?php echo htmlspecialchars($design['email']); ?>', '<?php echo htmlspecialchars($design['dimg']); ?>')" class="font-medium text-red-600 hover:text-red-800 hover:underline">
                                        Remove
                                    </button>
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
        // Delete modal functions are unchanged
        function openDeleteModal(did, dname, email, img) {
            document.getElementById('modal-delete-did').value = did;
            document.getElementById('modal-delete-email').value = email;
            document.getElementById('modal-delete-dname').value = dname;
            document.getElementById('modal-delete-dname-display').textContent = dname;
            document.getElementById('modal-delete-img').src = img;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() { document.getElementById('delete-modal').classList.add('hidden'); }
        
        document.addEventListener('DOMContentLoaded', () => {
            
            // Toast popup script
            function showPopup(type, message) {
                const popup = document.getElementById(type + '-popup');
                if(popup) {
                    popup.querySelector('span').textContent = message;
                    popup.classList.remove('hidden');
                    setTimeout(() => { popup.classList.remove('translate-x-full'); }, 10);
                    setTimeout(() => {
                        popup.classList.add('translate-x-full');
                        setTimeout(() => { popup.classList.add('hidden'); }, 300);
                    }, 5000);
                }
            }
            
            // Check for PHP session messages to show popups
            <?php if (isset($_SESSION['action_success'])): ?>
                showPopup('success', '<?php echo addslashes($_SESSION['action_success']); ?>');
                <?php unset($_SESSION['action_success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['action_error'])): ?>
                showPopup('error', '<?php echo addslashes($_SESSION['action_error']); ?>');
                <?php unset($_SESSION['action_error']); ?>
            <?php endif; ?>

            // === NEW: Dropdown Menu Logic ===
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenu = document.getElementById(button.getAttribute('data-dropdown-toggle'));
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const wasOpen = !dropdownMenu.classList.contains('hidden');
                    // Hide all dropdowns first
                    document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => {
                        document.getElementById(b.getAttribute('data-dropdown-toggle')).classList.add('hidden');
                        b.setAttribute('aria-expanded', 'false');
                    });
                    // If it wasn't already open, show it
                    if (!wasOpen) {
                        dropdownMenu.classList.toggle('hidden');
                        button.setAttribute('aria-expanded', !dropdownMenu.classList.contains('hidden'));
                    }
                });
            });

            // Hide dropdowns when clicking outside
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