<?php
// Initialize the session
session_start();
// Include config file
require_once "../dbcon.php";

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ..\login.php");
    exit;
}

// Initialize post variables
$Users_name = $Users_password = $confirm_Users_password = "";
// Initialize error messages
$Users_name_err = $Users_password_err = $confirm_Users_password_err = "";

// Processing form data when form is submitted
if (isset($_POST['new']) && $_POST['new'] == 1) {

    // Validate username
    if (trim($_POST["Users_name"]) == "") {
        $Users_name_err = "Please enter a username.";
    } else {
        $Users_name = trim($_POST["Users_name"]);
        // Check if the username is already in the database
        $Select_query = "SELECT * FROM People WHERE Users_name = '$Users_name'";
        if ($Select_result = mysqli_query($conn, $Select_query)) {
            $row_cnt = mysqli_num_rows($Select_result);
            if ($row_cnt > 0) {
                $Users_name_err = "This username is already taken.";
            }
        }
    }

    // Validate password
    if (trim($_POST["Users_password"]) != trim($_POST["confirm_Users_password"])) {
        $confirm_Users_password_err = "Password did not match.";
    } else {
        $Users_password = trim($_POST["Users_password"]);
        $confirm_Users_password = trim($_POST["confirm_Users_password"]);
    }

    // If everything is okay, insert input values into Users table
    if ($Users_name_err == "" && $Users_password_err == "" && $confirm_Users_password_err == "") {

        // Prepare an insert statement
        $Ins_query = "INSERT INTO Users (Users_name, Users_password) VALUES ('$Users_name', '$Users_password')";
        mysqli_query($conn, $Ins_query) or die("An error has occured.");
        $Message = "New Record Inserted Successfully.";
        // alert the success message
        echo "<script>alert('$Message');</script>";
        // Reset the page
        echo "<script>window.location.replace('a_insert.php')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Create an account</title>
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
                <a id="change_password" href="..\change_password.php" target="_self">Change password</a>&nbsp;&nbsp;&nbsp;
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
            <!-- Account insert form -->
            <div class="form">
                <h2>Create an account</h2>
                <p>Please fill this form to create an account.</p>
                <form name="form" method="post" action="" autocomplete="off">
                    <input type="hidden" name="new" value="1" />
                    <div class="input">
                        <p><label>Username</label></P>
                        <p><input class="u_insert" type="text" name="Users_name" placeholder="Enter Username using letters and numbers" value="<?php echo $Users_name; ?>" pattern="[a-zA-Z0-9]+" maxlength="10" title="Please enter user name using characters(a-Z) and numbers without spaces." oninvalid="this.setCustomValidity('Please enter user name using characters(a-Z) and numbers without spaces.')" oninput="setCustomValidity('')"></p>
                        <p class="msg"><?php echo $Users_name_err; ?></p>
                    </div>
                    <div class="input">
                        <p><label>Password</label></p>
                        <p><input class="u_insert" type="password" name="Users_password" placeholder="Enter password 6-10 characters without spaces" value="<?php echo $Users_password; ?>" pattern="[^' ']{6,10}" maxlength="10" title="Please enter password 6-10 characters without spaces." oninvalid="this.setCustomValidity('Please enter password 6-10 characters without spaces.')" oninput="setCustomValidity('')"></p>
                        <p class="msg"><?php echo $Users_password_err; ?></p>
                    </div>
                    <div class="input">
                        <p><label>Confirm Password</label></p>
                        <p><input class="u_insert" type="password" name="confirm_Users_password" placeholder="Please input your password again to confirm" maxlength="10" value="<?php echo $confirm_Users_password; ?>"></p>
                        <p class="msg"><?php echo $confirm_Users_password_err; ?></p>
                    </div>
                    <div class="form-group">
                        <p><input type="submit" class="btn btn-primary" value="Submit">
                            <a class="cancel_btn" href="account.php">Cancel</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>