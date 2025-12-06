<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val = $_SESSION['uid'];

$sql = "SELECT c.cid, u.dname, u.dtype, u.dprice, u.dimg, c.quantity 
        FROM cart c 
        INNER JOIN upload u ON c.did = u.did 
        WHERE c.uid = '$val'";
$rs = $db->selectData($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
</head>
<body>
<center>
    <h1>Your Cart</h1>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Design Name</th>
            <th>Type</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Remove</th>
        </tr>
        <?php
        $grandTotal = 0;
        while($row = mysqli_fetch_array($rs)) {
            $subtotal = $row['dprice'] * $row['quantity'];
            $grandTotal += $subtotal;
        ?>
        <tr>
            <td><img src="<?php echo $row['dimg']; ?>" width="80" height="80"></td>
            <td><?php echo $row['dname']; ?></td>
            <td><?php echo $row['dtype']; ?></td>
            <td><?php echo $row['dprice']; ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td><?php echo $subtotal; ?></td>
            <td><a href="removecart.php?cid=<?php echo $row['cid']; ?>">Remove</a></td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="5" align="right"><strong>Grand Total</strong></td>
            <td colspan="2"><?php echo $grandTotal; ?></td>
        </tr>
    </table>
    <br>
    <a href="checkout.php">Proceed to Checkout</a>
</center>
</body>
</html>