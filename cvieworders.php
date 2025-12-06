<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val = $_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Design</title>
</head>
<body>
    <tr>
        <td><a href="customerhome.php">Home</a></td>&nbsp;&nbsp;
        <td><a href="cupdate.php">My Profile</a></td>&nbsp;&nbsp;
        <td><a href="cviewt.php">View Tailors</a></td>&nbsp;&nbsp;
        <td><a href="viewdesigns.php">View Designs</a></td>&nbsp;&nbsp;
        <td><a href="meas.php">Upload Measurements</a></td>&nbsp;&nbsp;
        <td><a href="customreq1.php">Stitch Requesting</a></td>&nbsp;&nbsp;
        <td><a href="vieworders.php">View Orders</a></td>&nbsp;&nbsp;
        <td><a href="index.php">Logout</a></td>&nbsp;&nbsp;
</tr>
     <center>
        <h1>Order Details</h1>
        <form action="orderaction.php" method="post">
           
        <table border="1">
            <tr>
                <th>Design Name</th>
                <th>Design Type</th>
                <th>Description</th>
                <th>Design Price</th>
                <th>Design Image</th>
                <th>Tailor Name</th>
                <th>My Address</th>
                <th>PLace Order</th>
            </tr>
            <?php
            $sql = "SELECT u.dname, u.dtype, u.ddesc, u.dprice, u.dimg, t.tname, c.address,u.did 
                    FROM upload AS u 
                    JOIN treg AS t ON u.uid = t.tid 
                    JOIN creg AS c ON c.cid = '$val' where u.did = '{$_GET['id']}'"; // Adjust WHERE clause as needed
            $rs = $db->selectData($sql);
            while ($row = mysqli_fetch_array($rs)) {
            ?>
            <tr>
                <input type="hidden" name="did" value="<?php echo $_GET['id']; ?>">
                <td> <?php echo $row['dname']; ?> </td>
                <td> <?php echo $row['dtype']; ?> </td>
                <td> <?php echo $row['ddesc']; ?> </td>
                <td> ₹<?php echo $row['dprice']; ?> </td>
                <td><img src="<?php echo $row['dimg']; ?>" alt="Design Image" width="100" height="100"></td>
                <td> <?php echo $row['tname']; ?> </td>
                <td> <?php echo $row['address']; ?> </td>
                <td><input type="submit" value="Place Order"></td>
                
            </tr>
                
            <?php } ?>

            </table>
</body>
</html>