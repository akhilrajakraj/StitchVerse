
<?php
session_start();
require 'databasecon.php'; 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$customer_id = $_SESSION['user_id'];
$customer_name = "Customer";
$user_measurements_json = "{}"; 

// Fetch customer's first name for the header dropdown
$query_cname = "SELECT cname FROM creg WHERE cid = ?";
$result_cname = $db->selectData($query_cname, "i", $customer_id);
if ($result_cname && $result_cname->num_rows === 1) {
    $customer_data = $result_cname->fetch_assoc();
    $customer_name = explode(' ', trim($customer_data['cname']))[0];
}

// Fetch the user's saved measurements to pre-fill the form
$q_meas = "SELECT * FROM measurements WHERE uid = ?";
$result_meas = $db->selectData($q_meas, "i", $customer_id);
if ($result_meas && $result_meas->num_rows > 0) {
    $measurements_data = $result_meas->fetch_assoc();
    $user_measurements_json = json_encode($measurements_data);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Stitch Request - StitchVerse</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    
    <style>
        .font-pacifico { font-family: 'Pacifico', cursive; }
        .form-section { display: none; opacity: 0; transition: opacity 0.5s ease-in-out; }
        .form-section.visible { display: block; opacity: 1; }
        .required-label::after { content: ' *'; color: #ef4444; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="customerhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <nav class="hidden md:flex items-center space-x-8">
            <a href="viewdesigns.php" class="text-gray-700 hover:text-purple-600 transition-colors">Designs</a>
            <a href="cviewt.php" class="text-gray-700 hover:text-purple-600 transition-colors">Tailors</a>
            <a href="customreq1.php" class="text-purple-600 font-semibold border-b-2 border-purple-600 pb-1">Stitch Request</a>
          </nav>
          <div class="hidden md:flex items-center space-x-6">
            <div class="relative" id="profile-dropdown-container">
              <button id="profile-dropdown-button" class="flex items-center text-gray-700 hover:text-purple-600 focus:outline-none transition-colors">
                <span class="font-medium">My Account</span>
                <i class="ri-arrow-down-s-line ml-1"></i>
              </button>
              <div id="profile-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-50 ring-1 ring-black ring-opacity-5">
                   <div class="px-4 py-3 border-b border-gray-100"><p class="text-sm text-gray-500">Signed in as</p><p class="text-sm text-gray-900 font-semibold truncate"><?php echo htmlspecialchars($customer_name); ?></p></div>
                   <div class="py-1"><a href="cupdate.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Profile</a><a href="meas.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">My Measurements</a></div>
                   <div class="py-1 border-t border-gray-100"><a href="designorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Design Orders</a><a href="stitchingorders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-purple-600">Stitching Orders</a></div>
              </div>
            </div>
            <a href="index.php" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-4xl font-bold text-gray-800">Create a Stitch Request</h1>
                        <p class="text-gray-600 mt-2">Describe your dream outfit and let our tailors bring it to life.</p>
                    </div>
                    
                    <form id="stitch-request-form" action="stitchreqaction.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="sdesign_details" id="sdesign_details_hidden">

                        <div class="mb-8 border-b pb-8">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><span class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span> Choose Your Outfit</h2>
                            <div>
                                <label for="outfit_choice" class="block mb-2 text-sm font-medium text-gray-700 required-label">What would you like to get stitched?</label>
                                <select id="outfit_choice" required class="w-full p-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-purple-500">
                                    <option value="">-- Select an Outfit --</option>
                                    </select>
                            </div>
                        </div>

                        <div id="dynamic-form-container" class="space-y-8">
                            <div id="core-details-section" class="form-section">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><span class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">2</span> Core Details</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label for="sdname" class="block mb-1 text-sm font-medium text-gray-700 required-label">Request Name</label><input type="text" name="sdname" id="sdname" required class="w-full p-2 border border-gray-300 rounded-md bg-gray-50"></div>
                                    <div><label for="sdtype" class="block mb-1 text-sm font-medium text-gray-700 required-label">Outfit Type</label><input type="text" name="sdtype" id="sdtype" required class="w-full p-2 border border-gray-300 rounded-md bg-gray-50"></div>
                                    <div><label for="sfabric" class="block mb-1 text-sm font-medium text-gray-700 required-label">Fabric</label><input type="text" name="sfabric" id="sfabric" required class="w-full p-2 border border-gray-300 rounded-md bg-gray-50"></div>
                                    <div><label for="scolor" class="block mb-1 text-sm font-medium text-gray-700 required-label">Color</label><input type="text" name="scolor" id="scolor" required class="w-full p-2 border border-gray-300 rounded-md bg-gray-50"></div>
                                    <div class="md:col-span-2"><label for="spattern" class="block mb-1 text-sm font-medium text-gray-700 required-label">Pattern</label><input type="text" name="spattern" id="spattern" required class="w-full p-2 border border-gray-300 rounded-md bg-gray-50"></div>
                                </div>
                            </div>
                            <div id="measurements-section" class="form-section">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><span class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">3</span> Measurements (cm)</h2>
                                <div class="text-sm text-blue-800 bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">Your saved measurements are pre-filled. You can edit them <a href="meas.php" class="underline font-semibold" target="_blank">here</a>.</div>
                                <div id="measurements-fields" class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4"></div>
                            </div>
                            <div id="design-details-section" class="form-section">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><span class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">4</span> Design Options</h2>
                                <div id="design-fields" class="grid grid-cols-1 md:grid-cols-3 gap-6"></div>
                            </div>
                            <div id="logistics-section" class="form-section">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><span class="bg-purple-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">5</span> Final Touches</h2>
                                <div class="space-y-6">
                                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Special Instructions</label><textarea name="sinstructions" rows="3" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="e.g., Add lining, use specific buttons, embroidery on the sleeves..."></textarea></div>
                                    <div><label class="block mb-2 text-sm font-medium text-gray-700">Inspiration Image (Optional)</label><input type="file" name="simg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"></div>
                                    <div><label for="sddate" class="block mb-2 text-sm font-medium text-gray-700 required-label">Desired Delivery Date</label><input type="date" name="sddate" id="sddate" required class="w-full p-3 border border-gray-300 rounded-lg"></div>
                                    <div>
                                        <label for="tailor-select" class="block mb-2 text-sm font-medium text-gray-700">Assign to a Specific Tailor (Optional)</label>
                                        <select name="tid" id="tailor-select" class="w-full p-3 border border-gray-300 rounded-lg bg-white">
                                            <option value="">Any Available Tailor</option>
                                            <?php
                                                // MODIFICATION: Removed data-* attributes as search is removed.
                                                $tailorSQL = "SELECT tid, tname, email FROM treg WHERE status = 'Approved' ORDER BY tname ASC";
                                                $tailorResult = $db->selectData($tailorSQL);
                                                while ($trow = mysqli_fetch_array($tailorResult)) {
                                                    $tname = htmlspecialchars($trow['tname']);
                                                    $temail = htmlspecialchars($trow['email']);
                                                    echo '<option value="' . htmlspecialchars($trow['tid']) . '">' . $tname . ' (' . $temail . ')</option>';
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="submit-button-container" class="form-section mt-8 pt-6 border-t border-gray-200 flex justify-end">
                                <button type="submit" class="px-8 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow-lg transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <i class="ri-send-plane-2-line"></i> Submit Stitch Request
                                </button>
                            </div>
                        </div>
                    </form>
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
    document.addEventListener('DOMContentLoaded', function() {
        // --- DATA FROM PHP ---
        const savedMeasurements = <?php echo $user_measurements_json; ?>;

        // --- THE BRAIN: OUTFIT CONFIGURATION ---
        const outfitConfigs = {
            'womens_anarkali_festive': {
                name: "Anarkali Suit – Festive Wear", category: "Women’s Wear",
                prefills: { sdtype: 'Suit', sfabric: 'Silk', scolor: 'Green', spattern: 'Printed' },
                measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length', 'inseam', 'outseam'],
                designs: { sneck: { label: 'Neck Style', options: ['Round Neck', 'Boat Neck', 'Sweetheart'] }, ssleeve: { label: 'Sleeve Style', options: ['Full Sleeve', 'Three-Quarter', 'Churidar Sleeves'] } }
            },
            'womens_kurti_office': {
                name: "Office Kurti – Formal Wear", category: "Women’s Wear",
                prefills: { sdtype: 'Kurti', sfabric: 'Linen', scolor: 'Black', spattern: 'Solid' },
                measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length'],
                designs: { sneck: { label: 'Collar Style', options: ['Mandarin Collar', 'Classic Collar', 'Peter Pan Collar'] }, ssleeve: { label: 'Sleeve Length', options: ['Three-Quarter', 'Full Sleeve'] } }
            },
            'womens_anarkali_semi': {
                name: "Anarkali Kurti – Semi-Formal", category: "Women’s Wear",
                prefills: { sdtype: 'Kurti', sfabric: 'Polyester', scolor: 'Red', spattern: 'Solid' },
                measurements: ['height', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'arm_length'],
                designs: { sneck: { label: 'Neck Design', options: ['V-Neck', 'Round Neck', 'Keyhole'] }, ssleeve: { label: 'Sleeve Style', options: ['Full Sleeve', 'Bell Sleeves'] } }
            },
            'womens_shirt_palazzo': {
                name: "Mandarin Collar Shirt with Palazzo", category: "Women’s Wear",
                prefills: { sdtype: 'Suit', sfabric: 'Polyester', scolor: 'Beige/Navy', spattern: 'Solid' },
                measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'hip', 'sleeve_length', 'wrist', 'inseam', 'outseam'],
                designs: { ssleeve: { label: 'Shirt Sleeve', options: ['Full Sleeve', 'Roll-up Tabs'] }, bottom_style: { label: 'Bottom Style', options: ['Palazzo', 'Straight Pants'] } }
            },
            'womens_trousers_formal': {
                name: "Formal Linen Trousers – Women", category: "Women’s Wear",
                prefills: { sdtype: 'Trousers', sfabric: 'Linen', scolor: 'Charcoal Grey', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam', 'ankle'],
                designs: { fit: { label: 'Fit', options: ['Regular Fit', 'Slim Fit', 'Wide Leg'] }, pleats: { label: 'Pleats', options: ['No Pleats (Flat Front)', 'Single Pleat'] } }
            },
            'womens_blouse_bridal': {
                name: "Silk Bridal Blouse", category: "Women’s Wear",
                prefills: { sdtype: 'Blouse', sfabric: 'Silk', scolor: 'Maroon/Gold', spattern: 'Woven texture' },
                measurements: ['neck', 'shoulder', 'chest', 'bust', 'waist', 'arm_length', 'sleeve_length'],
                designs: { sneck: { label: 'Neck Design', options: ['Round Neck', 'High Neck', 'Sweetheart'] }, ssleeve: { label: 'Sleeve Style', options: ['Puff Sleeves', 'Elbow Length', 'Sleeveless'] }, back_design: { label: 'Back Design', options: ['Open Back', 'Covered', 'Deep U-Back'] } }
            },
            'womens_skirt_aline': {
                name: "A-Line Midi Skirt – Casual Chic", category: "Women’s Skirts & Bottoms",
                prefills: { sdtype: 'Skirt', sfabric: 'Cotton Poplin', scolor: 'Mustard Yellow', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'height'],
                designs: { waist_style: { label: 'Waist Style', options: ['High-Waist', 'Mid-Rise'] }, closure: { label: 'Closure', options: ['Side Zipper', 'Back Zipper', 'Elastic Waistband'] } }
            },
            'womens_skirt_pleated': {
                name: "Pleated Maxi Skirt – Formal Occasions", category: "Women’s Skirts & Bottoms",
                prefills: { sdtype: 'Skirt', sfabric: 'Georgette with Satin Lining', scolor: 'Emerald Green', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'height'],
                designs: { pleat_size: { label: 'Pleat Size', options: ['Micro Pleats', 'Box Pleats'] }, lining: { label: 'Lining', options: ['Full Lining', 'Half Lining'] } }
            },
             'womens_skirt_denim': {
                name: "Denim Mini Skirt – Street Style", category: "Women’s Skirts & Bottoms",
                prefills: { sdtype: 'Skirt', sfabric: 'Stretch Denim', scolor: 'Light Blue Wash', spattern: 'Faded' },
                measurements: ['waist', 'hip'],
                designs: { distress_level: { label: 'Distressing', options: ['None', 'Lightly Distressed', 'Heavily Ripped'] }, hem: { label: 'Hem', options: ['Clean Hem', 'Frayed Hem'] } }
            },
             'womens_skirt_mermaid': {
                name: "Mermaid Skirt – Elegant Fit", category: "Women’s Skirts & Bottoms",
                prefills: { sdtype: 'Skirt', sfabric: 'Satin Blend', scolor: 'Champagne Gold', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'knee', 'height'],
                designs: { train: { label: 'Train Length', options: ['No Train', 'Sweep Train', 'Chapel Train'] }, closure: { label: 'Closure', options: ['Invisible Zipper', 'Button Detail'] } }
            },
             'unisex_skirt_wrap': {
                name: "Wrap Skirt – Bohemian Style", category: "Unisex / Streetwear",
                prefills: { sdtype: 'Skirt', sfabric: 'Rayon', scolor: 'Multicolor Floral Print', spattern: 'Printed' },
                measurements: ['waist', 'hip', 'height'],
                designs: { length: { label: 'Length', options: ['Knee-Length', 'Midi', 'Ankle-Length'] }, tie_style: { label: 'Tie Style', options: ['Side Tie', 'Front Tie'] } }
            },
            'mens_shirt_formal': {
                name: "Classic Formal Shirt", category: "Men’s Wear",
                prefills: { sdtype: 'Shirt', sfabric: 'Egyptian Cotton', scolor: 'White', spattern: 'Solid' },
                measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length', 'wrist'],
                designs: { collar_type: { label: 'Collar', options: ['Classic', 'Cutaway', 'Spread'] }, cuff_style: { label: 'Cuff', options: ['Single Button', 'Double Button (Barrel)', 'French Cuffs'] } }
            },
            'mens_shirt_linen': {
                name: "Casual Linen Shirt", category: "Men’s Wear",
                prefills: { sdtype: 'Shirt', sfabric: 'Linen', scolor: 'Sky Blue', spattern: 'Solid/Light Stripes' },
                measurements: ['neck', 'shoulder', 'chest', 'waist', 'sleeve_length'],
                designs: { fit: { label: 'Fit', options: ['Regular Fit', 'Slim Fit'] }, placket: { label: 'Placket', options: ['Standard Placket', 'French Front'] } }
            },
            'mens_chinos_slim': {
                name: "Slim Fit Chinos", category: "Men’s Wear",
                prefills: { sdtype: 'Trousers', sfabric: 'Cotton-Twill Blend', scolor: 'Khaki', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'ankle'],
                designs: { rise: { label: 'Rise', options: ['Mid-Rise', 'Low-Rise'] }, cuff: { label: 'Cuff', options: ['No Cuff (Plain Hem)', 'Turn-up Cuff'] } }
            },
            'mens_trousers_wool': {
                name: "Tailored Wool Trousers", category: "Men’s Wear",
                prefills: { sdtype: 'Trousers', sfabric: 'Wool-Blend', scolor: 'Navy', spattern: 'Pinstripe' },
                measurements: ['waist', 'hip', 'thigh', 'knee', 'inseam', 'outseam'],
                designs: { pleats: { label: 'Pleats', options: ['No Pleats (Flat Front)', 'Single Pleat'] }, closure: { label: 'Closure', options: ['Hook and Bar', 'Button'] } }
            },
            'mens_cargo': {
                name: "Cargo Pants – Functional Casual", category: "Extra Men’s Wear",
                prefills: { sdtype: 'Trousers', sfabric: 'Cotton Twill', scolor: 'Olive Green', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'thigh', 'inseam', 'outseam'],
                designs: { pocket_style: { label: 'Pocket Style', options: ['Bellows Pockets', 'Flap Pockets'] }, fit: { label: 'Fit', options: ['Relaxed Fit', 'Straight Fit'] } }
            },
            'mens_polo': {
                name: "Polo T-Shirt – Smart Casual", category: "Extra Men’s Wear",
                prefills: { sdtype: 'T-Shirt', sfabric: 'Cotton Piqué', scolor: 'Burgundy', spattern: 'Solid' },
                measurements: ['neck', 'shoulder', 'chest', 'sleeve_length'],
                designs: { collar_style: { label: 'Collar', options: ['Ribbed Knit', 'Self-Fabric Collar'] }, placket_buttons: { label: 'Buttons', options: ['Two-Button', 'Three-Button'] } }
            },
            'mens_kurta': {
                name: "Mandarin Collar Kurta – Ethnic Formal", category: "Extra Men’s Wear",
                prefills: { sdtype: 'Kurta', sfabric: 'Silk-Cotton Blend', scolor: 'Deep Maroon', spattern: 'Self-textured' },
                measurements: ['neck', 'shoulder', 'chest', 'waist', 'hip', 'sleeve_length'],
                designs: { placket_style: { label: 'Placket', options: ['Concealed Placket', 'Exposed Button Placket'] }, length: { label: 'Length', options: ['Knee-Length', 'Below Knee'] } }
            },
            'unisex_hoodie': {
                name: "Oversized Hoodie", category: "Unisex / Streetwear",
                prefills: { sdtype: 'Hoodie', sfabric: 'Cotton Fleece', scolor: 'Black', spattern: 'Minimal logo' },
                measurements: ['shoulder', 'chest', 'sleeve_length', 'height'],
                designs: { hood_style: { label: 'Hood Style', options: ['Standard Hood', 'Double-Lined Hood'] }, pocket: { label: 'Pocket', options: ['Kangaroo Pocket', 'No Pocket'] } }
            },
            'unisex_joggers': {
                name: "Athletic Joggers", category: "Unisex / Streetwear",
                prefills: { sdtype: 'Joggers', sfabric: 'Polyester-Spandex Blend', scolor: 'Charcoal', spattern: 'Solid' },
                measurements: ['waist', 'hip', 'thigh', 'inseam', 'ankle'],
                designs: { cuff_style: { label: 'Cuff', options: ['Elastic Cuff', 'Zipper Cuff'] }, waistband: { label: 'Waistband', options: ['Drawstring', 'Elastic Only'] } }
            }
        };
        const allMeasurements = { height: 'Height', weight: 'Weight', neck: 'Neck', shoulder: 'Shoulder', chest: 'Chest', bust: 'Bust', waist: 'Waist', hip: 'Hip', arm_length: 'Arm Length', sleeve_length: 'Sleeve Length', bicep: 'Bicep', wrist: 'Wrist', thigh: 'Thigh', knee: 'Knee', calf: 'Calf', inseam: 'Inseam', outseam: 'Outseam', ankle: 'Ankle' };

        // --- DOM ELEMENTS ---
        const outfitSelect = document.getElementById('outfit_choice');
        const formContainer = document.getElementById('dynamic-form-container');
        const measurementsContainer = document.getElementById('measurements-fields');
        const designsContainer = document.getElementById('design-fields');
        const form = document.getElementById('stitch-request-form');

        // --- FUNCTIONS ---
        function populateOutfitSelector() {
            const categories = {};
            for (const key in outfitConfigs) {
                const config = outfitConfigs[key];
                if (!categories[config.category]) {
                    categories[config.category] = [];
                }
                categories[config.category].push({ key: key, name: config.name });
            }
            for (const categoryName in categories) {
                const optgroup = document.createElement('optgroup');
                optgroup.label = categoryName;
                categories[categoryName].forEach(outfit => {
                    const option = document.createElement('option');
                    option.value = outfit.key;
                    option.textContent = outfit.name;
                    optgroup.appendChild(option);
                });
                outfitSelect.appendChild(optgroup);
            }
        }
        
        function updateForm(outfitKey) {
            // Hide all sections first
            formContainer.querySelectorAll('.form-section').forEach(el => el.classList.remove('visible'));
            
            if (!outfitKey || !outfitConfigs[outfitKey]) return;

            const config = outfitConfigs[outfitKey];

            // 1. Pre-fill core details
            for(const key in config.prefills) {
                document.getElementById(key).value = config.prefills[key];
            }
            document.getElementById('sdname').value = config.name; // Set the request name

            // 2. Populate Measurements
            measurementsContainer.innerHTML = '';
            config.measurements.forEach(key => {
                const label = allMeasurements[key] || key;
                const value = savedMeasurements[key] || '';
                const fieldHTML = `<div>
                    <label for="meas_${key}" class="block mb-1 text-sm font-medium text-gray-700 required-label">${label}</label>
                    <input type="number" step="0.1" name="measurements[${key}]" id="meas_${key}" required class="w-full p-2 border border-gray-300 rounded-md" value="${value}" placeholder="cm">
                </div>`;
                measurementsContainer.insertAdjacentHTML('beforeend', fieldHTML);
            });

            // 3. Populate Design Options
            designsContainer.innerHTML = '';
            for (const key in config.designs) {
                const design = config.designs[key];
                let optionsHTML = '<option value="">-- Select --</option>';
                design.options.forEach(opt => { optionsHTML += `<option value="${opt}">${opt}</option>`; });
                const fieldHTML = `<div>
                    <label for="design_${key}" class="block mb-1 text-sm font-medium text-gray-700">${design.label}</label>
                    <select name="designs[${key}]" id="design_${key}" class="w-full p-3 border border-gray-300 rounded-lg bg-white">${optionsHTML}</select>
                </div>`;
                designsContainer.insertAdjacentHTML('beforeend', fieldHTML);
            }
            
            // 4. Show all sections with a slight delay for a smooth transition
            setTimeout(() => {
                formContainer.querySelectorAll('.form-section').forEach(el => el.classList.add('visible'));
            }, 100);
        }
        
        // --- Form Submission Handler (Solves Normalization) ---
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop form from submitting immediately
            
            let designDetailsString = [];
            const designSelects = designsContainer.querySelectorAll('select');
            
            designSelects.forEach(select => {
                if (select.value) {
                    const label = document.querySelector(`label[for='${select.id}']`).textContent;
                    designDetailsString.push(`${label}: ${select.value}`);
                }
            });

            // Combine the measurement values into the same string
            const measurementInputs = measurementsContainer.querySelectorAll('input');
            let measurementDetails = [];
             measurementInputs.forEach(input => {
                if (input.value) {
                    const label = document.querySelector(`label[for='${input.id}']`).textContent.replace(' *','');
                    measurementDetails.push(`${label}: ${input.value} cm`);
                }
            });
            
            // Set the value of the hidden input
            document.getElementById('sdesign_details_hidden').value = designDetailsString.join('\\n') + '\\n\\n--- Measurements ---\\n' + measurementDetails.join('\\n');
            
            // Now, submit the form
            form.submit();
        });

        // --- Initialization ---
        populateOutfitSelector();
        outfitSelect.addEventListener('change', (e) => updateForm(e.target.value));
        document.getElementById('sddate').setAttribute('min', new Date().toISOString().split('T')[0]);

        // Also add the dropdown script for the header
        const profileDropdownButton = document.getElementById('profile-dropdown-button');
        const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
        if (profileDropdownButton && profileDropdownMenu) {
            profileDropdownButton.addEventListener('click', (event) => {
                event.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
            });
        }
        window.addEventListener('click', (event) => {
            if (profileDropdownMenu && !profileDropdownMenu.contains(event.target) && profileDropdownButton && !profileDropdownButton.contains(event.target)) {
                 profileDropdownMenu.classList.add('hidden');
            }
        });

        // --- MODIFICATION: Live search for tailor dropdown has been REMOVED ---

    });
    </script>
</body>
</html>