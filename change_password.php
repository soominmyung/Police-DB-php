<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (getenv('DEMO_MODE') === '1') {
    http_response_code(200); 
    echo "Public demo is read only. New, edit and delete are disabled. "
       . "Please run the project locally to try full functionality.";
    exit;
}
// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "dbcon.php";

// Initialize post variables and error message
$new_password = $confirm_password = "";
$confirm_password_err = "";

// Processing data when form is submitted
if (isset($_POST['post']) && $_POST['post'] == 1) {

    $new_password = trim($_POST["new_password"]);

    // Validate confirm password
    if (trim($_POST["confirm_password"]) == "") {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($new_password != $confirm_password) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before updating the database
    if ($confirm_password_err == "") {
        $Users_name = $_SESSION["Users_name"];
        $sql = "UPDATE Users SET Users_password = '$new_password' WHERE Users_name = '$Users_name'";
        if ($result = mysqli_query($conn, $sql)) {
            $Message = "Updated password succesfully. Please log in again.";
            echo "<script>alert('$Message');</script>";
            // If password updated successfully, destroy the session and redirect to login page
            session_destroy();
            echo "<script>window.location.replace('login.php')</script>";
        } else {
            // alert error message if something went wrong with mysqli_query
            $Message = "Something went wrong. Please contact the administrator.";
            echo "<script>alert('$Message');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css\styles.css">
</head>

<body>
    <div class="wrapper">
        <!-- header and sidebar -->
        <div class="header">
            <div class="header-left"><a href="main.php"><img src='images\logo.jpg' /></a></div>
            <div class="header-center">
                <p>DIS Police DB System</p>
            </div>
            <div class="header-right">
                <h2 id="welcome"><?php echo $_SESSION["isadmin"] == true ? "(Admin)" : ""; ?>
                    Hello, <b><?php echo htmlspecialchars($_SESSION["Users_name"]); ?></b>.</h2>
                <a id="change_password" href="change_password.php" target="inner_page">Change password</a>
                <a id="logout" href="logout.php" class="btn btn-danger">Log out</a>
            </div>
            <div class="borderline"></div>
        </div>

        <div class="sidenav">
            <a href="main.php" target="_self">Home</a>
            <a href="people\people.php" target="_self">People</a>
            <a href="vehicle\vehicle.php" target="_self">Vehicle</a>
            <a href="report\report.php" target="_self">Report</a>
            <a href="offence\offence.php" target="_self">Offence</a>
            <a href="fines\fines.php" target="_self">Fines</a>
            <?php
            $isadmin = $_SESSION["isadmin"];
            if ($isadmin == true) {
                echo "<a href='account\account.php' target='_self'> Manage<br> account</a>";
            }
            ?>
        </div>
        <div class="content">
            <!-- password change form -->
            <!-- input pattern included -->
            <h2>Change Password</h2>
            <p>Please fill out this form to change your password.</p>
            <form action="" method="post" autocomplete="off">
                <input type="hidden" name="post" value="1" />
                <div class="input">
                    <label>New Password</label>
                    <p><input id="ch_password_new" type="password" name="new_password" placeholder="Enter new password 6-10 characters without spaces" pattern="[^' ']{6,10}" maxlength="10" title="Please enter password 6-10 characters without spaces" oninvalid="this.setCustomValidity('Please enter password 6-10 characters without spaces')" oninput="setCustomValidity('')" value="<?php echo $new_password; ?>" /></p>
                </div>
                <div class="input">
                    <label>Confirm Password</label>
                    <p><input id="ch_password_conf" type="password" name="confirm_password" placeholder="Please input your password again to confirm" maxlength="10" /></p>
                    <p><span class="msg"><?php echo $confirm_password_err; ?></span></p>
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit">
                    <a class="cancel_btn" href="main.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
