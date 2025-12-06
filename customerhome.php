
<?php
session_start();
require 'databasecon.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";


$query = "SELECT cname FROM creg WHERE cid = ?";
$result = $db->selectData($query, "i", $customer_id);

if ($result && $result->num_rows === 1) {
    $customer = $result->fetch_assoc();
    
    $customer_name = explode(' ', trim($customer['cname']))[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StitchVerse</title>
    
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
            <div class="mx-auto mb-4 w-20 h-20 flex items-center justify-center rounded-full bg-pink-100">
                <i class="ri-heart-line text-5xl text-pink-600"></i>
            </div>
            <h3 id="modal-title" class="text-3xl font-bold text-gray-800 mb-3">Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h3>
            <p id="modal-message" class="text-gray-600 text-lg mb-8">We're so happy to see you. Let's create your perfect outfit together.</p>
            <button id="modal-close-btn" class="w-full bg-pink-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-pink-700 transition-colors">Start Exploring</button>
        </div>
    </div>

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
              <button id="profile-dropdown-button" class="dropdown-button flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors" aria-expanded="false">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1 arrow-icon transition-transform duration-300"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm text-gray-500">Signed in as</p>
                  <p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p>
                </div>
                <div class="py-1">
                  <a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a>
                  <a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a>
                </div>
                <div class="py-1 border-t border-gray-100">
                  <a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a>
                  <a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a>
                </div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>

          <button id="menu-button" class="md:hidden p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <i class="ri-menu-line text-2xl"></i>
          </button>
        </div>
        
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4">
          <div class="flex flex-col space-y-2">
            <a href="viewdesigns.php" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">Designs</a>
            <a href="cviewt.php" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">Tailors</a>
            <a href="customreq1.php" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">Stitch Request</a>
            
            <div class="pt-4 mt-2 border-t border-gray-200">
              <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">My Account</p>
              <a href="cupdate.php" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">My Profile</a>
              <a href="meas.php" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">My Measurements</a>
              <a href="vieworders.php?type=design" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">Design Orders</a>
              <a href="vieworders.php?type=stitching" class="block py-2 px-3 rounded-md text-base font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-700">Stitching Orders</a>
            </div>

            <div class="pt-4 mt-2 border-t border-gray-200">
                 <a href="logout.php" class="bg-purple-600 text-white px-6 py-3 rounded-lg text-center block w-full font-medium">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>
    <main>
        <section class="relative min-h-screen flex items-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('images/customerhome.jpg.jpg')">
            <div class="container mx-auto px-6 z-10">
                <div class="max-w-4xl text-white">
                    <div id="hero-slider-content">
                        </div>
                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <a href="customreq1.php" class="bg-pink-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-pink-700 transition-all duration-300 transform hover:scale-105 text-center">Design Your Dress</a>
                        <a href="viewdesigns.php" class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-gray-900 transition-all duration-300 text-center">Browse Inspiration</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Your Dream Dress Journey</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">From initial concept to final creation, every step is crafted to bring your unique vision to life.</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-gradient-to-br from-pink-50 to-purple-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">1. Share Your Vision</h3>
                        <p class="text-gray-600 leading-relaxed">Start by sending a "Stitch Request" with your design ideas, inspiration photos, and measurements.</p>
                    </div>
                    <div class="bg-gradient-to-br from-pink-50 to-purple-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">2. Connect with Tailors</h3>
                        <p class="text-gray-600 leading-relaxed">Our expert tailors will review your request and provide you with quotes and suggestions to perfect your design.</p>
                    </div>
                    <div class="bg-gradient-to-br from-pink-50 to-purple-50 p-8 rounded-xl hover:shadow-lg transition-all transform hover:-translate-y-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">3. Creation & Delivery</h3>
                        <p class="text-gray-600 leading-relaxed">Once you accept an offer, your tailor begins crafting. Track your order and receive your dream dress at your doorstep.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-gradient-to-b from-pink-50 to-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Design Inspiration Gallery</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-12">Discover the endless possibilities for your custom dress. From romantic wedding gowns to powerful evening wear.</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                     <div class="group cursor-pointer transform hover:scale-105 transition-all duration-300">
                        <div class="relative overflow-hidden rounded-xl shadow-sm aspect-[4/5]"><img src="images/gallerywedding.jpg" alt="Wedding Gown" class="w-full h-full object-cover" loading="lazy"></div>
                    </div>
                    <div class="group cursor-pointer transform hover:scale-105 transition-all duration-300">
                        <div class="relative overflow-hidden rounded-xl shadow-sm aspect-[4/5]"><img src="images/galleryevening.jpg" alt="Evening Gown" class="w-full h-full object-cover" loading="lazy"></div>
                    </div>
                    <div class="group cursor-pointer transform hover:scale-105 transition-all duration-300">
                        <div class="relative overflow-hidden rounded-xl shadow-sm aspect-[4/5]"><img src="images/gallerycocktail.jpg" alt="Cocktail Dress" class="w-full h-full object-cover" loading="lazy"></div>
                    </div>
                    <div class="group cursor-pointer transform hover:scale-105 transition-all duration-300">
                        <div class="relative overflow-hidden rounded-xl shadow-sm aspect-[4/5]"><img src="images/gallerymaxi.jpg" alt="Maxi Dress" class="w-full h-full object-cover" loading="lazy"></div>
                    </div>
                </div>
                <div class="text-center mt-12">
                    <a href="viewdesigns.php" class="bg-pink-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-pink-700">Explore Full Collection</a>
                </div>
            </div>
        </section>
        
        <section class="py-20 bg-gradient-to-br from-purple-50 via-pink-50 to-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Dreams Made Reality</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Every dress tells a story. See how our custom creations have become part of life's most precious moments.</p>
                </div>
                <div class="max-w-6xl mx-auto">
                    <div class="relative bg-white rounded-2xl overflow-hidden shadow-xl grid md:grid-cols-2">
                         <div class="relative h-96 md:h-auto"><img src="images/cstorybride.jpg" alt="The Perfect Wedding Day" class="w-full h-full object-cover" loading="lazy"></div>
                        <div class="p-8 md:p-12 flex flex-col justify-center">
                            <h3 class="text-3xl font-bold text-gray-900 mb-4">The Perfect Wedding Day</h3>
                            <p class="text-lg text-gray-600 leading-relaxed">A bride's dream came true with a custom gown featuring hand-sewn pearls and vintage lace, creating an unforgettable moment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="py-20 bg-cover bg-center" style="background-image: linear-gradient(rgba(219, 39, 119, 0.8), rgba(147, 51, 234, 0.8)), url('images/ctabg.jpg')">
            <div class="container mx-auto px-6 text-center z-10">
                <h2 class="text-4xl md:text-6xl font-bold text-white mb-6">Your Perfect Dress is Waiting</h2>
                <p class="text-xl md:text-2xl text-gray-100 mb-12 max-w-3xl mx-auto">Don't just dream about the perfect dress - create it. Start with a simple consultation and let our experts guide you.</p>
                <a href="customreq1.php" class="bg-white text-pink-600 px-10 py-4 rounded-lg text-lg font-bold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">Design Your Dress Now</a>
            </div>
        </section>
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
        document.addEventListener('DOMContentLoaded', function() {
            
            const menuButton = document.getElementById('menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            menuButton.addEventListener('click', () => { mobileMenu.classList.toggle('hidden'); });

            // --- JAVASCRIPT FOR NEW DROPDOWN ---
            const profileDropdownButton = document.getElementById('profile-dropdown-button');
            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
            const profileDropdownContainer = document.getElementById('profile-dropdown-container');

            if (profileDropdownButton && profileDropdownMenu) {
                // Toggle dropdown on button click
                profileDropdownButton.addEventListener('click', (event) => {
                    event.stopPropagation(); // Prevents the window click listener from firing immediately
                    
                    // Toggle the menu's visibility
                    profileDropdownMenu.classList.toggle('hidden');
                    
                    // Check the current state and update ARIA attribute
                    const isExpanded = !profileDropdownMenu.classList.contains('hidden');
                    profileDropdownButton.setAttribute('aria-expanded', isExpanded);
                });


                // Close dropdown if clicked outside
                window.addEventListener('click', (event) => {
                    if (profileDropdownContainer && !profileDropdownContainer.contains(event.target)) {
                        profileDropdownMenu.classList.add('hidden');
                        // Also reset the ARIA attribute and arrow when closing from outside
                        profileDropdownButton.setAttribute('aria-expanded', 'false');
                    }
                });
            }
            // --- END OF NEW JAVASCRIPT ---

            // Hero Section Slider
            const heroSlides = [
                { title: "Your Dream Dress Awaits", subtitle: "From elegant wedding gowns to stunning evening wear - bring your vision to life with expert craftsmanship." },
                { title: "Couture Quality, Custom Made", subtitle: "Experience luxury tailoring with personalized designs created just for you by master artisans." },
                { title: "Perfect Fit, Every Time", subtitle: "Upload your inspiration, choose your style, and watch as skilled tailors create your perfect garment." }
            ];
            let currentHeroSlide = 0;
            const heroContentEl = document.getElementById('hero-slider-content');
            function updateHeroSlide() {
                const slide = heroSlides[currentHeroSlide];
                heroContentEl.innerHTML = `<h1 class="text-5xl md:text-7xl font-bold mb-6">${slide.title}</h1><p class="text-xl md:text-2xl mb-8 text-gray-100 leading-relaxed max-w-3xl">${slide.subtitle}</p>`;
            }
            setInterval(() => {
                currentHeroSlide = (currentHeroSlide + 1) % heroSlides.length;
                updateHeroSlide();
            }, 5000);
            updateHeroSlide();

            
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
            closeModalBtn.addEventListener('click', hideModal);

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