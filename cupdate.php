
<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$customer = null;
$customer_name = "Customer";

// Securely fetch the customer's data using a prepared statement.
$query = "SELECT * FROM creg WHERE cid = ?";
$result = $db->selectData($query, "i", $customer_id);

if ($result && $result->num_rows === 1) {
    $customer = $result->fetch_assoc();
    $customer_name = explode(' ', trim($customer['cname']))[0];
} else {
    die("Error: Could not retrieve your profile data. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .form-error { color: #dc2626; font-size: 0.875rem; height: 1.25rem; }
        .input-field:focus {
            outline: none; border-color: #9333ea;
            box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.4);
        }
        #alert-modal.hidden { display: none; }
        #modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div id="modal-content" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8 transform scale-95 opacity-0">
            <div id="modal-icon" class="w-20 h-20 rounded-full mx-auto flex items-center justify-center mb-5"></div>
            <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-2"></h3>
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
                 <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm font-semibold text-purple-600 bg-purple-50">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a></div>
                 <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 text-center sticky top-28">
                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 mx-auto flex items-center justify-center mb-4">
                        <i class="ri-user-smile-line text-6xl text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($customer['cname']); ?></h2>
                    <p class="text-gray-500 mt-1"><?php echo htmlspecialchars($customer['email']); ?></p>
                    <div class="mt-6 space-y-3">
                        <a href="stitchingorders.php" class="w-full block text-center bg-purple-50 text-purple-700 px-4 py-3 rounded-lg font-semibold text-sm hover:bg-purple-100 transition-colors">My Stitching Orders</a>
                        <a href="meas.php" class="w-full block text-center bg-pink-50 text-pink-700 px-4 py-3 rounded-lg font-semibold text-sm hover:bg-pink-100 transition-colors">My Measurements</a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="p-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-1">Edit Profile</h1>
                        <p class="text-gray-600 mb-6">Update your personal and contact information.</p>
                        <form id="update-form" action="cupaction.php" method="POST" class="border-t pt-6" novalidate>
                            <input type="hidden" name="id" id="id" value="<?php echo $customer['cid']; ?>">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><label for="cn" class="block mb-1 text-sm font-medium text-gray-700">Full Name</label><input type="text" id="cn" name="cn" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($customer['cname']); ?>" required><div id="cn-error" class="form-error"></div></div>
                                <div><label for="ph" class="block mb-1 text-sm font-medium text-gray-700">Phone</label><input type="tel" id="ph" name="ph" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($customer['phone']); ?>" required maxlength="10"><div id="ph-error" class="form-error"></div></div>
                                <div class="md:col-span-2"><label for="ad" class="block mb-1 text-sm font-medium text-gray-700">Address</label><input type="text" id="ad" name="ad" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($customer['address']); ?>" required><div id="ad-error" class="form-error"></div></div>
                                <div><label for="city" class="block mb-1 text-sm font-medium text-gray-700">City</label><input type="text" id="city" name="city" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($customer['city']); ?>" required><div id="city-error" class="form-error"></div></div>
                                <div><label for="dis" class="block mb-1 text-sm font-medium text-gray-700">District</label><select id="dis" name="dis" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md bg-white" required><option value="">Select District</option><?php $districts = ["Thiruvananthapuram", "Kollam", "Pathanamthitta", "Alappuzha", "Kottayam", "Idukki", "Ernakulam", "Thrissur", "Palakkad", "Malappuram", "Kozhikode", "Wayanad", "Kannur", "Kasaragod"]; ?><?php foreach ($districts as $dist): ?><option value="<?php echo $dist; ?>" <?php if ($customer['distr'] == $dist) echo 'selected'; ?>><?php echo $dist; ?></option><?php endforeach; ?></select><div id="dis-error" class="form-error"></div></div>
                                <div><label for="pin" class="block mb-1 text-sm font-medium text-gray-700">Pincode</label><input type="text" id="pin" name="pin" class="input-field w-full px-4 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($customer['pincode']); ?>" required maxlength="6"><div id="pin-error" class="form-error"></div></div>
                            </div>
                            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center gap-4">
                                <a href="customerhome.php" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-semibold text-sm transition-colors">Cancel</a>
                                <button type="submit" class="px-8 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow-md transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <i class="ri-save-line"></i> Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
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
        // Form validation logic (unchanged, but still powerful)
        const form = document.getElementById('update-form');
        const inputs = {
            cn: form.querySelector('#cn'), ad: form.querySelector('#ad'), city: form.querySelector('#city'),
            dis: form.querySelector('#dis'), ph: form.querySelector('#ph'), pin: form.querySelector('#pin')
        };
        const validations = {
            cn: value => value.length < 3 ? 'Name must be at least 3 characters.' : '',
            ad: value => value.length < 10 ? 'Please enter a complete address.' : '',
            city: value => value.length < 3 ? 'Please enter a valid city name.' : '',
            dis: value => !value ? 'Please select a district.' : '',
            ph: value => !/^[0-9]{10}$/.test(value) ? 'Phone number must be exactly 10 digits.' : '',
            pin: value => !/^[0-9]{6}$/.test(value) ? 'Pincode must be exactly 6 digits.' : ''
        };
        function validateField(input, validationFn) {
            const errorEl = document.getElementById(`${input.id}-error`);
            const message = validationFn(input.value.trim());
            errorEl.textContent = message;
            input.classList.toggle('border-red-500', !!message);
            return !message;
        }
        form.addEventListener('submit', function(e) {
            let isFormValid = true;
            for (const key in inputs) {
                if (!validateField(inputs[key], validations[key])) {
                    isFormValid = false;
                }
            }
            if (!isFormValid) {
                e.preventDefault();
                showModal('Incomplete Form', 'Please correct the errors before submitting.', 'error');
            }
        });

        // Modal Logic (restyled for consistency)
        const modal = document.getElementById('alert-modal');
        const modalContent = document.getElementById('modal-content');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        function showModal(title, message, type = 'success') {
            modal.querySelector('#modal-title').textContent = title;
            modal.querySelector('#modal-message').innerHTML = message;
            const iconContainer = modal.querySelector('#modal-icon');
            if(type === 'success') {
                iconContainer.innerHTML = `<div class="w-20 h-20 rounded-full bg-green-100 mx-auto flex items-center justify-center"><i class="ri-checkbox-circle-line text-5xl text-green-500"></i></div>`;
            } else {
                iconContainer.innerHTML = `<div class="w-20 h-20 rounded-full bg-red-100 mx-auto flex items-center justify-center"><i class="ri-error-warning-line text-5xl text-red-500"></i></div>`;
            }
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); modalContent.classList.remove('scale-95', 'opacity-0'); modalContent.classList.add('scale-100', 'opacity-100'); }, 50);
        }
        modalCloseBtn.addEventListener('click', () => {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
        if (new URLSearchParams(window.location.search).get('update') === 'success') {
            showModal('Success!', 'Your profile has been updated successfully.');
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Header dropdown logic
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