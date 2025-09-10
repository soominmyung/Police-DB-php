<?php       
    
    // MySQL database information       
    $servername = "mysql.cs.nott.ac.uk";
    $username = "psxsm27";
    $password = "Crystal!41";
    $dbname = "psxsm27";
  
    // Open the database connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    if(!$conn) {
       die ("Connection failed");
    }

?>