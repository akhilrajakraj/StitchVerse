<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val = $_SESSION['uid'];
$did = $_GET['id'];
$tname = $_GET['tname'];
$dname = $_GET['dname'];
$dtype = $_GET['dtype'];
$ddesc = $_GET['ddesc'];
$dimg= $_GET['dimg'];
$dprice = $_GET['dprice'];
$address = $_GET['address'];    
$sql = "INSERT INTO orderdesign (uid,did, tname, dname, dtype, ddesc, dimg, dprice, address,ostatus) 
        VALUES ('$val', '$did',$tname', '$dname', '$dtype', '$ddesc', '$dimg', '$dprice', '$address','ordered')";
$rs = $db->insertquery($sql);
if ($rs) {
    echo "<script>alert('Order placed successfully.'); window.location='cvieworders.php';</script>";
} else {
    echo "<script>alert('Failed to place order. Please try again.'); window.location='cvieworders.php';</script>";
}
?>