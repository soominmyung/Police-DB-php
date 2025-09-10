<?php
require('../dbcon.php');
$Fine_ID=$_REQUEST['Fine_ID'];
$sql = "DELETE FROM Fines WHERE Fine_ID=".$Fine_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: fines.php"); 
?>
