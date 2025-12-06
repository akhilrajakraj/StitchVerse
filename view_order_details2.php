<?php
session_start();
require 'databasecon.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID.");
}

$db = new DatabaseCon();
$order_id = $_GET['id'];

$sql = "SELECT o.oid, o.orderdate, o.ostatus, u.dname, u.dtype, u.dprice, u.dimg, u.ddesc,
               c.cname, c.email as cemail, c.phone as cphone,
               t.tname, t.email as temail
        FROM orderdesign o 
        JOIN creg c ON o.uid = c.cid 
        JOIN upload u ON o.did = u.did
        JOIN treg t ON u.uid = t.tid
        WHERE o.oid = ?";
$result = $db->selectData($sql, "i", $order_id);
if (!$result || $result->num_rows === 0) {
    die("Order not found.");
}
$details = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo htmlspecialchars($details['oid']); ?> - StitchVerse Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
        <div class="container mx-auto px-6 py-4"><div class="flex items-center justify-between"><a href="adminhomepage.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a><a href="index.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700 text-sm font-semibold">Logout</a></div></div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto mb-4"><a href="manageorder.php" class="text-sm text-purple-700 font-semibold hover:underline flex items-center gap-2"><i class="ri-arrow-left-line"></i> Back to All Orders</a></div>
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl p-8">
            <div class="border-b pb-4 mb-6 flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($details['dname']); ?></h1>
                    <p class="text-gray-500">Details for Design Order #<?php echo htmlspecialchars($details['oid']); ?></p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo strtolower($details['ostatus']) == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>"><?php echo htmlspecialchars($details['ostatus']); ?></span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><h3 class="font-semibold text-gray-700 mb-2">Customer Details</h3><p class="text-gray-600"><?php echo htmlspecialchars($details['cname']); ?></p><p class="text-gray-600"><?php echo htmlspecialchars($details['cemail']); ?></p><p class="text-gray-600"><?php echo htmlspecialchars($details['cphone']); ?></p></div>
                <div><h3 class="font-semibold text-gray-700 mb-2">Tailor Details</h3><p class="text-gray-600"><?php echo htmlspecialchars($details['tname']); ?></p><p class="text-gray-600"><?php echo htmlspecialchars($details['temail']); ?></p></div>
            </div>
            <div class="mt-6 border-t pt-6">
                <h3 class="font-semibold text-gray-700 mb-4">Order Summary</h3>
                <div class="flex items-center gap-6 bg-gray-50 p-4 rounded-lg">
                    <img src="<?php echo htmlspecialchars($details['dimg']); ?>" class="w-20 h-24 object-cover rounded-lg border">
                    <div class="flex-grow">
                        <p class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($details['dname']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($details['dtype']); ?></p>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars($details['ddesc']); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-bold text-purple-600">₹<?php echo number_format($details['dprice'], 2); ?></p>
                        <p class="text-sm text-gray-500">Order Date: <?php echo date("d M, Y", strtotime($details['orderdate'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>