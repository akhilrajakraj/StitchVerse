
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// The 'id' parameter from the URL is the Design ID (did)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Design ID provided.");
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$design_id = $_GET['id'];

// --- Fetch all necessary data in one go ---
// This query gets the design details, the tailor's name, and the customer's default address.
$sql = "SELECT 
            u.did, u.dname, u.dprice, u.dimg,
            t.tname,
            c.cname, c.address, c.city, c.distr, c.pincode
        FROM upload AS u 
        JOIN treg AS t ON u.uid = t.tid
        JOIN creg AS c ON c.cid = ?
        WHERE u.did = ?";
$result = $db->selectData($sql, "ii", $customer_id, $design_id);

if ($result && $result->num_rows === 1) {
    $data = $result->fetch_assoc();
    // For the header dropdown
    $customer_name = explode(' ', trim($data['cname']))[0];
} else {
    die("Error: Could not find the specified design or customer data.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Order - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
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
                <div class="py-1 border-t border-gray-100"><a href="vieworders.php?type=design" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="vieworders.php?type=stitching" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-12">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800">Checkout</h1>
                <p class="text-gray-600 mt-2">Please review your order details and confirm.</p>
            </div>
            
            <form action="orderaction1.php" method="post">
                <input type="hidden" name="did" value="<?php echo $data['did']; ?>">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white p-6 rounded-xl shadow-md">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Shipping Information</h2>
                            <div class="space-y-4">
                                <div>
                                    <label for="cname" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="cname" name="cname" value="<?php echo htmlspecialchars($data['cname']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="oadress" class="block text-sm font-medium text-gray-700">Shipping Address</label>
                                    <textarea id="oadress" name="oadress" rows="3" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm"><?php echo htmlspecialchars($data['address']); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                     <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                        <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($data['city']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="pincode" class="block text-sm font-medium text-gray-700">Pincode</label>
                                        <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($data['pincode']); ?>" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-xl shadow-md sticky top-28">
                            <h3 class="text-lg font-bold text-gray-800 border-b pb-4 mb-4">Order Summary</h3>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-24 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                                    <img src="<?php echo htmlspecialchars($data['dimg']); ?>" alt="<?php echo htmlspecialchars($data['dname']); ?>" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($data['dname']); ?></p>
                                    <p class="text-sm text-gray-500">Sold by: <?php echo htmlspecialchars($data['tname']); ?></p>
                                    <p class="text-lg font-bold text-gray-900 mt-1">₹<?php echo number_format($data['dprice']); ?></p>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2 text-sm border-t pt-4">
                                <?php 
                                    $price = $data['dprice'];
                                    $delivery_fee = 40; // Example delivery fee
                                    $total_amount = $price + $delivery_fee;
                                ?>
                                <div class="flex justify-between"><span class="text-gray-600">Item Price:</span><span class="font-medium text-gray-900">₹<?php echo number_format($price); ?></span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Delivery Fee:</span><span class="font-medium text-gray-900">₹<?php echo number_format($delivery_fee); ?></span></div>
                                <div class="flex justify-between text-base font-bold text-gray-800 border-t pt-3 mt-2"><span >Total Amount:</span><span class="text-purple-600">₹<?php echo number_format($total_amount); ?></span></div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" name="submit" class="w-full px-6 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-bold shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                    <i class="ri-secure-payment-line"></i> Proceed to Place Order
                                </button>
                                <a href="javascript:history.back()" class="block text-center text-xs font-medium text-gray-500 hover:text-purple-600 mt-3">&larr; Go Back & Change Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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