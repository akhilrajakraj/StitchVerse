<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header (Consistent with index.php) -->
    <header id="header" class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="index.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600">Gallery</a>
            <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600">How It Works</a>
            <a href="index.php#about-section" class="text-gray-700 hover:text-purple-600">About</a>
            <a href="index.php#contact-section" class="text-gray-700 hover:text-purple-600">Contact</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="login.php" class="text-purple-600 font-semibold">Login</a>
            <a href="creg.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">Customer Register</a>
          </div>
          <button id="menu-btn" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <!-- Mobile menu content -->
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex items-center justify-center min-h-[calc(100vh-81px)] py-16 px-4" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/backgroundindex.jpg'); background-size: cover; background-position: center;">
        
        <!-- Pop-up Modal for Success/Error Messages -->
        <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-auto text-center p-8">
                <div id="modal-icon" class="mx-auto mb-4"></div>
                <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4"></h3>
                <p id="modal-message" class="text-gray-600 mb-8"></p>
                <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700">OK</button>
            </div>
        </div>

        <!-- Forgot Password Form -->
        <div class="w-full max-w-md mx-auto p-8 bg-white rounded-2xl shadow-xl">
            <a href="index.php" class="text-3xl font-bold text-purple-600 font-pacifico block text-center mb-2">StitchVerse</a>
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Forgot Your Password?</h2>
            <p class="text-center text-gray-600 mb-8">No problem. Enter your email below and we'll send you a link to reset it.</p>
            
            <form action="send_reset_link.php" method="POST">
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="ri-mail-line text-gray-400"></i></div>
                        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 block w-full pl-10 p-3" placeholder="you@example.com" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700">Send Reset Link</button>
                <div class="text-center mt-6 text-sm text-gray-600">
                    Remembered your password? 
                    <a href="login.php" class="font-medium text-purple-600 hover:underline">Back to Login</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer (Consistent with index.php) -->
    <footer id="contact-section" class="bg-gray-900 text-white py-16">
        <!-- Footer content from index.php here... -->
          <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <a href="#" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
            <p class="text-gray-400 mb-4">Connecting talented tailors with customers worldwide for custom clothing that fits perfectly.</p>
             <div class="flex space-x-4">
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-facebook-fill text-xl"></i></a>
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-twitter-x-fill text-xl"></i></a>
               <a href="#" class="w-8 h-8 flex items-center justify-center hover:text-purple-400"><i class="ri-instagram-fill text-xl"></i></a>
             </div>
          </div>
          <div>
            <h4 class="text-lg font-semibold mb-4">For Customers</h4>
            <ul class="space-y-2">
              <li><a href="#gallery-section" class="text-gray-400 hover:text-white">Browse Gallery</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white">Find Tailors</a></li>
              <li><a href="login.php" class="text-gray-400 hover:text-white">Place an Order</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-lg font-semibold mb-4">For Tailors</h4>
            <ul class="space-y-2">
              <li><a href="treg.php" class="text-gray-400 hover:text-white">Join Platform</a></li>
              <li><a href="login.php" class="text-gray-400 hover:text-white">Dashboard Login</a></li>
              <li><a href="#" class="text-gray-400 hover:text-white">Support</a></li>
            </ul>
          </div>
            <div>
             <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
              <p class="text-gray-400">Thiruvananthapuram, Kerala, India</p>
             <p class="text-gray-400">contact@stitchverse.com</p>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 text-center">
          <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
        </div>
      </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            document.getElementById('menu-btn').addEventListener('click', () => {
                document.getElementById('mobile-menu').classList.toggle('hidden');
            });

            // Modal Logic
            const modal = document.getElementById('alert-modal');
            function showModal(title, message, type = 'success') {
                modal.querySelector('#modal-title').textContent = title;
                modal.querySelector('#modal-message').innerHTML = message;
                const iconContainer = modal.querySelector('#modal-icon');
                if(type === 'success') {
                    iconContainer.innerHTML = `<i class="ri-checkbox-circle-line text-5xl text-green-500"></i>`;
                } else {
                     iconContainer.innerHTML = `<i class="ri-error-warning-line text-5xl text-red-500"></i>`;
                }
                modal.classList.remove('hidden');
            }
            modal.querySelector('#modal-close-btn').addEventListener('click', () => modal.classList.add('hidden'));

            // Check for messages from the backend
            <?php
                if (isset($_SESSION['status_message'])) {
                    echo "showModal('Request Sent', '" . addslashes($_SESSION['status_message']) . "', 'success');";
                    unset($_SESSION['status_message']);
                }
            ?>
        });
    </script>
</body>
</html>