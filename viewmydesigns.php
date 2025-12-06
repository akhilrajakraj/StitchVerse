
<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Designs - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        /* Transitions for popups */
        #success-popup, #delete-popup, #info-popup {
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
        /* Style for the dropdown arrow animation */
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <!-- Pop-up messages for all actions -->
    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="ri-checkbox-circle-line text-2xl"></i>
        <span></span>
    </div>

    <div id="delete-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="ri-error-warning-line text-2xl"></i>
        <span></span>
    </div>
    
    <div id="info-popup" class="hidden fixed top-5 right-5 bg-blue-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="ri-information-line text-2xl"></i>
        <span></span>
    </div>


    <!-- === HEADER: WITH ACTIVE STATE FOR 'MY DESIGNS' === -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-6">
            <!-- Designs Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="designs-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Designs</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="designs-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="upd.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Upload</a>
                    <a href="viewmydesigns.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">My Designs</a>
                </div>
            </div>

            <!-- Other Dropdowns... -->
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
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                                <i class="ri-gallery-line text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">My Designs</h1>
                                <p class="text-purple-100 mt-1">Manage your uploaded designs</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <?php
                        // Using prepared statements for security
                        $sql = "SELECT * FROM upload WHERE uid = ? ORDER BY did DESC";
                        $rs = $db->selectData($sql, "i", $tailor_id);
                        if($rs && $rs->num_rows > 0) {
                    ?>
                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <?php while($row = $rs->fetch_assoc()) { ?>
                                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                                    <div class="aspect-square relative overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($row['dimg']); ?>" alt="<?php echo htmlspecialchars($row['dname']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="font-semibold text-gray-800 pr-2"><?php echo htmlspecialchars($row['dname']); ?></h3>
                                            <span class="text-purple-600 font-bold whitespace-nowrap">₹ <?php echo htmlspecialchars($row['dprice']); ?></span>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2"><?php echo htmlspecialchars($row['dtype']); ?></p>
                                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-2"><?php echo htmlspecialchars($row['ddesc']); ?></p>
                                    </div>
                                    <div class="px-4 pb-4 flex justify-end items-center gap-2 border-t border-gray-100 pt-3">
                                        <a href="upupload.php?id=<?php echo $row['did']; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-md transition-colors">Update</a>
                                        <a href="updel.php?id=<?php echo $row['did']; ?>" onclick="return confirm('Are you sure you want to delete this design?');" class="text-sm font-medium text-red-600 hover:text-red-800 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md transition-colors">Delete</a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php
                        } else {
                    ?>
                        <div class="text-center py-12">
                            <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="ri-image-line text-gray-400 text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">No designs uploaded yet.</h3>
                            <p class="text-gray-600">Go to the <a href="upd.php" class="text-purple-600 font-medium hover:underline">Upload</a> page to add your first design!</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown Menu Logic
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

            // --- Pop-up Message Logic ---
            function showPopup(popupId, message) {
                const popup = document.getElementById(popupId);
                if(popup) {
                    popup.querySelector('span').textContent = message;
                    popup.classList.remove('hidden');
                    setTimeout(() => {
                        popup.style.opacity = '1';
                        popup.style.transform = 'translateY(0)';
                    }, 10);
                    setTimeout(() => {
                        popup.style.opacity = '0';
                        popup.style.transform = 'translateY(-20px)';
                        setTimeout(() => popup.classList.add('hidden'), 500);
                    }, 4000);
                }
            }

            // --- Check for all possible session messages ---
            <?php if (isset($_SESSION['update_success'])): ?>
                showPopup('success-popup', '<?php echo addslashes($_SESSION['update_success']); ?>');
                <?php unset($_SESSION['update_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['delete_success'])): ?>
                showPopup('delete-popup', '<?php echo addslashes($_SESSION['delete_success']); ?>');
                <?php unset($_SESSION['delete_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['cancel_message'])): ?>
                showPopup('info-popup', '<?php echo addslashes($_SESSION['cancel_message']); ?>');
                <?php unset($_SESSION['cancel_message']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>