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
  <title>Search or Update Fine Record</title>
  <script src="..\script\jquery-3.5.1.min.js"></script>
  <link rel="stylesheet" href="..\css\styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
      <!-- fines search form -->
      <div class="search_box">
        <h2>Search or Update Fine Record</h2>
        <form name="F_search_form" method="POST" autocomplete="off">
          <p class="input_p">From&nbsp; <input type="date" name="from" placeholder="from" />
            &nbsp;To&nbsp; <input id="to_date" type="date" name="to" placeholder="to"></p>
          <script>
            // set max value of "to" date to today
            to_date.max = new Date().toISOString().split("T")[0];
          </script>
          <p class="input_p">Fine amount: &nbsp;<input type="number" name="min" placeholder="min" />
            &nbsp;To&nbsp; <input type="number" name="max" placeholder="max"></p>
          <p class="input_p">Fine Point: &nbsp;<input type="number" name="p_min" placeholder="min" />
            &nbsp;To&nbsp; <input type="number" name="p_max" placeholder="max"></p>

          <select id="f_select" name="F_category">
            <option value="fine_ID">Fine ID</option>
            <option value="Incident_ID">Incident ID</option>
            <option value="People_licence">People licence</option>
            <option value="Vehicle_licence">Vehicle licence</option>
            <option value="People_name">People name</option>
          </select>
          <input type="text" name="F_search" size="40" placeholder="Enter search keyword or just press search button" />
          <!-- pagination page number -->
          <input type="hidden" name="page" value=1 />
          <button class="search_btn"><i class="fa fa-search"></i></button>
        </form>
        <?php
        if ($isadmin == true) {
          echo "<button class='new_btn' onclick=\"window.location.href='f_ins_edit.php';\">new</button>";
        } ?>
      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          {
            window.location.href = "f_delete.php?Fine_ID=".concat(ID)
          }
        }

        // Edit function to open the edit page when Edit button pressed
        function Edit(ID) {
          // edit the chosen record using f_ins_edit.php file
          if (window.location.href = "f_ins_edit.php?Fine_ID=".concat(ID).concat("&&Status=1")) {} else {
            // alert error message if failed
            window.alert("An error has occured. Please contact administrator.");
          }
        }
      </script>

      <div class="container">

        <?php

        if (isset($_POST['F_search'])) {
          // pagination
          $per_page_record = 10;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }
          $start_from = ($page - 1) * $per_page_record;

          // get search words and category
          $F_search = trim($_POST["F_search"]);
          $F_category = $_POST["F_category"];

          // get date 'from' and 'to'
          // if 'to' date is not set, set it to today's date
          $From = $_POST['from'];
          if ($_POST['to'] != '' && $_POST['to'] != null) {
            $To = $_POST['to'];
          } else {
            $To = date("Y-m-d");
          }

          // get fine amount 'min' and 'max'
          // if 'max' is not set, set it to float max value
          $Min = $_POST['min'];
          if ($_POST['max'] != '' && $_POST['max'] != null) {
            $Max = $_POST['max'];
          } else {
            // $Max = PHP_FLOAT_MAX; if PHP 7.2+
            $Max = 1.7976931348623E+308;
          }

          // get fine points 'min(p_min)' and 'max(p_max)'
          // if 'p_max' is not set, set it to float max value
          $P_min = $_POST['p_min'];
          if ($_POST['p_max'] != '' && $_POST['p_max'] != null) {
            $P_max = $_POST['p_max'];
          } else {
            // $P_max = PHP_FLOAT_MAX; if PHP 7.2+
            $P_max = 1.7976931348623E+308;
          }

          // pagination total records
          $sql = "SELECT * FROM Fines NATURAL LEFT JOIN Incident NATURAL LEFT JOIN People NATURAL LEFT JOIN Vehicle 
            NATURAL LEFT JOIN Offence
            WHERE " . $F_category . " LIKE \"%" . $F_search . "%\" AND Incident_date BETWEEN '$From' AND '$To'
            AND Fine_amount BETWEEN '$Min' AND '$Max' AND Fine_points BETWEEN '$P_min' AND '$P_max' ORDER BY Fine_ID ASC;";
          $result = mysqli_query($conn, $sql);
          $total_records = mysqli_num_rows($result);

          // execute select query
          $sql_2 = "SELECT * FROM Fines NATURAL LEFT JOIN Incident NATURAL LEFT JOIN People NATURAL LEFT JOIN Vehicle 
            NATURAL LEFT JOIN Offence
            where " . $F_category . " LIKE \"%" . $F_search . "%\" AND Incident_date BETWEEN '$From' AND '$To'
            AND Fine_amount BETWEEN '$Min' AND '$Max' AND Fine_points BETWEEN '$P_min' AND '$P_max' ORDER BY Fine_ID ASC LIMIT $start_from, $per_page_record;";
          $result_2 = mysqli_query($conn, $sql_2);

          // select query output table along with delete and edit button
          if (mysqli_num_rows($result_2) > 0) {
            echo "<table>";  // start table
            // table header
            echo "<tr><th>Fine ID</th><th>Fine amount</th><th>Fine points</th><th>Personal licence</th><th>Vehicle licence</th>
          <th>Incident ID</th><th>Incident Date</th><th>Offence type</th><th>Max fine</th><th>Max points</th>";

            // if the account is administrator
            if ($isadmin == true) {
              echo "<th></th><th></th>";
            }
            echo "</tr>"; // table header

            while ($row = mysqli_fetch_assoc($result_2)) {
              echo "<tr>";
              echo "<td>" . $row["Fine_ID"] . "</td>";
              echo "<td>" . $row["Fine_amount"] . "</td>";
              echo "<td>" . $row["Fine_points"] . "</td>";
              echo "<td>" . $row["People_licence"] . "</td>";
              echo "<td>" . $row["Vehicle_licence"] . "</td>";
              echo "<td>" . $row["Incident_ID"] . "</td>";
              echo "<td>" . $row["Incident_date"] . "</td>";
              echo "<td>" . $row["Offence_description"] . "</td>";
              echo "<td>" . $row["Offence_maxFine"] . "</td>";
              echo "<td>" . $row["Offence_maxPoints"] . "</td>";

              // if the account is administrator
              if ($isadmin == true) {
                // Delete button executes JavaScript confirmDelete          
                echo "<td><button onclick='confirmDelete(" . $row["Fine_ID"] . ")'>Delete</button></td>";
                // Edit button executes JavaScript Edit
                echo "<td><button onclick='Edit(" . $row["Fine_ID"] . ")'>Edit</button></td>";
              }
              echo "</tr>";
            }
            echo "</table>";

            // pagination
            echo "<center><div class='pagination'>";
            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";
            if ($page >= 2) {
              echo "<button onclick=\"Page_move('$F_search', '$F_category', '$From', '$To', '$Min', '$Max', '$P_min', '$P_max', " . ($page - 1) . ")\">Prev </button>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
              if ($i == $page) {
                $pagLink .= "<button class='active' onclick=\"Page_move('$F_search', '$F_category', '$From', '$To', '$Min', '$Max', '$P_min', '$P_max', $i)\">" . $i . "</button>";
              } else {
                $pagLink .= "<button onclick=\"Page_move('$F_search', '$F_category', '$From', '$To', '$Min', '$Max', '$P_min', '$P_max', $i)\">" . $i . "</button>";
              }
            };

            echo $pagLink;

            if ($page < $total_pages) {
              echo "<button onclick=\"Page_move('$F_search', '$F_category', '$From', '$To', '$Min', '$Max', '$P_min', '$P_max', '" . ($page + 1) . "')\">Next</button>";
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
    function Page_move(F_search, F_category, From, To, Min, Max, P_min, P_max, page) {
      var form = document.F_search_form;
      form.F_search.value = F_search;
      form.F_category.value = F_category;
      form.from.value = From;
      form.to.value = To;
      form.min.value = Min;
      form.max.value = Max;
      form.p_min.value = P_min;
      form.p_max.value = P_max;
      form.page.value = page;
      form.submit();
    }
  </script>
</body>


</html>

