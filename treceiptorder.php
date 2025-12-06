<?php
session_start();
include('databasecon.php');
$db = new DatabaseCon();
$val = $_SESSION['uid'];
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $sql = "SELECT p.order_id, p.pdate AS p_date,  o.ostatus,u.dname,u.dtype,u.ddesc,u.dprice,u.dimg,             
    c.cname,c.address AS oadress,c.email AS customer_email,c.phone AS customer_phone,t.tname             
    FROM payment AS p INNER JOIN orderdesign AS o ON p.order_id = o.oid INNER JOIN upload AS u ON 
    o.did = u.did INNER JOIN creg AS c ON o.uid = c.cid INNER JOIN treg AS t ON u.uid = t.tid             
    WHERE p.order_id = '$order_id'";
    $res = $db->selectData($sql);
    if ($row = mysqli_fetch_array($res)) {
?>
<!DOCTYPE html>
<html>
<head><title>Payment Receipt</title></head>
<body>
    <center>
        <h2>Payment Receipt</h2>
        <table border="1" cellpadding="10">
            <tr><td>Order ID</td><td><?php echo $row['order_id']; ?></td></tr>
            <tr><td>Amount Paid</td><td>₹<?php echo number_format($row['dprice'], 2); ?></td></tr>
            <tr><td>Payment Date</td><td><?php echo $row['p_date']; ?></td></tr>
            <tr><td>Status</td><td><?php echo $row['ostatus']; ?></td></tr>
        </table>
        <a href="tailorhome.php">Back to Home</a>
    </center>
</body>
</html>
<?php
    } else {
        echo "<script>alert('Receipt not found.'); window.location='tailorhome.php';</script>";
    }
} else {
    echo "<script>alert('Invalid access.'); window.location='tailorhome.php';</script>";
}
?>