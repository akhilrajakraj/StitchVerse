<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- Get filter and search parameters ---
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- Fetch stats ---
$total_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq")->fetch_assoc()['count'];
$pending_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus='Pending'")->fetch_assoc()['count'];
$paid_requests = $db->selectData("SELECT COUNT(sdid) as count FROM stitchreq WHERE sstatus='Paid'")->fetch_assoc()['count'];

// --- Build the main query securely ---
$sql_main = "SELECT sr.sdid, sr.sdname, sr.sstatus, sr.simg, c.cname, c.email as cemail, t.tname, t.email as temail 
             FROM stitchreq sr
             JOIN creg c ON sr.uid = c.cid
             LEFT JOIN treg t ON sr.tid = t.tid
             WHERE 1=1";
$types = "";
$params = [];

if ($filter_status !== 'all') {
    $sql_main .= " AND sr.sstatus = ?";
    $types .= "s";
    $params[] = $filter_status;
}
if (!empty($search_term)) {
    $searchTermLike = "%" . $search_term . "%";
    $sql_main .= " AND (sr.sdname LIKE ? OR c.cname LIKE ? OR t.tname LIKE ?)";
    $types .= "sss";
    $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike;
}
$sql_main .= " ORDER BY sr.sdid DESC";

if (!empty($types)) {
    $rs_main = $db->selectData($sql_main, $types, ...$params);
} else {
    $rs_main = $db->selectData($sql_main);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stitch Requests - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } 
        #success-popup{z-index:100};
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3 transition-transform duration-300 translate-x-full"><i class="ri-checkbox-circle-line text-2xl"></i><span></span></div>
    <div id="cancel-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto">
            <div class="p-6 border-b flex justify-between items-center"><h3 class="text-xl font-bold text-gray-800">Cancel Request & Notify Parties</h3><button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div>
            <form action="managestraction.php" method="POST" class="p-6">
                <input type="hidden" name="sdid" id="modal-cancel-sdid">
                <input type="hidden" name="customer_email" id="modal-cancel-cemail">
                <input type="hidden" name="tailor_email" id="modal-cancel-temail">
                <input type="hidden" name="request_name" id="modal-cancel-rname">
                <div class="mb-4"><label class="block text-sm font-medium text-gray-700 mb-2">Reason for Cancellation*</label><textarea name="reason" required rows="4" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Provide a clear reason for cancelling this request. This will be sent to both the customer and the tailor."></textarea></div>
                <div class="flex justify-end gap-4"><button type="button" onclick="closeCancelModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">Close</button><button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2"><i class="ri-delete-bin-line"></i> Confirm & Send Notifications</button></div>
            </form>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
  <div class="container mx-auto px-6 py-4">
    <div class="flex items-center justify-between">
      <a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>

      <nav class="hidden md:flex items-center space-x-8">
        <a href="viewt.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Tailors</a>
        <a href="viewc.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Customers</a>
        <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Designs</a>
        <a href="managestr.php" class="text-purple-600 border-b-2 border-purple-600 font-semibold">Stitch Requests</a>
      </nav>

      <div class="hidden md:flex items-center space-x-4">
        <a href="index.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold flex items-center gap-2">
          <i class="ri-logout-box-line"></i> Logout
        </a>
      </div>
      <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
    </div>

    <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
      <div class="flex flex-col space-y-4 mt-4">
        <a href="viewt.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Tailors</a>
        <a href="viewc.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Customers</a>
        <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Designs</a>
        <a href="managestr.php" class="text-purple-600 font-semibold">Stitch Requests</a>
        <a href="index.php" class="bg-purple-100 text-purple-700 px-6 py-2 rounded-lg text-center font-semibold mt-2">Logout</a>
      </div>
    </div>
  </div>
</header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6"><div class="flex items-center"><div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-ruler-2-line text-2xl text-white"></i></div><div><h1 class="text-3xl font-bold text-white">Manage Stitch Requests</h1><p class="text-purple-100 mt-1">Oversee all custom tailoring requests on the platform.</p></div></div></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4"><div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl"><div class="text-2xl font-bold text-blue-600"><?php echo $total_requests; ?></div><div class="text-blue-600 text-sm font-medium">Total Requests</div></div><div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-xl"><div class="text-2xl font-bold text-yellow-600"><?php echo $pending_requests; ?></div><div class="text-yellow-600 text-sm font-medium">Pending Requests</div></div><div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl"><div class="text-2xl font-bold text-green-600"><?php echo $paid_requests; ?></div><div class="text-green-600 text-sm font-medium">Paid Requests</div></div></div>
            <div class="p-6 border-t border-gray-100"><form method="get" action="managestr.php" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"><div class="flex flex-wrap gap-2 items-center"><select name="status" onchange="this.form.submit()" class="w-full lg:w-auto pr-4 py-2 border border-gray-300 rounded-lg text-sm bg-white"><option value="all">Filter by Status</option><option value="Pending" <?php if($filter_status == 'Pending') echo 'selected'; ?>>Pending</option><option value="Accepted" <?php if($filter_status == 'Accepted') echo 'selected'; ?>>Accepted</option><option value="Paid" <?php if($filter_status == 'Paid') echo 'selected'; ?>>Paid</option><option value="Rejected" <?php if($filter_status == 'Rejected') echo 'selected'; ?>>Rejected</option></select></div><div class="relative"><i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by customer, tailor, request..." class="pl-10 pr-4 py-2 w-full lg:w-80 border border-gray-300 rounded-lg text-sm"/></div></form></div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Request Info</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Customer</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Assigned Tailor</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if($rs_main && mysqli_num_rows($rs_main) > 0): ?>
                        <?php while($row = mysqli_fetch_array($rs_main)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><div class="flex items-center space-x-4"><img src="<?php echo htmlspecialchars(explode(',', $row['simg'])[0]); ?>" class="w-12 h-16 object-cover rounded-lg border flex-shrink-0"><div><div class="font-bold text-gray-800"><?php echo htmlspecialchars($row['sdname']); ?></div><div class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($row['sdid']); ?></div></div></div></td>
                            <td class="px-6 py-4"><div class="font-medium text-gray-800"><?php echo htmlspecialchars($row['cname']); ?></div></td>
                            <td class="px-6 py-4"><div class="font-medium text-gray-800"><?php echo htmlspecialchars($row['tname'] ?? 'Unassigned'); ?></div></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo strtolower($row['sstatus']) == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo htmlspecialchars($row['sstatus']); ?></span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <a href="view_request_details.php?id=<?php echo $row['sdid']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</a>
                                    <button onclick="openCancelModal('<?php echo $row['sdid']; ?>', '<?php echo htmlspecialchars(addslashes($row['sdname'])); ?>', '<?php echo htmlspecialchars($row['cemail']); ?>', '<?php echo htmlspecialchars($row['temail'] ?? ''); ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="5" class="text-center py-16"><i class="ri-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Requests Found</h3><p class="text-gray-600 mt-1">Try adjusting your search or filter criteria.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16"><div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div></footer>
    
    <script>
        function openCancelModal(sdid, rname, cemail, temail) {
            document.getElementById('modal-cancel-sdid').value = sdid;
            document.getElementById('modal-cancel-rname').value = rname;
            document.getElementById('modal-cancel-cemail').value = cemail;
            document.getElementById('modal-cancel-temail').value = temail;
            document.getElementById('cancel-modal').classList.remove('hidden');
        }
        function closeCancelModal() { document.getElementById('cancel-modal').classList.add('hidden'); }
        
        document.addEventListener('DOMContentLoaded', () => { /* ... existing pop-up logic ... */ });
    </script>
</body>
</html>