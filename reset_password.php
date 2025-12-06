<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require 'databasecon.php';

// --- INITIALIZATION ---
$db = new DatabaseCon();
$error_message = '';
$success_message = '';
$show_form = false;
// Get the token from either the URL (GET) or the form submission (POST)
$token = $_POST['token'] ?? $_GET['token'] ?? '';

// --- PART 1: PROCESS FORM SUBMISSION (POST REQUEST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in both password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "The passwords do not match. Please try again.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        // --- Re-verify Token and Update Password ---
        $query = "SELECT uid FROM login WHERE reset_token = ? AND token_expiry > NOW()";
        $result = $db->selectData($query, "s", $token);

        if ($result && $result->num_rows === 1) {
            // Token is valid, proceed with update
            
            // ####################### INSECURE MODIFICATION #######################
            // As requested, the plain text password is being saved directly.
            // This is STRONGLY DISCOURAGED for security reasons.
            // The secure method using password_hash() has been omitted.
            // ##################### END OF INSECURE MODIFICATION ##################

            $update_query = "UPDATE login SET upass = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?";
            $db->executeQuery($update_query, "ss", $new_password, $token);

            $success_message = "Your password has been updated successfully!";

        } else {
            // Token was invalid upon submission
            $error_message = "This password reset link is invalid or has expired. Please request a new one.";
        }
    }
}

// --- PART 2: CHECK TOKEN TO DECIDE IF FORM SHOULD BE SHOWN ---
// We only do this if there hasn't been a successful password reset.
if (empty($success_message)) {
    if (!empty($token)) {
        $query = "SELECT uid FROM login WHERE reset_token = ? AND token_expiry > NOW()";
        $result = $db->selectData($query, "s", $token);
        
        if ($result && $result->num_rows === 1) {
            $show_form = true; // Token is valid, show the form
        } else {
            // This message shows on page load if the token is already bad,
            // or if a POST fails because the token expired during the process.
             if (empty($error_message)) { // Don't overwrite a more specific error from the POST attempt
                $error_message = "This password reset link is invalid or has expired.";
            }
        }
    } else {
        // This message shows if someone lands on the page without any token at all.
        $error_message = "No password reset token was found. Please use the link from your email.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - StitchVerse</title>
    
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
    <main class="flex items-center justify-center min-h-screen py-16 px-4" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/backgroundindex.jpg'); background-size: cover; background-position: center;">
        
        <div class="w-full max-w-md mx-auto p-8 bg-white rounded-2xl shadow-xl">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Choose a New Password</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 text-center" role="alert">
                    <strong class="font-bold block text-lg">Success!</strong>
                    <span class="block sm:inline mt-1"><?php echo $success_message; ?></span>
                </div>
                <a href="login.php" class="w-full block text-center bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700">
                    Proceed to Login
                </a>

            <?php elseif (!empty($error_message)): ?>
                <p class="text-center text-gray-600 mb-6">Please correct the issue below.</p>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <strong class="font-bold">Oops!</strong>
                    <span class="block sm:inline ml-2"><?php echo $error_message; ?></span>
                </div>
                 <a href="forgot_password.php" class="w-full block text-center bg-gray-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-gray-700">
                    Request a New Link
                </a>

            <?php endif; ?>
            
            <?php if ($show_form): ?>
                <p class="text-center text-gray-600 mb-8">Enter a new password for your account below.</p>
                <form action="reset_password.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="mb-5">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="ri-lock-password-line text-gray-400"></i></div>
                            <input type="password" id="password" name="new_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 block w-full pl-10 p-3" placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-700">Confirm New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i class="ri-lock-password-line text-gray-400"></i></div>
                            <input type="password" id="confirm_password" name="confirm_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 block w-full pl-10 p-3" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-purple-700">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>