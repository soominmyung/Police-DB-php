<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include config file
require_once "../dbcon.php";

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ..\login.php");
  exit;
}

// Initialize post variables
$Offence_description = $Offence_maxFine = $Offence_maxPoints = "";
// Initialize error message
$Ins_err = $Offence_description_err = $Offence_maxFine_err = $Offence_maxPoints_err = "";
// Status whether it is insert(0) or update(1)
$Status = 0;
// if edit, receive chosen Offence ID from the people page
if (isset($_GET["Offence_ID"]) && $_GET["Status"] == 1) {
  $Edit_Offence_ID = $_GET["Offence_ID"];
  $Status = $_GET["Status"];
  // find other values using the Offence_ID
  $Select_query = "SELECT * FROM Offence WHERE Offence_ID = '$Edit_Offence_ID'";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt == 1) {
      $row = mysqli_fetch_assoc($Select_result);
      $Offence_description = $row['Offence_description'];
      $Offence_maxFine = $row['Offence_maxFine'];
      $Offence_maxPoints = $row['Offence_maxPoints'];
    } else {
      $Message = "Failed to load edit page. Please contact administrator.";
      echo "<script>alert('$Message');</script>";
      echo "<script>window.location.replace('offence.php')</script>";
    }
  }
}

// Processing form data when form is submitted
if (isset($_POST['post']) && $_POST['post'] == 1) {
  // Store post variables
  $Offence_description = trim($_POST['Offence_description']);
  $Offence_maxFine = trim($_POST['Offence_maxFine']);
  $Offence_maxPoints = trim($_POST['Offence_maxPoints']);


  // check if trimmed input values are empty
  if ($Offence_description == "") {
    $Offence_description_err = "Please input description name.";
  }
  if ($Offence_maxFine == "") {
    $Offence_maxFine_err = "Please input max fine.";
  }
  if ($Offence_maxPoints == "") {
    $Offence_maxPoints_err = "Please input max points.";
  } else {
    // check if the offence_description is already in the database
    $Select_query = "SELECT * FROM Offence WHERE Offence_description = '$Offence_description'";
    if ($Select_result = mysqli_query($conn, $Select_query)) {
      $row_cnt = mysqli_num_rows($Select_result);
      if ($row_cnt > 0) {
        if ($Status == 0) {
          $Offence_description_err = "The offence description is already exists in the database.";
        } elseif ($Status == 1) {
          $row = mysqli_fetch_assoc($Select_result);
          $Offence_ID = $row['Offence_ID'];
          if ($Edit_Offence_ID != $Offence_ID) {
            $Offence_description_err = "The offence description is already exists in the database.";
          }
        }
      }
    }
  }

  // If everything is okay, insert into or update offence table
  if ($Offence_description_err == "" && $Offence_maxFine_err == "" && $Offence_maxPoints_err == "") {
    // if insert
    if ($Status == 0) {
      $sql = "INSERT INTO Offence (Offence_description,Offence_maxFine,Offence_maxPoints) 
        VALUES ('$Offence_description','$Offence_maxFine','$Offence_maxPoints')";
      if (mysqli_query($conn, $sql)) {
        $Message = "New Record Inserted Successfully.";
      } else {
        $Message = "Insert failed. Please contact administrator.";
      }
      
      // if update
    } elseif ($Status == 1) {
      $sql = "UPDATE Offence SET Offence_description = '$Offence_description', Offence_maxFine = '$Offence_maxFine',
    Offence_maxPoints = '$Offence_maxPoints' WHERE Offence_ID = '$Edit_Offence_ID'";
      if (mysqli_query($conn, $sql)) {
        $Message = "Updated Successfully.";
      } else {
        $Message = "Update failed. Please contact administrator.";
      }
    }
    // alert success or error message
    echo "<script>alert('$Message');</script>";
    // Reset the page
    echo "<script>window.location.replace('offence.php')</script>";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title><?php echo $Status == 1 ? "Update Offence" : "Insert New Offence" ?></title>
  <link rel="stylesheet" href="..\css\styles.css">
</head>

<body>
  <div class="wrapper">
    <!-- header and sidebar -->
    <div class="header">
      <div class="header-left"><a href="..\main.php"><img src='..\images\logo.jpg' /></a></div>
      <div class="header-center">
        <p>DIS Police DB System</p>
      </div>
      <div class="header-right">
        <h2 id="welcome"><?php echo $_SESSION["isadmin"] == true ? "[Admin]" : ""; ?>
          Hello, <b><?php echo htmlspecialchars($_SESSION["Users_name"]); ?></b>.</h2>
        <a id="change_password" href="..\change_password.php" target="_self">Change password</a>
        <a id="logout" href="..\logout.php" class="btn btn-danger">Log out</a>
      </div>
      <div class="borderline"></div>
    </div>

    <div class="sidenav">
      <a href="..\main.php" target="_self">Home</a>
      <a href="..\people\people.php" target="_self">People</a>
      <a href="..\vehicle\vehicle.php" target="_self">Vehicle</a>
      <a href="..\report\report.php" target="_self">Report</a>
      <a href="..\offence\offence.php" target="_self">Offence</a>
      <a href="..\fines\fines.php" target="_self">Fines</a>
      <?php
      $isadmin = $_SESSION["isadmin"];
      if ($isadmin == true) {
        echo "<a href='..\account\account.php' target='_self'> Manage<br> account</a>";
      }
      ?>
    </div>

    <div class="content">
      <!-- insert or update form -->
      <div class="form">
        <div>
          <h1><?php echo $Status == 1 ? "Update Offence" : "Insert New Offence" ?></h1>
          <p style="color:#FF0000;"><?php echo $Ins_err; ?></p>
          <form name="form" method="post" action="" autocomplete="off">
            <input class="o_ins_inp" type="hidden" name="post" value="1" />
            <label>Description</label>
            <p><input class="o_ins_inp" type="text" name="Offence_description" placeholder="Enter Description" value="<?php echo $Offence_description; ?>" required maxlength="40" oninvalid="this.setCustomValidity('Please enter description')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Offence_description_err; ?></p>
            <label>Max fine</label>
            <p><input class="o_ins_inp" type="number" name="Offence_maxFine" placeholder="Enter Max Fine" value="<?php echo $Offence_maxFine; ?>" required min="0" max="2000000000" oninvalid="this.setCustomValidity('Please enter max fine (0-2000000000)')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Offence_maxFine_err; ?></p>
            <label>Max points</label>
            <p><input class="o_ins_inp" type="number" name="Offence_maxPoints" placeholder="Enter Max Points" value="<?php echo $Offence_maxPoints; ?>" required min="0" max="2000000000" oninvalid="this.setCustomValidity('Please enter max points (0-2000000000)')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Offence_maxPoints_err; ?></p>
            <!-- 'Submit' button -->
            <p><input type="submit" value="<?php echo $Status == 1 ? "Update" : "Submit" ?>">
              <a class="cancel_btn" href="offence.php">Cancel</a>
            </p>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>


</html>
