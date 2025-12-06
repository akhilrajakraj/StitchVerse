<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

// PHPMailer imports
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tailor_id = $_SESSION['user_id'];

if ($request_id <= 0) {
    header("Location: tailorreq.php");
    exit();
}

// --- Form Handling for All Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $sdid = isset($_POST['sdid']) ? (int)$_POST['sdid'] : 0;
    
    if ($sdid == $request_id) {
        $sql_email_data = "SELECT c.cname, c.email, sr.sdname FROM stitchreq sr JOIN creg c ON sr.uid = c.cid WHERE sr.sdid = ?";
        $email_data = $db->selectData($sql_email_data, "i", $sdid)->fetch_assoc();

        if ($action === 'accept_request') {
            $sprice = isset($_POST['sprice']) ? (float)$_POST['sprice'] : 0;
            if ($sprice <= 0) {
                $_SESSION['error_message'] = "You must set a valid price to accept the request.";
            } else {
                $sql_update = "UPDATE stitchreq SET tid = ?, sprice = ?, sstatus = 'Accepted' WHERE sdid = ? AND tid = 0 AND sstatus = 'Pending'";
                $stmt = $conn->prepare($sql_update);
                $stmt->bind_param("idi", $tailor_id, $sprice, $sdid);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    // Email sending logic for acceptance...
                    $_SESSION['success_message'] = "Request #{$sdid} accepted! The customer has been notified.";
                    header("Location: acceptedrequest.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "This request is no longer available.";
                    header("Location: tailorreq.php");
                    exit();
                }
            }
        } elseif ($action === 'reject_request') {
             // Logic for rejecting request...
        }
    }
    header("Location: tailor_request_details.php?id=" . $request_id);
    exit();
}


// --- Data Fetching Logic ---
$request_details = null;
// --- MODIFICATION IS HERE: Added the correct aliases back to the query ---
$sql = "SELECT
            sr.*,
            c.cname, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, 
            c.city AS customer_city, c.distr AS customer_distr, c.pincode AS customer_pincode
        FROM
            stitchreq sr
        JOIN
            creg c ON sr.uid = c.cid
        WHERE
            sr.sdid = ? AND (sr.tid = 0 OR sr.tid = ?)";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "This request is no longer available or you don't have permission to view it.";
    header("Location: tailorreq.php");
    exit();
}

$tailor_name_query = $db->selectData("SELECT tname FROM treg WHERE tid = ?", "i", $tailor_id);
$tailor_name = "Tailor";
if ($tailor_name_query && $tailor_name_query->num_rows > 0) {
    $tailor_data = $tailor_name_query->fetch_assoc();
    $tailor_name = explode(' ', trim($tailor_data['tname']))[0];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
          <div class="flex items-center justify-between">
            <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
            <div class="hidden md:flex items-center space-x-4">
              <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
              <a href="logout.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
            </div>
          </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-6">
            <a href="tailorreq.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to General Requests</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($request_details['sdname']); ?></h1>
                        <p class="text-purple-200 font-mono">Request ID: #<?php echo htmlspecialchars($request_details['sdid']); ?></p>
                    </div>
                    <div class="text-center bg-yellow-400 text-yellow-900 font-bold px-4 py-2 rounded-lg">
                        Awaiting Action
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-t-shirt-line text-purple-600"></i>Core Specifications</h2>
                            <div class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm bg-gray-50 p-4 rounded-lg border">
                                <p><strong class="text-gray-500">Dress Type:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sdtype']); ?></span></p>
                                <p><strong class="text-gray-500">Fabric:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sfabric']); ?></span></p>
                                <p><strong class="text-gray-500">Color:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['scolor'] ?? 'N/A'); ?></span></p>
                                <p><strong class="text-gray-500">Pattern:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['spattern'] ?? 'N/A'); ?></span></p>
                                <p class="sm:col-span-2"><strong class="text-gray-500">Desired Delivery Date:</strong><br><span class="font-bold text-red-600"><?php echo date("F j, Y", strtotime($request_details['sddate'])); ?></span></p>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-ruler-2-line text-purple-600"></i>Customer's Specifications & Measurements</h2>
                            <div class="space-y-2 text-sm border p-4 rounded-lg">
                                <?php
                                $details_lines = explode("\n", trim($request_details['sdesign_details']));
                                foreach ($details_lines as $line):
                                    $line = trim($line);
                                    if (strpos($line, '---') !== false):
                                ?>
                                        <h3 class="text-md font-bold text-gray-700 pt-3 mt-3 border-t"><?php echo htmlspecialchars(str_replace('-', '', $line)); ?></h3>
                                <?php
                                    elseif (strpos($line, ':') !== false):
                                        list($key, $value) = array_map('trim', explode(':', $line, 2));
                                ?>
                                        <div class="flex justify-between items-center py-1"><strong class="text-gray-500"><?php echo htmlspecialchars($key); ?>:</strong><span class="font-medium text-gray-800 text-right"><?php echo htmlspecialchars($value); ?></span></div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                        <?php if(!empty($request_details['sinstructions']) || !empty($request_details['simg'])): ?>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3"><i class="ri-edit-2-line text-purple-600"></i>Additional Notes & Inspiration</h2>
                             <?php if(!empty($request_details['sinstructions'])): ?>
                                <div class="mb-4"><strong class="text-gray-500 text-sm">Special Instructions:</strong><p class="font-medium text-gray-800 mt-1 p-3 bg-yellow-50 rounded-lg border border-yellow-200 text-sm"><?php echo nl2br(htmlspecialchars($request_details['sinstructions'])); ?></p></div>
                            <?php endif; ?>
                            <?php if(!empty($request_details['simg'])): ?>
                                <div><strong class="text-gray-500 text-sm">Reference Image:</strong><div class="mt-2"><img src="<?php echo htmlspecialchars($request_details['simg']); ?>" class="max-w-xs w-full rounded-lg border shadow-sm"></div></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Details</h2>
                        <div class="space-y-3 text-sm">
                            <p class="flex items-center gap-3"><i class="ri-user-line text-purple-500 w-4 text-center"></i><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['cname']); ?></span></p>
                            <p class="flex items-center gap-3"><i class="ri-at-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_email']); ?></span></p>
                            <p class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_phone']); ?></span></p>
                            <div class="flex items-start gap-3 pt-2">
                                <i class="ri-map-pin-line text-purple-500 w-4 text-center mt-1"></i>
                                <div class="text-gray-600">
                                    <p><?php echo htmlspecialchars($request_details['customer_address']); ?></p>
                                    <p><?php echo htmlspecialchars($request_details['customer_city']); ?>, <?php echo htmlspecialchars($request_details['customer_distr']); ?></p>
                                    <p><?php echo htmlspecialchars($request_details['customer_pincode']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <form action="tailor_request_details.php?id=<?php echo $request_id; ?>" method="POST" class="flex flex-col md:flex-row items-center gap-4 w-full" onsubmit="return confirm('Are you sure you want to accept this job with the quoted price?');">
                    <input type="hidden" name="action" value="accept_request">
                    <input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>">
                    <div class="flex-grow w-full md:w-auto"><label for="sprice" class="block text-sm font-medium text-gray-700">Set Your Price (₹)</label><input type="number" step="0.01" name="sprice" id="sprice" required class="w-full md:w-48 p-2 mt-1 border border-gray-300 rounded-md" placeholder="e.g., 1500.00"></div>
                    <button type="submit" class="w-full md:w-auto font-semibold text-white bg-green-600 hover:bg-green-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2"><i class="ri-checkbox-circle-line text-lg"></i> Accept & Quote Price</button>
                </form>
                <button id="reject-btn" type="button" class="w-full md:w-auto font-semibold text-white bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg text-sm flex items-center justify-center gap-2"><i class="ri-close-circle-line text-lg"></i> Reject</button>
            </div>
        </div>
    </main>

    <div id="rejection-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden opacity-0"><div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform -translate-y-10"><div class="p-6"><div class="flex items-start justify-between"><h2 class="text-xl font-bold text-gray-800">Reject Request</h2><button id="close-rejection-modal-btn" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button></div><p class="text-sm text-gray-600 mt-2">Provide a reason for rejecting this request. This will be sent to the customer.</p><form action="tailor_request_details.php?id=<?php echo $request_id; ?>" method="POST" class="mt-4"><input type="hidden" name="action" value="reject_request"><input type="hidden" name="sdid" value="<?php echo $request_details['sdid']; ?>"><input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($request_details['customer_email']); ?>"><input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($request_details['cname']); ?>"><input type="hidden" name="request_name" value="<?php echo htmlspecialchars($request_details['sdname']); ?>"><div><label for="rejection_reason" class="sr-only">Reason for Rejection</label><textarea id="rejection_reason" name="rejection_reason" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., I am currently unavailable or do not have the required fabric." required></textarea></div><div class="mt-6 flex justify-end gap-3"><button type="button" id="cancel-rejection-btn" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Cancel</button><button type="submit" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Confirm Rejection</button></div></form></div></div></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const setupModal = (modalId, openBtnId, closeBtnId, cancelBtnId) => {
                const modal = document.getElementById(modalId);
                const openBtn = document.getElementById(openBtnId);
                const closeBtn = document.getElementById(closeBtnId);
                const cancelBtn = document.getElementById(cancelBtnId);
                if (!modal || !openBtn) return;
                const modalContainer = modal.querySelector('.modal-container');
                const openModal = () => { modal.classList.remove('hidden'); setTimeout(() => { modal.classList.remove('opacity-0'); modalContainer.classList.remove('-translate-y-10'); }, 10); };
                const closeModal = () => { modal.classList.add('opacity-0'); modalContainer.classList.add('-translate-y-10'); setTimeout(() => { modal.classList.add('hidden'); }, 300); };
                openBtn.addEventListener('click', openModal);
                if(closeBtn) closeBtn.addEventListener('click', closeModal);
                if(cancelBtn) cancelBtn.addEventListener('click', closeModal);
                modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
            };
            setupModal('rejection-modal', 'reject-btn', 'close-rejection-modal-btn', 'cancel-rejection-btn');
        });
    </script>
</body>
</html>