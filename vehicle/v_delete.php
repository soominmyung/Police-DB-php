<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "<script>
        alert('Public demo is read only. New, edit and delete are disabled.\\nPlease run the project locally to try full functionality.');
        </script>";
    echo "<script>window.location.replace('vehicle.php')</script>";
    exit;
}
require('../dbcon.php');
$Vehicle_ID=$_REQUEST['Vehicle_ID'];
$sql = "DELETE FROM Vehicle WHERE Vehicle_ID=".$Vehicle_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: vehicle.php"); 
?>




