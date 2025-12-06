<?php
session_start();
require 'databasecon.php';

// Security Check: Ensure a customer is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Ensure a valid Tailor ID is provided
if (!isset($_GET['tid']) || !is_numeric($_GET['tid'])) {
    die("Invalid Tailor ID provided.");
}

$db = new DatabaseCon();
$tailor_id = $_GET['tid'];
$customer_id = $_SESSION['user_id'];

// --- Step 1: Fetch the Tailor's Profile Information ---
$sql_tailor = "SELECT * FROM treg WHERE tid = ?";
$result_tailor = $db->selectData($sql_tailor, "i", $tailor_id);
if ($result_tailor->num_rows === 0) {
    die("Tailor not found.");
}
$tailor = $result_tailor->fetch_assoc();

// --- Step 2: Fetch existing feedback for this tailor, including customer names ---
$sql_feedback = "SELECT f.*, c.cname 
                 FROM feedb f
                 JOIN creg c ON f.uid = c.cid
                 WHERE f.tid = ?
                 ORDER BY f.fid DESC";
$rs_feedback = $db->selectData($sql_feedback, "i", $tailor_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback for <?php echo htmlspecialchars($tailor['tname']); ?> - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style> 
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .star-rating i { cursor: pointer; transition: color 0.2s; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600">View Designs</a>
                    <a href="cviewt.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">View Tailors</a>
                    <a href="meas.php" class="text-gray-700 hover:text-purple-600">Measurements</a>
                    <a href="customreq1.php" class="text-gray-700 hover:text-purple-600">Stitch Request</a>
                </nav>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="cupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
                    <a href="vieworders.php" class="text-gray-700 hover:text-purple-600">My Orders</a>
                    <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8 p-8 flex flex-col md:flex-row items-center gap-6">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 flex-shrink-0 flex items-center justify-center border-4 border-white ring-2 ring-purple-200">
                <i class="ri-user-star-line text-5xl text-purple-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">You are leaving feedback for</p>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($tailor['tname']); ?></h1>
                <p class="text-purple-600 font-medium mt-1">Specializes in <?php echo htmlspecialchars($tailor['spect']); ?> wear</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                        <h2 class="text-2xl font-bold text-white">Share Your Experience</h2>
                        <p class="text-purple-100 mt-1">Your feedback helps other customers.</p>
                    </div>
                    <form action="feedaction.php" method="GET" class="p-8">
                        <input type="hidden" name="tid" value="<?php echo htmlspecialchars($tailor_id); ?>">
                        
                        <div class="mb-6">
                            <label class="block mb-3 text-sm font-medium text-gray-700">Your Rating*</label>
                            <div class="star-rating flex items-center text-4xl text-gray-300" id="star-container">
                                <i class="ri-star-fill" data-value="1"></i>
                                <i class="ri-star-fill" data-value="2"></i>
                                <i class="ri-star-fill" data-value="3"></i>
                                <i class="ri-star-fill" data-value="4"></i>
                                <i class="ri-star-fill" data-value="5"></i>
                            </div>
                            <input type="hidden" name="rt" id="rating-value" value="0" required>
                        </div>

                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700" for="feedback-text">Your Feedback*</label>
                            <textarea name="fd" id="feedback-text" required rows="5" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="Describe your experience with this tailor..."></textarea>
                        </div>

                        <div class="pt-6 border-t border-gray-200 flex justify-end">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="ri-send-plane-2-line"></i> Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">What Others Are Saying</h2>
                <div class="space-y-4">
                    <?php if (mysqli_num_rows($rs_feedback) > 0): ?>
                        <?php while($row = mysqli_fetch_array($rs_feedback)): ?>
                        <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-purple-400">
                            <div class="flex justify-between items-center">
                                <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($row['cname']); ?></h4>
                                <div class="flex text-yellow-400">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="ri-star-<?php echo ($i <= $row['rate']) ? 'fill' : 'line'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-gray-600 mt-2 text-sm">"<?php echo htmlspecialchars($row['feedbck']); ?>"</p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                            <i class="ri-message-3-line text-5xl text-gray-300"></i>
                            <h3 class="text-xl font-semibold text-gray-800 mt-4">No Feedback Yet</h3>
                            <p class="text-gray-600 mt-1">Be the first to share your experience with <?php echo htmlspecialchars($tailor['tname']); ?>!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="bg-gray-900 text-white mt-16 py-16">
      <div class="container mx-auto px-6"><div class="text-center border-t border-gray-800 pt-8"><p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p></div></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stars = document.querySelectorAll('#star-container i');
            const ratingInput = document.getElementById('rating-value');
            let currentRating = 0;

            function updateStars(rating) {
                stars.forEach(star => {
                    if (star.dataset.value <= rating) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                });
            }

            stars.forEach(star => {
                star.addEventListener('mouseover', () => {
                    updateStars(star.dataset.value);
                });
                star.addEventListener('mouseout', () => {
                    updateStars(currentRating); // Revert to saved rating on mouse out
                });
                star.addEventListener('click', () => {
                    currentRating = star.dataset.value;
                    ratingInput.value = currentRating;
                    updateStars(currentRating);
                });
            });
        });
    </script>
</body>
</html>