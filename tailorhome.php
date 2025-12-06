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
$tailor_name = "Tailor"; // Default name

// Securely fetch the tailor's name for the welcome message
$query = "SELECT tname FROM treg WHERE tid = ?";
$result = $db->selectData($query, "i", $tailor_id);

if ($result && $result->num_rows === 1) {
    $tailor = $result->fetch_assoc();
    // Get the first name for a more personal greeting
    $tailor_name = explode(' ', trim($tailor['tname']))[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Dashboard - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-white">

    <div id="welcome-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8 transform transition-all scale-95 opacity-0" id="modal-content">
            <div class="mx-auto mb-4 w-20 h-20 flex items-center justify-center rounded-full bg-purple-100">
                <i class="ri-tools-line text-5xl text-purple-600"></i>
            </div>
            <h3 class="text-3xl font-bold text-gray-800 mb-3">Welcome, <?php echo htmlspecialchars($tailor_name); ?>!</h3>
            <p class="text-gray-600 text-lg mb-8">Your dashboard is ready. Let's grow your business together.</p>
            <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700 transition-colors">Let's Get to Work</button>
        </div>
    </div>
<header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <!-- === DESKTOP NAVIGATION - MODIFIED PART START === -->
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
          <!-- === DESKTOP NAVIGATION - MODIFIED PART END === -->
          
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
        
        <!-- === MOBILE MENU - MODIFIED PART START === -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="flex flex-col space-y-4 mt-4">
                <!-- Designs Group -->
                <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Designs</p>
                    <a href="upd.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Upload</a>
                    <a href="viewmydesigns.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">My Designs</a>
                </div>
                <!-- Design Orders Group -->
                <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Design Orders</p>
                    <a href="pendingorderpay.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Pending Payments</a>
                    <a href="paidorders.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Paid Orders</a>
                </div>
                <!-- Custom Orders Group -->
                <div class="space-y-2">
                    <p class="px-2 text-xs font-bold uppercase text-gray-400">Custom Orders</p>
                    <a href="tailorreq.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">General Requests</a>
                    <a href="personalrequest.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Personal Requests</a>
                    <a href="pendingrequest.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Pending Requests</a>
                    <a href="acceptedrequest.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Accepted Requests</a>
                    <a href="rejectedrequest.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Rejected Requests</a>
                    <a href="cancelledrequest.php" class="block px-2 py-1 text-gray-700 hover:text-purple-600 font-medium">Cancelled Requests</a>
                </div>

                <div class="flex flex-col space-y-3 pt-4 border-t border-gray-100">
                  <a href="tupdate.php" class="text-gray-700 hover:text-purple-600 font-medium">My Profile</a>
                  <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg text-center mt-2">Logout</a>
                </div>
            </div>
        </div>
        <!-- === MOBILE MENU - MODIFIED PART END === -->
      </div>
    </header>

    <main>
        <section class="relative min-h-screen flex items-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/tailorbghome.jpg')">
          <div class="container mx-auto px-6 z-10">
            <div id="hero-slider-content" class="max-w-4xl text-white">
                </div>
          </div>
        </section>

        <section class="py-20 bg-white">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Grow Your Tailoring Business</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto">Our platform provides everything you need to scale, succeed, and showcase your expertise.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Digital Storefront</h3>
                    <p class="text-gray-600 leading-relaxed">Create a professional online presence with portfolio galleries, pricing, and booking systems.</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Global Customer Reach</h3>
                    <p class="text-gray-600 leading-relaxed">Connect with clients worldwide through our platform. No geographic limitations.</p>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Management Tools</h3>
                    <p class="text-gray-600 leading-relaxed">Streamline your workflow with order management, customer communication, and scheduling tools.</p>
                </div>
            </div>
          </div>
        </section>

        <section class="py-20 bg-gradient-to-b from-purple-50 to-white">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Showcase Your Expertise</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-12">Display your finest work and attract premium clients. From bespoke suits to intricate gowns, demonstrate the quality that sets you apart.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                </div>
             <div class="text-center mt-12"><a href="upd.php" class="bg-purple-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-purple-700">Upload Your Designs</a></div>
          </div>
        </section>
        
        <section class="py-20 bg-gradient-to-br from-pink-50 via-purple-50 to-white">
          <div class="container mx-auto px-6">
            <div class="text-center mb-16">
              <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Success Stories</h2>
              <p class="text-xl text-gray-600 max-w-3xl mx-auto">See how our platform has helped artisans achieve their goals.</p>
            </div>
            <div class="max-w-6xl mx-auto">
              <div class="relative bg-white rounded-2xl overflow-hidden shadow-xl grid md:grid-cols-2">
                  <div class="relative h-96 md:h-auto"><img src="images/tailorcta.jpg" alt="From Local Shop to Global Business" class="w-full h-full object-cover" loading="lazy"></div>
                  <div class="p-8 md:p-12 flex flex-col justify-center">
                      <h3 class="text-3xl font-bold text-gray-900 mb-4">From Local Shop to Global Business</h3>
                      <p class="text-lg text-gray-600 leading-relaxed">A master tailor expanded from a small shop to serving international clients, increasing revenue by 300% through our global reach.</p>
                  </div>
              </div>
            </div>
          </div>
        </section>
        
        <section class="py-20 bg-cover bg-center" style="background-image: linear-gradient(rgba(126, 34, 206, 0.8), rgba(91, 33, 182, 0.8)), url('images/ctabgtailor.jpg')">
          <div class="container mx-auto px-6 text-center z-10">
            <h2 class="text-4xl md:text-6xl font-bold text-white mb-6">Your Craft Deserves Recognition</h2>
            <p class="text-xl md:text-2xl text-gray-100 mb-12 max-w-3xl mx-auto">Join the premier platform where master tailors showcase their skills and build their business.</p>
            <a href="upd.php" class="bg-white text-purple-600 px-10 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">Start Your Journey</a>
          </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-16">
      <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <a href="tailorhome.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
            <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide.</p>
          </div>
          <div>
            <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-2">
              <li><a href="designorder.php" class="text-gray-400 hover:text-white">Design Requests</a></li>
              <li><a href="tailorreq.php" class="text-gray-400 hover:text-white">Custom Requests</a></li>
              <li><a href="upd.php" class="text-gray-400 hover:text-white">Upload Designs</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-lg font-semibold mb-4">My Account</h4>
            <ul class="space-y-2">
              <li><a href="tupdate.php" class="text-gray-400 hover:text-white">My Profile</a></li>
              <li><a href="index.php" class="text-gray-400 hover:text-white">Logout</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-lg font-semibold mb-4">Company</h4>
            <ul class="space-y-2">
              <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center">
          <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
        </div>
      </div>
    </footer>

     <!-- === SCRIPT: MODIFIED FOR DROPDOWNS === -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle (Unchanged)
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (menuButton && mobileMenu) {
                menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });
            }

            // Hero Section Slider (Unchanged)
            const heroSlides = [
                { title: "Showcase Your Craftsmanship", subtitle: "Join StitchVerse and connect with customers worldwide. Display your skills, grow your business, and earn more." },
                { title: "Build Your Tailoring Empire", subtitle: "Access premium tools, manage orders seamlessly, and reach thousands of customers seeking quality custom clothing." }
            ];
            let currentHeroSlide = 0;
            const heroContentEl = document.getElementById('hero-slider-content');
            function updateHeroSlide() {
                const slide = heroSlides[currentHeroSlide];
                heroContentEl.innerHTML = `<h1 class="text-5xl md:text-7xl font-bold mb-6">${slide.title}</h1><p class="text-xl md:text-2xl mb-8 text-gray-100 leading-relaxed max-w-3xl">${slide.subtitle}</p><a href="upd.php" class="bg-purple-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-purple-700 transition-all duration-300 transform hover:scale-105">Start Uploading</a>`;
            }
            if (heroContentEl) {
                setInterval(() => {
                    currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
                    updateHeroSlide();
                }, 5000);
                updateHeroSlide();
            }

            // --- NEW: Dropdown Menu Logic ---
            
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
            // Welcome Modal Logic (Unchanged)
            const modal = document.getElementById('welcome-modal');
            const modalContent = document.getElementById('modal-content');
            const closeModalBtn = document.getElementById('modal-close-btn');
            function showModal() {
                modal.classList.remove('hidden');
                setTimeout(() => { modalContent.classList.remove('scale-95', 'opacity-0'); modalContent.classList.add('scale-100', 'opacity-100'); }, 50); 
            }
            function hideModal() {
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => { modal.classList.add('hidden'); }, 300);
            }
            if(closeModalBtn) {
                closeModalBtn.addEventListener('click', hideModal);
            }

            <?php
                if (isset($_SESSION['show_welcome_popup'])) {
                    echo "showModal();";
                    unset($_SESSION['show_welcome_popup']);
                }
            ?>
        });
    </script>
</body>
</html>