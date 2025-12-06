
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$design_feedbacks = [];
$stitch_feedbacks = [];

// --- 1. Fetch DESIGN PURCHASE Feedback ---
$query_designs = "SELECT 
                    f.feedbck, 
                    f.rate, 
                    c.cname, 
                    u.dname 
                  FROM feedb AS f
                  JOIN creg AS c ON f.uid = c.cid
                  LEFT JOIN upload AS u ON f.itemid = u.did
                  WHERE f.tid = ? AND f.feedtype = 'design_purchase'
                  ORDER BY f.fid DESC"; 

$result_designs = $db->selectData($query_designs, "i", $tailor_id);
if ($result_designs) {
    while ($row = $result_designs->fetch_assoc()) {
        $design_feedbacks[] = $row;
    }
}

// --- 2. Fetch STITCH REQUEST Feedback ---
// NOTE: This assumes feedtype is 'stitch_request'. Adjust if you use a different value.
$query_stitch = "SELECT 
                    f.feedbck, 
                    f.rate, 
                    c.cname, 
                    s.sdname 
                 FROM feedb AS f
                 JOIN creg AS c ON f.uid = c.cid
                 LEFT JOIN stitchreq AS s ON f.itemid = s.sdid
                 WHERE f.tid = ? AND f.feedtype = 'stitch_request'
                 ORDER BY f.fid DESC";

$result_stitch = $db->selectData($query_stitch, "i", $tailor_id);
if ($result_stitch) {
    while ($row = $result_stitch->fetch_assoc()) {
        $stitch_feedbacks[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - StitchVerse</title>
    
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
        /* Styles for the new tabs */
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            color: #9333ea; /* purple-600 */
            border-bottom-color: #9333ea;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-6">
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
            <div class="relative">
                <button data-dropdown-toggle="custom-orders-menu" class="dropdown-button text-gray-700 hover:text-purple-600 font-medium flex items-center gap-1">
                    <span>Custom Orders</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="custom-orders-menu" class="hidden absolute mt-2 w-56 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="tailorreq.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">General Requests</a>
                    <a href="personalrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Personal Requests</a>
                    <div class="my-1 border-t border-gray-100"></div>
                    <a href="pendingrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Pending Requests</a>
                    <a href="acceptedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Accepted Requests</a>
                    <a href="shipped_request.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Shipped Requests</a>
                    <a href="rejectedrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Rejected Requests</a>
                    <a href="cancelledrequest.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Cancelled Requests</a>
                </div>
            </div>
          </nav>
          
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                        <i class="ri-feedback-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Customer Feedback</h1>
                        <p class="text-purple-100 mt-1">Here's what your customers are saying about your work.</p>
                    </div>
                </div>
            </div>
            
            <div class="px-8">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-base" data-tab="designs">
                            Design Purchases
                        </button>
                        <button class="tab-button text-gray-500 hover:text-gray-700 whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-semibold text-base" data-tab="stitching">
                            Custom Stitching
                        </button>
                    </nav>
                </div>

                <div class="py-8">
                    <div class="tab-panel" id="designs-panel">
                        <?php if (empty($design_feedbacks)): ?>
                            <div class="text-center py-10"><div class="w-24 h-24 rounded-full bg-gray-100 mx-auto flex items-center justify-center mb-4"><i class="ri-t-shirt-2-line text-5xl text-gray-400"></i></div><h3 class="text-xl font-semibold text-gray-700">No Design Feedback Yet</h3><p class="text-gray-500 mt-2">You haven't received any feedback for your uploaded designs.</p></div>
                        <?php else: ?>
                            <div class="space-y-6">
                                <?php foreach ($design_feedbacks as $feedback): ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-start justify-between flex-wrap gap-2">
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($feedback['cname']); ?></h4>
                                                <p class="text-sm text-gray-500 mt-1">For Design: <span class="font-medium"><?php echo htmlspecialchars($feedback['dname'] ?? 'N/A'); ?></span></p>
                                            </div>
                                            <div class="flex items-center text-yellow-500"><div class="flex"><?php for ($i = 1; $i <= 5; $i++) { echo $i <= $feedback['rate'] ? '<i class="ri-star-fill"></i>' : '<i class="ri-star-line"></i>'; } ?></div><span class="ml-2 text-sm font-semibold text-gray-600">(<?php echo $feedback['rate']; ?>/5)</span></div>
                                        </div>
                                        <p class="text-gray-600 mt-4 prose max-w-none"><?php echo nl2br(htmlspecialchars($feedback['feedbck'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-panel hidden" id="stitching-panel">
                        <?php if (empty($stitch_feedbacks)): ?>
                            <div class="text-center py-10"><div class="w-24 h-24 rounded-full bg-gray-100 mx-auto flex items-center justify-center mb-4"><i class="ri-scissors-2-line text-5xl text-gray-400"></i></div><h3 class="text-xl font-semibold text-gray-700">No Stitching Feedback Yet</h3><p class="text-gray-500 mt-2">You haven't received any feedback for custom stitch requests.</p></div>
                        <?php else: ?>
                            <div class="space-y-6">
                                <?php foreach ($stitch_feedbacks as $feedback): ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-start justify-between flex-wrap gap-2">
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($feedback['cname']); ?></h4>
                                                <p class="text-sm text-gray-500 mt-1">For Request: <span class="font-medium"><?php echo htmlspecialchars($feedback['sdname'] ?? 'N/A'); ?></span></p>
                                            </div>
                                            <div class="flex items-center text-yellow-500"><div class="flex"><?php for ($i = 1; $i <= 5; $i++) { echo $i <= $feedback['rate'] ? '<i class="ri-star-fill"></i>' : '<i class="ri-star-line"></i>'; } ?></div><span class="ml-2 text-sm font-semibold text-gray-600">(<?php echo $feedback['rate']; ?>/5)</span></div>
                                        </div>
                                        <p class="text-gray-600 mt-4 prose max-w-none"><?php echo nl2br(htmlspecialchars($feedback['feedbck'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- Dropdown Menu Logic ---
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const dropdownMenuId = button.getAttribute('data-dropdown-toggle');
                const dropdownMenu = document.getElementById(dropdownMenuId);
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(otherButton => {
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

            // --- Tab Switching Logic ---
            const tabs = document.querySelectorAll('.tab-button');
            const panels = document.querySelectorAll('.tab-panel');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Deactivate all tabs and panels
                    tabs.forEach(item => {
                        item.classList.remove('active');
                        item.classList.replace('text-gray-900', 'text-gray-500');
                    });
                    panels.forEach(panel => panel.classList.add('hidden'));

                    // Activate the clicked tab and its corresponding panel
                    tab.classList.add('active');
                    tab.classList.replace('text-gray-500', 'text-gray-900');
                    const targetPanelId = tab.getAttribute('data-tab') + '-panel';
                    document.getElementById(targetPanelId).classList.remove('hidden');
                });
            });
        });
    </script>
</body>
</html>