<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "<script>
        alert('Public demo is read only. New, edit and delete are disabled.\\nPlease run the project locally to try full functionality.');
        window.location.replace('fines.php');
    </script>";
    exit;
}
require('../dbcon.php');
$Fine_ID=$_REQUEST['Fine_ID'];
$sql = "DELETE FROM Fines WHERE Fine_ID=".$Fine_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: fines.php"); 
?>



