
<?php
require 'databasecon.php'; // Make sure this path is correct

header('Content-Type: application/json');

$db = new DatabaseCon();

// Get POST data
$phone = $_POST['phone'] ?? '';
$current_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$response = ['exists' => false];

// Basic validation for the phone number
if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode($response);
    exit();
}

// --- Combined Logic ---

// 1. First, check if the phone number exists in the customer registration table ('creg').
// This check is universal for both new tailor registrations and updates.
$query_creg = "SELECT phone FROM creg WHERE phone = ?";
$result_creg = $db->selectData($query_creg, "s", $phone);

if ($result_creg && $result_creg->num_rows > 0) {
    // Phone number found in the customer table.
    $response['exists'] = true;
} else {
    // 2. If not in 'creg', then check the tailor registration table ('treg').
    // We'll use the original logic here to handle new registrations vs. updates correctly.
    
    $query_treg = "";
    $result_treg = null;

    if ($current_id > 0) {
        // This is an UPDATE check. 
        // We look for the phone number but exclude the record of the current tailor.
        $query_treg = "SELECT tid FROM treg WHERE phone = ? AND tid != ?";
        $result_treg = $db->selectData($query_treg, "si", $phone, $current_id);
    } else {
        // This is a new REGISTRATION check.
        // We just look for the phone number anywhere in the tailor table.
        $query_treg = "SELECT tid FROM treg WHERE phone = ?";
        $result_treg = $db->selectData($query_treg, "s", $phone);
    }

    // If the query returned any rows, it means the phone number exists for another tailor.
    if ($result_treg && $result_treg->num_rows > 0) {
        $response['exists'] = true;
    }
}

// Finally, send the JSON response back to the client.
echo json_encode($response);

?>
