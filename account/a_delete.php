<?php
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "<script>
        alert('Public demo is read only. New, edit and delete are disabled.\\nPlease run the project locally to try full functionality.');
        window.location.replace('account.php');
    </script>";
    exit;
}
require('../dbcon.php');
$Users_ID=$_REQUEST['Users_ID'];
$sql = "DELETE FROM Users WHERE Users_ID=".$Users_ID.";"; 
$result = mysqli_query($conn,$sql) or die ("Delete failed.");
header("Location: account.php"); 
?>



