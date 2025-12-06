<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Registration - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .form-error { color: #dc2626; font-size: 0.875rem; height: 1.25rem; }
        .form-success { color: #16a34a; font-size: 0.875rem; height: 1.25rem; }
        .input-field {
             width: 100%; padding: 0.75rem; font-size: 0.875rem; color: #1f2937;
             background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 0.5rem;
             transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field.border-red-500 { border-color: #ef4444; }
        .input-field.border-green-500 { border-color: #22c55e; }
        .input-field:focus {
             outline: none; border-color: #9333ea;
             box-shadow: 0 0 0 2px rgba(167, 139, 250, 0.4);
        }
        .password-criteria { font-size: 0.875rem; list-style-type: none; padding-left: 0; margin-top: 0.5rem; }
        .password-criteria li { transition: color 0.3s; color: #dc2626; }
        .password-criteria li.valid { color: #16a34a; }
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
            <a href="contact.php#" class="text-gray-700 hover:text-purple-600">Contact</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <a href="login.php" class="text-gray-700 hover:text-purple-600">Login</a>
            <a href="creg.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm">Customer Register</a>
            <a href="treg.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Tailor Register</a>
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
                    <a href="login.php" class="text-gray-700 hover:text-purple-600">Login</a>
                    <a href="creg.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg text-center">Customer Register</a>
                    <a href="treg.php" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg text-center mt-2">Tailor Register</a>
                </div>
            </div>
        </div>
      </div>
    </header>

    <main class="flex items-center justify-center py-16 px-4" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/backgroundindex.jpg'); background-size: cover; background-position: center;">
        
        <div id="alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto text-center p-8">
                <div id="modal-icon" class="mx-auto mb-4"></div>
                <h3 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4"></h3>
                <p id="modal-message" class="text-gray-600 mb-8"></p>
                <button id="modal-close-btn" class="w-full bg-purple-600 text-white py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors">OK</button>
            </div>
        </div>

        <div class="w-full max-w-3xl mx-auto p-8 bg-white rounded-2xl shadow-xl">
            <a href="index.php" class="text-3xl font-bold text-purple-600 font-pacifico block text-center mb-2">StitchVerse</a>
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Join as a Professional Tailor</h2>
            <form id="registration-form" action="tregaction.php" method="POST" novalidate>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div class="mb-4">
                        <label for="tname" class="block mb-2 text-sm font-medium text-gray-700">Full Name / Business Name</label>
                        <input type="text" id="tname" name="tname" class="input-field" placeholder="Enter your name" required>
                        <div id="tname-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Business Email (Gmail Only)</label>
                        <input type="email" id="email" name="email" class="input-field" placeholder="contact@gmail.com" required>
                        <div id="email-error" class="form-error"></div>
                    </div>
                    <div class="mb-4 md:col-span-2">
                        <label for="address" class="block mb-2 text-sm font-medium text-gray-700">Full Address</label>
                        <textarea id="address" name="address" class="input-field" placeholder="Shop No, Street, Landmark" rows="2" required></textarea>
                        <div id="address-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="city" class="block mb-2 text-sm font-medium text-gray-700">City / Town</label>
                        <input type="text" id="city" name="city" class="input-field" placeholder="e.g., Mavelikara" required>
                        <div id="city-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="distri" class="block mb-2 text-sm font-medium text-gray-700">District</label>
                        <select id="distri" name="distri" class="input-field" required>
                            <option value="">Select District</option>
                            <option value="Thiruvananthapuram">Thiruvananthapuram</option>
                            <option value="Kollam">Kollam</option>
                            <option value="Pathanamthitta">Pathanamthitta</option>
                            <option value="Alappuzha">Alappuzha</option>
                            <option value="Kottayam">Kottayam</option>
                            <option value="Idukki">Idukki</option>
                            <option value="Ernakulam">Ernakulam</option>
                            <option value="Thrissur">Thrissur</option>
                            <option value="Palakkad">Palakkad</option>
                            <option value="Malappuram">Malappuram</option>
                            <option value="Kozhikode">Kozhikode</option>
                            <option value="Wayanad">Wayanad</option>
                            <option value="Kannur">Kannur</option>
                            <option value="Kasaragod">Kasaragod</option>
                        </select>
                        <div id="distri-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="pinc" class="block mb-2 text-sm font-medium text-gray-700">6-Digit Pincode</label>
                        <input type="text" id="pinc" name="pinc" class="input-field" placeholder="e.g., 695001" required maxlength="6">
                        <div id="pinc-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-700">10-Digit Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="input-field" placeholder="e.g., 9876543210" required maxlength="10">
                        <div id="phone-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="spect" class="block mb-2 text-sm font-medium text-gray-700">Speciality</label>
                        <select id="spect" name="spect" class="input-field" required>
                            <option value="">Select Speciality</option>
                            <option value="All">All</option>
                            <option value="Traditional">Traditional</option>
                            <option value="Formal">Formal</option>
                            <option value="Casuals">Casuals</option>
                            <option value="Uniform">Uniform</option>
                            <option value="Bride&Groom wear">Bride & Groom Wear</option>
                        </select>
                        <div id="spect-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="quali" class="block mb-2 text-sm font-medium text-gray-700">Qualification / Experience</label>
                        <select id="quali" name="quali" class="input-field" required>
                            <option value="">-- Select Qualification --</option>
                            <option value="cert_tailoring">Certified Tailor (Vocational Training)</option>
                            <option value="diploma_tailoring">Diploma in Tailoring and Dress Designing</option>
                            <option value="iti_tailoring">ITI in Cutting & Sewing / Dress Making</option>
                            <option value="fashion_design_diploma">Diploma in Fashion Design</option>
                            <option value="bsc_fashion_design">B.Sc in Fashion Designing</option>
                            <option value="ba_costume_design">B.A in Costume and Fashion Design</option>
                            <option value="pg_fashion_design">PG in Fashion Design</option>
                            <option value="apprenticeship">Apprenticeship under a Master Tailor</option>
                            <option value="self_taught">Self-Taught with Verified Portfolio</option>
                            <option value="10_years_experience">10+ Years of Professional Experience</option>
                            <option value="5_years_experience">5+ Years of Professional Experience</option>
                            <option value="freelancer">Freelancer / Home-Based Tailor</option>
                            <option value="specialized_wear">Specialized in Bridal/Uniforms/Kids Wear</option>
                        </select>
                        <div id="quali-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="input-field pr-10" required>
                            <div id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
                                <i class="ri-eye-off-line text-gray-400"></i>
                            </div>
                        </div>
                        <ul id="password-criteria" class="password-criteria hidden">
                            <li id="pw-length">At least 8 characters</li>
                            <li id="pw-uppercase">Contains an uppercase letter (A-Z)</li>
                            <li id="pw-lowercase">Contains a lowercase letter (a-z)</li>
                            <li id="pw-number">Contains a number (0-9)</li>
                            <li id="pw-symbol">Contains a symbol (!, @, #, etc.)</li>
                        </ul>
                        <div id="password-error" class="form-error"></div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-700">Confirm Password</label>
                         <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" class="input-field pr-10" required>
                             <div id="toggleConfirmPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer">
                                <i class="ri-eye-off-line text-gray-400"></i>
                            </div>
                        </div>
                        <div id="confirm-password-error" class="form-error"></div>
                    </div>
                </div>

                <div class="mt-6 mb-4 md:col-span-2">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" value="agreed" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-purple-300" required>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-light text-gray-600">I accept the <a href="terms.php" class="font-medium text-purple-600 hover:underline" target="_blank">Terms and Conditions</a></label>
                        </div>
                    </div>
                    <div id="terms-error" class="form-error mt-1"></div>
                </div>

                <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700 transition-colors">Request for Approval</button>
                <div class="text-center mt-6 text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="font-medium text-purple-600 hover:underline">Login Now</a>
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
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('i');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('ri-eye-line');
            icon.classList.toggle('ri-eye-off-line');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('menu-btn').addEventListener('click', () => {
                document.getElementById('mobile-menu').classList.toggle('hidden');
            });
            
            document.getElementById('togglePassword').addEventListener('click', function() {
                togglePasswordVisibility('password');
            });
            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                togglePasswordVisibility('confirm_password');
            });

            const form = document.getElementById('registration-form');
            const inputs = {
                tname: form.querySelector('#tname'),
                email: form.querySelector('#email'),
                address: form.querySelector('#address'),
                city: form.querySelector('#city'),
                distri: form.querySelector('#distri'),
                pinc: form.querySelector('#pinc'),
                phone: form.querySelector('#phone'),
                spect: form.querySelector('#spect'),
                quali: form.querySelector('#quali'),
                password: form.querySelector('#password'),
                confirm_password: form.querySelector('#confirm_password'),
                terms: form.querySelector('#terms')
            };
            const errors = {
                tname: form.querySelector('#tname-error'),
                email: form.querySelector('#email-error'),
                address: form.querySelector('#address-error'),
                city: form.querySelector('#city-error'),
                distri: form.querySelector('#distri-error'),
                pinc: form.querySelector('#pinc-error'),
                phone: form.querySelector('#phone-error'),
                spect: form.querySelector('#spect-error'),
                quali: form.querySelector('#quali-error'),
                password: form.querySelector('#password-error'),
                confirm_password: form.querySelector('#confirm-password-error'),
                terms: form.querySelector('#terms-error')
            };
            const pwCriteriaList = document.getElementById('password-criteria');
            const pwCriteria = {
                length: document.getElementById('pw-length'),
                uppercase: document.getElementById('pw-uppercase'),
                lowercase: document.getElementById('pw-lowercase'),
                number: document.getElementById('pw-number'),
                symbol: document.getElementById('pw-symbol')
            };
            
            function validateField(input, errorEl, validationFn) {
                let message;
                if (input.type === 'checkbox') {
                    message = validationFn(input.checked);
                } else {
                    message = validationFn(input.value.trim());
                }
                
                errorEl.textContent = message;
                errorEl.classList.remove('form-success');
                input.classList.toggle('border-red-500', !!message);
                if (input.type !== 'checkbox') {
                    input.classList.toggle('border-green-500', !message && input.value.length > 0);
                }
                return !message;
            }

            const validations = {
                tname: value => value.length < 3 ? 'Name must be at least 3 characters.' : '',
                email: value => {
                    if (!/^\S+@\S+\.\S+$/.test(value)) return 'Please enter a valid email format.';
                    if (!/@(gmail\.com|google\.com)$/i.test(value)) return 'Only Gmail or Google accounts are accepted.';
                    return '';
                },
                address: value => value.length < 10 ? 'Please enter a complete address.' : '',
                city: value => value.length < 3 ? 'Please enter a valid city name.' : '',
                distri: value => !value ? 'Please select a district.' : '',
                pinc: value => !/^[0-9]{6}$/.test(value) ? 'Pincode must be exactly 6 digits.' : '',
                phone: value => !/^[0-9]{10}$/.test(value) ? 'Phone number must be exactly 10 digits.' : '',
                spect: value => !value ? 'Please select a speciality.' : '',
                quali: value => !value ? 'Please select a qualification.' : '',
                password: value => {
                    if (value.length < 8) return 'Password does not meet all requirements.';
                    if (!/[A-Z]/.test(value)) return 'Password does not meet all requirements.';
                    if (!/[a-z]/.test(value)) return 'Password does not meet all requirements.';
                    if (!/[0-9]/.test(value)) return 'Password does not meet all requirements.';
                    if (!/[^A-Za-z0-9]/.test(value)) return 'Password does not meet all requirements.';
                    return '';
                },
                confirm_password: value => value !== inputs.password.value ? 'Passwords do not match.' : (value.length === 0 ? 'Please confirm your password.' : ''),
                terms: checked => !checked ? 'You must accept the terms and conditions.' : ''
            };
            
            for (const key in inputs) {
                if (!['password', 'confirm_password', 'email', 'phone'].includes(key)) {
                    inputs[key].addEventListener('blur', () => validateField(inputs[key], errors[key], validations[key]));
                }
            }

            inputs.password.addEventListener('input', () => {
                const value = inputs.password.value;
                const isPasswordValid = validations.password(value) === '';
                if (value.length === 0 || isPasswordValid) {
                    pwCriteriaList.classList.add('hidden');
                } else {
                    pwCriteriaList.classList.remove('hidden');
                }
                pwCriteria.length.classList.toggle('valid', value.length >= 8);
                pwCriteria.uppercase.classList.toggle('valid', /[A-Z]/.test(value));
                pwCriteria.lowercase.classList.toggle('valid', /[a-z]/.test(value));
                pwCriteria.number.classList.toggle('valid', /[0-9]/.test(value));
                pwCriteria.symbol.classList.toggle('valid', /[^A-Za-z0-9]/.test(value));
                validateField(inputs.password, errors.password, validations.password);
                validateField(inputs.confirm_password, errors.confirm_password, validations.confirm_password);
            });

            inputs.confirm_password.addEventListener('input', () => {
                 validateField(inputs.confirm_password, errors.confirm_password, validations.confirm_password);
            });

            inputs.email.addEventListener('blur', () => {
                if (validateField(inputs.email, errors.email, validations.email)) {
                     fetch('check_email_tailor.php', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                         body: 'email=' + encodeURIComponent(inputs.email.value.trim())
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.exists) {
                             errors.email.textContent = 'This email is already registered.';
                             errors.email.classList.remove('form-success');
                             inputs.email.classList.add('border-red-500');
                             inputs.email.classList.remove('border-green-500');
                         } else {
                             errors.email.textContent = '✅ Email is available!';
                             errors.email.classList.add('form-success');
                             inputs.email.classList.remove('border-red-500');
                             inputs.email.classList.add('border-green-500');
                         }
                     });
                }
            });
            
            inputs.phone.addEventListener('blur', () => {
                if (validateField(inputs.phone, errors.phone, validations.phone)) {
                     fetch('check_phone_tailor.php', {
                         method: 'POST',
                         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                         body: 'phone=' + encodeURIComponent(inputs.phone.value.trim())
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.exists) {
                             errors.phone.textContent = 'This phone number is already registered.';
                             errors.phone.classList.remove('form-success');
                             inputs.phone.classList.add('border-red-500');
                             inputs.phone.classList.remove('border-green-500');
                         } else {
                             errors.phone.textContent = '✅ Phone number is available!';
                             errors.phone.classList.add('form-success');
                             inputs.phone.classList.remove('border-red-500');
                             inputs.phone.classList.add('border-green-500');
                         }
                     });
                }
            });

            form.addEventListener('submit', function(e) {
                let isFormValid = true;
                for (const key in inputs) {
                    if (!validateField(inputs[key], errors[key], validations[key])) {
                        isFormValid = false;
                    }
                }
                if (!isFormValid) {
                    e.preventDefault();
                    showModal('Incomplete Form', 'Please correct the errors before submitting.', 'error');
                }
            });
            
            const modal = document.getElementById('alert-modal');
            function showModal(title, message, type = 'error') {
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
            
            const closeModalBtn = modal.querySelector('#modal-close-btn');
            
            closeModalBtn.addEventListener('click', () => {
                // Check if the success icon is present in the modal
                if (modal.querySelector('.ri-checkbox-circle-line')) {
                    window.location.href = 'index.php';
                } else {
                    modal.classList.add('hidden');
                }
            });
            
            // This logic is for displaying a message if redirected from tregaction.php
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'pending') {
                showModal(
                    'Request Submitted!', 
                    'Your application is now pending review. You will receive an email shortly with more details. Thank you!', 
                    'success'
                );
            }

            <?php
                if (isset($_SESSION['error_message'])) {
                    echo "showModal('Registration Failed', '" . addslashes($_SESSION['error_message']) . "', 'error');";
                    unset($_SESSION['error_message']);
                }
            ?>
        });
    </script>
</body>
</html>
