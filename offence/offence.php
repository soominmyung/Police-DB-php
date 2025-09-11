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
  <title>List of Offence</title>
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
      <div class="search_box">
        <h2>List of Offence </h2>

        <?php $isadmin = $_SESSION["isadmin"];
        if ($isadmin == true) {
          echo
          "<h3 class='msg'>Warning: Any kinds of modifications to the offence type record must be made in accordance with the law.<br>
        Also, any related fine or report records will be affected.</h3>";
        }
        ?>
        <!-- searching form -->
        <form name="O_search_form" method="POST" autocomplete="off">
          <p class="input_p">Max Fine: &nbsp;<input type="number" name="min" placeholder="min" />
            &nbsp;To&nbsp; <input type="number" name="max" placeholder="max"></p>
          <p class="input_p">Max Point: &nbsp;<input type="number" name="p_min" placeholder="min" />
            &nbsp;To&nbsp; <input type="number" name="p_max" placeholder="max"></p>
          <select id="O_select" name="O_category">
            <option value="Offence_description">Offence description</option>
          </select>
          <input type="text" name="O_search" size="40" placeholder="Enter search keyword or just press search button" />
          <!-- pagination page number -->
          <input type="hidden" name="page" value=1 />
          <button class="search_btn"><i class="fa fa-search"></i></button>
        </form>
        <?php
        if ($isadmin == true) {
          echo "<button class='new_btn' onclick=\"window.location.href='off_ins_edit.php';\">new</button>";
        } ?>

      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          { // delete the chosen record using off_delete.php file
            window.location.href = "off_delete.php?Offence_ID=".concat(ID)
          }
        }

        // Edit function to open the edit page when Edit button pressed
        function Edit(ID) {
          // edit the chosen record using off_ins_edit.php file
          if (window.location.href = "off_ins_edit.php?Offence_ID=".concat(ID).concat("&&Status=1")) {} else {
            // alert error message if failed
            window.alert("An error has occured. Please contact administrator.");
          }
        }
      </script>

      <!-- List of offence table -->
      <div class="container">

        <?php
        if (isset($_POST['O_search'])) {
          // pagination
          $per_page_record = 8;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }

          $start_from = ($page - 1) * $per_page_record;

          // get search words and category
          $O_search = trim($_POST["O_search"]);
          $O_category = $_POST["O_category"];

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
          $sql = "SELECT * FROM Offence
            WHERE " . $O_category . " LIKE \"%" . $O_search . "%\" AND Offence_maxFine BETWEEN '$Min' AND '$Max' AND Offence_maxPoints 
            BETWEEN '$P_min' AND '$P_max' ORDER BY Offence_ID ASC;";
          $result = mysqli_query($conn, $sql);
          $total_records = mysqli_num_rows($result);

          // select query
          $sql_2 = "SELECT * FROM Offence
            WHERE " . $O_category . " LIKE \"%" . $O_search . "%\" AND Offence_maxFine BETWEEN '$Min' AND '$Max' AND Offence_maxPoints 
            BETWEEN '$P_min' AND '$P_max' ORDER BY Offence_ID ASC LIMIT $start_from, $per_page_record;";
          $result_2 = mysqli_query($conn, $sql_2);

          if (mysqli_num_rows($result_2) > 0) {
            echo "<table>";  // start table
            // table header
            echo "<tr><th>description</th><th>Max Fine</th><th>Max Points</th>";
            // if the account is administrator
            if ($isadmin == true) {
              echo "<th></th><th></th>";
            }
            echo "</tr>"; // table header

            while ($row = mysqli_fetch_assoc($result_2)) {
              echo "<tr>";
              echo "<td>" . $row["Offence_description"] . "</td>";
              echo "<td>" . $row["Offence_maxFine"] . "</td>";
              echo "<td>" . $row["Offence_maxPoints"] . "</td>";

              // if the account is administrator
              if ($isadmin == true) {
                // Delete button executes JavaScript confirmDelete     
                echo "<td><button onclick='confirmDelete(" . $row["Offence_ID"] . ")'>Delete</button></td>";
                // Edit button executes JavaScript Edit
                echo "<td><button onclick='Edit(" . $row["Offence_ID"] . ")'>Edit</button></td>";
              }
              echo "</tr>";
            }
            echo "</table>";

            // pagination
            echo "<center><div class='pagination'>";
            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";
            if ($page >= 2) {
              echo "<button onclick=\"Page_move('$O_category', '$O_search', '$Min', '$Max', '$P_min', '$P_max', " . ($page - 1) . ")\">Prev </button>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
              if ($i == $page) {
                $pagLink .= "<button class='active' onclick=\"Page_move('$O_category', '$O_search', '$Min', '$Max', '$P_min', '$P_max', $i)\">" . $i . "</button>";
              } else {
                $pagLink .= "<button onclick=\"Page_move('$O_category', '$O_search', '$Min', '$Max', '$P_min', '$P_max', $i)\">" . $i . "</button>";
              }
            };

            echo $pagLink;

            if ($page < $total_pages) {
              echo "<button onclick=\"Page_move('$O_category', '$O_search', '$Min', '$Max', '$P_min', '$P_max','" . ($page + 1) . "')\">Next</button>";
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
    function Page_move(O_category, O_search, Min, Max, P_min, P_max, page) {
      var form = document.O_search_form;
      form.O_category.value = O_category;
      form.O_search.value = O_search;
      form.min = Min;
      form.max = Max;
      form.p_min = P_min;
      form.p_max = P_max;
      form.page.value = page;
      form.submit();
    }
  </script>
</body>


</html>

