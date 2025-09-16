<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if the user is already logged in, if yes then redirect him to main page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: main.php");
    exit;
}

// Include config file
require_once "dbcon.php";

// Initialize POST values
$Users_name = $Users_password = "";
// Initialize error messages
$username_err = $password_err = "";

// Processing data when form is submitted
if (isset($_POST['login']) && $_POST['login'] == 1) {
    $Users_name = trim($_POST["Users_name"]);
    $Users_password = trim($_POST["Users_password"]);
    // Check if username is empty
    if (trim($Users_name) == "") {
        $username_err = "Please enter username.";
    }
    // Check if password is empty
    if (trim($Users_password) == "") {
        $password_err = "Please enter your password.";
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // check if the username exists in the database
        $Select_query = "SELECT * FROM Users WHERE Users_name = '$Users_name'";
        if ($Select_result = mysqli_query($conn, $Select_query)) {
            $row_cnt = mysqli_num_rows($Select_result);
            // if so, get the correct password to compare with the input password
            if ($row_cnt == 1) {
                $row = mysqli_fetch_assoc($Select_result);
                $correct_password = $row["Users_password"];
                $Users_name = $row["Users_name"];
                // if password is correct, begin a new session
                if ($Users_password == $correct_password) {
                    session_start();

                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["Users_name"] = $Users_name;

                    // check if the account is the administrator (haskins)
                    if (strcasecmp($Users_name, "haskins") == 0) {
                        $_SESSION["isadmin"] = true;
                    } else {
                        $_SESSION["isadmin"] = false;
                    }

                    // Redirect user to Main page
                    header("location: main.php");
                } else {
                    // Display an error message if password is not valid
                    $password_err = "Invalid password.";
                }
            } else {
                // Display an error message if username doesn't exist
                $username_err = "No account found with that username.";
            }
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
    <title>Login</title>
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
            </div>
            <div class="borderline"></div>
        </div>
        <div class="sidenav">
        </div>

        <!-- login form -->
        <div class="content" id="login_form">
            <div class="form">
                <h2>Login</h2>
                <p>Please fill in your ID and password to login.</p>
                <form action="" method="post" autocomplete="off">
                    <input type="hidden" name="login" value="1" />
                    <div class="input">
                        <p>
                            <input id="Username" type="text" name="Users_name" placeholder="Username" value="<?php echo $Users_name; ?>" required title="Please enter username" oninvalid="this.setCustomValidity('Please enter username')" oninput="setCustomValidity('')" />
                        </p>
                        <p><span class="msg"><?php echo $username_err; ?></span></p>
                    </div>
                    <div class="input">
                        <p>
                            <input id="Password" type="password" name="Users_password" placeholder="Password" required title="Please enter password" oninvalid="this.setCustomValidity('Please enter password')" oninput="setCustomValidity('')" />
                        </p>
                        <p><span class="msg"><?php echo $password_err; ?></span></p>
                    </div>
                    <P><input id="login" type="submit" value="Login"></p>
                </form>
                <div class="demo-accounts">
                    <h3>About this Web Application</h3>
                    <p>
                    This Police Database Management System was developed as a university coursework project.  
                    It demonstrates how PHP and MySQL can be used to build a full CRUD web application with:
                    </p>
                    <ul>
                        <li>Responsive forms that adapt to user input</li>
                        <li>Relational database with strong consistency checks</li>
                        <li>Role-based access control (different menus for admins vs. standard users)</li>
                        <li>Error handling that prevents invalid or inconsistent records</li>
                    </ul>
            
                    <h3>Demo Accounts</h3>
                    <ul>
                        <li><strong>Admin account</strong>: haskins / copper99</li>
                        <li><strong>Non-admin account</strong>: regan / plod123</li>
                        <li><strong>Non-admin account</strong>: carter / fuzz42</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
