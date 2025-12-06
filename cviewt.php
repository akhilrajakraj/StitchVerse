
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

// --- Fetch unique options for filters ---
$specialities = $db->selectData("SELECT DISTINCT spect FROM treg WHERE status = 'Approved' ORDER BY spect ASC");
$districts = $db->selectData("SELECT DISTINCT distri FROM treg WHERE status = 'Approved' ORDER BY distri ASC");

// Fetch customer's first name for the header dropdown
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}

// --- Get filter and search parameters from the URL ---
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$filter_speciality = isset($_GET['speciality']) ? $_GET['speciality'] : 'all';
$filter_district = isset($_GET['district']) ? $_GET['district'] : 'all';

// --- RE-ARCHITECTED SQL QUERY ---
$sql_main = "SELECT 
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
            WHERE t.status = 'Approved'";

if (!empty($search_term)) {
    $st = $db->getConnection()->real_escape_string($search_term);
    $sql_main .= " AND (t.tname LIKE '%$st%' OR t.city LIKE '%$st%')";
}
if ($filter_speciality !== 'all') {
    $sql_main .= " AND t.spect = '" . $db->getConnection()->real_escape_string($filter_speciality) . "'";
}
if ($filter_district !== 'all') {
    $sql_main .= " AND t.distri = '" . $db->getConnection()->real_escape_string($filter_district) . "'";
}

$sql_main .= " ORDER BY stitch_request_count DESC, avg_rating DESC, rating_count DESC";

$rs_main = $db->selectData($sql_main);
$results_count = $rs_main ? $rs_main->num_rows : 0;

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
    <title>Find a Tailor - StitchVerse</title>
    
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
            <a href="cviewt.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">Tailors</a>
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
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Find Your Perfect Artisan</h2>
                <form method="get" action="cviewt.php">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="lg:col-span-2">
                            <div class="relative"><i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name or city..." class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"/></div>
                        </div>
                        <div>
                            <select name="speciality" class="w-full pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                                <option value="all">All Specialities</option>
                                <?php mysqli_data_seek($specialities, 0); while($row = mysqli_fetch_array($specialities)): ?>
                                <option value="<?php echo htmlspecialchars($row['spect']); ?>" <?php if($filter_speciality == $row['spect']) echo 'selected'; ?>><?php echo htmlspecialchars($row['spect']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                         <div>
                            <select name="district" class="w-full pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                                <option value="all">All Districts</option>
                                <?php mysqli_data_seek($districts, 0); while($row = mysqli_fetch_array($districts)): ?>
                                <option value="<?php echo htmlspecialchars($row['distri']); ?>" <?php if($filter_district == $row['distri']) echo 'selected'; ?>><?php echo htmlspecialchars($row['distri']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button type="submit" class="bg-purple-600 text-white px-8 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">Search Tailors</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-sm text-gray-600 mb-4 font-medium"><?php echo $results_count; ?> tailors found.</div>

        <div class="space-y-4">
            <?php if($results_count > 0): ?>
                <?php while($tailor = mysqli_fetch_array($rs_main)): ?>
                <a href="tailor_profile.php?tid=<?php echo $tailor['tid']; ?>" class="block bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-gray-200 p-4 group">
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center flex-shrink-0">
                            <i class="ri-user-star-line text-5xl text-purple-600"></i>
                        </div>
                        <div class="flex-grow text-center sm:text-left">
                            <h3 class="text-lg font-bold text-gray-800 group-hover:text-purple-600 transition-colors"><?php echo htmlspecialchars($tailor['tname']); ?></h3>
                            <p class="text-sm text-purple-600 font-semibold"><?php echo htmlspecialchars($tailor['spect']); ?></p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center justify-center sm:justify-start gap-1"><i class="ri-map-pin-2-line"></i> <?php echo htmlspecialchars($tailor['city']); ?>, <?php echo htmlspecialchars($tailor['distri']); ?></p>
                            
                            <div class="flex items-center justify-center sm:justify-start gap-2 mt-2">
                                <span class="flex items-center gap-1 text-sm"><?php echo generate_stars($tailor['avg_rating']); ?></span>
                                <span class="text-sm text-gray-600">(<?php echo $tailor['rating_count']; ?> reviews)</span>
                            </div>

                            <div class="mt-3 flex items-center justify-center sm:justify-start gap-4 text-sm">
                                <span class="bg-green-100 text-green-800 font-medium px-3 py-1 rounded-full"><i class="ri-shopping-bag-3-line align-middle"></i> <?php echo $tailor['stitch_request_count']; ?> Projects</span>
                                <span class="bg-blue-100 text-blue-800 font-medium px-3 py-1 rounded-full"><i class="ri-medal-line align-middle"></i> <?php echo htmlspecialchars($tailor['quali']); ?></span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 hidden sm:block">
                            <i class="ri-arrow-right-s-line text-3xl text-gray-300 group-hover:text-purple-500 transition-colors"></i>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-2xl shadow-md">
                    <i class="ri-user-unfollow-line text-6xl text-gray-300"></i>
                    <h3 class="text-2xl font-semibold text-gray-800 mt-4">No Tailors Found</h3>
                    <p class="text-gray-600 mt-2">Try adjusting your search or filter criteria to find the perfect artisan.</p>
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