
<?php
session_start();
require_once 'databasecon.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. --- VALIDATE AND GET TAILOR ID ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: pendingtailors.php"); // Redirect if ID is missing or invalid
    exit();
}
$tailor_id = intval($_GET['id']);
$db = new DatabaseCon();

// 2. --- FETCH PENDING TAILOR DETAILS ---
$tailor = null;
$sql_tailor = "SELECT * FROM treg WHERE tid = ? AND status = 'pending'";
$result_tailor = $db->selectData($sql_tailor, "i", $tailor_id);
if ($result_tailor && $result_tailor->num_rows > 0) {
    $tailor = $result_tailor->fetch_assoc();
} else {
    // If no pending tailor is found with this ID, redirect back
    $_SESSION['action_error'] = "Pending tailor application not found or already processed.";
    header("Location: pendingtailors.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Application - <?php echo htmlspecialchars($tailor['tname']); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .modal.hidden { display: none; }
        .modal-content { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
    </style>
</head>
<body class="bg-gray-100">

    <!-- APPROVAL MODAL -->
    <div id="approve-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div id="approve-modal-content" class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto p-8 transform scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Approve Application</h3>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to approve the application for <strong class="font-bold"><?php echo htmlspecialchars($tailor['tname']); ?></strong>? An approval email will be sent and their login will be created.</p>
            
            <div class="flex justify-end gap-4">
                <button type="button" onclick="closeApproveModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                <a href="tailor_action.php?action=approve&id=<?php echo $tailor['tid']; ?>" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 flex items-center gap-2">
                    <i class="ri-checkbox-circle-line"></i> Confirm Approval
                </a>
            </div>
        </div>
    </div>

    <!-- REJECTION MODAL -->
    <div id="reject-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-60 z-[100] flex items-center justify-center p-4">
        <div id="reject-modal-content" class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-lg mx-auto p-8 transform scale-95 opacity-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-gray-800">Reject Application</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></button>
            </div>
            <p class="text-gray-600 mb-6">You are about to reject the application from <strong class="font-bold"><?php echo htmlspecialchars($tailor['tname']); ?></strong>. An email will be sent with the reason for rejection.</p>
            
            <form action="tailor_action.php" method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="tid" value="<?php echo $tailor['tid']; ?>">
                
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection</label>
                    <select name="reason" id="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="Incomplete or unclear information provided">Incomplete or unclear information</option>
                        <option value="Qualification or specialty does not meet our criteria">Qualification does not meet criteria</option>
                        <option value="Application appears to be spam or fraudulent">Spam or fraudulent application</option>
                        <option value="Other">Other (specify below)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="custom_message" class="block text-sm font-medium text-gray-700 mb-1">Additional Details (Optional)</label>
                    <textarea name="custom_message" id="custom_message" rows="3" placeholder="Provide more details for the rejection..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeRejectModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 flex items-center gap-2">
                        <i class="ri-close-circle-line"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- You can include your standard admin header here -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <!-- The full header code from pendingtailors.php goes here -->
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <div class="mb-6">
            <a href="pendingtailors.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-purple-600 font-medium text-sm transition-colors">
                <i class="ri-arrow-left-line"></i>
                <span>Back to Pending List</span>
            </a>
        </div>

        <!-- APPLICANT PROFILE SECTION -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="flex-grow">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-sm font-semibold text-yellow-600 bg-yellow-100 inline-block px-3 py-1 rounded-full mb-2">Pending Application</p>
                                <h1 class="text-4xl font-bold text-gray-800"><?php echo htmlspecialchars($tailor['tname']); ?></h1>
                                <p class="text-purple-600 font-semibold mt-1">Applying as: <?php echo htmlspecialchars($tailor['spect']); ?></p>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 my-6"></div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Applicant Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div class="flex items-center gap-3"><i class="ri-mail-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['email']); ?></span></div>
                            <div class="flex items-center gap-3"><i class="ri-phone-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['phone']); ?></span></div>
                            <div class="flex items-center gap-3"><i class="ri-map-pin-line text-purple-500 text-xl"></i><span><?php echo htmlspecialchars($tailor['city'] . ', ' . $tailor['distri']); ?></span></div>
                            <div class="flex items-center gap-3"><i class="ri-award-line text-purple-500 text-xl"></i><span><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($tailor['quali']))); ?></span></div>
                            <div class="flex items-center gap-3"><i class="ri-road-map-line text-purple-500 text-xl"></i><span>Pincode: <?php echo htmlspecialchars($tailor['pinc']); ?></span></div>
                            <div class="flex items-start gap-3 col-span-full"><i class="ri-building-line text-purple-500 text-xl pt-1"></i><span class="flex-1"><?php echo htmlspecialchars($tailor['address']); ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ACTION FOOTER -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex flex-col md:flex-row justify-end items-center gap-4">
                <p class="text-sm text-gray-600 mr-auto">Review the details carefully before making a decision.</p>
                <button onclick="openRejectModal()" class="w-full md:w-auto font-semibold text-white bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-close-circle-line text-lg"></i> Reject Application
                </button>
                <button onclick="openApproveModal()" class="w-full md:w-auto font-semibold text-white bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg text-sm flex items-center justify-center gap-2">
                    <i class="ri-checkbox-circle-line text-lg"></i> Approve Application
                </button>
            </div>
        </div>
    </main>
    
    <script>
        // --- Functions for Approval Modal ---
        function openApproveModal() {
            const modal = document.getElementById('approve-modal');
            const modalContent = document.getElementById('approve-modal-content');
            modal.classList.remove('hidden');
            setTimeout(() => { modalContent.classList.remove('scale-95', 'opacity-0'); }, 50);
        }

        function closeApproveModal() {
            const modal = document.getElementById('approve-modal');
            const modalContent = document.getElementById('approve-modal-content');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // --- Functions for Rejection Modal ---
        function openRejectModal() {
            const modal = document.getElementById('reject-modal');
            const modalContent = document.getElementById('reject-modal-content');
            modal.classList.remove('hidden');
            setTimeout(() => { modalContent.classList.remove('scale-95', 'opacity-0'); }, 50);
        }

        function closeRejectModal() {
            const modal = document.getElementById('reject-modal');
            const modalContent = document.getElementById('reject-modal-content');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
    </script>
</body>
</html>
