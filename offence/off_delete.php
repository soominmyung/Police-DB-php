<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "Public demo is read only. New, edit and delete are disabled. "
       . "Please run the project locally to try full functionality.";
    echo "<script>window.location.replace('offence.php')</script>";
    exit;
}
require_once "../dbcon.php";
$Offence_ID=$_REQUEST['Offence_ID'];
$sql = "DELETE FROM Offence WHERE Offence_ID=".$Offence_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: offence.php"); 
?>


