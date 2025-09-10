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
$Incident_ID = $Max_amount = $Max_points = $Fine_amount = $Fine_points = "";
// Initialize error messages
$Existing_select_err = $Fine_amount_err = $Fine_points_err = $Incident_ID_err = "";
// Status whether it is insert(0) or update(1)
$Status = 0;

// if edit, receive chosen Fine ID from the fines page
if (isset($_GET["Fine_ID"]) && isset($_GET["Status"]) and $_GET["Status"] == 1) {
  $Edit_fine_ID = $_GET["Fine_ID"];
  $Status = $_GET["Status"];
  // find other values using the Fine_ID
  $Select_query = "SELECT * FROM Fines NATURAL LEFT JOIN Incident NATURAL LEFT JOIN Offence WHERE Fine_ID = '$Edit_fine_ID';";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt == 1) {
      $row = mysqli_fetch_assoc($Select_result);
      $Fine_amount = trim($row['Fine_amount']);
      $Fine_points = trim($row['Fine_points']);
      $Max_amount = $row['Offence_maxFine'];
      $Max_points = $row['Offence_maxPoints'];
      $Incident_ID = $row['Incident_ID'];
    } else {
      $Message = "Failed to load edit page. Please contact administrator.";
      echo "<script>alert('$Message');</script>";
      echo "<script>window.location.replace('fines.php')</script>";
    }
  }
}

// Processing form data when form is submitted
if (isset($_POST['new']) && $_POST['new'] == 1) {
  // store post values for inserting or keeping input values
  $Fine_amount = trim($_POST['Fine_amount']);
  $Fine_points = trim($_POST['Fine_points']);
  $Max_amount = $_POST['Max_amount'];
  $Max_points = $_POST['Max_points'];
  $Incident_ID = $_POST['Incident_ID'];

  // Validate input values
  if ($Fine_amount == "") {
    $Fine_amount_err = "Please input fine amount.";
  } elseif ($Fine_amount > $Max_amount) {
    $Fine_amount_err = "Fine amount can't be bigger than Max fine";
  }
  if ($Fine_points == "") {
    $Fine_points_err = "Please input fine points.";
  } elseif ($Fine_points > $Max_points) {
    $Fine_points_err = "Fine points can't be bigger than Max points";
  }
  // Check if the incident has already been fined
  $Select_query = "SELECT * FROM Fines WHERE Incident_ID = '$Incident_ID'";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt > 0) {
      if ($Status == 0) {
        $Incident_ID_err = "The fine record for chosen incident is already exists in the database.";
      } elseif ($Status == 1) {
        $row = mysqli_fetch_assoc($Select_result);
        $Fine_ID = $row['Fine_ID'];
        if ($Edit_fine_ID != $Fine_ID) {
          $Incident_ID_err = "The fine record for chosen incident is already exists in the database.";
        }
      }
    }
  }

  if ($Incident_ID_err == "" && $Fine_amount_err == "" && $Fine_points_err == "") {
    // if insert into fines
    if ($Status == 0) {
      $sql = "INSERT INTO Fines (Fine_amount, Fine_points, Incident_ID) 
      VALUES ('$Fine_amount','$Fine_points','$Incident_ID')";
      // if update fines
    } else if ($Status == 1) {
      $sql = "UPDATE Fines SET Fine_amount = '$Fine_amount', Fine_points = '$Fine_points',
            Incident_ID = '$Incident_ID' WHERE Fine_ID = '$Edit_fine_ID';";
    }
    if (mysqli_query($conn, $sql)) {
      $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
    } else {
      $Message = "Update failed. Please contact administrator.";
    }
    // alert the success message
    echo "<script>alert('$Message');</script>";
    // Reset the page
    echo $Status == 0 ? "<script>window.location.replace('f_ins_edit.php')</script>" : "<script>window.location.replace('fines.php')</script>";
  }
}
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title><?php echo $Status == 1 ? "Update Fine Record" : "Insert New Fine Record" ?></title>
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
    <div class="content" id="f_ins">

      <script>
      // Select function which sets form input values when certain incident is selected
        function Select(Selected_I_ID, Max_amount, Max_points) {
          document.getElementById("I_is_Selected").value = 1;
          document.getElementById("Incident_ID").value = Selected_I_ID;
          document.getElementsByName("Max_amount")[0].value = Max_amount;
          document.getElementsByName("Max_points")[0].value = Max_points;
        };

      // SaveInput function which temporarily saves the input values when incident search button is pressed
        function SaveInput() {
          var saved_amount = document.getElementsByName("Fine_amount")[0].value;
          var saved_points = document.getElementsByName("Fine_points")[0].value;
          sessionStorage.setItem("saved_amount", saved_amount);
          sessionStorage.setItem("saved_points", saved_points);
          location.reload();
        }

      </script>
      <!-- Fine insert form area -->
      <!-- search bar form to choose incident -->
      <div class="form search_box">
        <h2><?php echo $Status == 1 ? "Update Fine Record" : "Insert New Fine Record" ?></h2>
        <h2>1. Search and Choose Report data</h2>
        <form name="f_ins_r_search" method="POST" onSubmit="SaveInput();">
          <select class="offence_type" name="R_category_2">
            <option value="All">Offence type - All</option>
            <?php
            $Off_sql = "SELECT * FROM Offence;";
            $Off_result = mysqli_query($conn, $Off_sql);
            if (mysqli_num_rows($Off_result) > 0) {
              while ($Off_row = mysqli_fetch_assoc($Off_result)) {
                echo "<option value='" . $Off_row["Offence_description"] . "'>" . $Off_row['Offence_description'] . "</option>";
              }
            }
            ?>
          </select><br>
          <p class="input_p">&nbsp;From&nbsp; <input type="date" name="from" placeholder="from" />
            &nbsp;To&nbsp; <input type="date" name="to" placeholder="to"></p>
          <p>
            <select name="R_category">
              <option value="Incident_ID">Incident ID</option>
              <option value="Vehicle_licence">Vehicle licence</option>
              <option value="Vehicle_colour">Vehicle colour</option>
              <option value="Vehicle_type">Vehicle type</option>
              <option value="People_name">Personal name</option>
              <option value="People_licence">Personal licence</option>
              <option value="Offence_description">Offence type</option>
            </select>
            <!-- search by offence type -->
            <input type="text" name="R_search" size="40" placeholder="Enter search keyword or just press search" />
            <input type="hidden" name="page" value="<?php echo isset($_POST['page']) ? $_POST['page'] : 1?>"/>
            <button class="search_btn"><i class="fa fa-search"></i></button>
          </p>
        </form>
      </div>

      <!-- input form -->
      <div class="input_form">
        <form method="post" action="" autocomplete="off">
          <input type="hidden" name="new" value="1" />

          <!-- input basic fine info -->
          <div class='f_basic_info'>
            <h2> 2. Input basic info </h2>
            <label>Selected Incident ID </label>
            <p><input class="f_input readonly" id="Incident_ID" type="text" name="Incident_ID" placeholder="Choose an incident on the left side" required value="<?php echo $Incident_ID; ?>" oninvalid="this.setCustomValidity('Please choose a record using search bar above.')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Incident_ID_err; ?></p>
            <label>Max fine amount </label>
            <p><input class="f_input readonly" id="Max_amount" type="number" name="Max_amount" placeholder="Choose an incident on the left side" required value="<?php echo $Max_amount; ?>" /></p>
            <label>Fine amount</label>
            <p class="msg"><?php echo $Fine_amount_err; ?></p>
            <p><input class="f_input" id="Fine_amount" type="number" name="Fine_amount" placeholder="Enter fine amount (< max amount)" value="<?php echo $Fine_amount; ?>" required min="0" oninvalid="this.setCustomValidity('Please input fine amount from 0 to max fine.')" oninput="setCustomValidity('')" /></p>
            <label>Max fine points </label>
            <p><input class="f_input readonly" id="Max_points" type="number" name="Max_points" placeholder="Choose an incident on the left side" required value="<?php echo $Max_points; ?>" /></p>
            <label>Fine points</label>
            <p class="msg"><?php echo $Fine_points_err; ?></p>
            <p><input class="f_input" id="Fine_points" type="number" name="Fine_points" placeholder="Enter fine points (< max points)" value="<?php echo $Fine_points; ?>" required min="0" oninvalid="this.setCustomValidity('Please input fine points from 0 to max points.')" oninput="setCustomValidity('')" /></p>
          </div>

          <!-- hidden input of the status whether the incident is selected(1) or not(0) -->
          <p><input id='I_is_Selected' type="hidden" name="I_is_Selected" value=0 /></p>

          <!-- submit and end of the form -->
          <hr>
          <p><input name="submit" type="submit" value="<?php echo $Status == 1 ? "Update" : "Submit" ?>" />
            <a class="cancel_btn" href="fines.php">Cancel</a></p>
        </form>
        <script>
          $(".readonly").keydown(function(e) {
            e.preventDefault();
          });
        </script>
      </div>

      <!-- existing incident result table -->
      <div class="search_result">
        <table class="search_table">

          <?php
          if (isset($_POST['R_search'])) {
            // pagination
            $per_page_record = 7;
            if (isset($_POST["page"])) {
              $page = intval($_POST["page"]);
            } else {
              $page = 1;
            }
            $start_from = ($page - 1) * $per_page_record;

            // get search words, categories, and dates
            $R_search = trim($_POST["R_search"]);
            $R_category = $_POST["R_category"];
            $R_category_2 = $_POST["R_category_2"];
            $From = $_POST['from'];

            // if 'to' date is not set, then set it to today's date
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
                WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Offence_description = '$R_category_2' ORDER BY Incident_ID ASC;";
              // select query
              $R_sql_1 = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence 
                WHERE " . $R_category . " LIKE \"%" . $R_search . "%\" AND Offence_description = '$R_category_2' ORDER BY Incident_ID ASC
                LIMIT $start_from, $per_page_record;";
            }

            $R_result = mysqli_query($conn, $R_sql);
            $total_records = mysqli_num_rows($R_result);

            $R_result_1 = mysqli_query($conn, $R_sql_1);

            if (mysqli_num_rows($R_result_1) > 0) {
              echo "<table>";  // start table
              echo "<tr><th>Incident ID</th><th>Date</th><th>Personal licence</th>
            <th>Vehicle licence</th><th>Offence type</th><th>Select</th></tr>"; // table header

              $n = 0;
              while ($R_row = mysqli_fetch_assoc($R_result_1)) {
                if ($n == 0) {
                  $checked = "checked='checked'";
                  echo "<script>Select('" . $R_row["Incident_ID"] . "', '" . $R_row["Offence_maxFine"] . "', '" . $R_row["Offence_maxPoints"] . "')</script>";
                } else {
                  $checked = "";
                }
                echo "<tr>";
                echo "<td>" . $R_row["Incident_ID"] . "</td>";
                echo "<td>" . $R_row["Incident_date"] . "</td>";
                echo "<td>" . $R_row["People_licence"] . "</td>";
                echo "<td>" . $R_row["Vehicle_licence"] . "</td>";
                echo "<td>" . $R_row["Offence_description"] . "</td>";
                echo "<td><input type='radio' name='radio_btn' value='Incident'
            onclick='Select(\"" . $R_row["Incident_ID"] . "\", \"" . $R_row["Offence_maxFine"] . "\", \"" . $R_row["Offence_maxPoints"] . "\")' " . $checked . "/></td>";
                echo "</tr>";
                $n++;
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
                echo "<button onclick=\"Page_move('$R_search', '$R_category', '$R_category_2', '$From', '$To', " . ($page + 1) . "')\">Next</button>";
              }
              echo "</div></center>";
            } else // if query result is empty 
            {
              echo "No result found.";
            }
          }
          ?>
        </table>

      </div>
      <!-- get temporary input values from sessionStorage saved by SaveInput function -->
      <script>
        if (sessionStorage.getItem("saved_amount") != undefined && sessionStorage.getItem("saved_amount") != "")
          document.getElementsByName("Fine_amount")[0].value = sessionStorage.getItem("saved_amount");
        if (sessionStorage.getItem("saved_points") != undefined && sessionStorage.getItem("saved_points") != "")
          document.getElementsByName("Fine_points")[0].value = sessionStorage.getItem("saved_points");
        sessionStorage.clear();

        // pagination javascript function
        function Page_move(R_search, R_category, R_category_2, From, To, page) {
          var form = document.f_ins_r_search;
          form.R_search.value = R_search;
          form.R_category.value = R_category;
          form.R_category_2.value = R_category_2;
          form.from.value = From;
          form.to.value = To;
          form.page.value = page;
          SaveInput();
          form.submit();
        }
      </script>
    </div>
  </div>
</body>


</html>