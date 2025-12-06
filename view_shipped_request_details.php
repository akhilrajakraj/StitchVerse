<?php
session_start();
require_once 'databasecon.php'; // Ensure this path is correct

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if a Request ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: shipped_request.php");
    exit();
}

$db = new DatabaseCon();
$request_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- Data Fetching Logic ---
$request_details = null;
$customer_measurements_json = "{}";
$design_details_parts = ['design' => '', 'measurements' => ''];

// This query is specifically for a shipped request belonging to the logged-in tailor
$sql = "SELECT
            sr.*,
            c.cname, c.email AS customer_email, c.phone AS customer_phone, c.address AS customer_address, c.city AS customer_city, c.distr AS customer_distr, c.pincode AS customer_pincode,
            m.*
        FROM
            stitchreq sr
        JOIN
            creg c ON sr.uid = c.cid
        LEFT JOIN
            measurements m ON sr.uid = m.uid
        WHERE
            sr.sdid = ? AND sr.tid = ? AND sr.sstatus = 'Shipped'";

$result = $db->selectData($sql, "ii", $request_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $request_details = $result->fetch_assoc();
    $customer_measurements_json = json_encode($request_details);
    
    // Parse the plain text sdesign_details column to separate design options from measurements
    if (!empty($request_details['sdesign_details'])) {
        $parts = explode('--- Measurements ---', $request_details['sdesign_details']);
        $design_details_parts['design'] = trim($parts[0]);
        if (isset($parts[1])) {
            $design_details_parts['measurements'] = trim($parts[1]);
        }
    }
} else {
    // If no shipped request is found, redirect back to the list.
    $_SESSION['error_message'] = "The shipped request could not be found or is not assigned to you.";
    header("Location: shipped_request.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipped Request Details #<?php echo htmlspecialchars($request_details['sdid']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <!-- === HEADER: WITH ACTIVE STATE FOR 'SHIPPED REQUESTS' === -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-6">
            <!-- Designs Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="designs-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Designs</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="designs-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="upd.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Upload</a>
                    <a href="viewmydesigns.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">My Designs</a>
                </div>
            </div>

            <!-- Design Orders Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="design-orders-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Design Orders</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="design-orders-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="pendingorderpay.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Payments</a>
                    <a href="paidorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Paid Orders</a>
                </div>
            </div>

            <!-- Custom Orders Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="custom-orders-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Custom Orders</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="custom-orders-menu" class="hidden absolute mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailorreq.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">General Requests</a>
                    <a href="personalrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Personal Requests</a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="pendingrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Requests</a>
                    <a href="acceptedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Accepted Requests</a>
                    <a href="shipped_request.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Shipped Requests</a>
                    <a href="rejectedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Rejected Requests</a>
                    <a href="cancelledrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Cancelled Requests</a>
                </div>
            </div>
          </nav>
          
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="mb-6">
            <a href="shipped_request.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Shipped Requests</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-6xl mx-auto">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-white">
                <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($request_details['sdname']); ?></h1>
                        <p class="text-purple-200 font-mono">Request ID: #<?php echo htmlspecialchars($request_details['sdid']); ?></p>
                    </div>
                    <div class="text-center bg-blue-500 text-white font-bold px-4 py-2 rounded-lg flex items-center gap-2">
                         <i class="ri-checkbox-circle-fill"></i>
                        <span>Shipped</span>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left Column: Request & Customer Details -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Request Details -->
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Stitching Specifications</h2>
                            <div class="grid sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                                <p><strong class="text-gray-500">Dress Type:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sdtype']); ?></span></p>
                                <p><strong class="text-gray-500">Fabric:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['sfabric']); ?></span></p>
                                <p><strong class="text-gray-500">Color:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['scolor'] ?? 'N/A'); ?></span></p>
                                <p><strong class="text-gray-500">Pattern:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['spattern'] ?? 'N/A'); ?></span></p>
                                
                                <?php if (!empty($design_details_parts['design'])): ?>
                                    <?php foreach (explode("\n", $design_details_parts['design']) as $line): ?>
                                        <?php if(strpos($line, ':') !== false): ?>
                                        <?php list($key, $value) = array_map('trim', explode(':', $line, 2)); ?>
                                        <p><strong class="text-gray-500"><?php echo htmlspecialchars($key); ?>:</strong><br><span class="font-medium text-gray-800"><?php echo htmlspecialchars($value); ?></span></p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <p><strong class="text-gray-500">Original Delivery Date:</strong><br><span class="font-medium text-gray-800"><?php echo date("F j, Y", strtotime($request_details['sddate'])); ?></span></p>
                                
                                <div class="sm:col-span-2">
                                    <strong class="text-gray-500">Special Instructions:</strong>
                                    <p class="font-medium text-gray-800 mt-1 p-3 bg-gray-50 rounded-lg"><?php echo nl2br(htmlspecialchars($request_details['sinstructions'])); ?></p>
                                </div>
                                <?php if(!empty($request_details['simg'])): ?>
                                <div class="sm:col-span-2">
                                    <strong class="text-gray-500">Reference Image:</strong>
                                    <div class="mt-2"><img src="<?php echo htmlspecialchars($request_details['simg']); ?>" class="max-w-xs w-full rounded-lg border"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Customer Details -->
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Customer Details</h2>
                            <div class="space-y-2 text-sm">
                                <p class="flex items-center gap-3"><i class="ri-user-line text-purple-500 w-4 text-center"></i><span class="font-medium text-gray-800"><?php echo htmlspecialchars($request_details['cname']); ?></span></p>
                                <p class="flex items-center gap-3"><i class="ri-at-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_email']); ?></span></p>
                                <p class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 w-4 text-center"></i><span class="text-gray-600"><?php echo htmlspecialchars($request_details['customer_phone']); ?></span></p>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Measurements -->
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-lg border">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Final Measurements Used</h2>
                        <?php if (isset($request_details['mid'])): ?>
                            <div id="relevant-measurements-container" class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                                <!-- Measurements will be dynamically inserted here by JavaScript -->
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">This customer has not saved their measurements.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Footer is intentionally left blank as there are no actions for a shipped order -->
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center border-t border-gray-800 pt-8">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- DATA FROM PHP ---
        const customerMeasurements = <?php echo $customer_measurements_json; ?>;
        const requestName = <?php echo json_encode($request_details['sdname']); ?>;

        // --- OUTFIT CONFIGURATION FOR MEASUREMENTS ---
        const outfitConfigs = {
            'womens_anarkali_festive': { name: "Anarkali Suit – Festive Wear", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length', 'inseam', 'outseam'] },
            'womens_kurti_office': { name: "Office Kurti – Formal Wear", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length'] },
            'womens_anarkali_semi': { name: "Anarkali Kurti – Semi-Formal", measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length'] },
            'womens_shirt_palazzo': { name: "Mandarin Collar Shirt with Palazzo", measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'wrist', 'inseam', 'outseam'] },
            'womens_trousers_formal': { name: "Formal Linen Trousers – Women", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam', 'ankle'] },
            'womens_blouse_bridal': { name: "Silk Bridal Blouse", measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'arm_length', 'sleeve_length'] },
            'womens_skirt_aline': { name: "A-Line Midi Skirt – Casual Chic", measurements: ['waist', 'hip', 'height'] },
            'womens_skirt_pleated': { name: "Pleated Maxi Skirt – Formal Occasions", measurements: ['waist', 'hip', 'height'] },
            'womens_skirt_denim': { name: "Denim Mini Skirt – Street Style", measurements: ['waist', 'hip'] },
            'womens_skirt_mermaid': { name: "Mermaid Skirt – Elegant Fit", measurements: ['waist', 'hip', 'knee', 'height'] },
            'unisex_skirt_wrap': { name: "Wrap Skirt – Bohemian Style", measurements: ['waist', 'hip', 'height'] },
            'mens_shirt_formal': { name: "Classic Formal Shirt", measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length', 'wrist'] },
            'mens_shirt_linen': { name: "Casual Linen Shirt", measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length'] },
            'mens_chinos_slim': { name: "Slim Fit Chinos", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'ankle'] },
            'mens_trousers_wool': { name: "Tailored Wool Trousers", measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam'] },
            'mens_cargo': { name: "Cargo Pants – Functional Casual", measurements: ['waist', 'hip', 'thigh', 'inseam', 'outseam'] },
            'mens_polo': { name: "Polo T-Shirt – Smart Casual", measurements: ['neck', 'shoulder', 'chest', 'sleeve_length'] },
            'mens_kurta': { name: "Mandarin Collar Kurta – Ethnic Formal", measurements: ['neck', 'shoulder', 'chest', 'waist', 'hip', 'sleeve_length'] },
            'unisex_hoodie': { name: "Oversized Hoodie", measurements: ['shoulder', 'chest', 'sleeve_length', 'height'] },
            'unisex_joggers': { name: "Athletic Joggers", measurements: ['waist', 'hip', 'thigh', 'inseam', 'ankle'] }
        };
        const allMeasurementsLabels = { height: 'Height', weight: 'Weight', neck: 'Neck', shoulder: 'Shoulder', chest: 'Chest', bust: 'Bust', waist: 'Waist', hip: 'Hip', arm_length: 'Arm Length', sleeve_length: 'Sleeve Length', bicep: 'Bicep', wrist: 'Wrist', thigh: 'Thigh', knee: 'Knee', calf: 'Calf', inseam: 'Inseam', outseam: 'Outseam', ankle: 'Ankle' };

        // --- FUNCTION TO DISPLAY RELEVANT MEASUREMENTS ---
        function displayRelevantMeasurements() {
            const container = document.getElementById('relevant-measurements-container');
            if (!container) return;

            let outfitKey = null;
            for (const key in outfitConfigs) {
                if (outfitConfigs[key].name === requestName) {
                    outfitKey = key;
                    break;
                }
            }

            if (outfitKey && outfitConfigs[outfitKey]) {
                const requiredMeasurements = outfitConfigs[outfitKey].measurements;
                let html = '';
                requiredMeasurements.forEach(key => {
                    const label = allMeasurementsLabels[key] || key.replace('_', ' ');
                    const value = customerMeasurements[key] || 'N/A';
                    if (value !== 'N/A' && value !== '') {
                         html += `<div><p class="text-gray-500 capitalize">${label}</p><p class="font-semibold text-gray-800">${value} cm</p></div>`;
                    }
                });
                container.innerHTML = html || '<p class="col-span-2 text-sm text-gray-500">No relevant measurements found for this outfit type.</p>';
            } else {
                 container.innerHTML = '<p class="col-span-2 text-sm text-gray-500">Could not determine required measurements for this custom request.</p>';
            }
        }
        
        displayRelevantMeasurements(); // Call the function on page load

        // --- Standard Dropdown & Mobile Menu Logic ---
        const menuButton = document.getElementById('menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        if(menuButton && mobileMenu) {
            menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
        }

        const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
        dropdownButtons.forEach(button => {
            const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
            const dropdownMenu = document.getElementById(dropdownMenuId);
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                document.querySelectorAll('.dropdown-button').forEach(otherButton => {
                    if (otherButton !== button) {
                        const otherMenuId = otherButton.getAttribute('data-dropdown-toggle');
                        document.getElementById(otherMenuId).classList.add('hidden');
                        otherButton.setAttribute('aria-expanded', 'false');
                    }
                });
                dropdownMenu.classList.toggle('hidden');
                const isExpanded = !dropdownMenu.classList.contains('hidden');
                button.setAttribute('aria-expanded', isExpanded);
            });
        });
        window.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(button => {
                const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                document.getElementById(dropdownMenuId).classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
            });
        });
    });
    </script>
</body>
</html>
