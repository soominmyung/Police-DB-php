<?php
require_once "../dbcon.php";
$Offence_ID=$_REQUEST['Offence_ID'];
$sql = "DELETE FROM Offence WHERE Offence_ID=".$Offence_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: offence.php"); 
?>
