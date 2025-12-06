
<?php
session_start();
require 'databasecon.php';

// --- 1. SECURITY AND SESSION CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: customerhome.php");
    exit();
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$customer_id = $_SESSION['user_id'];

// --- 2. DATA COLLECTION AND VALIDATION (NOW MORE FLEXIBLE) ---
$errors = [];

// Common fields
$tid = filter_input(INPUT_POST, 'tid', FILTER_VALIDATE_INT);
$rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_INT);
$feedbck = trim($_POST['feedbck'] ?? '');
$feedtype = trim($_POST['feedtype'] ?? '');

// Initialize variables for logic
$itemid = null;
$redirect_url = 'customerhome.php'; // A safe default redirect

// --- Conditional logic based on feedback type ---
if ($feedtype === 'design_purchase') {
    $oid = filter_input(INPUT_POST, 'oid', FILTER_VALIDATE_INT);
    $did = filter_input(INPUT_POST, 'did', FILTER_VALIDATE_INT);

    if (empty($oid) || empty($did)) {
        $errors[] = "Missing order or design information.";
    }
    $itemid = $did;
    $redirect_url = "designorder_details.php?id=" . urlencode($oid);

} elseif ($feedtype === 'stitch_request') {
    $posted_itemid = filter_input(INPUT_POST, 'itemid', FILTER_VALIDATE_INT);
    
    if (empty($posted_itemid)) {
        $errors[] = "Missing request information.";
    }
    $itemid = $posted_itemid;
    $redirect_url = "stitchrequest_details.php?id=" . urlencode($itemid);

} else {
    $errors[] = "Invalid feedback type submitted.";
}

// Common validation checks
if (empty($tid) || empty($rate) || empty($feedbck)) {
    $errors[] = "Rating, review, and tailor information are required.";
}
if ($rate < 1 || $rate > 5) {
    $errors[] = "Invalid rating value.";
}

// If any validation errors, redirect back with an error message
if (!empty($errors)) {
    $_SESSION['feedback_error'] = implode('<br>', $errors);
    header("Location: " . $redirect_url);
    exit();
}


// --- 3. PREVENT DUPLICATE FEEDBACK ---
$sql_check = "SELECT fid FROM feedb WHERE uid = ? AND itemid = ? AND feedtype = ?";
$check_result = $db->selectData($sql_check, "iis", $customer_id, $itemid, $feedtype);
if ($check_result && $check_result->num_rows > 0) {
    $_SESSION['feedback_error'] = "You have already submitted feedback for this item.";
    header("Location: " . $redirect_url);
    exit();
}


// --- 4. DATABASE INSERTION ---
try {
    $sql_insert = "INSERT INTO feedb (uid, tid, itemid, feedtype, feedbck, rate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    if ($stmt === false) {
        throw new Exception("SQL Prepare Error: " . $conn->error);
    }
    
    // Note the types: i for integer, s for string
    $stmt->bind_param("iiissi", $customer_id, $tid, $itemid, $feedtype, $feedbck, $rate);
    $stmt->execute();
    $stmt->close();

    // --- 5. SUCCESS FEEDBACK AND REDIRECT ---
    $_SESSION['feedback_success'] = "Thank you! Your feedback has been submitted successfully.";
    header("Location: " . $redirect_url);
    exit();

} catch (Exception $e) {
    // Handle any database errors
    error_log("Feedback submission failed: " . $e->getMessage());
    $_SESSION['feedback_error'] = "A technical issue occurred. Please try again later.";
    header("Location: " . $redirect_url);
    exit();
}
?>