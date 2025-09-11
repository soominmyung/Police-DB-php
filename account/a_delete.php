<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "Public demo is read only. New, edit and delete are disabled. "
       . "Please run the project locally to try full functionality.";
    exit;
}
require('../dbcon.php');
$Users_ID=$_REQUEST['Users_ID'];
$sql = "DELETE FROM Users WHERE Users_ID=".$Users_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: account.php"); 
?>

