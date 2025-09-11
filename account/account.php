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
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Search or Create Accounts</title>
  <script src="..\script\jquery-3.5.1.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
      <!-- search form -->
      <div class="search_box">
        <h2>Search or Create Accounts</h2>
        <form name="A_search_form" method="POST" autocomplete="off">
          <input type="text" name="A_search" size="40" placeholder="Enter account name or just press search button" />
          <!-- pagination page number -->
          <input type="hidden" name="page" value=1 />
          <button class="search_btn"><i class="fa fa-search"></i></button>
        </form>
        <button class="new_btn" onclick="window.location.href='a_insert.php';"> new </button>
      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          {
            if (window.location.href = "a_delete.php?Users_ID=".concat(ID)) {
              window.alert("Deleted successfully.");
            } else {
              window.alert("An error has occured.");
            }
          }
        }
      </script>

      <div class="container">

        <?php

        if (isset($_POST['A_search'])) {
          // pagination
          $per_page_record = 8;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }

          $start_from = ($page - 1) * $per_page_record;

          // get search words
          $A_search = trim($_POST["A_search"]);

          // pagination total records
          $sql = "SELECT * FROM Users where Users_name LIKE \"%" . $A_search . "%\";";
          $result = mysqli_query($conn, $sql);
          $total_records = mysqli_num_rows($result);

          // select query
          $sql_2 = "SELECT * FROM Users where Users_name LIKE \"%" . $A_search . "%\" LIMIT $start_from, $per_page_record;";
          $result_2 = mysqli_query($conn, $sql_2);

          if (mysqli_num_rows($result_2) > 0) {
            echo "<table>";  // start table
            echo "<tr><th>Account name</th><th></th></tr>"; // table header

            while ($row = mysqli_fetch_assoc($result_2)) {
              // output name and phone number as table row
              echo "<tr>";
              echo "<td>" . $row["Users_name"] . "</td>";

              // Delete button executes JavaScript confirmDelete          
              if ($row["Users_name"] != 'haskins') {
                echo "<td><button onclick='confirmDelete(" . $row["Users_ID"] . ")'>Delete</button></td>";
              } else {
                echo "<td></td>";
              }
              echo "</tr>";
            }
            echo "</table>";

            // pagination
            echo "<center><div class='pagination'>";
            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";
            if ($page >= 2) {
              echo "<button onclick=\"Page_move('$A_search', " . ($page - 1) . ")\">Prev </button>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
              if ($i == $page) {
                $pagLink .= "<button class='active' onclick=\"Page_move('$A_search', $i)\">" . $i . "</button>";
              } else {
                $pagLink .= "<button onclick=\"Page_move('$A_search', $i)\">" . $i . "</button>";
              }
            };

            echo $pagLink;

            if ($page < $total_pages) {
              echo "<button onclick=\"Page_move('$A_search','" . ($page + 1) . "')\">Next</button>";
            }
            echo "</div></center>";
          } else // if query result is empty 
          {
            echo "No result found.";
          }
        }

        ?>
      </div>
    </div>
  </div>
  <script>
    // pagination javascript function
    function Page_move(A_search, page) {
      var form = document.A_search_form;
      form.A_search.value = A_search;
      form.page.value = page;
      form.submit();
    }
  </script>
</body>


</html>
