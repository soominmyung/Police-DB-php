<?php
require('../dbcon.php');
$Vehicle_ID=$_REQUEST['Vehicle_ID'];
$sql = "DELETE FROM Vehicle WHERE Vehicle_ID=".$Vehicle_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: vehicle.php"); 
?>
