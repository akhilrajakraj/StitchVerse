
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];

// --- Get filter and search parameters from the URL ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Helper functions for styling ---
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'paid': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'accepted': return 'bg-green-100 text-green-800 border-green-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}
function getPriorityColor($priority) {
    switch (strtolower($priority)) {
        case 'expedited': return 'bg-red-100 text-red-800 border-red-200';
        default: return 'bg-green-100 text-green-800 border-green-200';
    }
}

// --- Fetch data for the stats cards using prepared statements (Made case-insensitive) ---
$sql_all = "SELECT COUNT(sdid) as count FROM stitchreq WHERE tid=? AND (LOWER(sstatus)='accepted' OR LOWER(sstatus)='paid')";
$all_count = $db->selectData($sql_all, "i", $tailor_id)->fetch_assoc()['count'];

$sql_accepted = "SELECT COUNT(sdid) as count FROM stitchreq WHERE tid=? AND LOWER(sstatus)='accepted'";
$accepted_count = $db->selectData($sql_accepted, "i", $tailor_id)->fetch_assoc()['count'];

$sql_paid = "SELECT COUNT(sdid) as count FROM stitchreq WHERE tid=? AND LOWER(sstatus)='paid'";
$paid_count = $db->selectData($sql_paid, "i", $tailor_id)->fetch_assoc()['count'];

// --- Build the main query with filtering and search (MODIFIED for robustness) ---
$sql_main = "SELECT sr.*, cr.cname, cr.email, cr.phone 
             FROM stitchreq sr
             LEFT JOIN creg cr ON sr.uid = cr.cid
             WHERE sr.tid = ? AND (LOWER(sr.sstatus)='accepted' OR LOWER(sr.sstatus)='paid')";

$params = [$tailor_id];
$types = "i";

if ($filter_status !== 'all') {
    // MODIFIED: Made filter case-insensitive
    $sql_main .= " AND LOWER(sr.sstatus) = ?";
    $params[] = strtolower($filter_status);
    $types .= "s";
}
if (!empty($search_term)) {
    $st_param = "%" . $search_term . "%";
    $sql_main .= " AND (cr.cname LIKE ? OR sr.sdname LIKE ? OR sr.sdid LIKE ?)";
    array_push($params, $st_param, $st_param, $st_param);
    $types .= "sss";
}
$sql_main .= " ORDER BY sr.sddate ASC";
$rs_main = $db->selectData($sql_main, $types, ...$params);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Requests - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-6">
            <a href="upd.php" class="text-gray-700 hover:text-purple-600">Upload Designs</a>
            <a href="designorder.php" class="text-gray-700 hover:text-purple-600">Design Requests</a>
            <a href="tailorreq.php" class="text-gray-700 hover:text-purple-600">Custom Requests</a>
            <a href="accrequest.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">Accepted Requests</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <!-- MODIFIED: Added session message display -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                <p class="font-bold">Notice</p>
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-check-double-line text-2xl text-white"></i></div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Accepted Requests</h1>
                            <p class="text-purple-100 mt-1">Manage your confirmed orders.</p>
                        </div>
                    </div>
                    <div class="text-white text-right">
                        <div class="text-2xl font-bold"><?php echo $all_count; ?></div>
                        <div class="text-purple-100 text-sm">Total Accepted</div>
                    </div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl"><div class="text-2xl font-bold text-blue-600"><?php echo $all_count; ?></div><div class="text-blue-600 text-sm font-medium">All</div></div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl"><div class="text-2xl font-bold text-green-600"><?php echo $accepted_count; ?></div><div class="text-green-600 text-sm font-medium">Accepted (Not Paid)</div></div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl"><div class="text-2xl font-bold text-purple-600"><?php echo $paid_count; ?></div><div class="text-purple-600 text-sm font-medium">Fully Paid</div></div>
            </div>
            <div class="p-6 border-t border-gray-100">
                <form method="get" action="accrequest.php" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="?status=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?php echo $filter_status == 'all' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">All (<?php echo $all_count; ?>)</a>
                        <a href="?status=Accepted" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?php echo strtolower($filter_status) == 'accepted' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Accepted (<?php echo $accepted_count; ?>)</a>
                        <a href="?status=Paid" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?php echo strtolower($filter_status) == 'paid' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Paid (<?php echo $paid_count; ?>)</a>
                    </div>
                    <div class="relative">
                        <i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name, dress, ID..." class="pl-10 pr-4 py-2 w-full lg:w-80 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"/>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <?php if($rs_main && $rs_main->num_rows > 0): ?>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dress Info</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Specifications</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status & Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $rs_main->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['cname'] ?? 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['email'] ?? ''); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-16 rounded-lg overflow-hidden flex-shrink-0 border"><img src="<?php echo htmlspecialchars(explode(',', $row['simg'])[0]); ?>" class="w-full h-full object-cover" alt="Dress Image"></div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['sdname']); ?></div>
                                        <div class="text-xs text-purple-600"><?php echo htmlspecialchars($row['sdtype']); ?></div>
                                        <div class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($row['sdid']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 max-w-sm">
                                <div class="text-xs text-gray-600 line-clamp-2" title="<?php echo htmlspecialchars($row['sinstructions']); ?>"><b class="text-gray-800">Instructions:</b> <?php echo htmlspecialchars($row['sinstructions']); ?></div>
                                <div class="text-xs text-gray-600 mt-1 line-clamp-2" title="<?php echo htmlspecialchars($row['scustom']); ?>"><b class="text-gray-800">Customizations:</b> <?php echo htmlspecialchars($row['scustom']); ?></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-xs text-gray-600"><b class="text-gray-800">Delivery:</b> <?php echo date("d M, Y", strtotime($row['sddate'])); ?></div>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium border <?php echo getPriorityColor($row['spriority']); ?>"><?php echo htmlspecialchars($row['spriority']); ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-semibold text-gray-800">₹<?php echo number_format($row['sprice'], 2); ?></div>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium border <?php echo getStatusColor($row['sstatus']); ?>"><?php echo htmlspecialchars($row['sstatus']); ?></span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-2">
                                    <a href="view_request_details3.php?id=<?php echo $row['sdid']; ?>" class="bg-purple-600 text-white text-center px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-purple-700">View Details</a>
                                    <a href="cmeas.php?id=<?php echo $row['uid'] ?>" target="_blank" class="text-xs font-medium text-blue-600 hover:text-blue-800 text-center">Measurements</a>
                                    <?php if (strtolower($row['sstatus']) == 'paid'): ?>
                                    <a href="paystr.php?id=<?php echo $row['sdid']; ?>" target="_blank" class="text-xs font-medium text-green-600 hover:text-green-800 text-center">Receipt</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-16">
                    <i class="ri-search-line text-5xl text-gray-300"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mt-4">No Requests Found</h3>
                    <p class="text-gray-600 mt-1">Try adjusting your search or filter criteria.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
