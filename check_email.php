<?php
// No session needed for this simple check
require 'databasecon.php';

// Check if email is provided via POST
if (isset($_POST['email'])) {
    $db = new DatabaseCon();
    $email = trim(strtolower($_POST['email']));

    // Prepare a query to check if the email exists in the login table
    $query = "SELECT uid FROM login WHERE uname = ?";
    $result = $db->selectData($query, "s", $email);

    // Prepare the response array
    $response = [];
    if ($result && $result->num_rows > 0) {
        // Email exists
        $response['exists'] = true;
    } else {
        // Email does not exist
        $response['exists'] = false;
    }

    // Set the content type to JSON and output the response
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
