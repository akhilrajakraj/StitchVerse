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

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

function getStatusColor($status) {
    switch (strtolower($status ?? '')) {
        case 'paid': case 'completed': return 'bg-green-100 text-green-800 border-green-200';
        default: return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    }
}
function getStatusIcon($status) {
    switch (strtolower($status ?? '')) {
        case 'paid': case 'completed': return 'ri-check-line';
        default: return 'ri-time-line';
    }
}

$sql_total = "SELECT COUNT(o.oid) as count FROM orderdesign o JOIN upload u ON o.did = u.did WHERE u.uid = ?";
$total_count = $db->selectData($sql_total, "i", $tailor_id)->fetch_assoc()['count'];

$sql_paid = "SELECT COUNT(o.oid) as count 
             FROM orderdesign o 
             JOIN payment p ON o.oid = p.order_id 
             JOIN upload u ON o.did = u.did 
             WHERE u.uid = ? AND p.pstatus = 'Paid'";
$paid_count = $db->selectData($sql_paid, "i", $tailor_id)->fetch_assoc()['count'];

$sql_main = "SELECT o.oid, o.ostatus, o.orderdate, u.dname, u.dtype, u.dprice, u.dimg, c.cname, c.email, c.phone, p.pstatus
             FROM orderdesign AS o
             INNER JOIN upload AS u ON o.did = u.did
             INNER JOIN creg AS c ON o.uid = c.cid
             LEFT JOIN payment AS p ON o.oid = p.order_id
             WHERE u.uid = ?";

$types = "i";
$params = [$tailor_id];

if ($filter_status === 'paid') {
    $sql_main .= " AND p.pstatus = 'Paid'";
}
if (!empty($search_term)) {
    $searchTermLike = "%" . $search_term . "%";
    $sql_main .= " AND (o.oid LIKE ? OR u.dname LIKE ? OR c.cname LIKE ?)";
    $types .= "sss";
    $params[] = $searchTermLike;
    $params[] = $searchTermLike;
    $params[] = $searchTermLike;
}
$sql_main .= " ORDER BY o.oid DESC";
$rs_main = $db->selectData($sql_main, $types, ...$params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design Order Requests - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
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
            <a href="designorder.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">Design Requests</a>
            <a href="tailorreq.php" class="text-gray-700 hover:text-purple-600">Custom Requests</a>
            <a href="accrequest.php" class="text-gray-700 hover:text-purple-600">Accepted Requests</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center"><div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-draft-line text-2xl text-white"></i></div><div><h1 class="text-3xl font-bold text-white">Design Sales Orders</h1><p class="text-purple-100 mt-1">Track sales of your pre-made designs.</p></div></div>
                    <div class="text-white text-right"><div class="text-2xl font-bold"><?php echo $total_count; ?></div><div class="text-purple-100 text-sm">Total Orders</div></div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl"><div class="text-2xl font-bold text-blue-600"><?php echo $total_count; ?></div><div class="text-blue-600 text-sm font-medium">All Orders</div></div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl"><div class="text-2xl font-bold text-green-600"><?php echo $paid_count; ?></div><div class="text-green-600 text-sm font-medium">Paid Orders</div></div>
            </div>
            <div class="p-6 border-t border-gray-100">
                <form method="get" action="designorder.php" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <a href="?status=all" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?php echo $filter_status == 'all' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">All Orders (<?php echo $total_count; ?>)</a>
                        <a href="?status=paid" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?php echo $filter_status == 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>">Paid (<?php echo $paid_count; ?>)</a>
                    </div>
                    <div class="relative"><i class="ri-search-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search by name, customer, ID..." class="pl-10 pr-4 py-2 w-full lg:w-80 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"/></div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <?php if($rs_main && mysqli_num_rows($rs_main) > 0): ?>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Order Details</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Design Info</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Customer Info</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Payment Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = mysqli_fetch_array($rs_main)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-6 whitespace-nowrap"><div class="text-sm font-semibold text-gray-900">ID: <?php echo htmlspecialchars($row['oid']); ?></div><div class="text-xs text-gray-500 mt-1"><?php echo date("d M, Y", strtotime($row['orderdate'])); ?></div></td>
                            <td class="px-6 py-6"><div class="flex items-start space-x-3"><div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 border"><img src="<?php echo htmlspecialchars($row['dimg']); ?>" alt="<?php echo htmlspecialchars($row['dname']); ?>" class="w-full h-full object-cover"></div><div><div class="text-sm font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($row['dname']); ?></div><div class="text-xs text-purple-600 mb-2"><?php echo htmlspecialchars($row['dtype']); ?></div><div class="text-sm font-semibold text-gray-800">₹<?php echo number_format($row['dprice'], 2); ?></div></div></div></td>
                            <td class="px-6 py-6"><div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['cname']); ?></div><div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($row['email']); ?></div><div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($row['phone']); ?></div></td>
                            <td class="px-6 py-6 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo getStatusColor($row['pstatus']); ?>"><i class="<?php echo getStatusIcon($row['pstatus']); ?> text-xs mr-1"></i><?php echo htmlspecialchars($row['pstatus'] ?? 'Not Paid'); ?></span></td>
                            
                            <td class="px-6 py-6 whitespace-nowrap">
                                <?php if (!is_null($row['pstatus']) && strtolower($row['pstatus']) == 'paid'): ?>
                                    <a href='paystr3.php?order_id=<?php echo $row['oid']; ?>' class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                        <i class="ri-receipt-line"></i> View Receipt
                                    </a>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">Payment not made</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-16"><i class="ri-search-line text-5xl text-gray-300"></i><h3 class="text-xl font-semibold text-gray-800 mt-4">No Orders Found</h3><p class="text-gray-600 mt-1">Orders placed by customers on your designs will appear here.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>

</body>
</html>