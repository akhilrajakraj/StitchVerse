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
$filter_district = isset($_GET['district']) ? $_GET['district'] : 'all';

// --- Fetch stats and filter options ---
$total_customers = $db->selectData("SELECT COUNT(cid) as count FROM creg WHERE cname != ''")->fetch_assoc()['count'];
$districts = $db->selectData("SELECT DISTINCT distr FROM creg WHERE distr != '' ORDER BY distr ASC");

// --- Build the main query securely ---
$sql_main = "SELECT * FROM creg WHERE cname != ''";
$types = "";
$params = [];

if ($filter_district !== 'all') {
    $sql_main .= " AND distr = ?";
    $types .= "s";
    $params[] = $filter_district;
}
if (!empty($search_term)) {
    $searchTermLike = "%" . $search_term . "%";
    $sql_main .= " AND (cname LIKE ? OR email LIKE ? OR city LIKE ?)";
    $types .= "sss";
    $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike;
}
$sql_main .= " ORDER BY cname ASC";

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
    <title>Manage Customers - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3 transition-transform duration-300 translate-x-full">
        <i class="ri-checkbox-circle-line text-2xl"></i><span></span>
    </div>
    <div id="error-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3 transition-transform duration-300 translate-x-full">
        <i class="ri-error-warning-line text-2xl"></i><span></span>
    </div>
    
    <div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto p-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full mx-auto flex items-center justify-center mb-4"><i class="ri-delete-bin-line text-5xl text-red-500"></i></div>
            <h3 class="text-2xl font-bold text-gray-800">Confirm Deletion</h3>
            <p class="text-gray-600 mt-2">Are you sure you want to permanently delete the customer <strong id="modal-delete-customer-name"></strong>? This will remove their login and profile.</p>
            <div class="flex justify-center gap-4 mt-8">
                <button onclick="closeDeleteModal()" class="px-8 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                <a id="modal-delete-confirm-link" href="#" class="px-8 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">Yes, Delete</a>
            </div>
        </div>
    </div>

     <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
  <div class="container mx-auto px-6 py-4">
    <div class="flex items-center justify-between">
      <a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>

      <nav class="hidden md:flex items-center space-x-8">
        <a href="viewt.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Tailors</a>
        <a href="viewc.php" class="text-purple-600 border-b-2 border-purple-600 font-semibold">Manage Customers</a>
        <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Designs</a>
        <a href="managestr.php" class="text-gray-700 hover:text-purple-600 font-medium">Stitch Requests</a>
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
        <a href="viewc.php" class="text-purple-600 font-semibold">Manage Customers</a>
        <a href="managedesigns.php" class="text-gray-700 hover:text-purple-600 font-medium">Manage Designs</a>
        <a href="managestr.php" class="text-gray-700 hover:text-purple-600 font-medium">Stitch Requests</a>
        <a href="index.php" class="bg-purple-100 text-purple-700 px-6 py-2 rounded-lg text-center font-semibold mt-2">Logout</a>
      </div>
    </div>
  </div>
</header>


    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6"><div class="flex items-center"><div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-team-line text-2xl text-white"></i></div><div><h1 class="text-3xl font-bold text-white">Manage Customers</h1><p class="text-purple-100 mt-1">Oversee all registered customer accounts.</p></div></div></div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4"><div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl"><div class="text-2xl font-bold text-blue-600"><?php echo $total_customers; ?></div><div class="text-blue-600 text-sm font-medium">Total Registered Customers</div></div></div>
            <div class="p-6 border-t border-gray-100"><form method="get" action="viewc.php" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"><div class="flex flex-wrap gap-2 items-center"><select name="district" onchange="this.form.submit()" class="w-full lg:w-auto pr-4 py-2 border border-gray-300 rounded-lg text-sm bg-white"><option value="all">Filter by District</option><?php while($row = mysqli_fetch_array($districts)): ?><option value="<?php echo htmlspecialchars($row['distr']); ?>" <?php if($filter_district == $row['distr']) echo 'selected'; ?>><?php echo htmlspecialchars($row['distr']); ?></option><?php endwhile; ?></select></div><div class="relative"><i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name, email, city..." class="pl-10 pr-4 py-2 w-full lg:w-80 border border-gray-300 rounded-lg text-sm"/></div></form></div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Customer Info</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Contact</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Location</th><th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th></tr></thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if($rs_main && mysqli_num_rows($rs_main) > 0): ?>
                        <?php while($row = mysqli_fetch_array($rs_main)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><div class="font-bold text-gray-800"><?php echo htmlspecialchars($row['cname']); ?></div><div class="text-xs text-gray-500">ID: <?php echo htmlspecialchars($row['cid']); ?></div></td>
                            <td class="px-6 py-4"><div class="text-sm text-gray-700"><?php echo htmlspecialchars($row['email']); ?></div><div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['phone']); ?></div></td>
                            <td class="px-6 py-4"><div class="text-sm text-gray-700"><?php echo htmlspecialchars($row['city']); ?></div><div class="text-xs text-gray-500"><?php echo htmlspecialchars($row['distr']); ?>, <?php echo htmlspecialchars($row['pincode']); ?></div></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="openDeleteModal('<?php echo $row['cid']; ?>', '<?php echo htmlspecialchars(addslashes($row['cname'])); ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr><td colspan="4" class="text-center py-16"><i class="ri-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Customers Found</h3><p class="text-gray-600 mt-1">Try adjusting your search or filter criteria.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16"><div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div></footer>
    
    <script>
        function openDeleteModal(customerId, customerName) {
            document.getElementById('modal-delete-customer-name').textContent = customerName;
            document.getElementById('modal-delete-confirm-link').href = `cdelete.php?id=${customerId}`;
            document.getElementById('delete-modal').classList.remove('hidden');
        }
        function closeDeleteModal() { document.getElementById('delete-modal').classList.add('hidden'); }

        document.addEventListener('DOMContentLoaded', () => {
            function showPopup(type, message) {
                const popup = document.getElementById(type + '-popup');
                if(popup) {
                    popup.querySelector('span').textContent = message;
                    popup.classList.remove('hidden');
                    setTimeout(() => { popup.classList.remove('translate-x-full'); }, 10);
                    setTimeout(() => { popup.classList.add('translate-x-full'); setTimeout(() => { popup.classList.add('hidden'); }, 300);}, 5000);
                }
            }
            <?php if (isset($_SESSION['action_success'])): ?>
                showPopup('success', '<?php echo addslashes($_SESSION['action_success']); ?>');
                <?php unset($_SESSION['action_success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['action_error'])): ?>
                showPopup('error', '<?php echo addslashes($_SESSION['action_error']); ?>');
                <?php unset($_SESSION['action_error']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>