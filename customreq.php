<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val=$_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="customerhome.php">Home</a>&nbsp;&nbsp;
    <a href="cupdate.php">My Profile</a>&nbsp;&nbsp;
    <a href="cviewt.php">View Tailors</a>&nbsp;&nbsp;
    <a href="meas.php">Upload Measurements</a>&nbsp;&nbsp;
    <a href="customreq1.php">Stitch Requesting</a></td>&nbsp;&nbsp;
    <a href="index.php">Logout</a>&nbsp;&nbsp;
    <center>
    <h1>Customer Requests</h1>
    <form action="custreqaction.php" method="post" enctype="multipart/form-data">
        
    <table>
        <tr> 
            <td>Dress Name</td>
            <td><input type="text" name="cdname"></td>
        </tr>
        <tr>
            <td>Dress Type</td>
            <td><input type="text" name="cdtype"></td>
        </tr>
        <tr>
            <td>Description</td>
            <td><input type="text" name="cdes"></td>
        </tr>
        <tr>
            <td>Delivery Date</td>
            <td><input type="date" name="cddate"></td>
        </tr>
        <tr>
            <td>Reference Image</td>
            <td><input type="file" name="file"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" value="Request"></td>
        </tr>
</table>
</form>
<h1>Request Details</h1>
<table border="1">
    <tr>
        <th>Request Id</th>
        <th>Dress Name</th>
        <th>Dress Type</th>
        <th>Description</th>
        <th>Delivery Date</th>
        <th>Reference Image</th>
</tr>
<?php
$sql = "SELECT * FROM customreq WHERE uid='$val'";
$rs = $db->selectData($sql);
while($row=mysqli_fetch_array($rs))
{
?>
<tr>
    <td><?php echo $row['cdid'] ?></td>
    <td><?php echo $row['cdname'] ?></td>
    <td><?php echo $row['cdtype'] ?></td>
    <td><?php echo $row['cdes'] ?></td>
    <td><?php echo $row['cddate'] ?></td>
    <td><a target="_blank" href="<?php echo $row['cdimg']; ?>"> <img src="<?php echo $row['cdimg'] ?>" width="100" height="100"> </a></td> 
</tr>
<?php } ?>
</table> 
</center>
<body>
</html>