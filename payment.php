<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure we're coming from the order review page with the correct data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['did'])) {
    // Redirect back if accessed directly or without a design ID
    header("Location: viewdesigns.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$design_id = intval($_POST['did']);
// Note: In a real app, you would sanitize and validate this address further.
$shipping_address = $_POST['address']; 

// --- Step 1: Fetch Design details to confirm price and show summary ---
$sql_design = "SELECT dname, dprice, dimg FROM upload WHERE did = ?";
$result_design = $db->selectData($sql_design, "i", $design_id);

if (!$result_design || $result_design->num_rows === 0) {
    die("Error: Invalid design specified for payment.");
}
$design = $result_design->fetch_assoc();
$amount = $design['dprice'];

// --- Step 2: Create a PENDING order in the `orderdesign` table ---
// This secures an order ID before attempting payment.
$order_status = 'Pending Payment';
$order_date = date('Y-m-d');

$sql_insert_order = "INSERT INTO orderdesign (uid, did, ostatus, orderdate) VALUES (?, ?, ?, ?)";
$stmt = $db->getConnection()->prepare($sql_insert_order);
$stmt->bind_param("iiss", $customer_id, $design_id, $order_status, $order_date);
$stmt->execute();
$order_id = $stmt->insert_id; // Get the ID of the new order
$stmt->close();

if (!$order_id) {
    die("There was a critical error creating your order. Please try again.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; } 
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
           <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600">View Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600">View Tailors</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="cupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
             <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800">Secure Payment</h1>
                <p class="text-gray-600 mt-2">Enter your payment details to complete the purchase.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <form action="payment_action.php" method="POST" id="payment-form">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <input type="hidden" name="uid" value="<?php echo $customer_id; ?>">

                        <div class="space-y-6">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">Card Type</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center p-3 border rounded-lg w-full cursor-pointer"><input type="radio" name="card_name" value="debit" checked class="mr-2 text-purple-600 focus:ring-purple-500"> Debit Card</label>
                                    <label class="flex items-center p-3 border rounded-lg w-full cursor-pointer"><input type="radio" name="card_name" value="credit" class="mr-2 text-purple-600 focus:ring-purple-500"> Credit Card</label>
                                </div>
                            </div>

                            <div>
                                <label for="card_no" class="block mb-2 text-sm font-medium text-gray-700">Card Number</label>
                                <div class="relative">
                                    <i class="ri-credits-card-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" id="card_no" name="card_no" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg" placeholder="0000 0000 0000 0000" required maxlength="16">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="carexp_dt" class="block mb-2 text-sm font-medium text-gray-700">Expiry Date</label>
                                    <input type="text" id="carexp_dt" name="carexp_dt" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="MM/YY" required>
                                </div>
                                <div>
                                    <label for="cvv" class="block mb-2 text-sm font-medium text-gray-700">CVV</label>
                                    <input type="password" id="cvv" name="cvv" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="•••" required maxlength="3">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-6 border-t">
                             <button type="submit" class="w-full px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <i class="ri-shield-check-line"></i> Pay ₹<?php echo number_format($amount); ?> Securely
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-28">
                     <h3 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h3>
                     <div class="flex items-center gap-4">
                         <img src="<?php echo htmlspecialchars($design['dimg']); ?>" alt="<?php echo htmlspecialchars($design['dname']); ?>" class="w-20 h-24 object-cover rounded-md bg-gray-100">
                         <div>
                             <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($design['dname']); ?></p>
                             <p class="text-sm text-gray-500">Order ID: #<?php echo $order_id; ?></p>
                             <p class="font-bold text-purple-600 mt-1">₹<?php echo number_format($amount); ?></p>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>

</body>
</html>