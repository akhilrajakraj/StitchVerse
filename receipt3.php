
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid Order ID provided.");
}

$db = new DatabaseCon();
$order_id = $_GET['order_id'];
$customer_id = $_SESSION['user_id'];

// Full Stack Enhancement: Securely fetch data and verify ownership
$sql = "SELECT 
            p.order_id, p.pdate,
            s.sdname, s.sdtype, s.sprice, s.sinstructions, s.sddate, s.simg,
            c.cname, c.address, c.city, c.distr, c.pincode,
            t.tname
        FROM payment AS p
        INNER JOIN stitchreq AS s ON p.order_id = s.sdid
        INNER JOIN creg AS c ON s.uid = c.cid
        LEFT JOIN treg AS t ON s.tid = t.tid
        WHERE p.order_id = ? AND s.uid = ? AND p.pstatus = 'Paid'";

$result = $db->selectData($sql, "ii", $order_id, $customer_id);

if ($result && $result->num_rows === 1) {
    $receipt = $result->fetch_assoc();
} else {
    echo "<script>alert('Receipt not found or you do not have permission to view it.'); window.location='stitchingorders.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-STITCH-<?php echo htmlspecialchars($receipt['order_id']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; } 
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; } /* Ensures backgrounds print in Chrome */
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="no-print container mx-auto px-6 py-4 flex items-center justify-between">
        <a href="stitchrequest_details.php?id=<?php echo htmlspecialchars($receipt['order_id']); ?>" class="text-sm font-medium text-purple-600 hover:text-purple-800 flex items-center gap-2">
            <i class="ri-arrow-left-line"></i> Back to Request Details
        </a>
        <button onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors flex items-center gap-2">
            <i class="ri-printer-line"></i> Print Receipt
        </button>
    </div>

    <main class="container mx-auto px-6 py-8">
        <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-xl shadow-lg">
            <div class="flex justify-between items-start border-b border-gray-200 pb-6">
                <div>
                    <a href="customerhome.php" class="text-3xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
                    <p class="text-sm text-gray-500 mt-1">Thiruvananthapuram, Kerala, India</p>
                </div>
                <div class="text-right">
                    <h1 class="text-3xl font-bold text-gray-800 uppercase">Invoice</h1>
                    <p class="text-sm text-gray-500">#INV-STITCH-<?php echo htmlspecialchars($receipt['order_id']); ?></p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mt-8">
                <div>
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Billed To</h2>
                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($receipt['cname']); ?></p>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        <?php echo htmlspecialchars($receipt['address']); ?><br>
                        <?php echo htmlspecialchars($receipt['city']); ?>, <?php echo htmlspecialchars($receipt['distr']); ?>, <?php echo htmlspecialchars($receipt['pincode']); ?>
                    </p>
                </div>
                <div class="md:text-right">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Details</h2>
                    <p class="text-gray-600 text-sm"><span class="font-semibold text-gray-800">Payment Date:</span> <?php echo date("d M, Y", strtotime($receipt['pdate'])); ?></p>
                    <p class="text-gray-600 text-sm"><span class="font-semibold text-gray-800">Desired By Date:</span> <?php echo date("d M, Y", strtotime($receipt['sddate'])); ?></p>
                </div>
            </div>

            <div class="mt-10">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Summary</h3>
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
                                <div class="flex items-center gap-4">
                                    <img src="<?php echo htmlspecialchars($receipt['simg']); ?>" class="w-12 h-16 object-cover rounded-md border">
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($receipt['sdname']); ?></p>
                                        <p class="text-sm text-gray-600">Custom Stitch Request by <?php echo htmlspecialchars($receipt['tname']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-800">₹<?php echo number_format($receipt['sprice'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end">
                <div class="w-full max-w-xs space-y-3">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>₹<?php echo number_format($receipt['sprice'], 2); ?></span></div>
                    <div class="flex justify-between text-gray-600"><span>Delivery Fee</span><span>₹40.00</span></div>
                    <div class="flex justify-between text-lg font-bold text-gray-800 border-t pt-3 mt-2"><span>Total Paid</span><span class="text-purple-600">₹<?php echo number_format($receipt['sprice'] + 40, 2); ?></span></div>
                </div>
            </div>

            <div class="mt-12 border-t pt-6 text-center">
                <p class="text-gray-600 font-semibold">Thank you for your business!</p>
                <p class="text-xs text-gray-500 mt-1">This is a computer-generated receipt. If you have any questions, please contact our support.</p>
            </div>
        </div>
    </main>

</body>
</html>