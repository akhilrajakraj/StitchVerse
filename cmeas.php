<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Customer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Customer ID provided.");
}

$db = new DatabaseCon();
$customer_id = $_GET['id'];
$measurements = null;
$customer_name = "Customer";

// --- Full Stack Enhancement: Securely fetch customer name and measurements ---
$sql_customer = "SELECT cname FROM creg WHERE cid = ?";
$res_customer = $db->selectData($sql_customer, "i", $customer_id);
if ($res_customer && $res_customer->num_rows === 1) {
    $customer_name = $res_customer->fetch_assoc()['cname'];
}

$sql_meas = "SELECT * FROM measurements WHERE uid = ?";
$res_meas = $db->selectData($sql_meas, "i", $customer_id);
if ($res_meas && $res_meas->num_rows === 1) {
    $measurements = $res_meas->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Measurements for <?php echo htmlspecialchars($customer_name); ?> - StitchVerse</title>
    
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
            <a href="designorder.php" class="text-gray-700 hover:text-purple-600">Design Requests</a>
            <a href="tailorreq.php" class="text-gray-700 hover:text-purple-600">Custom Requests</a>
            <a href="accrequest.php" class="text-purple-600 font-semibold">Accepted Requests</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <div class="max-w-4xl mx-auto mb-4">
            <a href="accrequest.php" class="text-sm text-purple-700 font-semibold hover:underline flex items-center gap-2">
                <i class="ri-arrow-left-line"></i>
                Back to Accepted Requests
            </a>
        </div>
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-ruler-line text-2xl text-white"></i></div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Customer Measurements</h1>
                            <p class="text-purple-100 mt-1">Viewing measurements for <span class="font-semibold"><?php echo htmlspecialchars($customer_name); ?></span></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($measurements): ?>
                <div class="p-8 space-y-8">
                    <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-body-scan-line text-purple-600 mr-3"></i>General Body</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Height</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['height']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Weight</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['weight']); ?> kg</p></div>
                        </div>
                    </div>

                    <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-shirt-line text-purple-600 mr-3"></i>Upper Body</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Neck</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['neck']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Shoulder</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['shoulder']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Chest</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['chest']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Bust</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['bust']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Waist</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['waist']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Hip</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['hip']); ?> cm</p></div>
                        </div>
                    </div>

                    <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-hand text-purple-600 mr-3"></i>Arms</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Arm Length</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['arm_length']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Sleeve Length</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['sleeve_length']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Bicep</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['bicep']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Wrist</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['wrist']); ?> cm</p></div>
                        </div>
                    </div>

                    <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-men-line text-purple-600 mr-3"></i>Lower Body</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Thigh</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['thigh']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Knee</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['knee']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Calf</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['calf']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Inseam</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['inseam']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Outseam</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['outseam']); ?> cm</p></div>
                            <div class="bg-gray-50 p-4 rounded-lg"><label class="block text-sm font-medium text-gray-500">Ankle</label><p class="text-lg font-bold text-gray-800 mt-1"><?php echo htmlspecialchars($measurements['ankle']); ?> cm</p></div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <div class="p-8 md:p-16 text-center">
                        <div class="w-24 h-24 rounded-full bg-yellow-100 mx-auto flex items-center justify-center mb-6">
                            <i class="ri-error-warning-line text-6xl text-yellow-500"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800">No Measurements Found</h2>
                        <p class="text-gray-600 mt-4 max-w-md mx-auto">The customer, <?php echo htmlspecialchars($customer_name); ?>, has not saved any measurements to their profile yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>

</body>
</html>