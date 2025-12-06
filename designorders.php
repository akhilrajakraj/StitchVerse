
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// --- Get and clear any action messages from the session for notifications ---
$success_message = '';
if (isset($_SESSION['action_success'])) {
    $success_message = $_SESSION['action_success'];
    unset($_SESSION['action_success']);
}
$error_message = '';
if (isset($_SESSION['action_error'])) {
    $error_message = $_SESSION['action_error'];
    unset($_SESSION['action_error']);
}
// --- End message handling ---

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
            return 'bg-green-100 text-green-800';
        case 'shipped':
            return 'bg-blue-100 text-blue-800';
        case 'payment pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// --- Get filter status from the URL ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- Fetch data for the stats cards ---
function getDesignOrderCount($db, $customerId, $status = '') {
    if (empty($status) || $status == 'all') {
        $sql = "SELECT COUNT(oid) as count FROM orderdesign WHERE uid = ?";
        return $db->selectData($sql, "i", $customerId)->fetch_assoc()['count'];
    } else {
        if ($status === 'Payment Pending') {
            $sql = "SELECT COUNT(oid) as count FROM orderdesign WHERE uid = ? AND ostatus NOT IN ('Paid', 'Shipped', 'Cancelled')";
            return $db->selectData($sql, "i", $customerId)->fetch_assoc()['count'];
        }
        $sql = "SELECT COUNT(oid) as count FROM orderdesign WHERE uid = ? AND ostatus = ?";
        return $db->selectData($sql, "is", $customerId, $status)->fetch_assoc()['count'];
    }
}
$count_all = getDesignOrderCount($db, $customer_id, 'all');
$count_pending = getDesignOrderCount($db, $customer_id, 'Payment Pending');
$count_paid = getDesignOrderCount($db, $customer_id, 'Paid');
$count_shipped = getDesignOrderCount($db, $customer_id, 'Shipped');
$count_cancelled = getDesignOrderCount($db, $customer_id, 'Cancelled');

// --- Build the main query based on the filter ---
$sql_orders = "SELECT o.oid, o.orderdate, o.ostatus, u.dname, u.dimg, u.dprice, t.tname 
               FROM orderdesign o
               JOIN upload u ON o.did = u.did
               JOIN treg t ON u.uid = t.tid
               WHERE o.uid = ?";

$param_types = "i";
$param_vars = [$customer_id];

if ($filter_status !== 'all') {
    if ($filter_status === 'Payment Pending') {
         $sql_orders .= " AND o.ostatus NOT IN ('Paid', 'Shipped', 'Cancelled')";
    } else {
        $sql_orders .= " AND o.ostatus = ?";
        $param_types .= "s";
        $param_vars[] = $filter_status;
    }
}
$sql_orders .= " ORDER BY o.orderdate DESC, o.oid DESC";

$rs_orders = $db->selectData($sql_orders, $param_types, ...$param_vars);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Design Orders - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- ===== Pop-up Notification Structure ===== -->
    <div id="toast-notification" class="hidden fixed top-5 right-5 z-[150] w-full max-w-xs p-4 text-gray-700 bg-white rounded-2xl shadow-xl" role="alert">
        <div class="flex items-center">
            <div id="toast-icon-container" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
                <!-- Icon will be dynamically inserted here -->
            </div>
            <div id="toast-message" class="ms-3 text-sm font-semibold"></div>
            <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" data-dismiss-target="#toast-notification" aria-label="Close">
                <span class="sr-only">Close</span>
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
    </div>
    <!-- ===== End Notification ===== -->

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
                 <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm font-semibold text-purple-600 bg-purple-50">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800">My Design Orders</h1>
            <p class="text-gray-600 mt-2">Track all your purchased designs and their shipping status.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <a href="designorders.php" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-purple-400 transition-all"><div class="text-2xl font-bold text-purple-600"><?php echo $count_all; ?></div><div class="text-purple-600 text-sm font-medium">Total Orders</div></a>
            <a href="designorders.php?status=Payment+Pending" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-yellow-400 transition-all"><div class="text-2xl font-bold text-yellow-600"><?php echo $count_pending; ?></div><div class="text-yellow-600 text-sm font-medium">Payment Pending</div></a>
            <a href="designorders.php?status=Paid" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-green-400 transition-all"><div class="text-2xl font-bold text-green-600"><?php echo $count_paid; ?></div><div class="text-green-600 text-sm font-medium">Paid</div></a>
            <a href="designorders.php?status=Shipped" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-blue-400 transition-all"><div class="text-2xl font-bold text-blue-600"><?php echo $count_shipped; ?></div><div class="text-blue-600 text-sm font-medium">Shipped</div></a>
            <a href="designorders.php?status=Cancelled" class="block bg-white p-4 rounded-xl shadow-sm border hover:border-red-400 transition-all"><div class="text-2xl font-bold text-red-600"><?php echo $count_cancelled; ?></div><div class="text-red-600 text-sm font-medium">Cancelled</div></a>
        </div>
        
        <div class="bg-white p-4 rounded-xl shadow-md mb-6">
            <form action="designorders.php" method="GET" class="flex items-center gap-4">
                <label for="status-filter" class="text-sm font-medium text-gray-700">Filter by status:</label>
                <select name="status" id="status-filter" onchange="this.form.submit()" class="w-full md:w-auto p-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                    <option value="all" <?php if($filter_status == 'all') echo 'selected'; ?>>All Orders</option>
                    <option value="Payment Pending" <?php if($filter_status == 'Payment Pending') echo 'selected'; ?>>Payment Pending</option>
                    <option value="Paid" <?php if($filter_status == 'Paid') echo 'selected'; ?>>Paid</option>
                    <option value="Shipped" <?php if($filter_status == 'Shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="Cancelled" <?php if($filter_status == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </form>
        </div>

        <div class="space-y-4">
            <?php if ($rs_orders && $rs_orders->num_rows > 0): ?>
                <?php while ($row = $rs_orders->fetch_assoc()): ?>
                    <?php
                        $current_status = $row['ostatus'];
                        if (!in_array(strtolower($current_status), ['paid', 'shipped', 'cancelled'])) {
                            $current_status = 'Payment Pending';
                        }
                    ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col md:flex-row items-center gap-4">
                        <div class="w-24 h-32 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                            <img src="<?php echo htmlspecialchars($row['dimg']); ?>" alt="<?php echo htmlspecialchars($row['dname']); ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow text-center md:text-left">
                            <p class="text-sm text-gray-500">Order ID: #<?php echo htmlspecialchars($row['oid']); ?></p>
                            <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($row['dname']); ?></h3>
                            <p class="text-sm text-gray-600">Sold by: <span class="font-medium"><?php echo htmlspecialchars($row['tname']); ?></span></p>
                            <p class="text-sm text-gray-600">Ordered on: <span class="font-medium"><?php echo date("d M, Y", strtotime($row['orderdate'])); ?></span></p>
                        </div>
                        <div class="flex-shrink-0 text-center md:text-right">
                            <p class="text-xl font-bold text-purple-600 mb-2">₹<?php echo number_format($row['dprice']); ?></p>
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold capitalize <?php echo getStatusColor($current_status); ?>">
                                <?php echo htmlspecialchars($current_status); ?>
                            </span>
                            <div class="mt-3">
                                <a href="designorder_details.php?id=<?php echo $row['oid']; ?>" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-black transition-colors">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-2xl shadow-md">
                    <i class="ri-file-search-line text-6xl text-gray-300"></i>
                    <h3 class="text-2xl font-semibold text-gray-800 mt-4">No Design Orders Found</h3>
                    <p class="text-gray-600 mt-2">You have no design orders with the selected status. Try a different filter!</p>
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

        // ===== Toast Notification Logic =====
        const successMessage = '<?php echo $success_message; ?>';
        const errorMessage = '<?php echo $error_message; ?>';
        const toast = document.getElementById('toast-notification');
        
        if (toast) {
            const toastMessageEl = document.getElementById('toast-message');
            const toastIconContainer = document.getElementById('toast-icon-container');

            function showToast(message, type) {
                toastMessageEl.textContent = message;
                toastIconContainer.innerHTML = '';
                toastIconContainer.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg';

                if (type === 'success') {
                    toastIconContainer.classList.add('text-green-500', 'bg-green-100');
                    toastIconContainer.innerHTML = `<i class="ri-check-double-line text-xl"></i>`;
                } else { // error
                    toastIconContainer.classList.add('text-red-500', 'bg-red-100');
                    toastIconContainer.innerHTML = `<i class="ri-error-warning-line text-xl"></i>`;
                }
                toast.classList.remove('hidden');
                setTimeout(() => { toast.classList.add('hidden'); }, 5000);
            }

            if (successMessage) showToast(successMessage, 'success');
            if (errorMessage) showToast(errorMessage, 'error');
            
            const dismissButton = toast.querySelector('[data-dismiss-target]');
            if(dismissButton) {
                dismissButton.addEventListener('click', () => { toast.classList.add('hidden'); });
            }
        }
    });
    </script>
</body>
</html>
