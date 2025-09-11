<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css\styles.css">
</head>

<body>
    <div class="wrapper">
        <!-- header and sidebar -->
        <div class="header">
            <!-- logo image -->
            <div class="header-left"><a href="main.php"><img src='images\logo.jpg' /></a></div>
            <!-- page title -->
            <div class="header-center">
                <p>DIS Police DB System</p>
            </div>

            <!-- greetings message with the account name, if the account is admin, "[Admin] account name" -->
            <div class="header-right">
                <h2 id="welcome"><?php echo $_SESSION["isadmin"] == true ? "[Admin]" : ""; ?>
                    Hello, <b><?php echo htmlspecialchars($_SESSION["Users_name"]); ?></b>.</h2>
                <!-- change password and logout button -->
                <a class="button" id="change_password" href="change_password.php" target="inner_page">Change password</a>
                <a class="button" id="logout" href="logout.php">Log out</a>
            </div>
            <div class="borderline"></div>
        </div>
        <!-- sidebar -->
        <div class="sidenav">
            <a href="main.php" target="_self">Home</a>
            <a href="people\people.php" target="_self">People</a>
            <a href="vehicle\vehicle.php" target="_self">Vehicle</a>
            <a href="report\report.php" target="_self">Report</a>
            <a href="offence\offence.php" target="_self">Offence</a>
            <a href="fines\fines.php" target="_self">Fines</a>

            <!-- "manage account" sidebar if the account is admin -->
            <?php
            $isadmin = $_SESSION["isadmin"];
            if ($isadmin == true) {
                echo "<a href='account\account.php' target='_self'> Manage<br> account</a>";
            }
            ?>
        </div>

        <!-- main page guide contents -->
        <div class="content" id="guide">
            <h2>DB Guide</h2>
            <br>
            <label> People </label>
            <p>- To search & edit or insert personal records (name, address, licence)</p>
            <label> Vehicle </label>
            <p>- To search & edit or insert vehicle records (type, colour, licence)</p>
            <label> Report </label>
            <p>- To search & edit or insert incident reports (date, engaged person, engaged vehicle, offence type, description)</p>
            <label> Offence </label>
            <p>- To search or insert/edit(admin only) type of offence (description, max fine, max point)</p>
            <label> Fines </label>
            <p>- To search or insert/edit(admin only) fine records (name, address, licence)</p>

            <?php $isadmin = $_SESSION["isadmin"];
            // additional guide contents about managing accounts if the account is admin
            if ($isadmin == true) {
                echo
                "<label> Manage account </label>
            <p>- To search or insert/delete accounts</p>";
            }
            ?>
            <br>
            <label class="msg"> Warning! </label>
            <p>- Any changes to certain record can affect other records.</p>
            <p>- For example, changes on a personal or vehicle record can affect report or fine records.</p>
            <p>- Therefore, please make sure to search and check other related records first.</p>

        </div>
    </div>
</body>

</html>
