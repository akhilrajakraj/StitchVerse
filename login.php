<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StitchVerse</title>
    
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

    <header id="header" class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="index.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600">Gallery</a>
            <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600">How It Works</a>
            <a href="about.php" class="text-gray-700 hover:text-purple-600">About</a>
            <a href="contact.php" class="text-gray-700 hover:text-purple-600">Contact</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="login.php" class="text-purple-600 font-semibold transition-colors">Login</a>
            <a href="creg.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">Customer Register</a>
            <a href="treg.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm">Tailor Register</a>
          </div>
          <button id="menu-btn" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100">
            <div class="flex flex-col space-y-3 mt-4">
              <a href="index.php#gallery-section" class="text-gray-700 hover:text-purple-600">Gallery</a>
              <a href="index.php#features-section" class="text-gray-700 hover:text-purple-600">How It Works</a>
              <a href="index.php#about-section" class="text-gray-700 hover:text-purple-600">About</a>
              <a href="index.php#contact-section" class="text-gray-700 hover:text-purple-600">Contact</a>
              <div class="flex flex-col space-y-2 pt-3 border-t border-gray-100">
                <a href="login.php" class="bg-purple-100 text-purple-700 px-6 py-2 rounded-lg text-center">Login</a>
                <a href="creg.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg text-center mt-2">Customer Register</a>
                <a href="treg.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg text-center mt-2">Tailor Register</a>
              </div>
            </div>
        </div>
      </div>
    </header>

    <main class="flex items-center justify-center min-h-[calc(100vh-81px)] py-16 px-4" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/backgroundindex.jpg'); background-size: cover; background-position: center;">
        
        <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-auto text-center p-8">
                <div id="modal-icon" class="mx-auto mb-4"><i class="ri-error-warning-line text-5xl text-red-500"></i></div>
                <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4"></h3>
                <p id="modal-message" class="text-gray-600 mb-8"></p>
                <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
            </div>
        </div>

        <div class="w-full max-w-md mx-auto p-8 bg-white rounded-2xl shadow-xl">
            <a href="index.php" class="text-3xl font-bold text-purple-600 font-pacifico block text-center mb-2">StitchVerse</a>
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Welcome Back!</h2>
            <form action="loginaction.php" method="POST">
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="ri-mail-line text-gray-400"></i></div>
                        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 p-3" placeholder="you@example.com" required>
                    </div>
                    <div id="email-status" class="text-sm mt-1 h-5"></div>
                </div>
                <div class="mb-5">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="ri-lock-line text-gray-400"></i></div>
                        <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 p-3 pr-10" placeholder="••••••••" required>
                        <div id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
                            <i class="ri-eye-off-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="text-right mb-6 -mt-4">
                    <a href="forgot_password.php" class="text-sm text-purple-600 hover:text-purple-800 hover:underline">Forgot your password?</a>
                </div>
                <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700 transition-colors">Login</button>
                <div class="text-center mt-6 text-sm text-gray-600">
                    Don't have an account? 
                    <a href="creg.php" class="font-medium text-purple-600 hover:underline">Register as Customer</a> or 
                    <a href="treg.php" class="font-medium text-purple-600 hover:underline">as Tailor</a>.
                </div>
            </form>
        </div>
    </main>

    <footer id="contact-section" class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8">
              <div>
                <a href="index.php" class="text-2xl font-bold text-purple-400 mb-4 block font-pacifico">StitchVerse</a>
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
                  <li><a href="index.php#gallery-section" class="text-gray-400 hover:text-white">Browse Gallery</a></li>
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
        // --- Mobile Menu Toggle ---
        document.getElementById('menu-btn').addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
        
        // --- COMPLETE VALIDATION & INTERACTIVITY SCRIPT ---
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('alert-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalMessage = document.getElementById('modal-message');
            const closeModalBtn = document.getElementById('modal-close-btn');
            
            function showModal(title, message) {
                modalTitle.textContent = title;
                modalMessage.textContent = message;
                modal.classList.remove('hidden');
            }

            function hideModal() {
                modal.classList.add('hidden');
            }

            closeModalBtn.addEventListener('click', hideModal);

            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            if (error) {
                let title = 'Error';
                let message = 'An unknown error occurred.';
                switch (error) {
                    case 'emptyfields':
                        title = 'Incomplete Form';
                        message = 'Please fill in both email and password fields.';
                        break;
                    case 'invalidemail':
                        title = 'Invalid Format';
                        message = 'Please enter a valid email address.';
                        break;
                    case 'emailnotfound':
                        title = 'Login Failed';
                        message = 'No account found with that email address.';
                        break;
                    case 'incorrectpassword':
                        title = 'Login Failed';
                        message = 'The password you entered is incorrect. Please try again.';
                        break;
                }
                showModal(title, message);
            }

            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('ri-eye-line');
                this.querySelector('i').classList.toggle('ri-eye-off-line');
            });

            const emailInput = document.getElementById('email');
            const emailStatus = document.getElementById('email-status');

            emailInput.addEventListener('blur', function() {
                const email = this.value;

                if (email === '') {
                    emailStatus.innerHTML = '';
                    return;
                }

                if (!validateEmailFormat(email)) {
                    emailStatus.innerHTML = '<span class="text-red-600">Invalid email ID format.</span>';
                    return;
                }

                fetch('check_email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailStatus.innerHTML = '<span class="text-green-600">✅ Email registered.</span>';
                    } else {
                        emailStatus.innerHTML = '<span class="text-red-600">Please enter a registered email ID.</span>';
                    }
                });
            });

            function validateEmailFormat(email) {
                const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(String(email).toLowerCase());
            }
        });

        // In login.php, inside your <script> tag
<?php
    if (isset($_SESSION['success_message'])) {
        // We'll use your existing modal function to show the message
        echo "showModal('Success!', '" . addslashes($_SESSION['success_message']) . "', 'success');";
        unset($_SESSION['success_message']);
    }
?>
    </script>
</body>
</html>