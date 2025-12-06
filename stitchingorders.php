
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// Fetch customer's name for the header
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}

// --- Helper function for styling status badges ---
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'paid':
        case 'shipped': // Changed from 'completed' to 'shipped'
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'payment pending':
            return 'bg-blue-100 text-blue-800';
        case 'rejected':
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// --- Get filter status from the URL ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- Fetch data for the stats cards ---
function getStitchRequestCount($db, $customerId, $status = '') {
    if (empty($status)) {
        $sql = "SELECT COUNT(sdid) as count FROM stitchreq WHERE uid = ?";
        return $db->selectData($sql, "i", $customerId)->fetch_assoc()['count'];
    } else {
        $sql = "SELECT COUNT(sdid) as count FROM stitchreq WHERE uid = ? AND sstatus = ?";
        return $db->selectData($sql, "is", $customerId, $status)->fetch_assoc()['count'];
    }
}

// NEW: Function to specifically get the count for "Payment Pending" status
function getPaymentPendingCount($db, $customerId) {
    $sql = "SELECT COUNT(sdid) as count FROM stitchreq WHERE uid = ? AND sstatus = 'Accepted' AND sprice IS NOT NULL AND sprice > 0";
    $result = $db->selectData($sql, "i", $customerId);
    // Ensure we return 0 if there's no result
    return $result ? $result->fetch_assoc()['count'] : 0;
}

$count_all = getStitchRequestCount($db, $customer_id);
$count_pending = getStitchRequestCount($db, $customer_id, 'Pending');
$count_accepted = getStitchRequestCount($db, $customer_id, 'Accepted');
$count_payment_pending = getPaymentPendingCount($db, $customer_id); // NEW count
$count_paid = getStitchRequestCount($db, $customer_id, 'Paid');

// --- Build the main query based on the filter ---
$sql_requests = "SELECT sr.*, t.tname 
                 FROM stitchreq sr 
                 LEFT JOIN treg t ON sr.tid = t.tid 
                 WHERE sr.uid = ?";

// Initialize parameters correctly
$param_types = "i";
$param_vars = [$customer_id];

// If a status filter is active, add it to the query and the parameters
// NEW: Added a special case for our derived 'Payment Pending' status
if ($filter_status === 'Payment Pending') {
    $sql_requests .= " AND sr.sstatus = 'Accepted' AND sr.sprice IS NOT NULL AND sr.sprice > 0";
} else if ($filter_status !== 'all') {
    $sql_requests .= " AND sr.sstatus = ?";
    $param_types .= "s";
    $param_vars[] = $filter_status;
}

$sql_requests .= " ORDER BY sr.sdid DESC";

// Combine the type string and variables into a final array for the function call
$final_params = array_merge([$param_types], $param_vars);
$rs_requests = $db->selectData($sql_requests, ...$final_params);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Stitching Orders - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; } 
        #alert-modal.hidden { display: none; }
        #modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div id="modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8 transform scale-95 opacity-0">
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-5 bg-green-100">
                <i class="ri-checkbox-circle-line text-5xl text-green-500"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Success!</h3>
            <p id="modal-message" class="text-gray-600 mb-8"></p>
            <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600 transition-colors">Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600 transition-colors">Tailors</a>
            <a href="customreq1.php" class="text-gray-700 hover:text-purple-600 transition-colors">Stitch Request</a>
          </nav>
          <div class="hidden md:flex items-center space-x-6">
            <div class="relative" id="profile-dropdown-container">
              <button id="profile-dropdown-button" class="flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                      <div class="px-4 py-3 border-b border-gray-100"><p class="text-sm text-gray-500">Signed in as</p><p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p></div>
                      <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a></div>
                      <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm font-semibold text-purple-600 bg-purple-50">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800">My Stitching Requests</h1>
            <p class="text-gray-600 mt-2">Track all your custom stitch requests and their progress.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <a href="stitchingorders.php" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-purple-400 transition-all"><div class="text-2xl font-bold text-purple-600"><?php echo $count_all; ?></div><div class="text-purple-600 text-sm font-medium">Total Requests</div></a>
            <a href="stitchingorders.php?status=Pending" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-yellow-400 transition-all"><div class="text-2xl font-bold text-yellow-600"><?php echo $count_pending; ?></div><div class="text-yellow-600 text-sm font-medium">Pending</div></a>
            <a href="stitchingorders.php?status=Accepted" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-green-400 transition-all"><div class="text-2xl font-bold text-green-600"><?php echo $count_accepted; ?></div><div class="text-green-600 text-sm font-medium">Accepted</div></a>
            <a href="stitchingorders.php?status=Payment+Pending" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-blue-400 transition-all"><div class="text-2xl font-bold text-blue-600"><?php echo $count_payment_pending; ?></div><div class="text-blue-600 text-sm font-medium">Payment Pending</div></a>
            <a href="stitchingorders.php?status=Paid" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-green-400 transition-all"><div class="text-2xl font-bold text-green-600"><?php echo $count_paid; ?></div><div class="text-green-600 text-sm font-medium">Paid</div></a>
        </div>
        
        <div class="bg-white p-4 rounded-xl shadow-md mb-6">
            <form action="stitchingorders.php" method="GET" class="flex items-center gap-4">
                <label for="status-filter" class="text-sm font-medium text-gray-700">Filter by status:</label>
                <select name="status" id="status-filter" onchange="this.form.submit()" class="w-full md:w-auto p-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                    <option value="all" <?php if($filter_status == 'all') echo 'selected'; ?>>All Statuses</option>
                    <option value="Pending" <?php if($filter_status == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Accepted" <?php if($filter_status == 'Accepted') echo 'selected'; ?>>Accepted</option>
                    <option value="Payment Pending" <?php if($filter_status == 'Payment Pending') echo 'selected'; ?>>Payment Pending</option>
                    <option value="Paid" <?php if($filter_status == 'Paid') echo 'selected'; ?>>Paid</option>
                    <option value="Shipped" <?php if($filter_status == 'Shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="Cancelled" <?php if($filter_status == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                    <option value="Rejected" <?php if($filter_status == 'Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </form>
        </div>

        <div class="space-y-4">
            <?php if ($rs_requests && $rs_requests->num_rows > 0): ?>
                <?php while ($row = $rs_requests->fetch_assoc()): ?>
                    <?php
                        // This display logic correctly handles showing "Payment Pending" already, no changes needed here.
                        $current_status = $row['sstatus'];
                        if (strtolower($row['sstatus']) == 'accepted' && !empty($row['sprice'])) {
                            $current_status = 'Payment Pending';
                        }
                    ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col md:flex-row items-center gap-4">
                        <div class="w-24 h-32 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                            <img src="<?php echo htmlspecialchars(!empty($row['simg']) ? $row['simg'] : 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($row['sdname']); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow text-center md:text-left">
                            <p class="text-sm text-gray-500">Request ID: #<?php echo htmlspecialchars($row['sdid']); ?></p>
                            <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($row['sdname']); ?></h3>
                            <p class="text-sm text-gray-600">Tailor: <span class="font-medium"><?php echo htmlspecialchars($row['tname'] ?? 'Pending Assignment'); ?></span></p>
                            <p class="text-sm text-gray-600">Desired by: <span class="font-medium"><?php echo date("d M, Y", strtotime($row['sddate'])); ?></span></p>
                        </div>
                        <div class="flex-shrink-0 text-center md:text-right">
                            <p class="text-xl font-bold text-purple-600 mb-2">
                                <?php echo !empty($row['sprice']) ? '₹' . number_format($row['sprice']) : 'Price TBD'; ?>
                            </p>
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold <?php echo getStatusColor($current_status); ?>">
                                <?php echo htmlspecialchars($current_status); ?>
                            </span>
                            <div class="mt-3">
                                <a href="stitchrequest_details.php?id=<?php echo $row['sdid']; ?>" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-black transition-colors">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-2xl shadow-md">
                    <i class="ri-file-search-line text-6xl text-gray-300"></i>
                    <h3 class="text-2xl font-semibold text-gray-800 mt-4">No Stitching Requests Found</h3>
                    <p class="text-gray-600 mt-2">You have no stitching requests with the selected status. Try a different filter!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <a href="customerhome.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
                    <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide for custom clothing that fits perfectly.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Customers</h4>
                    <ul class="space-y-2">
                        <li><a href="viewdesigns.php" class="text-gray-400 hover:text-white">Browse Gallery</a></li>
                        <li><a href="cviewt.php" class="text-gray-400 hover:text-white">Find Tailors</a></li>
                        <li><a href="customreq1.php" class="text-gray-400 hover:text-white">Place Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">My Account</h4>
                    <ul class="space-y-2">
                        <li><a href="cupdate.php" class="text-gray-400 hover:text-white">My Profile</a></li>
                        <li><a href="meas.php" class="text-gray-400 hover:text-white">Measurements</a></li>
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Logout</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- SCRIPT FOR SUCCESS MODAL ---
        const modal = document.getElementById('alert-modal');
        const modalContent = document.getElementById('modal-content');
        const modalMessage = document.getElementById('modal-message');
        const closeModalBtn = document.getElementById('modal-close-btn');

        function showModal(message) {
            modalMessage.innerHTML = message;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 50);
        }

        function hideModal() {
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        
        closeModalBtn.addEventListener('click', hideModal);

        <?php
            if (isset($_SESSION['stitch_request_success'])) {
                echo "showModal(" . json_encode($_SESSION['stitch_request_success']) . ");";
                unset($_SESSION['stitch_request_success']); 
            }
        ?>

        // --- SCRIPT FOR HEADER DROPDOWN ---
        const profileDropdownButton = document.getElementById('profile-dropdown-button');
        const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
        if (profileDropdownButton && profileDropdownMenu) {
            profileDropdownButton.addEventListener('click', (event) => {
                event.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
            });
            window.addEventListener('click', (event) => {
                if (!profileDropdownButton.contains(event.target)) {
                    profileDropdownMenu.classList.add('hidden');
                }
            });
        }
    });
    </script>
</body>
</html>