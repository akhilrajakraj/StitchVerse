
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Design ID is provided via the URL
if (!isset($_GET['did']) || !is_numeric($_GET['did'])) {
    die("Invalid Design ID provided.");
}

$db = new DatabaseCon();
$design_id = $_GET['did'];
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// --- Main Query to fetch all details for this specific design ---
$sql_design = "SELECT 
                    u.*, 
                    t.tname, 
                    t.spect, 
                    t.tid,
                    IFNULL(ratings.avg_rating, 0) as avg_rating,
                    IFNULL(ratings.rating_count, 0) as rating_count,
                    IFNULL(orders.order_count, 0) as order_count
                FROM upload u
                JOIN treg t ON u.uid = t.tid
                LEFT JOIN (
                    SELECT itemid, AVG(rate) as avg_rating, COUNT(fid) as rating_count FROM feedb WHERE feedtype = 'design_purchase' GROUP BY itemid
                ) as ratings ON u.did = ratings.itemid
                LEFT JOIN (
                    SELECT did, COUNT(oid) as order_count FROM orderdesign GROUP BY did
                ) as orders ON u.did = orders.did
                WHERE u.did = ?";

$result_design = $db->selectData($sql_design, "i", $design_id);
if ($result_design->num_rows === 0) {
    die("Design not found.");
}
$design = $result_design->fetch_assoc();


// --- Query to fetch all individual feedback entries for this design ---
$sql_feedback = "SELECT f.*, c.cname 
                 FROM feedb f 
                 JOIN creg c ON f.uid = c.cid 
                 WHERE f.feedtype = 'design_purchase' AND f.itemid = ? 
                 ORDER BY f.fid DESC";
$rs_feedback = $db->selectData($sql_feedback, "i", $design_id);
$feedback_entries = [];
if ($rs_feedback) {
    while($row = $rs_feedback->fetch_assoc()) {
        $feedback_entries[] = $row;
    }
}

// Fetch customer's first name for the header dropdown
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}

// Helper function to generate star ratings
function generate_stars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= '<i class="ri-star-fill ' . ($i <= round($rating) ? 'text-amber-400' : 'text-gray-300') . '"></i>';
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($design['dname']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-white">

    <!-- CONSISTENT HEADER -->
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
                <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Left Column: Image -->
            <div class="w-full">
                <div class="aspect-[4/5] bg-gray-100 rounded-xl overflow-hidden shadow-lg sticky top-28">
                     <img src="<?php echo htmlspecialchars($design['dimg']); ?>" alt="<?php echo htmlspecialchars($design['dname']); ?>" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="w-full">
                <p class="text-sm font-medium text-purple-600">By <?php echo htmlspecialchars($design['tname']); ?></p>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($design['dname']); ?></h1>
                
                <div class="flex items-center gap-4 mt-3">
                    <div class="flex items-center gap-1"><?php echo generate_stars($design['avg_rating']); ?></div>
                    <a href="#reviews" class="text-sm text-gray-600 hover:underline"><?php echo $design['rating_count']; ?> ratings</a>
                </div>

                <div class="mt-6">
                    <?php
                        $price = $design['dprice'];
                        $mrp = round($price * 1.40);
                        $discount_percent = round((($mrp - $price) / $mrp) * 100);
                    ?>
                    <p class="text-green-600 font-bold text-lg"> -<?php echo $discount_percent; ?>% <span class="text-4xl font-bold text-gray-900 ml-2">₹<?php echo number_format($price); ?></span></p>
                    <p class="text-sm text-gray-500 ml-1 mt-1">M.R.P.: <del>₹<?php echo number_format($mrp); ?></del></p>
                    <p class="text-sm text-gray-600">Inclusive of all taxes</p>
                </div>

                <div class="mt-6 p-4 bg-pink-50 rounded-lg border border-pink-200">
                    <h3 class="font-bold text-pink-800 flex items-center gap-2"><i class="ri-gift-line"></i> Available Offers</h3>
                    <ul class="text-sm text-pink-700 mt-2 list-disc list-inside space-y-1">
                        <li><span class="font-semibold">Bank Offer:</span> Upto ₹1,500 discount on select Credit Cards.</li>
                        <li><span class="font-semibold">Partner Offer:</span> Get a 3-month subscription of StitchVerse Prime.</li>
                    </ul>
                </div>

                <div class="mt-6 text-sm">
                    <p><span class="font-semibold w-24 inline-block">Sold By:</span> <a href="#" class="text-purple-600 hover:underline"><?php echo htmlspecialchars($design['tname']); ?></a></p>
                    <p><span class="font-semibold w-24 inline-block">Speciality:</span> <?php echo htmlspecialchars($design['spect']); ?></p>
                    <p><span class="font-semibold w-24 inline-block">Type:</span> <?php echo htmlspecialchars($design['dtype']); ?></p>
                </div>

                <div class="mt-8 border-t pt-6">
                    <!-- MODIFICATION HERE -->
                    <a href="cvieworders2.php?id=<?php echo $design['did']; ?>" class="w-full block text-center bg-pink-600 text-white px-6 py-4 rounded-xl font-bold text-lg hover:bg-pink-700 transition-transform transform hover:scale-105 shadow-lg">
                        <i class="ri-shopping-bag-3-fill align-middle"></i> Buy Now
                    </a>
                </div>

                <div class="mt-6 prose prose-sm max-w-none">
                    <h3 class="font-bold">Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($design['ddesc'])); ?></p>
                </div>
            </div>
        </div>

        <!-- Ratings & Reviews Section -->
        <div id="reviews" class="mt-16 pt-8 border-t">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Ratings & Customer Reviews</h2>
            <?php if (!empty($feedback_entries)): ?>
            <div class="space-y-6">
                <?php foreach($feedback_entries as $feedback): ?>
                <div class="border-b pb-6">
                    <div class="flex items-center mb-2">
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg mr-3">
                            <?php echo strtoupper(substr($feedback['cname'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($feedback['cname']); ?></p>
                            <p class="text-xs text-gray-500">Verified Purchase</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mb-2"><?php echo generate_stars($feedback['rate']); ?></div>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($feedback['feedbck'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <i class="ri-question-answer-line text-6xl text-gray-300"></i>
                <h3 class="text-xl font-semibold text-gray-800 mt-4">No Reviews Yet</h3>
                <p class="text-gray-600 mt-1">Be the first to review this design after you purchase it!</p>
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
                        <li><a href="vieworders.php" class="text-gray-400 hover:text-white">My Orders</a></li>
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
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- JAVASCRIPT FOR HEADER DROPDOWN ---
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