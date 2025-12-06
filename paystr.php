<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID provided.");
}

$db = new DatabaseCon();
$order_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// Full Stack Enhancement: Securely fetch data for a STITCH REQUEST and verify ownership.
$sql = "SELECT 
            p.order_id, p.pdate,
            s.sdname, s.sdtype, s.sprice, s.sinstructions, s.sddate, s.simg,
            c.cname, c.address, c.city, c.distr, c.pincode, c.email, c.phone
        FROM payment AS p
        INNER JOIN stitchreq AS s ON p.order_id = s.sdid
        INNER JOIN creg AS c ON s.uid = c.cid
        WHERE p.order_id = ? AND s.tid = ? AND p.pstatus = 'Paid'";

$result = $db->selectData($sql, "ii", $order_id, $tailor_id);

if ($result && $result->num_rows === 1) {
    $details = $result->fetch_assoc();
} else {
    echo "<script>alert('Receipt not found or you do not have permission to view it.'); window.location='accrequest.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo htmlspecialchars($details['order_id']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; } 
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="no-print container mx-auto px-6 py-4 flex items-center justify-between">
        <a href="accrequest.php" class="text-sm text-gray-600 hover:text-purple-600 flex items-center gap-2">
            <i class="ri-arrow-left-line"></i> Back to Accepted Requests
        </a>
        <button onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors flex items-center gap-2">
            <i class="ri-printer-line"></i> Print Details
        </button>
    </div>

    <main class="container mx-auto px-6 py-8">
        <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-lg">
            <div class="flex justify-between items-start border-b pb-6">
                <div>
                    <a href="index.php" class="text-3xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
                    <p class="text-sm text-gray-500 mt-1">Order Fulfillment Details</p>
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-800 uppercase">Order Details</h1>
                    <p class="text-sm text-gray-500">Request ID: #<?php echo htmlspecialchars($details['order_id']); ?></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mt-8">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Customer Details</h2>
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($details['cname']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($details['address']); ?>, <?php echo htmlspecialchars($details['city']); ?></p>
                    <p class="text-gray-600 mt-2"><i class="ri-mail-line text-gray-400 mr-1"></i> <?php echo htmlspecialchars($details['email']); ?></p>
                    <p class="text-gray-600"><i class="ri-phone-line text-gray-400 mr-1"></i> <?php echo htmlspecialchars($details['phone']); ?></p>
                </div>
                <div class="md:text-right">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">Order Timeline</h2>
                    <p class="text-gray-600"><strong>Payment Date:</strong> <?php echo date("d M, Y", strtotime($details['pdate'])); ?></p>
                    <p class="text-gray-600"><strong>Delivery Due:</strong> <?php echo date("d M, Y", strtotime($details['sddate'])); ?></p>
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border bg-green-100 text-green-800 border-green-200">
                        <i class="ri-shield-check-line mr-2"></i> Paid
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($details['sdname']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($details['sdtype']); ?> (Custom Stitch Request)</p>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-800">₹<?php echo number_format($details['sprice'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end">
                <div class="w-full max-w-xs space-y-3">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>₹<?php echo number_format($details['sprice'], 2); ?></span></div>
                    <div class="flex justify-between text-lg font-bold text-gray-800 border-t pt-3 mt-2"><span>Total Paid</span><span class="text-purple-600">₹<?php echo number_format($details['sprice'], 2); ?></span></div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>