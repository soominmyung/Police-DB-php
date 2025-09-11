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
  <title>Search or Update Vehicle Record</title>
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
        <h2>Search or Update Vehicle Record</h2>
        <form name="V_search_form" method="POST" autocomplete="off">
          <select name="V_category">
            <option value="Vehicle_licence">Vehicle licence</option>
            <option value="Vehicle_colour">Vehicle colour</option>
            <option value="Vehicle_type">Vehicle type</option>
            <option value="People_name">Owner name</option>
            <option value="People_licence">Owner licence</option>
          </select>
          <input type="text" name="V_search" size="40" placeholder="Enter search keyword or just press search button" />
          <button class="search_btn"><i class="fa fa-search"></i></button>
        </form>
        <?php
        if ($isadmin == true) {
            echo '<button class="new_btn" onclick="window.location.href=\'v_ins_edit.php\';"> new </button>';
        }
        ?>
      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          { // delete the chosen record using V_delete.php file
            if (window.location.href = "v_delete.php?Vehicle_ID=".concat(ID)) {
              window.alert("Deleted successfully.");
            } else {
              window.alert("An error has occured.");
            }
          }
        }

        // Edit function to open the edit page when Edit button pressed
        function Edit(ID) {
          // edit the chosen record using v_ins_edit.php file
          if (window.location.href = "v_ins_edit.php?Vehicle_ID=".concat(ID).concat("&&Status=1")) {} else {
            // alert error message if failed
            window.alert("An error has occured. Please contact administrator.");
          }
        }
      </script>

      <div class="container">

        <?php
        if (isset($_POST['V_search'])) {
          // pagination
          $per_page_record = 10;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }
          $start_from = ($page - 1) * $per_page_record;

          // get search words and category
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
            echo "<table id='v_table'>";  // start table
            echo "<tr>";
            echo "<th>Type</th>";
            echo "<th>Colour</th>";
            echo "<th>Vehicle licence</th>";
            echo "<th>Owner name</th>";
            echo "<th>Owner licence</th>";
            if ($isadmin == true) {
                echo "<th></th><th></th>";
            }
            echo "</tr>";


            while ($row = mysqli_fetch_assoc($result_2)) {
              echo "<tr>";
              echo "<td>" . $row["Vehicle_type"] . "</td>";
              echo "<td>" . $row["Vehicle_colour"] . "</td>";
              echo "<td>" . $row["Vehicle_licence"] . "</td>";
              echo "<td>" . $row["People_name"] . "</td>";
              echo "<td>" . $row["People_licence"] . "</td>";

            if ($isadmin == true) {
                // Delete button executes JavaScript confirmDelete
                echo "<td><button onclick='confirmDelete(" . $row["Vehicle_ID"] . ")'>Delete</button></td>";
                // Edit button executes JavaScript Edit
                echo "<td><button onclick='Edit(" . $row["Vehicle_ID"] . ")'>Edit</button></td>";
            }

              echo "</tr>";
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
    </div>
  </div>
  <script>
    // pagination javascript function
    function Page_move(V_category, V_search, page) {
      var form = document.createElement('form');
      form.setAttribute('method', "POST");
      form.setAttribute('action', "vehicle.php");
      document.charset = "utf-8";
      var V_category_i = document.createElement("input");
      V_category_i.setAttribute("type", "hidden");
      V_category_i.setAttribute("name", "V_category");
      V_category_i.setAttribute("value", V_category);
      form.appendChild(V_category_i);
      var V_search_i = document.createElement("input");
      V_search_i.setAttribute("type", "hidden");
      V_search_i.setAttribute("name", "V_search");
      V_search_i.setAttribute("value", V_search);
      form.appendChild(V_search_i);
      var page_i = document.createElement("input");
      page_i.setAttribute("type", "hidden");
      page_i.setAttribute("name", "page");
      page_i.setAttribute("value", page);
      form.appendChild(page_i);
      document.body.appendChild(form);
      form.submit();
    }
  </script>
</body>


</html>







