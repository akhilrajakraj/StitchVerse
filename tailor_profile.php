
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Tailor ID is provided via the URL
if (!isset($_GET['tid']) || !is_numeric($_GET['tid'])) {
    die("Invalid Tailor ID provided.");
}

$db = new DatabaseCon();
$tailor_id = $_GET['tid'];
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// --- Query 1: Fetch the Tailor's Main Profile, Stats, and check if they are Approved ---
$sql_tailor = "SELECT 
                    t.*,
                    IFNULL(ratings.avg_rating, 0) as avg_rating,
                    IFNULL(ratings.rating_count, 0) as rating_count,
                    IFNULL(requests.stitch_request_count, 0) as stitch_request_count
                FROM treg t
                LEFT JOIN (
                    SELECT tid, AVG(rate) as avg_rating, COUNT(fid) as rating_count FROM feedb GROUP BY tid
                ) as ratings ON t.tid = ratings.tid
                LEFT JOIN (
                    SELECT tid, COUNT(sdid) as stitch_request_count FROM stitchreq GROUP BY tid
                ) as requests ON t.tid = requests.tid
                WHERE t.tid = ? AND t.status = 'Approved'";

$result_tailor = $db->selectData($sql_tailor, "i", $tailor_id);
if ($result_tailor->num_rows === 0) {
    die("Tailor not found or is not currently active.");
}
$tailor = $result_tailor->fetch_assoc();


// --- MODIFIED Query 2: Fetch this Tailor's Uploaded Designs with their individual stats ---
$sql_designs = "SELECT 
                    u.*,
                    t.tname, t.spect,
                    IFNULL(ratings.avg_rating, 0) as avg_rating,
                    IFNULL(ratings.rating_count, 0) as rating_count,
                    IFNULL(orders.order_count, 0) as order_count
                FROM upload u
                JOIN treg t ON u.uid = t.tid
                LEFT JOIN (
                    SELECT itemid, AVG(rate) as avg_rating, COUNT(fid) as rating_count 
                    FROM feedb WHERE feedtype = 'design_purchase' GROUP BY itemid
                ) as ratings ON u.did = ratings.itemid
                LEFT JOIN (
                    SELECT did, COUNT(oid) as order_count 
                    FROM orderdesign GROUP BY did
                ) as orders ON u.did = orders.did
                WHERE u.uid = ?
                ORDER BY order_count DESC, avg_rating DESC";
$rs_designs = $db->selectData($sql_designs, "i", $tailor_id);


// --- Query 3: Fetch all Feedback for this Tailor ---
// MODIFICATION: Added f.feedtype to the SELECT statement to know the feedback source.
$sql_feedback = "SELECT f.rate, f.feedbck, c.cname, f.feedtype 
                 FROM feedb f 
                 JOIN creg c ON f.uid = c.cid 
                 WHERE f.tid = ? 
                 ORDER BY f.fid DESC";
$rs_feedback = $db->selectData($sql_feedback, "i", $tailor_id);

$feedback_entries = [];
if ($rs_feedback) {
    while ($row = $rs_feedback->fetch_assoc()) {
        $feedback_entries[] = $row;
    }
}

// Fetch the logged-in customer's name for the header
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
    <title><?php echo htmlspecialchars($tailor['tname']); ?>'s Profile - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gray-50">

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
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 md:flex md:items-center md:gap-8">
                <div class="w-32 h-32 rounded-full bg-white/20 border-4 border-white/50 mx-auto md:mx-0 flex-shrink-0 flex items-center justify-center">
                    <i class="ri-user-star-fill text-6xl text-white"></i>
                </div>
                <div class="text-center md:text-left flex-grow">
                    <h1 class="text-4xl font-bold text-white mt-4 md:mt-0"><?php echo htmlspecialchars($tailor['tname']); ?></h1>
                    <p class="text-purple-100 mt-2 text-lg">Specializing in <span class="font-semibold"><?php echo htmlspecialchars($tailor['spect']); ?></span></p>
                    <div class="mt-3 flex items-center justify-center md:justify-start gap-6 text-white">
                        <div class="flex items-center gap-2"><?php echo generate_stars($tailor['avg_rating']); ?> <span class="text-sm opacity-80">(<?php echo $tailor['rating_count']; ?> reviews)</span></div>
                        <div class="flex items-center gap-2"><i class="ri-shopping-bag-3-fill"></i> <span class="font-semibold"><?php echo $tailor['stitch_request_count']; ?>+</span> Projects Completed</div>
                    </div>
                </div>
                <div class="md:ml-auto mt-6 md:mt-0 text-center">
                    <a href="customreq1.php?tid=<?php echo $tailor['tid']; ?>" class="bg-white text-purple-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100 transition-all shadow-lg transform hover:scale-105">Request Custom Work</a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">About <?php echo htmlspecialchars(explode(' ', trim($tailor['tname']))[0]); ?></h3>
                <p class="text-gray-600 leading-relaxed">A skilled artisan specializing in <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($tailor['spect']); ?></span> with over <span class="font-semibold text-gray-800"><?php echo str_replace('_', ' ', htmlspecialchars($tailor['quali'])); ?></span>. Dedicated to crafting high-quality garments with precision and care, ensuring every piece is a perfect fit for our valued customers.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Contact & Location</h3>
                <div class="space-y-3 text-sm">
                    <p class="flex items-center gap-3"><i class="ri-mail-line text-purple-600 w-4 text-center"></i> <span class="text-gray-700"><?php echo htmlspecialchars($tailor['email']); ?></span></p>
                    <p class="flex items-center gap-3"><i class="ri-phone-line text-purple-600 w-4 text-center"></i> <span class="text-gray-700"><?php echo htmlspecialchars($tailor['phone']); ?></span></p>
                    <p class="flex items-start gap-3"><i class="ri-map-pin-line text-purple-600 w-4 text-center pt-1"></i> <span class="text-gray-700"><?php echo htmlspecialchars($tailor['address']); ?>, <?php echo htmlspecialchars($tailor['city']); ?>, <?php echo htmlspecialchars($tailor['distri']); ?> - <?php echo htmlspecialchars($tailor['pinc']); ?></span></p>
                </div>
            </div>
        </div>

        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Designs by <?php echo htmlspecialchars(explode(' ', trim($tailor['tname']))[0]); ?></h2>
            <?php if($rs_designs && $rs_designs->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while($design = $rs_designs->fetch_assoc()): 
                    $price = $design['dprice'];
                    $mrp = round($price * 1.40);
                    $saved_amount = $mrp - $price;
                    $discount_percent = round(($saved_amount / $mrp) * 100);
                    $min_delivery_date = date('j M', strtotime('+2 weeks'));
                    $max_delivery_date = date('j M', strtotime('+4 weeks'));
                ?>
                <a href="cviewdes.php?did=<?php echo $design['did']; ?>" class="block bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-200 p-4">
                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="w-full sm:w-1/4 flex-shrink-0">
                            <div class="aspect-[4/5] bg-gray-100 rounded-lg overflow-hidden">
                                <img src="<?php echo htmlspecialchars($design['dimg']); ?>" alt="<?php echo htmlspecialchars($design['dname']); ?>" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex-grow flex flex-col">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo htmlspecialchars($design['dtype']); ?></span>
                                    <span class="bg-pink-100 text-pink-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo htmlspecialchars($design['spect']); ?> Expert</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 hover:text-purple-600 transition-colors"><?php echo htmlspecialchars($design['dname']); ?></h3>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="flex items-center gap-1 text-sm"><?php echo generate_stars($design['avg_rating']); ?></span>
                                    <span class="text-sm text-gray-600">(<?php echo $design['rating_count']; ?> ratings)</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-3 leading-relaxed max-h-12 overflow-hidden">
                                    <?php echo htmlspecialchars(mb_strimwidth(strip_tags($design['ddesc']), 0, 150, "...")); ?>
                                </p>
                            </div>
                            <div class="mt-auto pt-4">
                                <?php if ($design['order_count'] > 0): ?>
                                    <div class="mb-3"><span class="text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-md"><i class="ri-shopping-cart-2-fill align-bottom"></i> <?php echo $design['order_count']; ?>+ bought</span></div>
                                <?php else: ?>
                                     <div class="mb-3"><span class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-md"><i class="ri-sparkling-2-fill align-bottom"></i> Be the first to own this!</span></div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <span class="text-3xl font-bold text-gray-900">₹<?php echo number_format($price); ?></span>
                                    <span class="text-gray-500 ml-2"><del>₹<?php echo number_format($mrp); ?></del></span>
                                    <p class="text-sm text-green-600 font-semibold">You save ₹<?php echo number_format($saved_amount); ?> (<?php echo $discount_percent; ?>%)</p>
                                </div>
                                <div class="text-sm text-gray-700">
                                    <p>Estimated delivery between <span class="font-bold"><?php echo $min_delivery_date; ?> – <?php echo $max_delivery_date; ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-md"><i class="ri-t-shirt-air-line text-6xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Designs Uploaded Yet</h3><p class="text-gray-600 mt-1">This tailor currently has no public designs. You can request custom work directly!</p></div>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Customer Feedback</h2>
             <?php if(!empty($feedback_entries)): ?>
            <div class="space-y-6">
                <?php foreach($feedback_entries as $feedback): ?>
                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="flex items-center mb-2">
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg mr-3"><?php echo strtoupper(substr($feedback['cname'], 0, 1)); ?></div>
                        <div>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($feedback['cname']); ?></p>
                            <div class="flex items-center gap-1 text-sm"><?php echo generate_stars($feedback['rate']); ?></div>
                        </div>
                    </div>
                    <p class="text-gray-700 leading-relaxed italic">"<?php echo nl2br(htmlspecialchars($feedback['feedbck'])); ?>"</p>
                    
                    <div class="mt-4 text-right">
                        <?php if ($feedback['feedtype'] === 'design_purchase'): ?>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <i class="ri-shopping-cart-line align-middle mr-1"></i> Feedback for: Design Purchase
                            </span>
                        <?php elseif ($feedback['feedtype'] === 'stitch_request'): ?>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1 rounded-full">
                                <i class="ri-scissors-cut-line align-middle mr-1"></i> Feedback for: Stitch Request
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-md"><i class="ri-question-answer-line text-6xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Reviews Yet</h3><p class="text-gray-600 mt-1">This tailor has not received any feedback from customers.</p></div>
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