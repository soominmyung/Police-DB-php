<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && getenv('DEMO_MODE') === '1'
    && isset($_POST['post']) && $_POST['post'] == 1) {
    echo "<script>alert('Public demo is read only. Creating/updating incidents is disabled.');</script>";
    echo "<script>window.location.replace('report.php')</script>";
    exit;
}
// Include config file
require_once "../dbcon.php";

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: ..\login.php");
  exit;
}

// Initialize post variables
$Incident_date = $Incident_report = $Offence_description = $Offence_ID =
  $Vehicle_type = $Vehicle_colour = $Vehicle_licence = $People_name = $People_address = $People_licence =
  $Ex_people_ID = $Ex_vehicle_ID = $Ex_p_licence = $Ex_v_licence = "";
// Initialize alert message
$Message = $Offence_description_err = $I_report_err = $People_name_err = $People_address_err = $People_licence_err =
  $Vehicle_type_err = $Vehicle_colour_err = $Vehicle_licence_err = "";
// Initialize chosen person and vehicle status
$P_status = $V_status = "";
// Status whether it is insert(0) or update(1)
$Status = 0;

// if edit, receive chosen Incident ID from the report page
if (isset($_GET["Incident_ID"]) && isset($_GET["Status"]) && $_GET["Status"] == 1) {
  $Edit_incident_ID = $_GET["Incident_ID"];
  $Status = $_GET["Status"];
  // find other values using the Incident ID
  $Select_query = "SELECT * FROM Incident NATURAL LEFT JOIN Vehicle NATURAL LEFT JOIN People NATURAL LEFT JOIN Offence WHERE Incident_ID = '$Edit_incident_ID';";
  if ($Select_result = mysqli_query($conn, $Select_query)) {
    $row_cnt = mysqli_num_rows($Select_result);
    if ($row_cnt == 1) {
      $row = mysqli_fetch_assoc($Select_result);
      // get previous vehicle status
      $Ex_vehicle_ID = $row['Vehicle_ID'];
      if ($Ex_vehicle_ID == "") {
        $V_status = "unknown_vehicle";
      } else {
        $V_status = "existing_vehicle";
        $Ex_v_licence = $row['Vehicle_licence'];
      }
      // get previous people status
      $Ex_people_ID = $row['People_ID'];
      if ($Ex_people_ID == "") {
        $P_status = "unknown_person";
      } else {
        $P_status = "existing_person";
        $Ex_p_licence = $row['People_licence'];
      }
      // get other previous information
      $Incident_date = $row['Incident_date'];
      $Incident_report = $row['Incident_report'];
      $Offence_ID = $row['Offence_ID'];
      $Offence_description = $row['Offence_description'];
    } else {
      $Message = "Failed to load edit page. Please contact administrator.";
      echo "<script>alert('$Message');</script>";
      echo "<script>window.location.replace('report.php')</script>";
    }
  }
}

// Processing form data when form is submitted
if (isset($_POST['post']) && $_POST['post'] == 1) {
  // prevent sessionStorage function below
  echo "<script>sessionStorage.clear();</script>";
  // store post values for inserting or keeping input values
  $Incident_date = trim($_POST['Incident_date']);
  $Incident_report = trim($_POST['Incident_report']);
  $Offence_description = trim($_POST['Offence_description']);
  // get Offence_ID by selected offence description
  $sql = "SELECT * FROM Offence WHERE Offence_description = '$Offence_description'";
  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $Offence_ID = $row['Offence_ID'];
  } else {
    $Offence_description_err = "Chosen offence type is not valid. Please contact the administrator.";
  }
  $Status = $_POST['Status'];
  $P_status = $_POST['p_radio_btn'];
  $V_status = $_POST['v_radio_btn'];
  $Ex_p_licence = $_POST['Ex_p_licence'];
  $Ex_v_licence = $_POST['Ex_v_licence'];
  $People_name = trim($_POST['People_name']);
  $People_address = trim($_POST['People_address']);
  $People_licence = trim($_POST['People_licence']);
  $Vehicle_type = trim($_POST['Vehicle_type']);
  $Vehicle_colour = trim($_POST['Vehicle_colour']);
  $Vehicle_licence = trim($_POST['Vehicle_licence']);
  $Ex_people_ID = $_POST['Ex_people_ID'];
  $Ex_vehicle_ID = $_POST['Ex_vehicle_ID'];

  // Check if incident description input value is empty after trimming
  if ($Incident_report == "") {
    $I_report_err = "Please input description.";
  }
  // If everything is okay, insert input values into Incident table
  if ($Offence_description_err == "" && $I_report_err == "") {
    // if choose an existing person
    if ($P_status == "existing_person") {

      // existing person, existing vehicle
      if ($V_status == "existing_vehicle") {
        // if insert into Incident
        if ($Status == 0) {
          $sql = "INSERT INTO Incident 
            (Vehicle_ID, People_ID, Incident_date, Incident_report, Offence_ID) VALUES
            ('$Ex_vehicle_ID', '$Ex_people_ID', '$Incident_date', '$Incident_report', '$Offence_ID');";

          // if update Incident
        } else if ($Status == 1) {
          $sql = "UPDATE Incident SET Vehicle_ID = '$Ex_vehicle_ID', People_ID = '$Ex_people_ID', 
            Incident_date = '$Incident_date', Incident_report = '$Incident_report', Offence_ID = '$Offence_ID'
            WHERE Incident_ID = '$Edit_incident_ID';";
        }
        if (mysqli_query($conn, $sql)) {
          $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        } else {
          $Status == 0 ? $Message = "Insert failed. Please contact administrator." : $Message = "Update failed. Please contact administrator.";
        }
        // alert the success message
        echo "<script>alert('$Message');</script>";
        // Reset the page
        echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

        // existing person, new vehicle
      } else if ($V_status == "new_vehicle") {

        // Check if trimmed vehicle basic info input values are empty
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
            $Vehicle_licence_err = "The vehicle licence is already exists in the database.";
          }
        }

        if ($Vehicle_type_err == "" && $Vehicle_colour_err == "" && $Vehicle_licence_err == "") {
          try {
            // use transaction to insert data into tables
            mysqli_autocommit($conn, false);

            // insert into Vehicle
            $V_ins_query = mysqli_query($conn, "INSERT INTO Vehicle 
              (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
              ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
            $Vehicle_ID = mysqli_insert_id($conn);

            // if insert into Incident
            if ($Status == 0) {
              $sql = mysqli_query($conn, "INSERT INTO Incident 
                (Vehicle_ID, People_ID, Incident_date, Incident_report, Offence_ID) VALUES ('$Vehicle_ID', '$Ex_people_ID', '$Incident_date', '$Incident_report', '$Offence_ID');");
              // if update Incident
            } else if ($Status == 1) {
              $sql = mysqli_query($conn, "UPDATE Incident SET
                Vehicle_ID = '$Vehicle_ID', People_ID = '$Ex_people_ID', Incident_date = '$Incident_date', 
                Incident_report = '$Incident_report', Offence_ID = '$Offence_ID' WHERE Incident_ID = '$Edit_incident_ID';");
            }

            // commit transaction
            mysqli_commit($conn);
            $Status == 0 ? $Message = 'Inserted successfully.' : $Message = 'Updated successfully.';
            echo "<script>alert('$Message');</script>";
            // Reset the page
            echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

            // throw exception if transaction fails
          } catch (Exception $e) {
            mysqli_rollback($conn);
            $Message = 'An error occured during transaction. Rollback.';
            echo "<script>alert('$Message');</script>";
          }

          mysqli_close($conn);
        }
        // existing person, unknown vehicle
      } else if ($V_status == "unknown_vehicle") {
        // if insert into Incident
        if ($Status == 0) {
          $sql = "INSERT INTO Incident (People_ID, Incident_date, Incident_report, Offence_ID) VALUES ($Ex_people_ID', '$Incident_date', '$Incident_report', '$Offence_ID');";
          // if update Incident
        } else if ($Status == 1) {
          $sql = "UPDATE Incident SET Vehicle_ID = NULL, 
            People_ID = '$Ex_people_ID', Incident_date = '$Incident_date', 
            Incident_report = '$Incident_report', Offence_ID = '$Offence_ID' WHERE Incident_ID = '$Edit_incident_ID';";
        }
        if (mysqli_query($conn, $sql)) {
          $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        } else {
          $Status == 0 ? $Message = "Insert failed. Please contact administrator." : $Message = "Update failed. Please contact administrator.";
        }
        // alert the success message
        echo "<script>alert('$Message');</script>";
        // Reset the page
        echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";
      }
      // if choose a new person
    } elseif ($P_status == "new_person") {
      // Check if the trimmed personal input values are empty
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
      // If everything is okay, insert input values into tables
      if ($People_name_err == "" && $People_address_err == "" && $People_licence_err == "") {
        // new person, existing vehicle
        if ($V_status == "existing_vehicle") {
          try {
            // use transaction to insert data into tables
            mysqli_autocommit($conn, false);

            // insert into People
            $P_ins_query = mysqli_query($conn, "INSERT INTO People (People_name,People_address,People_licence) 
              VALUES ('$People_name','$People_address','$People_licence')");
            $People_ID = mysqli_insert_id($conn);

            // if insert into Incident
            if ($Status == 0) {
              $sql = mysqli_query($conn, "INSERT INTO Incident 
                (Vehicle_ID, People_ID, Incident_date, Incident_report, Offence_ID) VALUES
                ('$Ex_vehicle_ID', '$People_ID', '$Incident_date', '$Incident_report', '$Offence_ID');");

              // if update Incident
            } else if ($Status == 1) {
              $sql = mysqli_query($conn, "UPDATE Incident SET Vehicle_ID = '$Ex_vehicle_ID', People_ID = '$People_ID', 
                Incident_date = '$Incident_date', Incident_report = '$Incident_report', Offence_ID = '$Offence_ID'
                WHERE Incident_ID = '$Edit_incident_ID';");
            }

            // commit transaction
            mysqli_commit($conn);
            $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
            echo "<script>alert('$Message');</script>";
            // Reset the page
            echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

            // throw exception if transaction fails
          } catch (Exception $e) {
            mysqli_rollback($conn);
            $Message = 'An error occured during transaction. Rollback.';
            echo "<script>alert('$Message');</script>";
          }
          mysqli_close($conn);
          // new person, new vehicle
        } else if ($V_status == "new_vehicle") {
          // Check if trimmed vehicle basic info input values are empty
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
              $Vehicle_licence_err = "The vehicle licence is already exists in the database.";
            }
          }

          if ($Vehicle_type_err == "" && $Vehicle_colour_err == "" && $Vehicle_licence_err == "") {
            try {
              // use transaction to insert data into tables
              mysqli_autocommit($conn, false);

              // insert into People
              $P_ins_query = mysqli_query($conn, "INSERT INTO People (People_name,People_address,People_licence) 
                VALUES ('$People_name','$People_address','$People_licence')");
              $People_ID = mysqli_insert_id($conn);

              // insert into Vehicle
              $V_ins_query = mysqli_query($conn, "INSERT INTO Vehicle 
                (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
                ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
              $Vehicle_ID = mysqli_insert_id($conn);

              // if insert into Incident
              if ($Status == 0) {
                $sql = mysqli_query($conn, "INSERT INTO Incident 
                  (Vehicle_ID, People_ID, Incident_date, Incident_report, Offence_ID) VALUES
                  ('$Vehicle_ID', '$People_ID', '$Incident_date', '$Incident_report', '$Offence_ID');");

                // if update Incident
              } else if ($Status == 1) {
                $sql = mysqli_query($conn, "UPDATE Incident SET Vehicle_ID = '$Vehicle_ID', People_ID = '$People_ID', 
                  Incident_date = '$Incident_date', Incident_report = '$Incident_report', Offence_ID = '$Offence_ID'
                  WHERE Incident_ID = '$Edit_incident_ID';");
              }

              // commit transaction
              mysqli_commit($conn);
              $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
              echo "<script>alert('$Message');</script>";
              // Reset the page
              echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

              // throw exception if transaction fails
            } catch (Exception $e) {
              mysqli_rollback($conn);
              $Message = 'An error occured during transaction. Rollback.';
              echo "<script>alert('$Message');</script>";
            }
            mysqli_close($conn);
          }
          // new person, unknown vehicle
        } else if ($V_status == "unknown_vehicle") {
          try {
            // use transaction to insert data into tables
            mysqli_autocommit($conn, false);

            // insert into People
            $P_ins_query = mysqli_query($conn, "INSERT INTO People (People_name,People_address,People_licence) 
              VALUES ('$People_name','$People_address','$People_licence')");
            $People_ID = mysqli_insert_id($conn);

            // if insert into Incident
            if ($Status == 0) {
              $sql = mysqli_query($conn, "INSERT INTO Incident 
                (People_ID, Incident_date, Incident_report, Offence_ID) VALUES
                ('$People_ID', '$Incident_date', '$Incident_report', '$Offence_ID');");
              // if update Incident
            } else if ($Status == 1) {
              $sql = mysqli_query($conn, "UPDATE Incident SET Vehicle_ID = NULL, 
              People_ID = '$People_ID', Incident_date = '$Incident_date', 
              Incident_report = '$Incident_report', Offence_ID = '$Offence_ID' WHERE Incident_ID = '$Edit_incident_ID';");
            }

            // commit transaction
            mysqli_commit($conn);
            $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
            echo "<script>alert('$Message');</script>";
            // Reset the page
            echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

            // throw exception if transaction fails
          } catch (Exception $e) {
            mysqli_rollback($conn);
            $Message = 'An error occured during transaction. Rollback.';
            echo "<script>alert('$Message');</script>";
          }
          mysqli_close($conn);
        }
      }

      // if choose an unknown person
    } elseif ($P_status == "unknown_person") {
      // unknown person, existing vehicle
      if ($V_status == "existing_vehicle") {

        // if insert into Incident
        if ($Status == 0) {
          $sql = "INSERT INTO Incident 
            (Vehicle_ID, Incident_date, Incident_report, Offence_ID) VALUES
            ('$Ex_vehicle_ID', '$Incident_date', '$Incident_report', '$Offence_ID');";

          // if update Incident
        } else if ($Status == 1) {
          $sql = "UPDATE Incident SET Vehicle_ID = '$Ex_vehicle_ID', People_ID = NULL,
            Incident_date = '$Incident_date', Incident_report = '$Incident_report', Offence_ID = '$Offence_ID'
            WHERE Incident_ID = '$Edit_incident_ID';";
        }
        if (mysqli_query($conn, $sql)) {
          $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        } else {
          $Status == 0 ? $Message = "Insert failed. Please contact administrator." : $Message = "Update failed. Please contact administrator.";
        }
        // alert the success message
        echo "<script>alert('$Message');</script>";
        // Reset the page
        echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";
        // unknown person, new vehicle
      } else if ($V_status == "new_vehicle") {

        // Check if trimmed vehicle basic info input values are empty
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
            $Vehicle_licence_err = "The vehicle licence is already exists in the database.";
          }
        }
        // If everything is okay
        if ($Vehicle_type_err == "" && $Vehicle_colour_err == "" && $Vehicle_licence_err == "") {
          try {
            // use transaction to insert data into tables
            mysqli_autocommit($conn, false);

            // insert into Vehicle
            $V_ins_query = mysqli_query($conn, "INSERT INTO Vehicle 
              (Vehicle_type,Vehicle_colour,Vehicle_licence) VALUES
              ('$Vehicle_type','$Vehicle_colour','$Vehicle_licence');");
            $Vehicle_ID = mysqli_insert_id($conn);

            // if insert into Incident
            if ($Status == 0) {
              $sql = mysqli_query($conn, "INSERT INTO Incident 
                (Vehicle_ID, Incident_date, Incident_report, Offence_ID) VALUES
                ('$Vehicle_ID', '$Incident_date', '$Incident_report', '$Offence_ID');");

              // if update Incident
            } else if ($Status == 1) {
              $sql = mysqli_query($conn, "UPDATE Incident SET Vehicle_ID = '$Vehicle_ID', People_ID = NULL,
                Incident_date = '$Incident_date', Incident_report = '$Incident_report', Offence_ID = '$Offence_ID'
                WHERE Incident_ID = '$Edit_incident_ID';");
            }

            // commit transaction
            mysqli_commit($conn);
            $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";

            echo "<script>alert('$Message');</script>";
            // Reset the page
            echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";

            // throw exception if transaction fails
          } catch (Exception $e) {
            mysqli_rollback($conn);
            $Message = 'An error occured during transaction. Rollback.';
            echo "<script>alert('$Message');</script>";
          }
          mysqli_close($conn);
        }
        // unknown person, unknown vehicle
      } else if ($V_status == "unknown_vehicle") {
        // if insert into Incident
        if ($Status == 0) {
          $sql = "INSERT INTO Incident 
            (Incident_date, Incident_report, Offence_ID) VALUES
            ('$Incident_date', '$Incident_report', '$Offence_ID');";
          // if update Incident
        } else if ($Status == 1) {
          $sql = "UPDATE Incident SET Vehicle_ID = NULL, People_ID = NULL,
            Incident_date = '$Incident_date', 
            Incident_report = '$Incident_report', Offence_ID = '$Offence_ID' WHERE Incident_ID = '$Edit_incident_ID';";
        }
        if (mysqli_query($conn, $sql)) {
          $Status == 0 ? $Message = "Inserted successfully." : $Message = "Updated successfully.";
        } else {
          $Status == 0 ? $Message = "Insert failed. Please contact administrator." : $Message = "Update failed. Please contact administrator.";
        }
        // alert the success message
        echo "<script>alert('$Message');</script>";
        // Reset the page
        echo $Status == 0 ? "<script>window.location.replace('r_ins_edit.php')</script>" : "<script>window.location.replace('report.php')</script>";
      }
    }
  }
}

?>

<script>
  // function to change input form when selecting 'existing person' or 'new person'
  function contentsView(type1, type2) {
    if (type1 == 1) {
      var E_elements = document.getElementsByClassName('ex_person');
      var N_elements = document.getElementsByClassName('new_p_input');
      var Id_name = 'new_person';
      var Element_name = 'Ex_p_licence';
      var Status_name = 'P_status';
    }
    if (type1 == 2) {
      var E_elements = document.getElementsByClassName('ex_vehicle');
      var N_elements = document.getElementsByClassName('new_v_input');
      var Id_name = 'new_vehicle';
      var Element_name = 'Ex_v_licence';
      var Status_name = 'V_status';
    }

    // if selected existing person or vehicle
    if (type2 == 'ex') {
      for (var i = 0, length = E_elements.length; i < length; i++) {
        E_elements[i].style.display = 'block';
      }
      document.getElementById(Id_name).style.display = 'none';
      document.getElementsByName(Element_name)[0].required = true;
      for (var i = 0, length = N_elements.length; i < length; i++) {
        N_elements[i].required = false;
      }
      // if selected new person or vehicle
    } else if (type2 == 'new') {
      for (var i = 0, length = E_elements.length; i < length; i++) {
        E_elements[i].style.display = 'none';
      }
      document.getElementById(Id_name).style.display = 'block';
      document.getElementsByName(Element_name)[0].required = false;
      for (var i = 0, length = N_elements.length; i < length; i++) {
        N_elements[i].required = true;
      }

      // if selected unknown person or vehicle
    } else if (type2 == 'unknown') {
      for (var i = 0, length = E_elements.length; i < length; i++) {
        E_elements[i].style.display = 'none';
      }
      document.getElementById(Id_name).style.display = 'none';
      document.getElementsByName(Element_name)[0].required = false;
      for (var i = 0, length = N_elements.length; i < length; i++) {
        N_elements[i].required = false;
      }
    }
  }
  // Select function which sets form input values when certain person or vehicle is selected
  function Select(Type, Selected_ID, Selected_licence) {
    if (Type == 1) {
      document.getElementsByName("Ex_people_ID")[0].value = Selected_ID;
      document.getElementsByName("Ex_p_licence")[0].value = Selected_licence;
    }
    if (Type == 2) {
      document.getElementById("Ex_vehicle_ID").value = Selected_ID;
      document.getElementsByName("Ex_v_licence")[0].value = Selected_licence;
    }

  };

  // javascript function to temporarily save input values when pressing search button
  function SaveInput() {
    // temporarily save incident basic info
    var saved_date = document.getElementsByName("Incident_date")[0].value;
    var saved_report = document.getElementsByName("Incident_report")[0].value;
    var saved_off_type = document.getElementsByName("Offence_description")[0].value;
    sessionStorage.setItem("saved_date", saved_date);
    sessionStorage.setItem("saved_report", saved_report);
    sessionStorage.setItem("saved_off_type", saved_off_type);

    // status (insert 0 or update 1)
    var saved_status = document.getElementsByName("Status")[0].value;
    sessionStorage.setItem("saved_status", saved_status);

    // personal info
    var saved_p_status = $('input[name=p_radio_btn]:checked').val();
    sessionStorage.setItem("saved_p_status", saved_p_status);
    var saved_p_ID = document.getElementById("Ex_people_ID").value;
    sessionStorage.setItem("saved_p_ID", saved_p_ID);
    if (saved_p_status == "new_person") {
      var saved_p_name = document.getElementsByName("People_name")[0].value;
      var saved_p_add = document.getElementsByName("People_address")[0].value;
      var saved_p_li = document.getElementsByName("People_licence")[0].value
      sessionStorage.setItem("saved_p_name", saved_p_name);
      sessionStorage.setItem("saved_p_add", saved_p_add);
      sessionStorage.setItem("saved_p_li", saved_p_li);
    }
    var saved_p_licence = document.getElementsByName("Ex_p_licence")[0].value;
    sessionStorage.setItem("saved_p_licence", saved_p_licence);

    // vehicle info
    var saved_v_status = $('input[name=v_radio_btn]:checked').val();
    sessionStorage.setItem("saved_v_status", saved_v_status);
    var saved_v_ID = document.getElementById("Ex_vehicle_ID").value;
    sessionStorage.setItem("saved_v_ID", saved_v_ID);
    if (saved_v_status == "new_vehicle") {
      var saved_v_type = document.getElementsByName("Vehicle_type")[0].value;
      var saved_v_colour = document.getElementsByName("Vehicle_colour")[0].value;
      var saved_v_li = document.getElementsByName("Vehicle_licence")[0].value
      sessionStorage.setItem("saved_v_type", saved_v_type);
      sessionStorage.setItem("saved_v_colour", saved_v_colour);
      sessionStorage.setItem("saved_v_li", saved_v_li);
    }
    var saved_v_licence = document.getElementsByName("Ex_v_licence")[0].value;
    sessionStorage.setItem("saved_v_licence", saved_v_licence);
    location.reload();
  }
</script>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?php echo $Status == 1 ? "Update Incident Record" : "Insert New Incident Record" ?></title>
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
    <div class="content" id="r_ins">
      <h2 id="r_title"><?php echo $Status == 1 ? "Update Incident Record" : "Insert New Incident Record" ?></h2>
      <!-- input form -->
      <div id="inc_basic">
        <form name="basic_form" method="post" action="" autocomplete="off">
          <input type="hidden" name="post" value="1" />
          <input type="hidden" name="Status" value="<?php echo $Status; ?>" />

          <!-- input basic incident info -->
          <h2 id="inc_basic_h2">1. Input basic info </h2>
      </div>
      <div id="inc_date">
        <label>Incident date</label><br>
        <input id="Incident_date" type="date" name="Incident_date" value="<?php echo $Incident_date; ?>" required />
      </div>
      <script>
        // set max value of "to" date to today
        Incident_date.max = new Date().toISOString().split("T")[0];
      </script>
      <div id="inc_des">
        <label>Incident description</label><br>
        <input class="description" id="Incident_report" type="text" name="Incident_report" placeholder="Enter description" value="<?php echo $Incident_report; ?>" required maxlength="300" />
        <p class="msg"><?php echo $I_report_err; ?></p>
      </div>
      <div id="inc_type">
        <label>Offence type</label><br>
        <select class="offence_type" name="Offence_description">
          <?php
          $Off_sql = "SELECT * FROM Offence;";
          $Off_result = mysqli_query($conn, $Off_sql);
          if (mysqli_num_rows($Off_result) > 0) {
            $n = 0;
            while ($Off_row = mysqli_fetch_assoc($Off_result)) {
              if ($n == 0 && $Offence_description == "") {
                $Selected = "selected = 'selected'";
              } else {
                $Selected = "";
              }
              echo "<option value='" . $Off_row["Offence_description"] . "' " . $Selected . ">" . $Off_row['Offence_description'] . "</option>";
              $n++;
            }
          }
          ?>
        </select>
        <p class="msg"><?php echo $Offence_description_err; ?></p>
      </div>

      <!-- involved person info -->
      <div class="person_info">
        <h2>2. Person involved </h2>
      </div>
      <div class="p_radio_btns">
        <!-- choose the person is already in the database or not -->
        <div class='radio_btn'>
          <input type="radio" name="p_radio_btn" value="existing_person" onclick="contentsView(1, 'ex');" required />Person already in the database
          <input type="radio" name="p_radio_btn" value="new_person" onclick="contentsView(1, 'new');" />Person not in the database
          <input type="radio" name="p_radio_btn" value="unknown_person" onclick="contentsView(1, 'unknown');" />Unknown Person
        </div>
      </div>

      <div class="p_licence_box">
        <div class="ex_person" style='display:none'><label id="p_box_lbl">Selected person Licence: &nbsp;&nbsp;</label>
          <div class="p_box_input">
            <input id="Ex_p_licence" class="readonly" type="text" name="Ex_p_licence" placeholder="search & choose a person" value="<?php echo $Ex_p_licence ?>" oninvalid="this.setCustomValidity('Please choose a person using search bar below.')" oninput="setCustomValidity('')" />
          </div>
        </div>
      </div>

      <!-- involved vehicle info -->
      <div class="vehicle_info">
        <h2>3. Vehicle involved </h2>
      </div>
      <div class="v_licence_box">
        <div class="ex_vehicle" style='display:none'><label id="v_box_lbl">Selected vehicle Licence: &nbsp;&nbsp;</label>
          <div class="v_box_input">
            <input id="Ex_v_licence" class="readonly" type="text" name="Ex_v_licence" placeholder="search & choose a vehicle" value="<?php echo $Ex_v_licence ?>" oninvalid="this.setCustomValidity('Please choose a person using search bar below.')" oninput="setCustomValidity('')" />
          </div>
        </div>
      </div>
      <!-- make readonly class buttons read only -->
      <script>
        $(".readonly").keydown(function(e) {
          e.preventDefault();
        });
      </script>
      <div class="v_radio_btns">
        <!-- choose the vehicle is already in the database or not -->
        <div class='radio_btn'>
          <input type="radio" name="v_radio_btn" value="existing_vehicle" onclick="contentsView(2, 'ex');" required />Vehicle already in the database
          <input type="radio" name="v_radio_btn" value="new_vehicle" onclick="contentsView(2, 'new');" />Vehicle not in the database
          <input type="radio" name="v_radio_btn" value="unknown_vehicle" onclick="contentsView(2, 'unknown');" />Unknown Vehicle
        </div>
      </div>
      <div class="r_p_result">
        <!-- input information of new person only visible when 'new person' is chosen -->
        <div id='new_person' style='display:none'>
          <h2>2.2. Input info of new person</h2>
          <label>Name</label>
          <p><input class='new_p_input p_name' type="text" name="People_name" placeholder="Enter name (letters and spaces)" value="<?php echo $People_name; ?>" pattern="[A-Za-z ]+" maxlength="40" oninvalid="this.setCustomValidity('Please enter name using only letters(a-Z) and spaces.')" oninput="setCustomValidity('')" /></p>
          <p class="msg"><?php echo $People_name_err; ?></p>
          <label>Address</label>
          <p><textarea class='new_p_input p_address' rows="4" cols="50" name="People_address" placeholder="Enter address" maxlength="100" oninvalid="this.setCustomValidity('Please enter address.')" oninput="setCustomValidity('')"><?php echo $People_address; ?></textarea></p>
          <p class="msg"><?php echo $People_address_err; ?></p>
          <label>Personal Licence</label>
          <p><input class='new_p_input p_licence' type="text" name="People_licence" placeholder="Enter 16-digit licence (letters and numbers without spaces)" value="<?php echo $People_licence; ?>" pattern="[a-zA-Z0-9]{16}" maxlength="16" title='Please enter 16-digit licence using letters and numbers without spaces.' oninvalid="this.setCustomValidity('Please enter 16-digit licence using letters and numbers without spaces.')" oninput="setCustomValidity(''); this.value = this.value.toUpperCase()" /></p>
          <p class="msg"><?php echo $People_licence_err; ?></p>
        </div>
      </div>

      <!-- input information of new vehicle only visible when 'new vehicle' is chosen -->
      <div class="r_v_result">
        <div id='new_vehicle' style='display:none'>
          <h2>3.2. Input info of new vehicle</h2>
          <label>Type</label>
          <p><input class='new_v_input v_type' type="text" name="Vehicle_type" placeholder="Enter Type" value="<?php echo $Vehicle_type; ?>" maxlength="20" oninvalid="this.setCustomValidity('Please enter vehicle type.')" oninput="setCustomValidity('')" /></p>
          <p class="msg"><?php echo $Vehicle_type_err; ?></p>
          <label>Colour</label>
          <p><input class='new_v_input v_colour' type="text" name="Vehicle_colour" placeholder="Enter colour without special characters" value="<?php echo $Vehicle_colour; ?>" pattern="[a-zA-Z0-9- ]+" maxlength="20" oninvalid="this.setCustomValidity('Please enter vehicle colour.')" oninput="setCustomValidity('')" /></p>
          <p class="msg"><?php echo $Vehicle_colour_err; ?></p>
          <label>Vehicle Licence</label>
          <p><input class='new_v_input v_licence' type="text" name="Vehicle_licence" placeholder="Enter 7-digit vehicle licence (letters and numbers)" value="<?php echo $Vehicle_licence; ?>" pattern="[a-zA-Z0-9]{7}" maxlength="7" title="Please enter 7-digit licence using letters and numbers without spaces." oninvalid="this.setCustomValidity('Please enter 7-digit licence using letters and numbers without spaces.')" oninput="setCustomValidity(''); this.value = this.value.toUpperCase()" /></p>
          <p class="msg"><?php echo $Vehicle_licence_err; ?></p>
        </div>
      </div>

      <!-- hidden input of the chosen existing person or vehicle ID,
      which will be set automatically by choosing from the result table -->
      <p><input id="Ex_people_ID" type="hidden" name="Ex_people_ID" value="<?php echo $Ex_people_ID ?>" /></p>
      <p><input id="Ex_vehicle_ID" type="hidden" name="Ex_vehicle_ID" value="<?php echo $Ex_vehicle_ID ?>" /></p>

      <!-- submit and end of the form -->
      <p id="r_submit"><input class="submit_btn" type="submit" value="<?php echo $Status == 1 ? "Update" : "Submit" ?>">
        <a class="cancel_btn" href="report.php">Cancel</a>
      </p>

      </form>

      <script>
        // get temporary input values from sessionStorage saved by SaveInput function
        if (sessionStorage.getItem("saved_p_licence") != undefined && sessionStorage.getItem("saved_p_licence") != "") {
          document.getElementsByName("Ex_p_licence")[0].value = sessionStorage.getItem("saved_p_licence");
        }
        if (sessionStorage.getItem("saved_v_licence") != undefined && sessionStorage.getItem("saved_v_licence") != "") {
          document.getElementsByName("Ex_v_licence")[0].value = sessionStorage.getItem("saved_v_licence");
        }
        if (sessionStorage.getItem("saved_p_ID") != undefined && sessionStorage.getItem("saved_p_ID") != "") {
          document.getElementById("Ex_people_ID").value = sessionStorage.getItem("saved_p_ID");
        }
        if (sessionStorage.getItem("saved_v_ID") != undefined && sessionStorage.getItem("saved_v_ID") != "") {
          document.getElementById("Ex_vehicle_ID").value = sessionStorage.getItem("saved_v_ID");
        }
      </script>
      <div class="r_p_result">
        <!-- search bar form to input information of existing person -->
        <div class="ex_person search_box" style='display:none'>
          <h2>2.2. Search & Select existing person</h2>
          <form name="r_ins_p_search" method="POST" onSubmit="SaveInput();" autocomplete="off">
            <select class="r_ins_search" name="P_category">
              <option value="People_name">Name</option>
              <option value="People_licence">Licence number</option>
            </select>
            <input type="text" name="P_search" size="40" placeholder="Enter search keyword or just press search" /> <button class="search_btn r_ins_btn"><i class="fa fa-search"></i></button>
            <input type="hidden" name="page_p" value=1 />
          </form><br>&nbsp;<br>&nbsp;<br>
        </div>
        <div class="ex_person search_result">
          <!-- existing person search result table -->

          <?php

          if (isset($_POST['P_search'])) {
            // pagination
            $per_page_record = 5;
            if (isset($_POST["page_p"])) {
              $page_p = $_POST["page_p"];
            } else {
              $page_p = 1;
            }
            $start_from = ($page_p - 1) * $per_page_record;

            $P_search = trim($_POST["P_search"]);
            $P_category = $_POST["P_category"];

            // pagination total records
            $p_sql = "SELECT * FROM People WHERE " . $P_category . " LIKE \"%" . $P_search . "%\";";
            $p_result = mysqli_query($conn, $p_sql);
            $total_records = mysqli_num_rows($p_result);

            // select query
            $sql = "SELECT * FROM People WHERE " . $P_category . " LIKE \"%" . $P_search . "%\" LIMIT $start_from, $per_page_record;";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
              echo "<table id='r_ins_p_table'>";  // start table
              echo "<tr><th>Name</th><th>Address</th><th>Personal licence</th><th>Select</th></tr>"; // table header

              $n = 0;
              while ($row = mysqli_fetch_assoc($result)) {
                if ($n == 0) {
                  $checked = "checked='checked'";
                  echo "<script>Select(1, '" . $row["People_ID"] . "', '" . $row["People_licence"] . "')</script>";
                } else {
                  $checked = "";
                }
                echo "<tr>";
                echo "<td>" . $row["People_name"] . "</td>";
                echo "<td>" . $row["People_address"] . "</td>";
                echo "<td>" . $row["People_licence"] . "</td>";
                echo "<td><input type='radio' name='radio_btn' value='ex_person'
              onclick='Select(1, \"" . $row["People_ID"] . "\", \"" . $row["People_licence"] . "\")' " . $checked . "/></td>";
                echo "</tr>";
                $n++;
              }
              echo "</table>";

              // pagination
              echo "<center><div class='pagination'>";
              $total_pages = ceil($total_records / $per_page_record);
              $pagLink = "";
              if ($page_p >= 2) {
                echo "<button onclick=\"Page_move_p('$P_category', '$P_search', " . ($page_p - 1) . ")\">Prev </button>";
              }

              for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page_p) {
                  $pagLink .= "<button class='active' onclick=\"Page_move_p('$P_category', '$P_search', $i)\">" . $i . "</button>";
                } else {
                  $pagLink .= "<button onclick=\"Page_move_p('$P_category', '$P_search', $i)\">" . $i . "</button>";
                }
              };

              echo $pagLink;

              if ($page_p < $total_pages) {
                echo "<button onclick=\"Page_move_p('$P_category', '$P_search', '" . ($page_p + 1) . "')\">Next</button>";
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
      <!-- search bar form to input information of existing vehicle -->
      <div class="ex_vehicle r_v_result search_box" style='display:none'>
        <h2>3.2. Search & Select existing vehicle</h2>
        <form name="r_ins_v_search" method="POST" onSubmit="SaveInput();" autocomplete="off">
          <select class="r_ins_search" name="V_category">
            <option value="Vehicle_licence">Vehicle licence</option>
            <option value="Vehicle_colour">Vehicle colour</option>
            <option value="Vehicle_type">Vehicle type</option>
            <option value="People_name">Owner name</option>
            <option value="People_licence">Owner licence</option>
          </select>
          <input type="text" name="V_search" size="40" placeholder="Enter search keyword or just press search" /> <button class="search_btn r_ins_btn"><i class="fa fa-search"></i></button>
          <input type="hidden" name="page" value=1 />
        </form><br>&nbsp;<br>&nbsp;<br>
        <!-- existing vehicle search result table -->
        <div class="ex_vehicle search_result">

          <?php

          if (isset($_POST['V_search'])) {
            // pagination
            $per_page_record = 5;
            if (isset($_POST["page"])) {
              $page = $_POST["page"];
            } else {
              $page = 1;
            }
            $start_from = ($page - 1) * $per_page_record;

            $V_search = trim($_POST["V_search"]);
            $V_category = $_POST["V_category"];

            // pagination total records
            $sql = "SELECT * FROM Vehicle NATURAL LEFT JOIN Ownership NATURAL LEFT JOIN People WHERE " . $V_category . " LIKE \"%" . $V_search . "%\";";
            $result = mysqli_query($conn, $sql);
            $total_records = mysqli_num_rows($result);

            // select query
            $sql_2 = "SELECT * FROM Vehicle NATURAL LEFT JOIN Ownership NATURAL LEFT JOIN People WHERE " . $V_category . " LIKE \"%" . $V_search . "%\" LIMIT $start_from, $per_page_record;";
            $result_2 = mysqli_query($conn, $sql_2);

            if (mysqli_num_rows($result_2) > 0) {
              echo "<table id='r_ins_v_table'>";  // start table
              echo "<tr><th>Type</th><th>Colour</th><th>Vehicle licence</th><th>Owner name</th><th>Owner licence</th><th>Select</th></tr>"; // table header

              $n = 0;
              while ($row = mysqli_fetch_assoc($result_2)) {
                if ($n == 0) {
                  $checked = "checked='checked'";
                  echo "<script>Select(2, '" . $row["Vehicle_ID"] . "', '" . $row["Vehicle_licence"] . "')</script>";
                } else {
                  $checked = "";
                }
                echo "<tr>";
                echo "<td>" . $row["Vehicle_type"] . "</td>";
                echo "<td>" . $row["Vehicle_colour"] . "</td>";
                echo "<td>" . $row["Vehicle_licence"] . "</td>";
                echo "<td>" . $row["People_name"] . "</td>";
                echo "<td>" . $row["People_licence"] . "</td>";

                echo "<td><input type='radio' name='radio_btn' value='ex_vehicle'
              onclick='Select(2, \"" . $row["Vehicle_ID"] . "\", \"" . $row["Vehicle_licence"] . "\")' " . $checked . "/></td>";
                echo "</tr>";
                $n++;
              }
              echo "</table>";
              // pagination
              echo "<center><div class='pagination'>";
              $total_pages = ceil($total_records / $per_page_record);
              $pagLink = "";
              if ($page >= 2) {
                echo "<button onclick=\"Page_move('$V_category', '$V_search', " . ($page - 1) . ")\">Prev </button>";
              }

              for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                  $pagLink .= "<button class='active' onclick=\"Page_move('$V_category', '$V_search', $i)\">" . $i . "</button>";
                } else {
                  $pagLink .= "<button onclick=\"Page_move('$V_category', '$V_search', $i)\">" . $i . "</button>";
                }
              };

              echo $pagLink;

              if ($page < $total_pages) {
                echo "<button onclick=\"Page_move('$V_category', '$V_search', '" . ($page + 1) . "')\">Next</button>";
              }
              echo "</div></center>";
            } else // if query result is empty 
            {
              echo "No result found.";
            }
          }

          ?>
        </div>

        <script>
          // get temporary input values from sessionStorage saved by SaveInput function

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

          // if there is a post value, make one of v_radio_btn clicked based on it
          var php_v_status = "<?php echo $V_status ?>";
          if (php_v_status !== "") {
            $("input[name=v_radio_btn][value=" + php_v_status + "]").prop('checked', true).trigger('click');
          }

          // if there is a sessionStorage value, make one of v_radio_btn clicked based on it
          if (sessionStorage.getItem("saved_v_status") != undefined && sessionStorage.getItem("saved_v_status") != "") {
            var saved_v_status = sessionStorage.getItem("saved_v_status");
            $("input[name=v_radio_btn][value=" + saved_v_status + "]").prop('checked', true).trigger('click');
          }

          // get other temporary input values 
          if (sessionStorage.getItem("saved_date") != undefined && sessionStorage.getItem("saved_date") != "") {
            document.getElementById("Incident_date").value = sessionStorage.getItem("saved_date");
          }
          if (sessionStorage.getItem("saved_report") != undefined && sessionStorage.getItem("saved_report") != "") {
            document.getElementById("Incident_report").value = sessionStorage.getItem("saved_report");
          }
          if (sessionStorage.getItem("saved_v_type") != undefined && sessionStorage.getItem("saved_v_type") != "") {
            document.getElementsByName("Vehicle_type")[0].value = sessionStorage.getItem("saved_v_type");
          }
          if (sessionStorage.getItem("saved_v_colour") != undefined && sessionStorage.getItem("saved_v_colour") != "") {
            document.getElementsByName("Vehicle_colour")[0].value = sessionStorage.getItem("saved_v_colour");
          }
          if (sessionStorage.getItem("saved_v_li") != undefined && sessionStorage.getItem("saved_v_li") != "") {
            document.getElementsByName("Vehicle_licence")[0].value = sessionStorage.getItem("saved_v_li");
          }
          if (sessionStorage.getItem("saved_p_name") != undefined && sessionStorage.getItem("saved_p_name") != "") {
            document.getElementsByName("People_name")[0].value = sessionStorage.getItem("saved_p_name");
          }
          if (sessionStorage.getItem("saved_p_add") != undefined && sessionStorage.getItem("saved_p_add") != "") {
            document.getElementsByName("People_address")[0].value = sessionStorage.getItem("saved_p_add");
          }
          if (sessionStorage.getItem("saved_p_li") != undefined && sessionStorage.getItem("saved_p_li") != "") {
            document.getElementsByName("People_licence")[0].value = sessionStorage.getItem("saved_p_li");
          }

          if (sessionStorage.getItem("saved_status") != undefined && sessionStorage.getItem("saved_status") != "") {
            document.getElementsByName("Status")[0].value = sessionStorage.getItem("saved_status");
          }

          // if there is a post value, make one of offence type (offence description) selected based on it
          var php_offence_description = "<?php echo $Offence_description ?>";
          if (php_offence_description !== "") {
            document.getElementsByName("Offence_description")[0].value = php_offence_description;
          }

          // if there is a sessionStorage value, make one of offence type (offence description) selected based on it
          if (sessionStorage.getItem("saved_off_type") != undefined && sessionStorage.getItem("saved_off_type") != "") {
            document.getElementsByName("Offence_description")[0].value = sessionStorage.getItem("saved_off_type");
          }
          sessionStorage.clear();

          // people pagination javascript function
          function Page_move_p(P_category, P_search, page_p) {
            var form_p = document.r_ins_p_search;
            form_p.P_category.value = P_category;
            form_p.P_search.value = P_search;
            form_p.page_p.value = page_p;
            SaveInput();
            form_p.submit();
          }

          // vehicle pagination javascript function
          function Page_move(V_category, V_search, page) {
            var form = document.r_ins_v_search;
            form.V_category.value = V_category;
            form.V_search.value = V_search;
            form.page.value = page;
            SaveInput();
            form.submit();
          }
        </script>
      </div>
    </div>
</body>


</html>


