<?php
require('../dbcon.php');
$Incident_ID=$_REQUEST['Incident_ID'];
$sql = "DELETE FROM Incident WHERE Incident_ID=".$Incident_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: report.php"); 
?>
