<?php
session_start();
require 'databasecon.php';

// --- AJAX Phone Number Check ---
// This block handles the asynchronous request from the JavaScript to check if a phone number exists.
if (isset($_POST['check_phone'])) {
    header('Content-Type: application/json');
    $phone = $_POST['check_phone'];
    $tailor_id = $_SESSION['user_id'] ?? 0;
    
    $db_check = new DatabaseCon();
    
    // Check in both customer and tailor tables, excluding the current tailor's own record to ensure uniqueness across the platform.
    $query = "(SELECT phone FROM treg WHERE phone = ? AND tid != ?) UNION (SELECT phone FROM creg WHERE phone = ?)";
    $result = $db_check->selectData($query, "sis", $phone, $tailor_id, $phone);
    
    if ($result && $result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit(); // Stop script execution after sending JSON response
}


// --- Main Page Logic ---
// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$tailor = null;

// Securely fetch the current tailor's data
$query = "SELECT * FROM treg WHERE tid = ?";
$result = $db->selectData($query, "i", $tailor_id);

if ($result && $result->num_rows === 1) {
    $tailor = $result->fetch_assoc();
} else {
    // A more graceful error handling
    die("Error: Could not retrieve your profile data. Please try logging in again.");
}

// Define the qualification options array for easy use in PHP
$qualifications = [
    "cert_tailoring" => "Certified Tailor (Vocational Training)",
    "diploma_tailoring" => "Diploma in Tailoring and Dress Designing",
    "iti_tailoring" => "ITI in Cutting & Sewing / Dress Making",
    "fashion_design_diploma" => "Diploma in Fashion Design",
    "bsc_fashion_design" => "B.Sc in Fashion Designing",
    "ba_costume_design" => "B.A in Costume and Fashion Design",
    "pg_fashion_design" => "PG in Fashion Design",
    "apprenticeship" => "Apprenticeship under a Master Tailor",
    "self_taught" => "Self-Taught with Verified Portfolio",
    "10_years_experience" => "10+ Years of Professional Experience",
    "5_years_experience" => "5+ Years of Professional Experience",
    "freelancer" => "Freelancer / Home-Based Tailor",
    "specialized_wear" => "Specialized in Bridal/Uniforms/Kids Wear"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .form-error { color: #dc2626; font-size: 0.875rem; height: 1.25rem; }
        .input-field {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            transition: all 0.2s ease-in-out;
        }
        .input-field.border-red-500 { border-color: #ef4444; }
        .input-field.border-green-500 { border-color: #22c55e; }
        .input-field:focus {
            outline: none; border-color: #9333ea;
            box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.4);
        }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-container { transition: transform 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <div id="alert-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white rounded-2xl shadow-xl w-full max-w-sm mx-auto text-center p-8 transform scale-95 opacity-0" id="modal-content">
            <div id="modal-icon" class="mx-auto mb-4"></div>
            <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4"></h3>
            <p id="modal-message" class="text-gray-600 mb-8"></p>
            <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
        </div>
    </div>

    <div id="cancel-modal" class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="modal-container bg-white w-full max-w-md rounded-lg shadow-xl transform scale-95 opacity-0">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-yellow-100 mx-auto flex items-center justify-center mb-4">
                    <i class="ri-error-warning-line text-5xl text-yellow-500"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Are you sure?</h2>
                <p class="text-sm text-gray-600 mt-2 mb-6">Any unsaved changes will be lost. Do you want to cancel and go back to the homepage?</p>
                <div class="flex justify-center gap-4">
                    <button id="confirm-cancel-btn" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">Yes, Cancel</button>
                    <button id="close-cancel-modal-btn" type="button" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">No, Stay</button>
                </div>
            </div>
        </div>
    </div>

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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 mx-auto flex items-center justify-center mb-4">
                        <i class="ri-user-line text-6xl text-purple-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($tailor['tname']); ?></h2>
                    <p class="text-gray-500 mt-1"><?php echo htmlspecialchars($tailor['email']); ?></p>
                    
                    <div class="mt-6">
                        <a href="view_feedback.php" class="inline-flex items-center justify-center w-full px-4 py-3 bg-purple-100 text-purple-700 font-semibold rounded-lg hover:bg-purple-200 transition-colors">
                            <i class="ri-feedback-line mr-2"></i> View Customer Feedback
                        </a>
                    </div>
                    
                    <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border bg-green-100 text-green-800 border-green-200">
                        <i class="ri-shield-check-line mr-2"></i> Verified Tailor
                    </div>
                </div>
            </div>
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                                <i class="ri-user-settings-line text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Edit Profile</h1>
                                <p class="text-purple-100 mt-1">Keep your professional information up-to-date.</p>
                            </div>
                        </div>
                    </div>
                    <form id="update-form" action="tupaction.php" method="POST" class="p-8" novalidate>
                        <input type="hidden" name="id" value="<?php echo $tailor['tid']; ?>">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                            <div class="mb-4"><label for="tname" class="block mb-2 text-sm font-medium text-gray-700">Full Name / Business Name</label><div class="relative"><i class="ri-user-3-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" id="tname" name="tname" class="input-field w-full pl-10 pr-4 py-3" value="<?php echo htmlspecialchars($tailor['tname']); ?>" required></div><div id="tname-error" class="form-error"></div></div>
                            <div class="mb-4"><label for="phone" class="block mb-2 text-sm font-medium text-gray-700">Phone</label><div class="relative"><i class="ri-phone-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="tel" id="phone" name="ph" class="input-field w-full pl-10 pr-4 py-3" value="<?php echo htmlspecialchars($tailor['phone']); ?>" required maxlength="10"></div><div id="phone-error" class="form-error"></div></div>
                            <div class="mb-4 md:col-span-2"><label for="address" class="block mb-2 text-sm font-medium text-gray-700">Address</label><div class="relative"><i class="ri-map-pin-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" id="address" name="tadd" class="input-field w-full pl-10 pr-4 py-3" value="<?php echo htmlspecialchars($tailor['address']); ?>" required></div><div id="address-error" class="form-error"></div></div>
                            <div class="mb-4"><label for="city" class="block mb-2 text-sm font-medium text-gray-700">City</label><div class="relative"><i class="ri-building-4-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" id="city" name="tcity" class="input-field w-full pl-10 pr-4 py-3" value="<?php echo htmlspecialchars($tailor['city']); ?>" required></div><div id="city-error" class="form-error"></div></div>
                            <div class="mb-4"><label for="distri" class="block mb-2 text-sm font-medium text-gray-700">District</label><div class="relative"><i class="ri-road-map-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"></i><select id="distri" name="tdis" class="input-field w-full appearance-none pl-10 pr-4 py-3 bg-white" required><?php $districts = ["Thiruvananthapuram", "Kollam", "Pathanamthitta", "Alappuzha", "Kottayam", "Idukki", "Ernakulam", "Thrissur", "Palakkad", "Malappuram", "Kozhikode", "Wayanad", "Kannur", "Kasaragod"]; ?><?php foreach ($districts as $dist): ?><option value="<?php echo $dist; ?>" <?php if ($tailor['distri'] == $dist) echo 'selected'; ?>><?php echo $dist; ?></option><?php endforeach; ?></select></div><div id="distri-error" class="form-error"></div></div>
                            <div class="mb-4"><label for="pinc" class="block mb-2 text-sm font-medium text-gray-700">Pincode</label><div class="relative"><i class="ri-compass-3-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i><input type="text" id="pinc" name="pinc" class="input-field w-full pl-10 pr-4 py-3" value="<?php echo htmlspecialchars($tailor['pinc']); ?>" required maxlength="6"></div><div id="pinc-error" class="form-error"></div></div>
                            <div class="mb-4"><label for="spect" class="block mb-2 text-sm font-medium text-gray-700">Speciality</label><div class="relative"><i class="ri-price-tag-3-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"></i><select id="spect" name="sp" class="input-field w-full appearance-none pl-10 pr-4 py-3 bg-white" required><?php $specialities = ["All", "Traditional", "Formal", "Casuals", "Uniform", "Bride&Groom wear"]; ?><?php foreach ($specialities as $spec): ?><option value="<?php echo $spec; ?>" <?php if ($tailor['spect'] == $spec) echo 'selected'; ?>><?php echo $spec; ?></option><?php endforeach; ?></select></div><div id="spect-error" class="form-error"></div></div>
                            
                            <div class="mb-4 md:col-span-2">
                                <label for="quali" class="block mb-2 text-sm font-medium text-gray-700">Qualification / Experience</label>
                                <div class="relative"><i class="ri-award-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"></i>
                                    <select id="quali" name="qf" class="input-field w-full appearance-none pl-10 pr-4 py-3 bg-white" required>
                                        <option value="">-- Select Qualification --</option>
                                        <?php foreach ($qualifications as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" <?php if ($tailor['quali'] == $value) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="quali-error" class="form-error"></div>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-end gap-4">
                            <button id="cancel-update-btn" type="button" class="px-8 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-semibold flex items-center gap-2">
                                <i class="ri-close-line"></i> Cancel
                            </button>
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="ri-save-line"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('update-form');
            const inputs = {
                tname: form.querySelector('#tname'),
                address: form.querySelector('#address'),
                city: form.querySelector('#city'),
                distri: form.querySelector('#distri'),
                pinc: form.querySelector('#pinc'),
                phone: form.querySelector('#phone'),
                spect: form.querySelector('#spect'),
                quali: form.querySelector('#quali')
            };
            const errors = {
                tname: form.querySelector('#tname-error'),
                address: form.querySelector('#address-error'),
                city: form.querySelector('#city-error'),
                distri: form.querySelector('#distri-error'),
                pinc: form.querySelector('#pinc-error'),
                phone: form.querySelector('#phone-error'),
                spect: form.querySelector('#spect-error'),
                quali: form.querySelector('#quali-error')
            };
            
            // --- Real-time Phone Validation ---
            let phoneValidationTimeout;
            let isPhoneCheckPending = false;

            async function checkPhoneUniqueness(phoneValue) {
                isPhoneCheckPending = true;
                try {
                    const formData = new FormData();
                    formData.append('check_phone', phoneValue);
                    
                    const response = await fetch('tupdate.php', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    isPhoneCheckPending = false;
                    return data.exists ? 'This phone number is already registered.' : '';
                } catch (error) {
                    console.error('Phone check failed:', error);
                    isPhoneCheckPending = false;
                    return 'Could not verify phone number.'; // Network or server error
                }
            }
            
            async function validatePhoneField() {
                const phoneInput = inputs.phone;
                const phoneErrorEl = errors.phone;
                const phoneValue = phoneInput.value.trim();
                
                if (!/^[0-9]{10}$/.test(phoneValue)) {
                    phoneErrorEl.textContent = 'Phone number must be exactly 10 digits.';
                    phoneInput.classList.add('border-red-500');
                    phoneInput.classList.remove('border-green-500');
                    return false;
                }
                
                phoneErrorEl.textContent = 'Checking...';
                const uniquenessError = await checkPhoneUniqueness(phoneValue);
                phoneErrorEl.textContent = uniquenessError;
                phoneInput.classList.toggle('border-red-500', !!uniquenessError);
                phoneInput.classList.toggle('border-green-500', !uniquenessError);
                return !uniquenessError;
            }

            inputs.phone.addEventListener('input', () => {
                clearTimeout(phoneValidationTimeout);
                phoneValidationTimeout = setTimeout(validatePhoneField, 500); // Debounce for 500ms
            });
            

            // --- General Field Validation ---
            function validateField(input, errorEl, validationFn) {
                const message = validationFn(input.value.trim());
                errorEl.textContent = message;
                input.classList.toggle('border-red-500', !!message);
                input.classList.toggle('border-green-500', !message && input.value.length > 0);
                return !message;
            }

            const validations = {
                tname: value => value.length < 3 ? 'Name must be at least 3 characters.' : '',
                address: value => value.length < 10 ? 'Please enter a complete address.' : '',
                city: value => value.length < 3 ? 'Please enter a valid city name.' : '',
                distri: value => !value ? 'Please select a district.' : '',
                pinc: value => !/^[0-9]{6}$/.test(value) ? 'Pincode must be exactly 6 digits.' : '',
                spect: value => !value ? 'Please select a speciality.' : '',
                quali: value => !value ? 'Please select a qualification.' : ''
            };
            
            for (const key in inputs) {
                if(key !== 'phone') { // Phone has its own handler
                    inputs[key].addEventListener('input', () => validateField(inputs[key], errors[key], validations[key]));
                }
            }

            // --- Form Submission ---
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                let isFormValid = true;
                
                // Validate all non-phone fields
                for (const key in inputs) {
                    if (key !== 'phone' && !validateField(inputs[key], errors[key], validations[key])) {
                        isFormValid = false;
                    }
                }
                
                // Validate phone field and wait for it to finish
                const isPhoneValid = await validatePhoneField();
                if (!isPhoneValid) {
                    isFormValid = false;
                }
                
                if (isFormValid) {
                    form.submit();
                } else {
                    showModal('Incomplete Form', 'Please correct the errors before submitting.', 'error');
                }
            });

            // --- Modal Logic ---
            function setupModal(modalId, openBtnId, closeBtnIds, confirmBtnId, confirmAction) {
                const modal = document.getElementById(modalId);
                const openBtn = document.getElementById(openBtnId);
                if (!modal || !openBtn) return;
                
                const modalContainer = modal.querySelector('.modal-container');
                const openModal = () => {
                    modal.classList.remove('hidden');
                    setTimeout(() => { modal.classList.add('opacity-100'); modalContainer.classList.add('scale-100', 'opacity-100'); modalContainer.classList.remove('scale-95', 'opacity-0'); }, 10);
                };
                const closeModal = () => {
                    modal.classList.remove('opacity-100');
                    modalContainer.classList.remove('scale-100', 'opacity-100');
                    modalContainer.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                };

                openBtn.addEventListener('click', openModal);
                closeBtnIds.forEach(id => document.getElementById(id)?.addEventListener('click', closeModal));
                if (confirmBtnId && confirmAction) {
                    document.getElementById(confirmBtnId).addEventListener('click', confirmAction);
                }
            }
            
            // Setup Cancel Modal
            setupModal('cancel-modal', 'cancel-update-btn', ['close-cancel-modal-btn'], 'confirm-cancel-btn', () => {
                window.location.href = 'tailorhome.php';
            });

            // --- General Alert Modal ---
            const alertModal = document.getElementById('alert-modal');
            const alertModalContent = document.getElementById('modal-content');
            const alertModalCloseBtn = document.getElementById('modal-close-btn');
            function showModal(title, message, type = 'success') {
                alertModal.querySelector('#modal-title').textContent = title;
                alertModal.querySelector('#modal-message').innerHTML = message;
                const iconContainer = alertModal.querySelector('#modal-icon');
                if(type === 'success') { iconContainer.innerHTML = `<div class="w-16 h-16 rounded-full bg-green-100 mx-auto flex items-center justify-center mb-4"><i class="ri-checkbox-circle-line text-5xl text-green-500"></i></div>`; } 
                else { iconContainer.innerHTML = `<div class="w-16 h-16 rounded-full bg-red-100 mx-auto flex items-center justify-center mb-4"><i class="ri-error-warning-line text-5xl text-red-500"></i></div>`; }
                alertModal.classList.remove('hidden');
                setTimeout(() => { alertModal.classList.add('opacity-100'); alertModalContent.classList.add('scale-100', 'opacity-100'); alertModalContent.classList.remove('scale-95', 'opacity-0'); }, 50);
            }
            alertModalCloseBtn.addEventListener('click', () => {
                alertModal.classList.remove('opacity-100');
                alertModalContent.classList.remove('scale-100', 'opacity-100');
                alertModalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => alertModal.classList.add('hidden'), 300);
            });
            
            // --- Show update success message ---
            if (new URLSearchParams(window.location.search).get('update') === 'success') {
                showModal('Success!', 'Your profile has been updated successfully.');
                window.history.replaceState({}, document.title, window.location.pathname);
            }

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
        });
    </script>
</body>
</html>