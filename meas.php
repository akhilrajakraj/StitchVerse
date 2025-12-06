
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$data = null; 
$customer_name = "Customer";

// Fetch customer's name for the header
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}


// Fetch existing measurements to pre-fill the form
$q = "SELECT * FROM measurements WHERE uid = ?";
$res = $db->selectData($q, "i", $customer_id);
if ($res) {
    $data = $res->fetch_assoc();
}

// If form is submitted, update or insert (This logic is excellent and remains unchanged)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = $db->getConnection();
    $measurements_data = [
        'height' => $_POST['height'] ?? null, 'weight' => $_POST['weight'] ?? null,
        'neck' => $_POST['neck'] ?? null, 'shoulder' => $_POST['shoulder'] ?? null,
        'chest' => $_POST['chest'] ?? null, 'bust' => $_POST['bust'] ?? null,
        'waist' => $_POST['waist'] ?? null, 'hip' => $_POST['hip'] ?? null,
        'arm_length' => $_POST['arm_length'] ?? null, 'sleeve_length' => $_POST['sleeve_length'] ?? null,
        'bicep' => $_POST['bicep'] ?? null, 'wrist' => $_POST['wrist'] ?? null,
        'thigh' => $_POST['thigh'] ?? null, 'knee' => $_POST['knee'] ?? null,
        'calf' => $_POST['calf'] ?? null, 'inseam' => $_POST['inseam'] ?? null,
        'outseam' => $_POST['outseam'] ?? null, 'ankle' => $_POST['ankle'] ?? null,
    ];
    $types = "dddddddddddddddddd"; 

    if ($data) {
        $u = "UPDATE measurements SET height=?, weight=?, neck=?, shoulder=?, chest=?, bust=?, waist=?, hip=?, arm_length=?, sleeve_length=?, bicep=?, wrist=?, thigh=?, knee=?, calf=?, inseam=?, outseam=?, ankle=? WHERE uid=?";
        $stmt = $conn->prepare($u);
        $params = array_merge(array_values($measurements_data), [$customer_id]);
        $stmt->bind_param($types . "i", ...$params);
        $stmt->execute();
    } else {
        $s = "INSERT INTO measurements(uid, height, weight, neck, shoulder, chest, bust, waist, hip, arm_length, sleeve_length, bicep, wrist, thigh, knee, calf, inseam, outseam, ankle) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($s);
        $stmt->bind_param("i" . $types, $customer_id, ...array_values($measurements_data));
        $stmt->execute();
    }

    $_SESSION['meas_success'] = "Your measurements have been saved successfully!";
    header("Location: meas.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Measurements - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; } 
        #alert-modal.hidden { display: none; }
        #modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div id="modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8 transform scale-95 opacity-0">
            <div class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-5 bg-green-100">
                <i class="ri-checkbox-circle-line text-5xl text-green-500"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Success!</h3>
            <p id="modal-message" class="text-gray-600 mb-8"></p>
            <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
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
                 <div class="px-4 py-3 border-b border-gray-100"><p class="text-sm text-gray-500">Signed in as</p><p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p></div>
                 <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm font-semibold text-purple-600 bg-purple-50">My Measurements</a></div>
                 <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-gray-800">My Measurements</h1>
                        <p class="text-gray-600 mt-2">Provide accurate measurements for a perfect fit on all your orders.</p>
                    </div>
                    
                    <form method="post" action="meas.php">
                        <div class="text-sm text-blue-800 bg-blue-50 border border-blue-200 rounded-lg p-3 mb-8">Tip: All measurements should be in centimeters (cm) for consistency.</div>

                        <div class="space-y-8">
                            <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-body-scan-line text-purple-600 mr-3"></i>General</h2><div class="grid grid-cols-1 md:grid-cols-2 gap-6"><div><label class="block mb-1 text-sm font-medium text-gray-700">Height</label><input type="number" step="0.1" name="height" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['height'] ?? '') ?>" placeholder="cm"></div><div><label class="block mb-1 text-sm font-medium text-gray-700">Weight</label><input type="number" step="0.1" name="weight" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['weight'] ?? '') ?>" placeholder="kg"></div></div></div>
                            <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-shirt-line text-purple-600 mr-3"></i>Upper Body</h2><div class="grid grid-cols-2 md:grid-cols-3 gap-6"><div><label class="mb-1 text-sm font-medium text-gray-700">Neck</label><input type="number" step="0.1" name="neck" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['neck'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Shoulder</label><input type="number" step="0.1" name="shoulder" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['shoulder'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Chest</label><input type="number" step="0.1" name="chest" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['chest'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Bust</label><input type="number" step="0.1" name="bust" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['bust'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Waist</label><input type="number" step="0.1" name="waist" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['waist'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Hip</label><input type="number" step="0.1" name="hip" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['hip'] ?? '') ?>" placeholder="cm"></div></div></div>
                            <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-hand text-purple-600 mr-3"></i>Arms</h2><div class="grid grid-cols-2 md:grid-cols-4 gap-6"><div><label class="mb-1 text-sm font-medium text-gray-700">Arm Length</label><input type="number" step="0.1" name="arm_length" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['arm_length'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Sleeve Length</label><input type="number" step="0.1" name="sleeve_length" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['sleeve_length'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Bicep</label><input type="number" step="0.1" name="bicep" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['bicep'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Wrist</label><input type="number" step="0.1" name="wrist" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['wrist'] ?? '') ?>" placeholder="cm"></div></div></div>
                            <div><h2 class="text-xl font-bold text-gray-800 border-b pb-3 mb-6 flex items-center"><i class="ri-men-line text-purple-600 mr-3"></i>Lower Body</h2><div class="grid grid-cols-2 md:grid-cols-3 gap-6"><div><label class="mb-1 text-sm font-medium text-gray-700">Thigh</label><input type="number" step="0.1" name="thigh" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['thigh'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Knee</label><input type="number" step="0.1" name="knee" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['knee'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Calf</label><input type="number" step="0.1" name="calf" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['calf'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Inseam</label><input type="number" step="0.1" name="inseam" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['inseam'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Outseam</label><input type="number" step="0.1" name="outseam" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['outseam'] ?? '') ?>" placeholder="cm"></div><div><label class="mb-1 text-sm font-medium text-gray-700">Ankle</label><input type="number" step="0.1" name="ankle" class="w-full p-2 border border-gray-300 rounded-md" value="<?= htmlspecialchars($data['ankle'] ?? '') ?>" placeholder="cm"></div></div></div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center gap-4">
                            <a href="customerhome.php" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-semibold text-sm transition-colors">Cancel</a>
                            <button type="submit" class="px-8 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow-md transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="ri-save-line"></i> Save Measurements
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <footer class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <a href="customerhome.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
                    <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide for custom clothing that fits perfectly.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">For Customers</h4>
                    <ul class="space-y-2">
                        <li><a href="viewdesigns.php" class="text-gray-400 hover:text-white">Browse Gallery</a></li>
                        <li><a href="cviewt.php" class="text-gray-400 hover:text-white">Find Tailors</a></li>
                        <li><a href="customreq1.php" class="text-gray-400 hover:text-white">Place Order</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">My Account</h4>
                    <ul class="space-y-2">
                        <li><a href="cupdate.php" class="text-gray-400 hover:text-white">My Profile</a></li>
                        <li><a href="meas.php" class="text-gray-400 hover:text-white">Measurements</a></li>
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Logout</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // --- UPGRADED MODAL SCRIPT ---
        const modal = document.getElementById('alert-modal');
        const modalContent = document.getElementById('modal-content');
        const modalMessage = document.getElementById('modal-message');
        const closeModalBtn = document.getElementById('modal-close-btn');
        function showModal(message) {
            modalMessage.textContent = message;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 50);
        }
        function hideModal() {
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
        closeModalBtn.addEventListener('click', hideModal);
        
        <?php if (isset($_SESSION['meas_success'])): ?>
            showModal('<?php echo addslashes($_SESSION['meas_success']); ?>');
            <?php unset($_SESSION['meas_success']); ?>
        <?php endif; ?>

        // --- HEADER DROPDOWN SCRIPT ---
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