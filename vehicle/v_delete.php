<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "Public demo is read only. New, edit and delete are disabled. "
       . "Please run the project locally to try full functionality.";
    exit;
}
require('../dbcon.php');
$Vehicle_ID=$_REQUEST['Vehicle_ID'];
$sql = "DELETE FROM Vehicle WHERE Vehicle_ID=".$Vehicle_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: vehicle.php"); 
?>

