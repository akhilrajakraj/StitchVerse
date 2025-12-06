<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Design ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Design ID provided.");
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$design_id = $_GET['id'];
$measurements_exist = false;

// --- CRITICAL: Check if the user has saved their measurements ---
$sql_meas = "SELECT mid FROM measurements WHERE uid = ?";
$result_meas = $db->selectData($sql_meas, "i", $customer_id);
if ($result_meas && $result_meas->num_rows > 0) {
    $measurements_exist = true;
}

// --- Fetch Design and Tailor details to pre-populate the form ---
$sql_details = "SELECT u.*, t.tname, t.tid 
                FROM upload u 
                JOIN treg t ON u.uid = t.tid
                WHERE u.did = ?";
$result_details = $db->selectData($sql_details, "i", $design_id);

if ($result_details->num_rows === 0) {
    die("Design not found.");
}
$design = $result_details->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Customization - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600">View Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600">View Tailors</a>
            <a href="meas.php" class="text-gray-700 hover:text-purple-600">Measurements</a>
            <a href="customreq1.php" class="text-gray-700 hover:text-purple-600">Stitch Request</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="cupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="vieworders.php" class="text-gray-700 hover:text-purple-600">My Orders</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4"><i class="ri-edit-2-line text-2xl text-white"></i></div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Request a Customization</h1>
                            <p class="text-purple-100 mt-1">Request changes to "<?php echo htmlspecialchars($design['dname']); ?>" by <?php echo htmlspecialchars($design['tname']); ?>.</p>
                        </div>
                    </div>
                </div>
                
                <?php if ($measurements_exist): ?>
                <form action="reqdesaction.php" method="POST" enctype="multipart/form-data" class="p-8">
                    <input type="hidden" name="uid" value="<?php echo $customer_id; ?>">
                    <input type="hidden" name="tid" value="<?php echo $design['tid']; ?>">
                    <input type="hidden" name="sdname" value="<?php echo htmlspecialchars($design['dname']); ?>">
                    <input type="hidden" name="sdtype" value="<?php echo htmlspecialchars($design['dtype']); ?>">
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-t-shirt-line text-purple-600 mr-3"></i>Based on this Design</h2>
                        <div class="flex items-start gap-6">
                            <img src="<?php echo htmlspecialchars($design['dimg']); ?>" class="w-24 h-32 object-cover rounded-lg border">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($design['dname']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($design['dtype']); ?></p>
                                <p class="text-lg font-bold text-purple-600 mt-2">Base Price: ₹<?php echo number_format($design['dprice']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-edit-2-line text-purple-600 mr-3"></i>Describe Your Customizations</h2>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Your desired changes*</label>
                            <textarea name="sinstructions" required rows="5" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="e.g., I would like this dress in a navy blue color. Please change the neckline to a V-neck and add full-length sleeves."></textarea>
                        </div>
                        <div class="mt-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700">Inspiration Images for your changes (optional)</label>
                            <input type="file" name="simg[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-calendar-event-line text-purple-600 mr-3"></i>Logistics</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Desired Delivery Date*</label>
                                <input type="date" name="sddate" required min="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border border-gray-300 rounded-lg">
                            </div>
                             <div><label class="block mb-2 text-sm font-medium text-gray-700">Priority</label>
                                <div class="flex gap-6 mt-3"><label class="flex items-center"><input type="radio" name="spriority" value="Standard" checked class="mr-2"> Standard</label><label class="flex items-center"><input type="radio" name="spriority" value="Expedited" class="mr-2"> Expedited</label></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="ri-send-plane-2-line"></i> Submit Customization Request
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="p-8 md:p-16 text-center">
                    <div class="w-24 h-24 rounded-full bg-red-100 mx-auto flex items-center justify-center mb-6"><i class="ri-error-warning-line text-6xl text-red-500"></i></div>
                    <h2 class="text-3xl font-bold text-gray-800">Measurements Required</h2>
                    <p class="text-gray-600 mt-4 max-w-md mx-auto">To request a customization, you must first have your measurements saved to your profile. This allows the tailor to know if your request is feasible.</p>
                    <a href="meas.php" class="mt-8 inline-block bg-pink-600 text-white px-10 py-4 rounded-lg text-lg font-bold hover:bg-pink-700 transition-all transform hover:scale-105 shadow-lg">Add My Measurements Now</a>
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