<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";

// Fetch customer's first name for the header dropdown
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}

// --- Get filter and search parameters from the URL ---
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_speciality = isset($_GET['speciality']) ? $_GET['speciality'] : 'all';

// --- Fetch unique options for filters from the latest schema ---
$design_types = $db->selectData("SELECT DISTINCT dtype FROM upload ORDER BY dtype ASC");
$specialities = $db->selectData("SELECT DISTINCT spect FROM treg ORDER BY spect ASC");

// --- SQL QUERY (Unchanged) ---
$sql_main = "SELECT 
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
                SELECT 
                    itemid, 
                    AVG(rate) as avg_rating, 
                    COUNT(fid) as rating_count 
                FROM feedb 
                WHERE feedtype = 'design_purchase' 
                GROUP BY itemid
            ) as ratings ON u.did = ratings.itemid
            LEFT JOIN (
                SELECT 
                    did, 
                    COUNT(oid) as order_count 
                FROM orderdesign 
                GROUP BY did
            ) as orders ON u.did = orders.did
            WHERE 1=1";

if (!empty($search_term)) {
    $st = $db->getConnection()->real_escape_string($search_term);
    $sql_main .= " AND (u.dname LIKE '%$st%' OR u.dtype LIKE '%$st%' OR t.tname LIKE '%$st%')";
}
if ($filter_type !== 'all') {
    $sql_main .= " AND u.dtype = '" . $db->getConnection()->real_escape_string($filter_type) . "'";
}
if ($filter_speciality !== 'all') {
    $sql_main .= " AND t.spect = '" . $db->getConnection()->real_escape_string($filter_speciality) . "'";
}

$sql_main .= " ORDER BY order_count DESC, avg_rating DESC, rating_count DESC";

$rs_main = $db->selectData($sql_main);
$results_count = $rs_main ? $rs_main->num_rows : 0;

// Helper function to generate star ratings
function generate_stars($rating) {
    $stars = '';
    $full_stars = floor($rating);
    $half_star = $rating - $full_stars >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

    for ($i = 0; $i < $full_stars; $i++) {
        $stars .= '<i class="ri-star-fill text-amber-400"></i>';
    }
    if ($half_star) {
        $stars .= '<i class="ri-star-half-fill text-amber-400"></i>';
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $stars .= '<i class="ri-star-line text-amber-400"></i>';
    }
    return $stars;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspiration Gallery - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } 
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- HEADER (Unchanged) -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600 transition-colors">Tailors</a>
            <a href="customreq1.php" class="text-gray-700 hover:text-purple-600 transition-colors">Stitch Request</a>
          </nav>

          <div class="hidden md:flex items-center space-x-6">
            <div class="relative" id="profile-dropdown-container">
              <button id="profile-dropdown-button" class=" dropdown-button flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1 arrow-icon transition-transform duration-300"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm text-gray-500">Signed in as</p>
                  <p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p>
                </div>
                <div class="py-1">
                  <a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a>
                  <a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a>
                </div>
                <div class="py-1 border-t border-gray-100">
                  <a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a>
                  <a href="stitchingorders.php?type=stitching" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a>
                </div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>
    
    <main class="container mx-auto px-6 py-8">
        <!-- FILTER SECTION (Unchanged) -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Find Your Perfect Design</h2>
                <form method="get" action="viewdesigns.php">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="lg:col-span-2">
                            <div class="relative"><i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name, type, tailor..." class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"/></div>
                        </div>
                        <div>
                            <select name="type" class="w-full pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                                <option value="all">All Types</option>
                                <?php mysqli_data_seek($design_types, 0); while($row_type = mysqli_fetch_array($design_types)): ?>
                                <option value="<?php echo htmlspecialchars($row_type['dtype']); ?>" <?php if($filter_type == $row_type['dtype']) echo 'selected'; ?>><?php echo htmlspecialchars($row_type['dtype']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <select name="speciality" class="w-full pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                                <option value="all">All Specialities</option>
                                <?php mysqli_data_seek($specialities, 0); while($row_spec = mysqli_fetch_array($specialities)): ?>
                                <option value="<?php echo htmlspecialchars($row_spec['spect']); ?>" <?php if($filter_speciality == $row_spec['spect']) echo 'selected'; ?>><?php echo htmlspecialchars($row_spec['spect']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                     <div class="mt-4 text-right">
                        <button type="submit" class="bg-purple-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">Apply Filters</button>
                     </div>
                </form>
            </div>
        </div>

        <div class="text-sm text-gray-600 mb-4 font-medium"><?php echo $results_count; ?> designs found.</div>
        
        <!-- FINALLY MODIFIED E-COMMERCE STYLE DESIGN LISTING -->
        <div class="space-y-4">
            <?php if($results_count > 0): ?>
                <?php while($row = mysqli_fetch_array($rs_main)): 
                    $price = $row['dprice'];
                    $mrp = round($price * 1.40);
                    $saved_amount = $mrp - $price;
                    $discount_percent = round(($saved_amount / $mrp) * 100);
                    
                    $min_delivery_date = date('j M', strtotime('+2 weeks'));
                    $max_delivery_date = date('j M', strtotime('+4 weeks'));
                ?>
                <a href="cviewdes.php?did=<?php echo $row['did']; ?>" class="block bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-200 p-4">
                    <div class="flex flex-col sm:flex-row gap-6">
                        <!-- Image Column -->
                        <div class="w-full sm:w-1/4 flex-shrink-0">
                            <div class="aspect-[4/5] bg-gray-100 rounded-lg overflow-hidden">
                                <img src="<?php echo htmlspecialchars($row['dimg']); ?>" alt="<?php echo htmlspecialchars($row['dname']); ?>" class="w-full h-full object-cover">
                            </div>
                        </div>

                        <!-- Details Column -->
                        <div class="flex-grow flex flex-col">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo htmlspecialchars($row['dtype']); ?></span>
                                    <span class="bg-pink-100 text-pink-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo htmlspecialchars($row['spect']); ?> Expert</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 hover:text-purple-600 transition-colors"><?php echo htmlspecialchars($row['dname']); ?></h3>
                                <p class="text-sm text-gray-500 mt-1">By <span class="font-medium text-gray-700"><?php echo htmlspecialchars($row['tname']); ?></span></p>

                                <div class="flex items-center gap-2 mt-2">
                                    <span class="flex items-center gap-1 text-sm"><?php echo generate_stars($row['avg_rating']); ?></span>
                                    <span class="text-sm text-gray-600">(<?php echo $row['rating_count']; ?> ratings)</span>
                                </div>
                                
                                <p class="text-sm text-gray-600 mt-3 leading-relaxed max-h-12 overflow-hidden">
                                    <?php echo htmlspecialchars(mb_strimwidth(strip_tags($row['ddesc']), 0, 150, "...")); ?>
                                </p>
                            </div>

                            <div class="mt-auto pt-4">
                                <!-- MODIFICATION: Purchase Count Logic -->
                                <div class="mb-3">
                                    <?php if ($row['order_count'] > 0): ?>
                                        <span class="text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-md"><i class="ri-shopping-cart-2-fill align-bottom"></i> <?php echo $row['order_count']; ?>+ bought</span>
                                    <?php else: ?>
                                        <span class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-md"><i class="ri-sparkling-2-fill align-bottom"></i> Be the first to own this!</span>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <span class="text-3xl font-bold text-gray-900">₹<?php echo number_format($price); ?></span>
                                    <span class="text-gray-500 ml-2">
                                        <del>₹<?php echo number_format($mrp); ?></del>
                                    </span>
                                    <p class="text-sm text-green-600 font-semibold">
                                        You save ₹<?php echo number_format($saved_amount); ?> (<?php echo $discount_percent; ?>%)
                                    </p>
                                </div>
                                
                                <div class="text-sm text-gray-700">
                                    <p>Estimated delivery between <span class="font-bold"><?php echo $min_delivery_date; ?> – <?php echo $max_delivery_date; ?></span></p>
                                    <p class="text-xs text-gray-500 italic mt-1">*Final delivery date depends on the shipping partner.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-2xl shadow-md">
                    <i class="ri-t-shirt-air-line text-7xl text-gray-300"></i>
                    <h3 class="text-2xl font-semibold text-gray-800 mt-4">No Designs Found</h3>
                    <p class="text-gray-600 mt-2">Try adjusting your search or filter criteria to find what you're looking for.</p>
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
    document.addEventListener('DOMContentLoaded', function() {
        const profileDropdownButton = document.getElementById('profile-dropdown-button');
            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
            const profileDropdownContainer = document.getElementById('profile-dropdown-container');
        if (profileDropdownButton && profileDropdownMenu) {
                // Toggle dropdown on button click
                profileDropdownButton.addEventListener('click', (event) => {
                    event.stopPropagation(); // Prevents the window click listener from firing immediately
                    
                    // Toggle the menu's visibility
                    profileDropdownMenu.classList.toggle('hidden');
                    
                    // Check the current state and update ARIA attribute
                    const isExpanded = !profileDropdownMenu.classList.contains('hidden');
                    profileDropdownButton.setAttribute('aria-expanded', isExpanded);
                });


                // Close dropdown if clicked outside
                window.addEventListener('click', (event) => {
                    if (profileDropdownContainer && !profileDropdownContainer.contains(event.target)) {
                        profileDropdownMenu.classList.add('hidden');
                        // Also reset the ARIA attribute and arrow when closing from outside
                        profileDropdownButton.setAttribute('aria-expanded', 'false');
                    }
                });
            }
    });
    </script>

</body>
</html>