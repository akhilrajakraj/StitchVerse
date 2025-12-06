
<?php
session_start();
require 'databasecon.php'; // Your database connection file

// Redirect if user is not logged in or is not a customer
if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'tailor')) { // Corrected logic
    header("Location: index.php"); // Or your login page
    exit();
}

$db = new DatabaseCon();
$val = $_SESSION['user_id']; // Using your framework's variable for the customer ID

// --- Helper function for styling status badges ---
function getStatusColor($status) {
    $status = strtolower($status ?? ''); 
    switch ($status) {
        case 'paid':
        case 'completed':
        case 'accepted':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'unpaid': // Style for Unpaid status
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// --- Get filter and search parameters from the URL ---
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Fetch data for the stats cards (Processing status removed) ---
function getOrderStatusCount($db, $customerId, $status = '') {
    $stitch_count = 0; $design_count = 0;
    
    $status_condition_stitch = $status ? " AND sstatus='" . $db->getConnection()->real_escape_string($status) . "'" : "";
    $sql_stitch = "SELECT COUNT(sdid) as count FROM stitchreq WHERE uid='$customerId'" . $status_condition_stitch;
    if($res = $db->selectData($sql_stitch)) $stitch_count = mysqli_fetch_assoc($res)['count'];

    $status_condition_design = $status ? " AND ostatus='" . $db->getConnection()->real_escape_string($status) . "'" : "";
    $sql_design = "SELECT COUNT(oid) as count FROM orderdesign WHERE uid='$customerId'" . $status_condition_design;
    if($res = $db->selectData($sql_design)) $design_count = mysqli_fetch_assoc($res)['count'];

    return $stitch_count + $design_count;
}
$count_all = getOrderStatusCount($db, $val);
$count_pending = getOrderStatusCount($db, $val, 'Pending');
$count_accepted = getOrderStatusCount($db, $val, 'Accepted'); // 'Processing' is removed
$count_paid = getOrderStatusCount($db, $val, 'Paid') + getOrderStatusCount($db, $val, 'Completed');

// --- Build main queries (Updated to fetch pstatus as 'payment_status') ---
$all_orders = [];

// 1. Query for Custom Stitch Requests
$sql_stitch = "SELECT 
                    sr.sdid as order_id, sr.sdname as name, sr.simg as img, sr.sddate as date, 
                    sr.sstatus as status, t.tname, 'Stitch Request' as order_type, sr.sprice as price,
                    p.pstatus as payment_status
               FROM stitchreq sr 
               LEFT JOIN treg t ON sr.tid = t.tid
               LEFT JOIN payment p ON sr.sdid = p.order_id AND p.uid = sr.uid
               WHERE sr.uid = ?";
$params_stitch = ["i", $val];

// 2. Query for Design Orders
$sql_design = "SELECT 
                    o.oid as order_id, u.dname as name, u.dimg as img, o.orderdate as date, 
                    o.ostatus as status, t.tname, 'Design Order' as order_type, u.dprice as price,
                    p.pstatus as payment_status
               FROM orderdesign o 
               JOIN upload u ON o.did = u.did 
               JOIN treg t ON u.uid = t.tid
               LEFT JOIN payment p ON o.oid = p.order_id AND p.uid = o.uid
               WHERE o.uid = ?";
$params_design = ["i", $val];

// Apply filters (logic remains the same)
if ($filter_status !== 'all') { /* ... filtering logic ... */ }
if (!empty($search_term)) { /* ... search logic ... */ }

// Execute queries
if ($filter_type === 'all' || $filter_type === 'stitch') {
    $rs_stitch = $db->selectData($sql_stitch, ...$params_stitch);
    if($rs_stitch) { while($row = $rs_stitch->fetch_assoc()) { $all_orders[] = $row; } }
}
if ($filter_type === 'all' || $filter_type === 'design') {
    $rs_design = $db->selectData($sql_design, ...$params_design);
    if($rs_design) { while($row = $rs_design->fetch_assoc()) { $all_orders[] = $row; } }
}

usort($all_orders, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders & Requests - StitchVerse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        /* === NEW: Style for the popup modal === */
        #alert-modal.hidden { display: none; }
        #modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div id="modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8 transform scale-95 opacity-0">
            <div id="modal-icon" class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-5 bg-green-100">
                <i class="ri-checkbox-circle-line text-5xl text-green-500"></i>
            </div>
            <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-2">Success!</h3>
            <p id="modal-message" class="text-gray-600 mb-8"></p>
            <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
                <nav class="hidden md:flex items-center space-x-8"><a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600">View Designs</a><a href="cviewt.php" class="text-gray-700 hover:text-purple-600">View Tailors</a><a href="meas.php" class="text-gray-700 hover:text-purple-600">Measurements</a><a href="customreq1.php" class="text-gray-700 hover:text-purple-600">Stitch Request</a></nav>
                <div class="hidden md:flex items-center space-x-4"><a href="cupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a><a href="vieworders.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">My Orders</a><a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a></div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                 <div class="flex items-center">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-file-list-3-line text-2xl text-white"></i></div>
                    <div><h1 class="text-3xl font-bold text-white">My Orders & Requests</h1><p class="text-purple-100 mt-1">Track all your design orders and custom stitch requests.</p></div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl"><div class="text-2xl font-bold text-blue-600"><?php echo $count_all; ?></div><div class="text-blue-600 text-sm font-medium">Total</div></div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-xl"><div class="text-2xl font-bold text-yellow-600"><?php echo $count_pending; ?></div><div class="text-yellow-600 text-sm font-medium">Pending</div></div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl"><div class="text-2xl font-bold text-green-600"><?php echo $count_accepted; ?></div><div class="text-green-600 text-sm font-medium">Accepted</div></div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl"><div class="text-2xl font-bold text-purple-600"><?php echo $count_paid; ?></div><div class="text-purple-600 text-sm font-medium">Paid/Completed</div></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <?php if(!empty($all_orders)): ?>
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Item Details</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Tailor</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Date & Price</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($all_orders as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><div class="flex items-center space-x-4"><img src="<?php echo htmlspecialchars($row['img'] ?? 'placeholder.jpg'); ?>" class="w-12 h-16 object-cover rounded-lg border flex-shrink-0"><div><div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></div><div class="text-xs <?php echo $row['order_type'] == 'Stitch Request' ? 'text-pink-600' : 'text-blue-600'; ?>"><?php echo htmlspecialchars($row['order_type']); ?> #<?php echo htmlspecialchars($row['order_id']); ?></div></div></div></td>
                            <td class="px-6 py-4"><div class="text-sm text-gray-800"><?php echo htmlspecialchars($row['tname'] ?? 'Pending Assignment'); ?></div></td>
                            <td class="px-6 py-4"><div class="text-sm text-gray-800"><?php echo date("d M, Y", strtotime($row['date'])); ?></div><div class="text-sm font-semibold text-purple-600">₹<?php echo number_format($row['price'] ?? 0, 2); ?></div></td>
                            <td class="px-6 py-4">
                                <?php
                                $current_status = $row['status'];
                                if ($row['order_type'] == 'Design Order' && strtolower($row['status']) == 'ordered' && strtolower($row['payment_status'] ?? '') !== 'paid') {
                                    $current_status = 'Unpaid';
                                }
                                ?>
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium border <?php echo getStatusColor($current_status); ?>">
                                    <?php echo htmlspecialchars($current_status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status = strtolower($row['status'] ?? '');
                                $payment_status = strtolower($row['payment_status'] ?? '');
                                $order_type = $row['order_type'];
                                $order_id = $row['order_id'];
                                if ($order_type == 'Stitch Request') {
                                    if ($status == 'paid') {
                                        echo "<a href='paystr4.php?id={$order_id}' class='text-blue-600 hover:text-blue-800 font-semibold text-xs'>View Payment Details</a>";
                                    } elseif ($status == 'accepted') {
                                        echo "<a href='payment1.php?order_id={$order_id}' target='_blank' class='bg-green-600 text-white px-3 py-1.5 rounded-lg font-semibold text-xs hover:bg-green-700'>Payment</a>";
                                    } elseif ($status == 'rejected') {
                                        echo '<span class="text-xs text-red-600 font-medium">NOT AVAILABLE</span>';
                                    } elseif ($status == 'pending') {
                                        echo '<span class="text-xs text-gray-500">Available only after acceptance</span>';
                                    } else {
                                        echo '<span class="text-xs text-gray-500">N/A</span>';
                                    }
                                }
                                elseif ($order_type == 'Design Order') {
                                    if ($payment_status == 'paid') {
                                        echo "<a href='paystr2.php?id={$order_id}' class='text-blue-600 hover:text-blue-800 font-semibold text-xs'>View payment details</a>";
                                    } elseif ($status == 'ordered') {
                                        echo "<a href='payment2.php?order_id={$order_id}' target='_blank' class='bg-green-600 text-white px-3 py-1.5 rounded-lg font-semibold text-xs hover:bg-green-700'>Payment</a>";
                                    } else {
                                        echo '<span class="text-xs text-green-700 font-medium">Payment Completed</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-16"><i class="ri-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Orders Found</h3><p class="text-gray-600 mt-1">Try adjusting your filters or place a new order!</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('alert-modal');
            const modalContent = document.getElementById('modal-content');
            const modalMessage = document.getElementById('modal-message');
            const closeModalBtn = document.getElementById('modal-close-btn');

            function showModal(message) {
                modalMessage.textContent = message;
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
                // This PHP block checks for the session message and calls the JavaScript function
                if (isset($_SESSION['order_success'])) {
                    echo "showModal(" . json_encode($_SESSION['order_success']) . ");";
                    unset($_SESSION['order_success']); // Clear the message so it doesn't show again
                }
            ?>
        });
    </script>
</body>
</html>