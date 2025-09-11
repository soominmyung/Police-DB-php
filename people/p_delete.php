<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "Public demo is read only. New, edit and delete are disabled. "
       . "Please run the project locally to try full functionality.";
    exit;
}
require('../dbcon.php');
$People_ID=$_REQUEST['People_ID'];
$sql = "DELETE FROM People WHERE People_ID=".$People_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: people.php"); 

?>
