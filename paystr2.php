<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID provided.");
}

$db = new DatabaseCon();
$order_id = $_GET['id'];
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// Securely fetch data and verify ownership
$sql = "SELECT 
            o.oid, o.orderdate, 
            u.dname, u.dtype, u.ddesc, u.dprice, u.dimg, 
            t.tname, 
            c.cname, c.address, c.city, c.distr, c.pincode,
            p.pid, p.pdate, p.card_name, p.card_no
        FROM orderdesign AS o
        JOIN upload AS u ON o.did = u.did
        JOIN treg AS t ON u.uid = t.tid
        JOIN creg AS c ON o.uid = c.cid
        JOIN payment AS p ON o.oid = p.order_id
        WHERE p.order_id = ? AND o.uid = ? AND p.pstatus = 'Paid'";

$result = $db->selectData($sql, "ii", $order_id, $customer_id);

if ($result && $result->num_rows === 1) {
    $details = $result->fetch_assoc();
    $customer_name = explode(' ', trim($details['cname']))[0];
} else {
    die("Error: Could not find payment details for this order.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - Order #<?php echo htmlspecialchars($details['oid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gray-50 min-h-screen">

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
                 <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="designorder_details.php?id=<?php echo $details['oid']; ?>" class="inline-block mb-2 text-sm font-medium text-purple-600 hover:text-purple-800 transition-colors">&larr; Back to Order Details</a>
                    <h1 class="text-3xl font-bold text-gray-800">Payment Details</h1>
                </div>
                <a href="receipt2.php?order_id=<?php echo $details['oid']; ?>" target="_blank" class="px-4 py-2 bg-gray-800 text-white text-sm text-center rounded-lg hover:bg-black font-semibold flex items-center justify-center gap-2">
                    <i class="ri-download-2-line"></i> Download Receipt
                </a>
            </div>
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-b pb-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Billed To</h3>
                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($details['cname']); ?></p>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            <?php echo htmlspecialchars($details['address']); ?><br>
                            <?php echo htmlspecialchars($details['city']); ?>, <?php echo htmlspecialchars($details['distr']); ?>, <?php echo htmlspecialchars($details['pincode']); ?>
                        </p>
                    </div>
                    <div class="md:text-right">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Transaction Details</h3>
                        <p class="text-gray-600 text-sm"><span class="font-semibold text-gray-800">Order ID:</span> #<?php echo htmlspecialchars($details['oid']); ?></p>
                        <p class="text-gray-600 text-sm"><span class="font-semibold text-gray-800">Payment ID:</span> #<?php echo htmlspecialchars($details['pid']); ?></p>
                        <p class="text-gray-600 text-sm"><span class="font-semibold text-gray-800">Date Paid:</span> <?php echo date("d F, Y", strtotime($details['pdate'])); ?></p>
                    </div>
                </div>

                <div class="py-6 border-b">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Method</h3>
                    <div class="flex items-center gap-3">
                        <i class="ri-bank-card-line text-2xl text-purple-600"></i>
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($details['card_name']); ?></p>
                            <p class="text-sm text-gray-500">Paid with card ending in <?php echo htmlspecialchars(substr($details['card_no'], -4)); ?></p>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-md">
                            <div class="flex items-center gap-4">
                                <img src="<?php echo htmlspecialchars($details['dimg']); ?>" class="w-12 h-16 object-cover rounded-md border">
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($details['dname']); ?></p>
                                    <p class="text-sm text-gray-500">Sold by <?php echo htmlspecialchars($details['tname']); ?></p>
                                </div>
                            </div>
                            <p class="text-md font-semibold text-gray-800">₹<?php echo number_format($details['dprice']); ?></p>
                        </div>
                        <div class="flex justify-between text-gray-600 text-sm">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($details['dprice']); ?></span>
                        </div>
                         <div class="flex justify-between text-gray-600 text-sm">
                            <span>Delivery Fee</span>
                            <span>₹40.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-800 border-t pt-3 mt-2">
                            <span>Total Paid</span>
                            <span class="text-purple-600">₹<?php echo number_format($details['dprice'] + 40); ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- HEADER DROPDOWN SCRIPT ---
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