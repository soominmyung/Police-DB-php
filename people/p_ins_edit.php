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
$People_name = $People_address = $People_licence = "";
// Initialize error messages
$People_name_err = $People_address_err = $People_licence_err = "";
// Status whether it is insert(0) or update(1)
$Status = 0;
// if edit, receive chosen People ID from the people page
if (isset($_GET["People_ID"]) && $_GET["Status"] == 1) {
  $Edit_people_ID = $_GET["People_ID"];
  $Status = $_GET["Status"];
  // find other values using the People ID
  $Select_query = "SELECT * FROM People WHERE People_ID = '$Edit_people_ID'";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt == 1) {
      $row = mysqli_fetch_assoc($Select_result);
      $People_name = $row['People_name'];
      $People_address = $row['People_address'];
      $People_licence = $row['People_licence'];
    } else {
      $Message = "Failed to load edit page. Please contact administrator.";
      echo "<script>alert('$Message');</script>";
      echo "<script>window.location.replace('people.php')</script>";
    }
  }
}

// Processing form data when form is submitted
if (isset($_POST['post']) && $_POST['post'] == 1) {
  // Store post variables
  $People_name = trim($_POST['People_name']);
  $People_address = trim($_POST['People_address']);
  $People_licence = trim($_POST['People_licence']);

  // Check if the same personal licence number is already in the database
  if ($People_name == "") {
    $People_name_err = "Please input Personal name.";
  }
  if ($People_address == "") {
    $People_address_err = "Please input personal address.";
  }
  if ($People_licence == "") {
    $People_licence_err = "Please input personal licence number.";
  } else {
    // check if the People_licence is already in the database
    $Select_query = "SELECT * FROM People WHERE People_licence = '$People_licence'";
    if ($Select_result = mysqli_query($conn, $Select_query)) {
      $row_cnt = mysqli_num_rows($Select_result);
      if ($row_cnt > 0) {
        if ($Status == 0) {
          $People_licence_err = "The licence number is already exists in the database.";
        } elseif ($Status == 1) {
          $row = mysqli_fetch_assoc($Select_result);
          $People_ID = $row['People_ID'];
          if ($Edit_people_ID != $People_ID) {
            $People_licence_err = "The licence number is already exists in the database.";
          }
        }
      }
    }
  }

  // If everything is okay, insert into or update people table
  if ($People_name_err == "" && $People_address_err == "" && $People_licence_err == "") {
    // if insert
    if ($Status == 0) {
      $sql = "INSERT INTO People (People_name, People_address, People_licence) 
        VALUES ('$People_name','$People_address','$People_licence')";
      if (mysqli_query($conn, $sql)) {
        $Message = "New Record Inserted Successfully.";
      } else {
        $Message = "Insert failed. Please contact administrator.";
      }
      // if update
    } elseif ($Status == 1) {
      $sql = "UPDATE People SET People_name = '$People_name', People_address = '$People_address',
        People_licence = '$People_licence' WHERE People_ID = '$Edit_people_ID'";
      if (mysqli_query($conn, $sql)) {
        $Message = "Updated Successfully.";
      } else {
        $Message = "Update failed. Please contact administrator.";
      }
    }
    // alert success or error message
    echo "<script>alert('$Message');</script>";
    // Reset the page
    echo "<script>window.location.replace('people.php')</script>";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title><?php echo $Status == 1 ? "Update Personal Record" : "Insert New Personal Record" ?></title>
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
        <h2><?php echo $Status == 1 ? "Update Personal Record" : "Insert New Personal Record" ?></h2>
        <form name="form" method="post" action="" autocomplete="off">
          <input type="hidden" name="post" value="1" />
          <div class="input">
            <label>Name</label>
            <p><input class="p_name" type="text" name="People_name" placeholder="Enter name (letters and spaces)" value="<?php echo $People_name; ?>" required pattern="[A-Za-z ]+" maxlength="40" oninvalid="this.setCustomValidity('Please enter name using only letters(a-Z) and spaces.')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $People_name_err; ?></p>
          </div>
          <div class="input">
            <label>Address</label>
            <p><textarea class="p_address" rows="4" cols="50" name="People_address" placeholder="Enter address" required maxlength="100" oninvalid="this.setCustomValidity('Please enter address.')" oninput="setCustomValidity('')"><?php echo $People_address; ?></textarea></p>
            <p class="msg"><?php echo $People_address_err; ?></p>
          </div>
          <div class="input">
            <label>Licence</label>
            <p><input class="p_licence" type="text" name="People_licence" placeholder="Enter 16-digit licence (letters and numbers without spaces)" value="<?php echo $People_licence; ?>" required pattern="[a-zA-Z0-9]{16}" maxlength="16" title='Please enter 16-digit licence using letters and numbers without spaces.' oninvalid="this.setCustomValidity('Please enter 16-digit licence using letters and numbers without spaces.')" oninput="setCustomValidity(''); this.value = this.value.toUpperCase()" /></p>
            <p class="msg"><?php echo $People_licence_err; ?></p>
          </div>
          <p><input type="submit" value="<?php echo $Status == 1 ? "Update" : "Submit" ?>">
            <a class="cancel_btn" href="people.php">Cancel</a></p>
          </p>
        </form>
      </div>
    </div>
  </div>
</body>


</html>
