<?php
session_start();
require 'databasecon.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id === 0) {
    die("Invalid request ID.");
}

// --- Handle POST Actions (Email Sending & Cancellation) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // Fetch customer email for both actions
    $sql_customer_email = "SELECT cr.email, cr.cname FROM stitchreq sr JOIN creg cr ON sr.uid = cr.cid WHERE sr.sdid = ?";
    $customer_info = $db->selectData($sql_customer_email, "i", $request_id)->fetch_assoc();
    $customer_email = $customer_info['email'];
    $customer_name = $customer_info['cname'];

    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com'; 
        $mail->Password   = 'cqdaqntjinoeclpr';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Tailor Support');
        $mail->addAddress($customer_email, $customer_name);
        $mail->isHTML(true);

        if ($action === 'send_email') {
            $subject = $_POST['subject'];
            $message = nl2br(htmlspecialchars($_POST['message']));

            $mail->Subject = $subject;
            $mail->Body    = "<h3>Message from your Tailor regarding Request #{$request_id}</h3><p>{$message}</p><p>Thank you,<br>The StitchVerse Team</p>";
            
            $mail->send();
            $_SESSION['message'] = "Email sent successfully to {$customer_name}.";

        } elseif ($action === 'cancel_request') {
            $reason = htmlspecialchars($_POST['cancellation_reason']);
            
            // Update the database
            $sql_cancel = "UPDATE stitchreq SET sstatus = 'Rejected' WHERE sdid = ? AND tid = ?";
            $db->executeQuery($sql_cancel, "ii", $request_id, $tailor_id);

            $mail->Subject = "Update on your StitchVerse Request #{$request_id}";
            $mail->Body    = "<h3>Your Request #{$request_id} has been cancelled.</h3>
                              <p>Hi {$customer_name},</p>
                              <p>We're sorry to inform you that your tailor has had to cancel your stitch request.</p>
                              <p><b>Reason provided by the tailor:</b></p>
                              <blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin-left: 10px; font-style: italic;'>{$reason}</blockquote>
                              <p>If you have any questions, please feel free to reach out. We apologize for any inconvenience.</p>
                              <p>Sincerely,<br>The StitchVerse Team</p>";

            $mail->send();
            // MODIFIED: Set an 'error' session message for the red alert
            $_SESSION['error'] = "Request #{$request_id} has been cancelled and the customer has been notified.";
            header("Location: accrequest.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    // Redirect back to the same page to show message and prevent resubmission
    header("Location: view_request_details3.php?id=" . $request_id);
    exit();
}


// --- Fetch all details for the request ---
$sql_details = "SELECT sr.*, cr.cname, cr.email, cr.phone 
                FROM stitchreq sr
                JOIN creg cr ON sr.uid = cr.cid
                WHERE sr.sdid = ? AND sr.tid = ?";
$result = $db->selectData($sql_details, "ii", $request_id, $tailor_id);

if (!$result || $result->num_rows === 0) {
    die("Could not find this request or you do not have permission to view it.");
}
$details = $result->fetch_assoc();
$image_paths = explode(',', $details['simg']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details for Request #<?php echo $details['sdid']; ?> - StitchVerse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style> .font-pacifico { font-family: 'Pacifico', cursive; } </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen">

    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
      <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <a href="tailorhome.php" class="text-2xl font-bold text-purple-600 font-pacifico">StitchVerse</a>
          <a href="accrequest.php" class="text-sm font-semibold text-purple-600 hover:underline">← Back to Accepted Requests</a>
        </div>
      </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Request #<?php echo $details['sdid']; ?> - <?php echo htmlspecialchars($details['sdname']); ?></h1>
            <p class="text-gray-500">For customer: <?php echo htmlspecialchars($details['cname']); ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Image -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-28">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Inspiration Image</h2>
                    <img src="<?php echo htmlspecialchars($image_paths[0]); ?>" alt="Inspiration Image" class="w-full h-auto object-cover rounded-lg shadow-md">
                </div>
            </div>

            <!-- Right Column: Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="col-span-2"><h3 class="text-lg font-bold text-purple-700 border-b pb-2 mb-2">Dress Specifications</h3></div>
                        <div><dt class="font-semibold text-gray-600">Dress Type</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['sdtype']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Fabric</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['sfabric']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Color</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['scolor']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Pattern</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['spattern']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Neck Design</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['sneck']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Shoulder Style</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['sshoulder']); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Sleeve Style</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['ssleeve']); ?></dd></div>
                        
                        <div class="col-span-2"><h3 class="text-lg font-bold text-purple-700 border-b pb-2 mb-2 mt-4">Instructions & Comments</h3></div>
                        <div class="col-span-2"><dt class="font-semibold text-gray-600">Special Instructions</dt><dd class="text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($details['sinstructions']); ?></dd></div>
                        <div class="col-span-2"><dt class="font-semibold text-gray-600">Customization Requests</dt><dd class="text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($details['scustom']); ?></dd></div>
                        <div class="col-span-2"><dt class="font-semibold text-gray-600">Additional Comments</dt><dd class="text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($details['scomments']); ?></dd></div>
                        
                        <div class="col-span-2"><h3 class="text-lg font-bold text-purple-700 border-b pb-2 mb-2 mt-4">Logistics</h3></div>
                        <div><dt class="font-semibold text-gray-600">Desired Delivery Date</dt><dd class="text-gray-900"><?php echo date("F j, Y", strtotime($details['sddate'])); ?></dd></div>
                        <div><dt class="font-semibold text-gray-600">Priority</dt><dd class="text-gray-900"><?php echo htmlspecialchars($details['spriority']); ?></dd></div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Communication & Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Email Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Contact Customer</h2>
                <form action="view_request_details3.php?id=<?php echo $request_id; ?>" method="POST">
                    <input type="hidden" name="action" value="send_email">
                    <div class="space-y-4">
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" name="subject" id="subject" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500" required value="Regarding your Stitch Request #<?php echo $request_id; ?>">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="message" id="message" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500" required></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-blue-700">Send Email</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cancellation Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Cancel Request</h2>
                <p class="text-sm text-gray-600 mb-4">If you cannot fulfill this order, please provide a reason and confirm cancellation. This action is final and will notify the customer.</p>
                <button type="button" id="show-cancel-form" class="w-full bg-red-600 text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-red-700">Cancel This Request</button>
                
                <form id="cancel-form" action="view_request_details3.php?id=<?php echo $request_id; ?>" method="POST" class="hidden mt-4">
                    <input type="hidden" name="action" value="cancel_request">
                    <div>
                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700">Reason for Cancellation</label>
                        <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-red-500 focus:border-red-500" required></textarea>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="w-full bg-red-800 text-white py-2.5 px-4 rounded-lg font-semibold hover:bg-red-900">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('show-cancel-form').addEventListener('click', function() {
            document.getElementById('cancel-form').classList.remove('hidden');
            this.classList.add('hidden');
        });
    </script>

</body>
</html>
