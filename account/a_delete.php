<?php
require('../dbcon.php');
$Users_ID=$_REQUEST['Users_ID'];
$sql = "DELETE FROM Users WHERE Users_ID=".$Users_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: account.php"); 
?>
