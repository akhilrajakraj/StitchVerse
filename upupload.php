
<?php
session_start();
require 'databasecon.php'; // Ensure this path is correct

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

// Check if an ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Set a cancel message and redirect
    $_SESSION['cancel_message'] = "Invalid design ID provided.";
    header("Location: viewmydesigns.php");
    exit();
}

$db = new DatabaseCon();
$design_id = $_GET['id'];
$tailor_id = $_SESSION['user_id'];

// --- SECURITY ENHANCEMENT: Use Prepared Statements ---
// Fetch the design details, ensuring the design belongs to the logged-in tailor
$sql = "SELECT * FROM upload WHERE did = ? AND uid = ?";
$result = $db->selectData($sql, "ii", $design_id, $tailor_id);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // If no design is found for this tailor, set a message and redirect
    $_SESSION['delete_message'] = "Design not found or you do not have permission to edit it.";
    header("Location: viewmydesigns.php");
    exit();
}

// --- LOGIC FOR CANCEL BUTTON ---
if (isset($_GET['action']) && $_GET['action'] === 'cancel') {
    $_SESSION['cancel_message'] = "Update was cancelled.";
    header("Location: viewmydesigns.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Design - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .dropdown-button[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <!-- === HEADER: MODIFIED FOR CONSISTENCY === -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          
          <nav class="hidden md:flex items-center space-x-6">
            <!-- Designs Dropdown -->
            <div class="relative">
                <button data-dropdown-toggle="designs-menu" class="dropdown-button text-purple-600 font-medium flex items-center gap-1">
                    <span>Designs</span>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
                </button>
                <div id="designs-menu" class="hidden absolute mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-20 border border-gray-100">
                    <a href="upd.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600">Upload</a>
                    <a href="viewmydesigns.php" class="block px-4 py-2 text-sm text-purple-600 bg-purple-50 font-semibold">My Designs</a>
                </div>
            </div>
            <!-- Other Dropdowns... -->
          </nav>
          
          <div class="hidden md:flex items-center space-x-4">
            <a href="tupdate.php" class="text-gray-700 hover:text-purple-600">My Profile</a>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm">Logout</a>
          </div>
          <button id="menu-button" class="md:hidden p-2"><i class="ri-menu-line text-xl"></i></button>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <i class="ri-edit-line text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Update Your Design</h1>
                            <p class="text-purple-100 mt-1">Make changes and save your work.</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form action="updadaction.php" method="post" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['did']); ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($row['dimg']); ?>">

                        <div class="grid md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                   <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                                   <div class="aspect-square rounded-lg overflow-hidden border">
                                        <img src="<?php echo htmlspecialchars($row['dimg']); ?>" alt="Current Design Image" class="w-full h-full object-cover">
                                   </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Image (Optional)</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-400">
                                        <i class="ri-image-add-line text-3xl text-gray-400 mx-auto mb-2"></i>
                                        <input type="file" name="new_image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                        <p class="text-xs text-gray-500 mt-2">Leave empty to keep the current image.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                 <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Design Name *</label>
                                    <div class="relative">
                                        <i class="ri-palette-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                        <input type="text" name="dname" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($row['dname']); ?>">
                                    </div>
                                  </div>
                                  <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Design Type *</label>
                                    <div class="relative">
                                        <i class="ri-price-tag-3-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                        <input type="text" name="dtype" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($row['dtype']); ?>">
                                    </div>
                                  </div>
                                  <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Design Description *</label>
                                   <div class="relative">
                                        <i class="ri-file-text-line text-gray-400 absolute left-3 top-4"></i>
                                        <textarea name="ddesc" required rows="4" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 resize-none"><?php echo htmlspecialchars($row['ddesc']); ?></textarea>
                                   </div>
                                  </div>
                                  <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Design Price (₹) *</label>
                                    <div class="relative">
                                        <i class="ri-money-rupee-circle-line text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                        <input type="number" name="dprice" required min="0" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" value="<?php echo htmlspecialchars($row['dprice']); ?>">
                                    </div>
                                  </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6 border-t border-gray-200 gap-4">
                            <a href="upupload.php?id=<?php echo $design_id; ?>&action=cancel" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-semibold transition-colors">Cancel</a>
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="ri-save-line"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white mt-16 py-16">
        <div class="container mx-auto px-6">
            <div class="text-center border-t border-gray-800 pt-8">
                <p class="text-gray-400 text-sm">© <?php echo date("Y"); ?> StitchVerse. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- The Dropdown script is the same as the other pages -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dropdown Menu Logic
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
        });
    </script>
</body>
</html>