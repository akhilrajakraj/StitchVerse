<?php
include('databasecon.php');
$db = new DatabaseCon();

$val = isset($_GET['uid']) ? $_GET['uid'] : '';
$height = isset($_GET['height']) ? $_GET['height'] : '';
$weight = isset($_GET['weight']) ? $_GET['weight'] : '';
$chest = isset($_GET['chest']) ? $_GET['chest'] : '';
$neck = isset($_GET['neck']) ? $_GET['neck'] : '';
$shoulder = isset($_GET['shoulder']) ? $_GET['shoulder'] : '';
$bust = isset($_GET['bust']) ? $_GET['bust'] : '';
$waist = isset($_GET['waist']) ? $_GET['waist'] : '';
$hip = isset($_GET['hip']) ? $_GET['hip'] : '';
$arm_length = isset($_GET['arm_length']) ? $_GET['arm_length'] : '';
$sleeve_length = isset($_GET['sleeve_length']) ? $_GET['sleeve_length'] : '';
$bicep = isset($_GET['biceps']) ? $_GET['biceps'] : '';
$wrist = isset($_GET['wrist']) ? $_GET['wrist'] : '';
$thigh = isset($_GET['thigh']) ? $_GET['thigh'] : '';
$knee = isset($_GET['knee']) ? $_GET['knee'] : '';
$calf = isset($_GET['calf']) ? $_GET['calf'] : '';
$inseam = isset($_GET['inseam']) ? $_GET['inseam'] : '';
$outseam = isset($_GET['outseam']) ? $_GET['outseam'] : '';
$ankle = isset($_GET['ankle']) ? $_GET['ankle'] : '';

// Check if measurement exists for this user
$query = "SELECT * FROM measurements WHERE uid='$val'";
$result = $db->selectData($query);
    $update = "UPDATE measurements SET 
        height='$height', weight='$weight', chest='$chest', neck='$neck', shoulder='$shoulder', bust='$bust', waist='$waist', hip='$hip', 
        arm_length='$arm_length', sleeve_length='$sleeve_length', bicep='$bicep', wrist='$wrist', thigh='$thigh', knee='$knee', calf='$calf', 
        inseam='$inseam', outseam='$outseam', ankle='$ankle' WHERE uid='$val'";
    $db->insertQuery($update);
    echo "<script>alert('Measurements updated successfully');window.location.href='mview.php';</script>";

?>