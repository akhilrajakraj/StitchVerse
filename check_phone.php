<?php
header('Content-Type: application/json');
require 'databasecon.php';

$response = ['exists' => false];

if (isset($_POST['phone'])) {
    $phone = $_POST['phone'];
    
    // Check if the phone number has exactly 10 digits and is numeric
    if (preg_match('/^[0-9]{10}$/', $phone)) {
        $db = new DatabaseCon();
        
        // Check in customer registration table
        $query = "SELECT phone FROM creg WHERE phone = ?";
        $result = $db->selectData($query, "s", $phone);

        if ($result && $result->num_rows > 0) {
            $response['exists'] = true;
        } else {
            // Optional: You could also check the tailor table if phone numbers must be unique across the whole system
             $query_t = "SELECT phone FROM treg WHERE phone = ?";
            $result_t = $db->selectData($query_t, "s", $phone);
            if ($result_t && $result_t->num_rows > 0) {
                $response['exists'] = true;
            }
        }
    }
}

echo json_encode($response);
?>