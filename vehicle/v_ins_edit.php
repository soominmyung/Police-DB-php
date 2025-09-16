<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && getenv('DEMO_MODE') === '1'
    && isset($_POST['post']) && $_POST['post'] == 1) {
    echo "<script>alert('Public demo is read only. Creating/updating vehicles is disabled.');</script>";
    echo "<script>window.location.replace('vehicle.php')</script>";
    exit;
}
// Include config file
require_once "../dbcon.php";

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ..\login.php");
  exit;
}

// Initialize post variables for input values
$Vehicle_type = $Vehicle_colour = $Vehicle_licence = $People_name = $People_address = $People_licence = $Ex_p_licence = "";
// Initialize error messages
$Vehicle_type_err = $Vehicle_colour_err = $Vehicle_licence_err
  = $People_name_err = $People_address_err = $People_licence_err = "";
// Initialize chosen owner status
$P_status = "";
// Status whether it is insert(0) or update(1)
$Status = 0;

// if edit, receive chosen Vehicle ID from the vehicle page
if (isset($_GET["Vehicle_ID"]) && isset($_GET["Status"]) and $_GET["Status"] == 1) {
  $Edit_vehicle_ID = $_GET["Vehicle_ID"];
  $Status = $_GET["Status"];
  // find other values using the Vehicle_ID
  $Select_query = "SELECT * FROM Vehicle NATURAL LEFT JOIN Ownership NATURAL LEFT JOIN People WHERE Vehicle_ID = '$Edit_vehicle_ID';";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt == 1) {
      $row = mysqli_fetch_assoc($Select_result);
      $Vehicle_type = $row['Vehicle_type'];
      $Vehicle_colour = $row['Vehicle_colour'];
      $Vehicle_licence = $row['Vehicle_licence'];
      $Ex_people_ID = $row['People_ID'];
      if ($Ex_people_ID == "") {
        $P_status = "unknown";
      } else {
        $P_status = "existing_owner";
        $Ex_p_licence = $row['People_licence'];
      }
    } else {
      $Message = "Failed to load edit page. Please contact administrator.";
      echo "<script>alert('$Message');</script>";
      echo "<script>window.location.replace('vehicle.php')</script>";
    }
  }
}

// Processing form data when form is submitted
if (isset($_POST['post']) && $_POST['post'] == 1) {
  // prevent sessionStorage function below
  echo "<script>sessionStorage.clear();</script>";
  // store post values for inserting or keeping input values
  $Vehicle_type = trim($_POST['Vehicle_type']);
  $Vehicle_colour = trim($_POST['Vehicle_colour']);
  $Vehicle_licence = trim($_POST['Vehicle_licence']);
  $P_status = $_POST['p_radio_btn'];
  $Ex_p_licence = $_POST['Ex_p_licence'];
  $People_name = trim($_POST['People_name']);
  $People_address = trim($_POST['People_address']);
  $People_licence = trim($_POST['People_licence']);
  $Ex_people_ID = $_POST['Ex_people_ID'];

  // Check if vehicle basic info input values are empty after trimming
  if ($Vehicle_type == "") {
    $Vehicle_type_err = "Please input vehicle type.";
  }
  if ($Vehicle_colour == "") {
    $Vehicle_colour_err = "Please input vehicle colour.";
  }
  // Check if the same vehicle licence number is already in the database
  $Select_query = "SELECT * FROM Vehicle WHERE Vehicle_licence = '$Vehicle_licence'";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt > 0) {
      if ($Status == 0) {
        $Vehicle_licence_err = "The licence number is already exists in the database.";
      } elseif ($Status == 1) {
        $row = mysqli_fetch_assoc($Select_result);
        $Vehicle_ID = $row['Vehicle_ID'];
        // if update, check if the licence number is not the previous one
        if ($Edit_vehicle_ID != $Vehicle_ID) {
          $Vehicle_licence_err = "The licence number is already exists in the database.";
        }
      }
    }
  }

  // If everything is okay, insert input values into Vehicle table
  if ($Vehicle_type_err == "" && $Vehicle_colour_err == "" && $Vehicle_licence_err == "") {
    // if choose an existing owner
    if ($P_status == "existing_owner") {
      try {
        // use transaction to insert data into tables
        mysqli_autocommit($conn, false);

        // if insert into Vehicle
        if ($Status == 0) {
          $sql1 = mysqli_query($conn, "INSERT INTO Vehicle 
            (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
            ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
          $Vehicle_ID = mysqli_insert_id($conn);
          $sql2 = mysqli_query($conn, "INSERT INTO Ownership VALUES
          ('$Ex_people_ID', '$Vehicle_ID');");

          // if update Vehicle
        } else if ($Status == 1) {
          $sql1 = mysqli_query($conn, "UPDATE Vehicle SET Vehicle_type = '$Vehicle_type', Vehicle_colour = '$Vehicle_colour',
          Vehicle_licence = '$Vehicle_licence' WHERE Vehicle_ID = '$Edit_vehicle_ID'");
          // check if the vehicle was owned by someone else
          $Select_query = "SELECT * FROM Ownership WHERE Vehicle_ID = '$Edit_vehicle_ID'";
          if ($Select_result = mysqli_query($conn, $Select_query)) {
            $row_cnt = mysqli_num_rows($Select_result);
            if ($row_cnt > 0) {
              // if so, update the Ownership
              $sql2 = mysqli_query($conn, "UPDATE Ownership SET People_ID = '$Ex_people_ID' WHERE Vehicle_ID = '$Edit_vehicle_ID'");
            } else {
              // if not, insert new Ownership record
              $sql2 = mysqli_query($conn, "INSERT INTO Ownership VALUES ('$Ex_people_ID', '$Edit_vehicle_ID');");
            }
          }
        }

        // commit transaction
        mysqli_commit($conn);
        // alert message
        $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        echo "<script>alert('$Message');</script>";
        // go back to the previous page
        $Status == 0 ? $location = "v_ins_edit.php" : $location = "vehicle.php";
        echo "<script>window.location.replace('$location')</script>";
        // throw exception if transaction fails
      } catch (Exception $e) {
        mysqli_rollback($conn);
        $Message = 'An error occured during transaction. Rollback.';
        echo "<script>alert('$Message');</script>";
      }
      mysqli_close($conn);
      // if the owner is not on the database
    } elseif ($P_status == "new_owner") {
      // validate personal info
      if ($People_name == "") {
        $People_name_err = "Please input Personal name.";
      }
      if ($People_address == "") {
        $People_address_err = "Please input personal address.";
      }
      // check if the People_licence is already in the database
      $Select_query = "SELECT * FROM People WHERE People_licence = '$People_licence'";
      if ($Select_result = mysqli_query($conn, $Select_query)) {
        $row_cnt = mysqli_num_rows($Select_result);
        if ($row_cnt > 0) {
          $People_licence_err = "The licence number is already exists in the database.";
        }
      }

      if ($People_name_err == "" && $People_address_err == "" && $People_licence_err == "") {
        try {
          // use transaction to insert data into tables
          mysqli_autocommit($conn, false);

          // insert into People
          $sql1 = mysqli_query($conn, "INSERT INTO People (People_name,People_address,People_licence) 
            VALUES ('$People_name','$People_address','$People_licence')");
          $People_ID = mysqli_insert_id($conn);

          // if insert into Vehicle
          if ($Status == 0) {
            $sql2 = mysqli_query($conn, "INSERT INTO Vehicle 
            (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
            ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
            $Vehicle_ID = mysqli_insert_id($conn);
            $sql3 = mysqli_query($conn, "INSERT INTO Ownership VALUES
              ('$People_ID', '$Vehicle_ID');");

            // if update Vehicle
          } else if ($Status == 1) {
            $sql2 = mysqli_query($conn, "UPDATE Vehicle SET Vehicle_type = '$Vehicle_type', Vehicle_colour = '$Vehicle_colour',
              Vehicle_licence = '$Vehicle_licence' WHERE Vehicle_ID = '$Edit_vehicle_ID'");
            // check if the vehicle was owned by someone else
            $Select_query = "SELECT * FROM Ownership WHERE Vehicle_ID = '$Edit_vehicle_ID'";
            if ($Select_result = mysqli_query($conn, $Select_query)) {
              $row_cnt = mysqli_num_rows($Select_result);
              if ($row_cnt > 0) {
                // if so, update the Ownership
                $sql3 = mysqli_query($conn, "UPDATE Ownership SET People_ID = '$People_ID' WHERE Vehicle_ID = '$Edit_vehicle_ID'");
              } else {
                // if not, insert new Ownership record
                $sql3 = mysqli_query($conn, "INSERT INTO Ownership VALUES ('$People_ID', '$Edit_vehicle_ID');");
              }
            }
          }

          // commit transaction
          mysqli_commit($conn);
          // alert message
          $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
          echo "<script>alert('$Message');</script>";
          // go back to the previous page
          $Status == 0 ? $location = "v_ins_edit.php" : $location = "vehicle.php";
          echo "<script>window.location.replace('$location')</script>";

          // throw exception if transaction fails
        } catch (Exception $e) {
          mysqli_rollback($conn);
          $Message = 'An error occured during transaction. Rollback.';
          echo "<script>alert('$Message');</script>";
        }
        mysqli_close($conn);
      }
      // if the owner is unknown
    } else if ($P_status == "unknown") {
      // if insert into Vehicle
      try {
        // use transaction to insert data into tables
        mysqli_autocommit($conn, false);

        if ($Status == 0) {
          $sql = mysqli_query($conn, "INSERT INTO Vehicle 
            (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
            ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
          // if update Vehicle
        } else if ($Status == 1) {
          // update vehicle info
          $sql1 = mysqli_query($conn, "UPDATE Vehicle SET Vehicle_type = '$Vehicle_type', Vehicle_colour = '$Vehicle_colour',
            Vehicle_licence = '$Vehicle_licence' WHERE Vehicle_ID = '$Edit_vehicle_ID';");
          // delete ownership record
          $sql2 = mysqli_query($conn, "DELETE FROM Ownership WHERE Vehicle_ID = '$Edit_vehicle_ID';");
        }

        // commit transaction
        mysqli_commit($conn);
        // alert message
        $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        echo "<script>alert('$Message');</script>";
        // go back to the previous page
        $Status == 0 ? $location = "v_ins_edit.php" : $location = "vehicle.php";
        echo "<script>window.location.replace('$location')</script>";

        // throw exception if transaction fails
      } catch (Exception $e) {
        mysqli_rollback($conn);
        $Message = 'An error occured during transaction. Rollback.';
        echo "<script>alert('$Message');</script>";
      }
      mysqli_close($conn);
    }
  }
}

?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title><?php echo $Status == 1 ? "Update Vehicle Record" : "Insert New Vehicle Record" ?></title>
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
    <div class="content" id="v_ins">
      <!-- Vehicle insert form area -->
      <script>
        // function to change input form when selecting 'existing owner' or 'new owner'
        function contentsView(Owner_type) {

          var E_elements = document.getElementsByClassName('existing_owner');
          var N_elements = document.getElementsByClassName('new_owner_input');

          // if selected an existing owner
          if (Owner_type == 'existing_owner') {
            for (var i = 0, length = E_elements.length; i < length; i++) {
              E_elements[i].style.display = 'block';
            }
            document.getElementById('new_owner').style.display = 'none';
            document.getElementsByName('Ex_p_licence')[0].required = true;
            for (var i = 0, length = N_elements.length; i < length; i++) {
              N_elements[i].required = false;
            }

            // if selected new owner
          } else if (Owner_type == 'new_owner') {
            for (var i = 0, length = E_elements.length; i < length; i++) {
              E_elements[i].style.display = 'none';
            }
            document.getElementById('new_owner').style.display = 'block';
            document.getElementsByName('Ex_p_licence')[0].required = false;
            for (var i = 0, length = N_elements.length; i < length; i++) {
              N_elements[i].required = true;
            }

            // if selected unknown owner
          } else if (Owner_type == 'unknown') {
            for (var i = 0, length = E_elements.length; i < length; i++) {
              E_elements[i].style.display = 'none';
            }
            document.getElementById('new_owner').style.display = 'none';
            document.getElementsByName('Ex_p_licence')[0].required = false;
            for (var i = 0, length = N_elements.length; i < length; i++) {
              N_elements[i].required = false;
            }
          }
        };

        // Select function which sets form input values when certain owner is selected
        function Select(Selected_p_ID, Selected_p_licence) {
          document.getElementById("Ex_people_ID").value = Selected_p_ID;
          document.getElementsByName("Ex_p_licence")[0].value = Selected_p_licence;
        };

        // javascript function to temporarily save input values when pressing search button
        function SaveInput() {
          var saved_type = document.getElementsByName("Vehicle_type")[0].value;
          var saved_colour = document.getElementsByName("Vehicle_colour")[0].value;
          var saved_licence = document.getElementsByName("Vehicle_licence")[0].value;
          var saved_p_status = $('input[name=p_radio_btn]:checked').val();
          sessionStorage.setItem("saved_p_status", saved_p_status);
          sessionStorage.setItem("saved_type", saved_type);
          sessionStorage.setItem("saved_colour", saved_colour);
          sessionStorage.setItem("saved_licence", saved_licence);

          location.reload();
        }
      </script>

      <!-- input form -->
      <h2 id="v_title"><?php echo $Status == 1 ? "Update Vehicle Record" : "Insert New Vehicle Record" ?></h2>
      <div id="v_basic_info">
        <form name="form" method="post" action="" autocomplete="off">
          <input type="hidden" name="post" value="1" />
          <!-- input basic vehicle info -->
          <h2> 1. Input vehicle info </h2>
          <div class="input">
            <label>Type</label>
            <p><input class="v_type" id="Vehicle_type" type="text" name="Vehicle_type" placeholder="Enter type" value="<?php echo $Vehicle_type; ?>" required maxlength="20" oninvalid="this.setCustomValidity('Please enter vehicle type.')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Vehicle_type_err; ?></p>
          </div>
          <div class="input">
            <label>Colour</label>
            <p><input class="v_colour" id="Vehicle_colour" type="text" name="Vehicle_colour" placeholder="Enter colour without special characters" value="<?php echo $Vehicle_colour; ?>" required pattern="[a-zA-Z0-9- ]+" maxlength="20" oninvalid="this.setCustomValidity('Please enter colour without special characters')" oninput="setCustomValidity('')" /></p>
            <p class="msg"><?php echo $Vehicle_colour_err; ?></p>
          </div>
          <div class="input">
            <label>Vehicle licence</label>
            <p><input class="v_licence" id="Vehicle_licence" type="text" name="Vehicle_licence" placeholder="Enter 7-digit vehicle licence (letters and numbers)" value="<?php echo $Vehicle_licence; ?>" required pattern="[a-zA-Z0-9]{7}" maxlength="7" title="Please enter 7-digit licence using letters and numbers without spaces." oninvalid="this.setCustomValidity('Please enter 7-digit licence using letters and numbers without spaces.')" oninput="setCustomValidity(''); this.value = this.value.toUpperCase()" /></p>
            <p class="msg"><?php echo $Vehicle_licence_err; ?></p>
          </div>
      </div>

      <!-- owner status info -->
      <div id="v_owner_info">
        <div class="input">
          <h2> 2. Choose owner status </h2>
          <p><label class="existing_owner" style='display:none'>Selected Owner Licence </label></p>
          <p><input id="Ex_p_licence" class="existing_owner readonly exp_licence" placeholder="Choose an owner using the search bar" style='display:none' type="text" name="Ex_p_licence" value="<?php echo $Ex_p_licence ?>" oninvalid="this.setCustomValidity('Please choose an owner using search bar on the right side.')" oninput="setCustomValidity('')" /></p>
        </div>

        <!-- make the readonly class button read only -->
        <script>
          $(".readonly").keydown(function(e) {
            e.preventDefault();
          });
        </script>

        <!-- raidio button to choose the owner is already in the database or not -->
        <div class='radio_btn'>
          <p><input type="radio" name="p_radio_btn" value="existing_owner" onclick="contentsView('existing_owner');" required />Person already in the database</p>
          <p><input type="radio" name="p_radio_btn" value="new_owner" onclick="contentsView('new_owner');" />Person not in the database</p>
          <p><input type="radio" name="p_radio_btn" value="unknown" onclick="contentsView('unknown');" />Unknown</p>
        </div>
      </div>
      <div class="third">
        <!-- input information of new owner only visible when 'new owner' is chosen -->
        <div class="form" id='new_owner' style='display:none'>
          <h2>3. Input info of new owner</h2>
          <label>Name</label>
          <p><input class='new_owner_input p_name' type="text" name="People_name" placeholder="Enter name (letters and spaces)" value="<?php echo $People_name; ?>" pattern="[A-Za-z ]+" maxlength="40" oninvalid="this.setCustomValidity('Please enter name using only letters(a-Z) and spaces.')" oninput="setCustomValidity('')" /></p>
          <p class="msg"><?php echo $People_name_err; ?></p>
          <label>Address</label>
          <p><textarea class='new_owner_input p_address' rows="4" cols="50" name="People_address" placeholder="Enter address" maxlength="100" oninvalid="this.setCustomValidity('Please enter address.')" oninput="setCustomValidity('')"><?php echo $People_address; ?></textarea></p>
          <p class="msg"><?php echo $People_address_err; ?></p>
          <label>Licence</label>
          <p><input class='new_owner_input p_licence' type="text" name="People_licence" placeholder="Enter 16-digit licence (letters and numbers without spaces)" value="<?php echo $People_licence; ?>" pattern="[a-zA-Z0-9]{16}" maxlength="16" title='Please enter 16-digit licence using letters and numbers without spaces.' oninvalid="this.setCustomValidity('Please enter 16-digit licence using letters and numbers without spaces.')" oninput="setCustomValidity(''); this.value = this.value.toUpperCase()" /></p>
          <p class="msg"><?php echo $People_licence_err; ?></p>
        </div>

        <!-- hidden input of the chosen existing owner's ID,
      which will be set automatically by choosing from the result table -->
        <p><input id="Ex_people_ID" type="hidden" name="Ex_people_ID" value="<?php echo $Ex_people_ID ?>" /></p>
      </div>
      <!-- submit and end of the form -->
      <p id="v_submit"><input class="submit_btn" type="submit" value="<?php echo $Status == 1 ? "Update" : "Submit" ?>">
        <a class="cancel_btn" href="vehicle.php">Cancel</a>
      </p>
      </p>
      </form>
      <div class="third">
        <!-- search bar form to input information of existing owner -->
        <div class="search_box existing_owner" style='display:none'>
          <h2>3. Search & Select existing owner</h2>
          <form name="v_ins_p_search" method="POST" onSubmit="SaveInput();" autocomplete="off">
            <select id="v_insert_slt" name="p_category">
              <option value="People_name">Name</option>
              <option value="People_licence">Licence number</option>
            </select>
            <input type="text" name="p_search" placeholder="Enter search keyword or just press search" size="40" /> <button class="search_btn"><i class="fa fa-search"></i></button>
            <input type="hidden" name="page" value=1 />
          </form><br>&nbsp;<br>&nbsp;<br>
        </div>
        <!-- existing owner search result table -->
        <div class="existing_owner search_result">
          <?php
          if (isset($_POST['p_search'])) {
            // pagination
            $per_page_record = 5;
            if (isset($_POST["page"])) {
              $page = intval($_POST["page"]);
            } else {
              $page = 1;
            }
            $start_from = ($page - 1) * $per_page_record;

            $p_search = trim($_POST["p_search"]);
            $p_category = $_POST["p_category"];

            // pagination total records
            $p_sql = "SELECT * FROM People where " . $p_category . " LIKE \"%" . $p_search . "%\";";
            $p_result = mysqli_query($conn, $p_sql);
            $total_records = mysqli_num_rows($p_result);

            // select query
            $sql = "SELECT * FROM People where " . $p_category . " LIKE \"%" . $p_search . "%\" LIMIT $start_from, $per_page_record;";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
              echo "<table>";  // start table
              echo "<tr><th>Name</th><th>Address</th><th>Licence</th><th>Select</th></tr>"; // table header

              $n = 0;
              while ($row = mysqli_fetch_assoc($result)) {
                if ($n == 0) {
                  $checked = "checked='checked'";
                  echo "<script>Select('" . $row["People_ID"] . "', '" . $row["People_licence"] . "')</script>";
                } else {
                  $checked = "";
                }
                echo "<tr>";
                echo "<td>" . $row["People_name"] . "</td>";
                echo "<td>" . $row["People_address"] . "</td>";
                echo "<td>" . $row["People_licence"] . "</td>";
                echo "<td><input type='radio' name='radio_btn' value='existing_owner'
              onclick='Select(\"" . $row["People_ID"] . "\", \"" . $row["People_licence"] . "\")' " . $checked . "/></td>";
                echo "</tr>";
                $n++;
              }
              echo "</table>";

              // pagination
              echo "<center><div class='pagination'>";
              $total_pages = ceil($total_records / $per_page_record);
              $pagLink = "";
              if ($page >= 2) {
                echo "<button onclick=\"Page_move('$p_category', '$p_search', " . ($page - 1) . ")\">Prev </button>";
              }

              for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                  $pagLink .= "<button class='active' onclick=\"Page_move('$p_category', '$p_search', $i)\">" . $i . "</button>";
                } else {
                  $pagLink .= "<button onclick=\"Page_move('$p_category', '$p_search', $i)\">" . $i . "</button>";
                }
              };

              echo $pagLink;

              if ($page < $total_pages) {
                echo "<button onclick=\"Page_move('$p_category', '$p_search', '" . ($page + 1) . "')\">Next</button>";
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
      // if there is a post value, make one of p_radio_btn clicked based on it
      var php_p_status = "<?php echo $P_status ?>";
      if (php_p_status !== "") {
        $("input[name=p_radio_btn][value=" + php_p_status + "]").prop('checked', true).trigger('click');
      }

      // if there is a sessionStorage value, make one of p_radio_btn clicked based on it
      if (sessionStorage.getItem("saved_p_status") != undefined && sessionStorage.getItem("saved_p_status") != "") {
        var saved_p_status = sessionStorage.getItem("saved_p_status");
        $("input[name=p_radio_btn][value=" + saved_p_status + "]").prop('checked', true).trigger('click');
      }

      // get temporary input values from sessionStorage saved by SaveInput function
      if (sessionStorage.getItem("saved_type") != undefined && sessionStorage.getItem("saved_type") != "") {
        document.getElementById("Vehicle_type").value = sessionStorage.getItem("saved_type");
      }
      if (sessionStorage.getItem("saved_colour") != undefined && sessionStorage.getItem("saved_colour") != "") {
        document.getElementById("Vehicle_colour").value = sessionStorage.getItem("saved_colour");
      }
      if (sessionStorage.getItem("saved_licence") != undefined && sessionStorage.getItem("saved_licence") != "") {
        document.getElementById("Vehicle_licence").value = sessionStorage.getItem("saved_licence");
      }

      sessionStorage.clear();

      // pagination javascript function
      function Page_move(p_category, p_search, page) {
        var form = document.v_ins_p_search;
        form.p_category.value = p_category;
        form.p_search.value = p_search;
        form.page.value = page;
        SaveInput();
        form.submit();
      }
    </script>
  </div>
  </div>
</body>


</html>


