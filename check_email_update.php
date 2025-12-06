<?php
require 'databasecon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['user_id'])) {
    $db = new DatabaseCon();
    $conn = $db->getConnection();
    
    $email = $_POST['email'];
    $user_id = (int)$_POST['user_id'];
    
    // Check if the email exists for a DIFFERENT user
    $sql = "SELECT cid FROM creg WHERE email = ? AND cid != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    header('Content-Type: application/json');
    if ($result->num_rows > 0) {
        // Email exists for another user
        echo json_encode(['exists' => true]);
    } else {
        // Email is available or belongs to the current user
        echo json_encode(['exists' => false]);
    }
}
?>