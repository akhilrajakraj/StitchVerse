<?php 
 header('Content-Type: application/json'); 
 require 'databasecon.php'; 

 $response = ['exists' => false]; 

 // Check if email is posted 
 if (isset($_POST['email'])) { 
     $email = trim(strtolower($_POST['email'])); 
     
     // Validate email format on server before querying 
     if (filter_var($email, FILTER_VALIDATE_EMAIL)) { 
         $db = new DatabaseCon(); 
         
         // An efficient query to check both tables at once.
         // UNION ALL is faster than UNION because it doesn't check for duplicates between the two selects.
         $query = "
             (SELECT email FROM creg WHERE email = ?)
             UNION ALL
             (SELECT email FROM treg WHERE email = ?)
         ";
         
         // We need to bind the email parameter twice, once for each placeholder '?'
         $result = $db->selectData($query, "ss", $email, $email); 

         // If the query returns one or more rows, it means the email was found in at least one table.
         if ($result && $result->num_rows > 0) { 
             $response['exists'] = true; 
         } 
     } 
 } 

 echo json_encode($response); 
 ?>