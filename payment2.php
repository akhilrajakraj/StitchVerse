
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

// Securely fetch order details and verify ownership
$sql = "SELECT o.oid, o.ostatus, u.dname, u.dimg, u.dprice, t.tname, c.cname 
        FROM orderdesign o
        JOIN upload u ON o.did = u.did
        JOIN treg t ON u.uid = t.tid
        JOIN creg c ON o.uid = c.cid
        WHERE o.oid = ? AND o.uid = ?";
$result = $db->selectData($sql, "ii", $order_id, $customer_id);

if ($result && $result->num_rows === 1) {
    $order = $result->fetch_assoc();
    $customer_name = explode(' ', trim($order['cname']))[0];
} else {
    die("Error: Could not find the specified order for your account.");
}

// Check if the order is already paid
$sql_payment = "SELECT pid FROM payment WHERE order_id = ? AND pstatus = 'Paid'";
$payment_result = $db->selectData($sql_payment, "i", $order_id);
$is_paid = ($payment_result && $payment_result->num_rows > 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .payment-tab { cursor: pointer; border-bottom: 4px solid transparent; }
        .payment-tab.active { border-color: #9333ea; color: #9333ea; }
        .form-error { color: #dc2626; font-size: 0.875rem; height: 1.25rem; transition: all 0.2s; }
        .btn-disabled { background-color: #d1d5db; cursor: not-allowed; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600 transition-colors">Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600 transition-colors">Tailors</a>
            <a href="customreq1.php" class="text-gray-700 hover:text-purple-600 transition-colors">Stitch Request</a>
          </nav>
          <div class="hidden md:flex items-center space-x-6">
            <div class="relative" id="profile-dropdown-container">
              <button id="profile-dropdown-button" class="flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                <div class="px-4 py-3 border-b border-gray-100"><p class="text-sm text-gray-500">Signed in as</p><p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name ?? 'Customer'); ?></p></div>
                <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a></div>
                <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="text-center p-6 bg-gray-50 border-b">
                    <h1 class="text-3xl font-bold text-gray-800">Secure Payment</h1>
                    <p class="text-gray-600 mt-1">For Order #<?php echo htmlspecialchars($order['oid']); ?></p>
                </div>
                
                <?php if ($is_paid): ?>
                    <div class="p-8 md:p-12 text-center"><div class="w-24 h-24 rounded-full bg-green-100 mx-auto flex items-center justify-center mb-6"><i class="ri-shield-check-line text-6xl text-green-500"></i></div><h2 class="text-3xl font-bold text-gray-800">Payment Complete</h2><p class="text-gray-600 mt-4 max-w-md mx-auto">This order has already been paid for. No further action is needed.</p><a href="designorders.php" class="mt-8 inline-block bg-purple-600 text-white px-10 py-3 rounded-lg text-lg font-bold hover:bg-purple-700">View My Design Orders</a></div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-6 bg-gray-50/70">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h3>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="flex items-start gap-4">
                                <img src="<?php echo htmlspecialchars($order['dimg']); ?>" class="w-20 h-24 object-cover rounded-md flex-shrink-0">
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($order['dname']); ?></h4>
                                    <p class="text-sm text-gray-500">By: <?php echo htmlspecialchars($order['tname']); ?></p>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2 text-sm border-t pt-4">
                                <div class="flex justify-between"><span class="text-gray-600">Item Price:</span><span class="font-medium text-gray-900">₹<?php echo number_format($order['dprice']); ?></span></div>
                                <div class="flex justify-between"><span class="text-gray-600">Delivery Fee:</span><span class="font-medium text-gray-900">₹40.00</span></div>
                                <div class="flex justify-between text-base font-bold text-gray-800 border-t pt-2 mt-2"><span >Total Payable:</span><span class="text-purple-600">₹<?php echo number_format($order['dprice'] + 40); ?></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="border-b mb-6"><nav class="flex -mb-px space-x-6"><div id="card-tab" class="payment-tab active py-4 px-1 text-sm font-semibold">Credit/Debit Card</div></nav></div>
                        <form id="payment-form" action="payaction2.php" method="POST" novalidate>
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['oid']); ?>">
                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($order['dprice']); ?>">
                            <div class="space-y-5">
                                <div>
                                    <label for="card_no" class="block mb-1 text-sm font-medium text-gray-700">Card Number</label>
                                    <div class="relative"><i class="ri-credits-card-line text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i><input type="text" id="card_no" name="card_no" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric"></div>
                                    <div id="card_no-error" class="form-error"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="carexp_dt" class="block mb-1 text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input type="text" id="carexp_dt" name="carexp_dt" required class="w-full py-2 px-3 border border-gray-300 rounded-lg" placeholder="MM / YY" maxlength="7" inputmode="numeric">
                                        <div id="carexp_dt-error" class="form-error"></div>
                                    </div>
                                    <div>
                                        <label for="cvv" class="block mb-1 text-sm font-medium text-gray-700">CVV</label>
                                        <input type="password" id="cvv" name="cvv" required class="w-full py-2 px-3 border border-gray-300 rounded-lg" placeholder="•••" maxlength="3" inputmode="numeric">
                                        <div id="cvv-error" class="form-error"></div>
                                    </div>
                                </div>
                                <div>
                                    <label for="card_name" class="block mb-1 text-sm font-medium text-gray-700">Card Holder Name</label>
                                    <input type="text" id="card_name" name="card_name" required class="w-full py-2 px-3 border border-gray-300 rounded-lg" placeholder="Name as on card">
                                    <div id="card_name-error" class="form-error"></div>
                                </div>
                                <div class="pt-2">
                                    <button id="submit-btn" type="submit" class="w-full py-3 bg-purple-600 text-white rounded-lg font-semibold shadow-md transition-all flex items-center justify-center gap-2 btn-disabled">
                                        <i class="ri-shield-check-line"></i> <span>Pay ₹<?php echo number_format($order['dprice'] + 40); ?> Securely</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('payment-form');
        const submitBtn = document.getElementById('submit-btn');
        const inputs = {
            card_no: form.querySelector('#card_no'),
            carexp_dt: form.querySelector('#carexp_dt'),
            cvv: form.querySelector('#cvv'),
            card_name: form.querySelector('#card_name')
        };

        inputs.card_no.addEventListener('input', (e) => { e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim(); });
        inputs.carexp_dt.addEventListener('input', (e) => {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length > 2) value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
            e.target.value = value;
        });

        const validate = {
            card_no: value => {
                const stripped = value.replace(/\s/g, '');
                if (stripped.length !== 16) return 'Card number must be 16 digits.';
                return '';
            },
            carexp_dt: value => {
                if (!/^(0[1-9]|1[0-2])\s\/\s\d{2}$/.test(value)) return 'Invalid date format (MM / YY).';
                const [month, year] = value.split(' / ');
                const expiryDate = new Date(`20${year}`, month - 1);
                const currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0);
                expiryDate.setDate(expiryDate.getDate() + 1);
                if (expiryDate < currentDate) return 'Card has expired.';
                return '';
            },
            // MODIFICATION 2: Validation logic and error message updated for 3 digits
            cvv: value => {
                if (!/^\d{3}$/.test(value)) return 'CVV must be 3 digits.';
                return '';
            },
            card_name: value => {
                if (value.trim().length < 2) return 'Please enter the card holder name.';
                return '';
            }
        };

        function validateField(fieldName) {
            const inputEl = inputs[fieldName];
            const errorEl = document.getElementById(`${fieldName}-error`);
            const errorMessage = validate[fieldName](inputEl.value);
            errorEl.textContent = errorMessage;
            inputEl.classList.toggle('border-red-500', !!errorMessage);
            inputEl.classList.toggle('focus:border-red-500', !!errorMessage);
            inputEl.classList.toggle('border-green-500', !errorMessage && inputEl.value.length > 0);
            checkFormValidity();
        }
        
        function checkFormValidity() {
            let isFormValid = true;
            for (const fieldName in inputs) {
                if (validate[fieldName](inputs[fieldName].value) || inputs[fieldName].value.trim() === '') {
                    isFormValid = false;
                    break;
                }
            }
            if (isFormValid) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-disabled');
                submitBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-disabled');
                submitBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
            }
        }

        for (const fieldName in inputs) { inputs[fieldName].addEventListener('input', () => validateField(fieldName)); }
        form.addEventListener('submit', function(e) {
            let isFormValidOnSubmit = true;
            for (const fieldName in inputs) { if (validate[fieldName](inputs[fieldName].value)) isFormValidOnSubmit = false; }
            if (!isFormValidOnSubmit) e.preventDefault();
        });
        
        checkFormValidity();

        const profileDropdownButton = document.getElementById('profile-dropdown-button');
        const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
        if (profileDropdownButton && profileDropdownMenu) {
            profileDropdownButton.addEventListener('click', (event) => {
                event.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
            });
            window.addEventListener('click', (event) => {
                if (!profileDropdownButton.contains(event.target)) {
                    profileDropdownMenu.classList.add('hidden');
                }
            });
        }
    });
    </script>
</body>
</html>