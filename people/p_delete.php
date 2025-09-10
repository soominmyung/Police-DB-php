<?php
require('../dbcon.php');
$People_ID=$_REQUEST['People_ID'];
$sql = "DELETE FROM People WHERE People_ID=".$People_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: people.php"); 
?>