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
    <title>Upload Designs - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        /* Transitions for popups */
        #success-popup, #delete-popup {
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
        /* Style for the dropdown arrow animation */
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <!-- Pop-up messages are UNCHANGED -->
    <div id="success-popup" class="hidden fixed top-5 right-5 bg-green-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="ri-checkbox-circle-line text-2xl"></i>
        <span></span>
    </div>

    <div id="delete-popup" class="hidden fixed top-5 right-5 bg-red-500 text-white py-3 px-6 rounded-lg shadow-xl z-50 flex items-center gap-3">
        <i class="ri-error-warning-line text-2xl"></i>
        <span></span>
    </div>

    <!-- The header with the new dropdowns is UNCHANGED -->
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
                    <a href="upd.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">Upload</a>
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
        
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <!-- The mobile menu from tailorhome.php should be copied here for consistency -->
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <i class="ri-upload-2-line text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Upload Your Design</h1>
                            <p class="text-purple-100 mt-1">Share your latest creations with the world.</p>
                        </div>
                    </div>
                </div>
                <div class="p-8">
                    <form action="updaction.php" method="post" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Design Name *</label>
                                <div class="relative">
                                    <i class="ri-palette-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" name="dname" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., Summer Floral Dress">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Design Type *</label>
                                <div class="relative">
                                    <i class="ri-price-tag-3-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" name="dtype" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., Casual Wear, Formal Wear">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Design Description *</label>
                            <div class="relative">
                                <i class="ri-file-text-line text-gray-400 absolute left-3 top-4"></i>
                                <textarea name="ddesc" required rows="4" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 resize-none" placeholder="Describe materials, fit, special features..."></textarea>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Design Price (₹) *</label>
                                <div class="relative">
                                    <i class="ri-money-rupee-circle-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="number" name="dprice" required min="0" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Enter price in INR">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Design Image *</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400">
                                    <i class="ri-image-add-line text-4xl text-gray-400 mx-auto mb-2"></i>
                                    <input type="file" name="file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="ri-upload-cloud-2-line"></i>
                                Upload Design
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- THE "MY DESIGNS" SECTION HAS BEEN REMOVED FROM THIS PAGE -->

        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-16 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center border-t border-gray-800 pt-8">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- === SCRIPT: MODIFIED FOR DROPDOWNS & POPUPS === -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if(menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
            }

            // --- Dropdown Menu Logic ---
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

            // --- Pop-up Message Logic (Unchanged) ---
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

            <?php if (isset($_SESSION['upload_success'])): ?>
                showPopup('success-popup', '<?php echo addslashes($_SESSION['upload_success']); ?>');
                <?php unset($_SESSION['upload_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['update_success'])): ?>
                showPopup('success-popup', '<?php echo addslashes($_SESSION['update_success']); ?>');
                <?php unset($_SESSION['update_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['delete_success'])): ?>
                showPopup('delete-popup', '<?php echo addslashes($_SESSION['delete_success']); ?>');
                <?php unset($_SESSION['delete_success']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>