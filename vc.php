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
    <title>Request Details</title>
</head>
<body>
    <h1>Request Details</h1>
    <table border="1">
        <tr>
            <th>Request Id</th>
            <th>Dress Name</th>
            <th>Dress Type</th>
            <th>Fabric Type</th>
            <th>Color</th>
            <th>Pattern</th>
            <th>Neck Design</th>
            <th>Shoulder Style</th>
            <th>Sleeve Style</th>
            <th>Special Instructions</th>
            <th>Customizations</th>
            <th>Priority</th>
            <th>Delivery Date</th>
            <th>Reference Images</th>
            <th>Order Status</th>
            <th>Payment</th>
        </tr>
        <?php
        $sql = "SELECT o.*, p.status AS status 
                FROM stitchreq o 
                LEFT JOIN payment p ON o.sdid = p.order_id 
                WHERE o.uid='$val' ORDER BY o.sdid DESC";
        $rs = $db->selectData($sql);
        while($row = mysqli_fetch_array($rs)) {
        ?>
        <tr>
            <td><?php echo $row['sdid'] ?></td>
            <td><?php echo $row['sdname'] ?></td>
            <td><?php echo $row['sdtype'] ?></td>
            <td><?php echo $row['sfabric'] ?></td>
            <td><?php echo $row['scolor'] ?></td>
            <td><?php echo $row['spattern'] ?></td>
            <td><?php echo $row['sneck'] ?></td>
            <td><?php echo $row['sshoulder'] ?></td>
            <td><?php echo $row['ssleeve'] ?></td>
            <td><?php echo $row['sinstructions'] ?></td>
            <td><?php echo $row['scustom'] ?></td>
            <td><?php echo $row['spriority'] ?></td>
            <td><?php echo $row['sddate'] ?></td>
            <td>
                <?php 
                $images = explode(',', $row['simg']); 
                foreach($images as $img){
                    if(trim($img) != ""){
                        echo '<a target="_blank" href="'.$img.'"><img src="'.$img.'" width="100" height="100"></a> ';
                    }
                }
                ?>
            </td>
            <td><?php echo $row['sstatus'] ?></td>
            <td>
                <?php
                if (strtolower($row['sstatus']) == 'rejected') {
                    echo "Not Available";
                } elseif ($row['status'] == 'Paid') {
                    echo "Paid | <a href='receipt.php?order_id=".$row['sdid']."' target='_blank'>View Receipt</a>";
                } else {
                    ?>
                    <form action="payment.php" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $row['sdid']; ?>">
                        <input type="submit" value="Pay Now">
                    </form>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>