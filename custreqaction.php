<?php
session_start();
include('databasecon.php');
$db=new DatabaseCon();
$val=$_SESSION['uid'];

$cdn=$_POST['cdname'];
$cdt=$_POST['cdtype'];
$cds=$_POST['cdes'];
$cdt=$_POST['cddate'];

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$check = getimagesize($_FILES["file"]["tmp_name"]);
if($check !== false) {
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO customreq (uid, cdname, cdtype, cdes, cddate, cdimg) VALUES ('$val', '$cdn', '$cdt', '$cds', '$cdt', '$target_file')";
        $db->insertQuery($sql);
        echo "<script>alert('Request submitted successfully');window.location='customreq.php';</script>";
    } else {
        echo "<script>alert('Error uploading your file.');window.history.back();</script>";
    }
} else {
    echo "<script>alert('File is not an image.');window.history.back();</script>";
}
