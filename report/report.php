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
  <title>Search or Update Incident Record</title>
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
      <!-- searching form -->
      <div class="search_box">
        <h2>Search or Update Incident Record</h2>
        <form name="R_search_form" method="POST" autocomplete="off">
          <!-- search by offence type -->
          <p><select class="offence_type" name="R_category_2">
              <option value="All" selected="selected">Offence type - All</option>
              <?php
              $Off_sql = "SELECT * FROM Offence;";
              $Off_result = mysqli_query($conn, $Off_sql);
              if (mysqli_num_rows($Off_result) > 0) {
                while ($Off_row = mysqli_fetch_assoc($Off_result)) {
                  echo "<option value='" . $Off_row["Offence_description"] . "'>" . $Off_row['Offence_description'] . "</option>";
                }
              }
              ?>
            </select></p>
          <br>
          <p class=input_p>From <input type="date" name="from" placeholder="from" />
            To <input id="to_date" type="date" name="to" placeholder="to"></p>
          <script>
            // set max value of "to" date to today
            to_date.max = new Date().toISOString().split("T")[0];
          </script>
          <p>
            <select name="R_category">
              <option value="Incident_ID">Incident ID</option>
              <option value="Vehicle_licence">Vehicle licence</option>
              <option value="Vehicle_colour">Vehicle colour</option>
              <option value="Vehicle_type">Vehicle type</option>
              <option value="People_name">Owner name</option>
              <option value="People_licence">Owner licence</option>
            </select>
            <input type="text" name="R_search" size="40" placeholder="Enter search keyword or just press search button" />
            <!-- pagination page number -->
            <input type="hidden" name="page" value=1 />
            <button class="search_btn"><i class="fa fa-search"></i></button>
          </p>
        </form>
        <?php
        if ($isadmin == true) {
            echo '<button class="new_btn" onclick="window.location.href=\'r_ins_edit.php\';"> new </button>';
        }
        ?>
      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          { // delete the chosen record using r_delete.php file
            if (window.location.href = "r_delete.php?Incident_ID=".concat(ID)) {
              window.alert("Deleted successfully.");
            } else {
              window.alert("An error has occured.");
            }
          }
        }

        // Edit function to open the edit page when Edit button pressed
        function Edit(ID) {
          // edit the chosen record using r_ins_edit.php file
          if (window.location.href = "r_ins_edit.php?Incident_ID=".concat(ID).concat("&&Status=1")) {} else {
            // alert error message if failed
            window.alert("An error has occured. Please contact administrator.");
          }
        }
      </script>

      <!-- Search result table -->
      <div class="container">

        <?php

        if (isset($_POST['R_search'])) {
          // pagination
          $per_page_record = 8;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }
          $start_from = ($page - 1) * $per_page_record;

          // get search words, categories, and dates
          $R_search = trim($_POST["R_search"]);
          $R_category = $_POST["R_category"];
          $R_category_2 = $_POST["R_category_2"];
          $From = $_POST['from'];

          // if to date is not set, set it to today's date
          if ($_POST['to'] != '' && $_POST['to'] != null) {
            $To = $_POST['to'];
          } else {
            $To = date("Y-m-d");
          }

          if ($R_category_2 == 'All') {
            // pagination total records
            $R_sql = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence 
              WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Incident_date BETWEEN '$From' AND '$To' ORDER BY Incident_ID ASC;";
            // select query
            $R_sql_1 = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence 
              WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Incident_date BETWEEN '$From' AND '$To' ORDER BY Incident_ID ASC
              LIMIT $start_from, $per_page_record;";
          } else {
            // pagination total records
            $R_sql = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence 
              WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Incident_date BETWEEN '$From' AND '$To' AND
              Offence_description = '$R_category_2' ORDER BY Incident_ID ASC;";
            // select query
            $R_sql_1 = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence 
              WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Incident_date BETWEEN '$From' AND '$To' AND
              Offence_description = '$R_category_2' ORDER BY Incident_ID ASC
              LIMIT $start_from, $per_page_record;";
          }
          $R_result = mysqli_query($conn, $R_sql);
          $total_records = mysqli_num_rows($R_result);

          $R_result_1 = mysqli_query($conn, $R_sql_1);

          if (mysqli_num_rows($R_result_1) > 0) {
              echo "<table>";  // start table
              echo "<tr>";
              echo "<th class='inc_ID_th'>Incident ID</th>";
              echo "<th>Date</th>";
              echo "<th>Personal name</th>";
              echo "<th>Personal licence</th>";
              echo "<th>Vehicle licence</th>";
              echo "<th>Offence type</th>";
              echo "<th>Report</th>";
              echo "<th>Vehicle type</th>";
              echo "<th>Vehicle colour</th>";
              if ($isadmin == true) {
                  echo "<th class='btn'></th>";
                  echo "<th class='btn'></th>";
              }
              echo "</tr>";
        
            while ($R_row = mysqli_fetch_assoc($R_result_1)) {
                echo "<tr>";
                echo "<td>" . $R_row["Incident_ID"] . "</td>";
                echo "<td>" . $R_row["Incident_date"] . "</td>";
                echo "<td>" . $R_row["People_name"] . "</td>";
                echo "<td>" . $R_row["People_licence"] . "</td>";
                echo "<td>" . $R_row["Vehicle_licence"] . "</td>";
                echo "<td>" . $R_row["Offence_description"] . "</td>";
                echo "<td>" . $R_row["Incident_report"] . "</td>";
                echo "<td>" . $R_row["Vehicle_type"] . "</td>";
                echo "<td>" . $R_row["Vehicle_colour"] . "</td>";
        
                if ($isadmin == true) {
                    // Delete button executes JavaScript confirmDelete
                    echo "<td><button onclick='confirmDelete(" . $R_row["Incident_ID"] . ")'>Delete</button></td>";
                    // Edit button executes JavaScript Edit
                    echo "<td><button onclick='Edit(" . $R_row["Incident_ID"] . ")'>Edit</button></td>";
                }
        
                echo "</tr>";
            }
            echo "</table>";
            // pagination
            echo "<center><div class='pagination'>";
            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";
            if ($page >= 2) {
              echo "<button onclick=\"Page_move('$R_search', '$R_category', '$R_category_2', '$From', '$To', " . ($page - 1) . ")\">Prev </button>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
              if ($i == $page) {
                $pagLink .= "<button class='active' onclick=\"Page_move('$R_search', '$R_category', '$R_category_2', '$From', '$To', $i)\">" . $i . "</button>";
              } else {
                $pagLink .= "<button onclick=\"Page_move('$R_search', '$R_category', '$R_category_2', '$From', '$To', $i)\">" . $i . "</button>";
              }
            };

            echo $pagLink;

            if ($page < $total_pages) {
              echo "<button onclick=\"Page_move('$R_search', '$R_category', '$R_category_2', '$From', '$To', '" . ($page + 1) . "')\">Next</button>";
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
    function Page_move(R_search, R_category, R_category_2, From, To, page) {
      var form = document.R_search_form;
      form.R_search.value = R_search;
      form.R_category.value = R_category;
      form.R_category_2.value = R_category_2;
      form.from.value = From;
      form.to.value = To;
      form.page.value = page;
      form.submit();
    }
  </script>
</body>


</html>


