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
  <title>Search or Update Personal Record</title>
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
      <!-- Search people form -->
      <div class="search_box">
        <h2>Search or Update Personal Record</h2>
        <form name="P_search_form" method="POST" autocomplete="off">
          <select name="p_category">
            <option value="People_name">Name</option>
            <option value="People_licence">Licence number</option>
            <option value="People_address">Address</option>
          </select>
          <input class="search" type="text" name="p_search" size="40" placeholder="Enter search keyword or just press search button" />
          <!-- pagination page number -->
          <input type="hidden" name="page" value=1 />
          <button class="search_btn"><i class="fa fa-search"></i></button>
        </form>
        <button class="new_btn" onclick="window.location.href='p_ins_edit.php';"> new </button>

      </div>

      <script>
        // A JavaScript function to confirm delete
        function confirmDelete(ID) {
          var conf = confirm("Are you sure?");
          if (conf == true) // if OK pressed
          { // delete the chosen record using p_delete.php file
            if (window.location.href = "p_delete.php?People_ID=".concat(ID)) {
              window.alert("Deleted successfully.");
            } else {
              // alert error message if failed
              window.alert("An error has occured. Please contact administrator.");
            }
          }
        }

        // Edit function to open the edit page when Edit button pressed
        function Edit(ID) {
          // edit the chosen record using p_ins_edit.php file
          if (window.location.href = "p_ins_edit.php?People_ID=".concat(ID).concat("&&Status=1")) {} else {
            // alert error message if failed
            window.alert("An error has occured. Please contact administrator.");
          }
        }
      </script>

      <!-- Search result table -->
      <div class="container">

        <?php

        if (isset($_POST['p_search'])) {
          // pagination
          $per_page_record = 10;
          if (isset($_POST["page"])) {
            $page = $_POST["page"];
          } else {
            $page = 1;
          }
          $start_from = ($page - 1) * $per_page_record;

          // get search words and category
          $p_search = trim($_POST["p_search"]);
          $p_category = $_POST["p_category"];

          // pagination total records
          $p_sql = "SELECT * FROM People WHERE " . $p_category . " LIKE \"%" . $p_search . "%\";";
          $p_result = mysqli_query($conn, $p_sql);
          $total_records = mysqli_num_rows($p_result);

          // select query
          $sql = "SELECT * FROM People WHERE " . $p_category . " LIKE \"%" . $p_search . "%\" LIMIT $start_from, $per_page_record;";
          $result = mysqli_query($conn, $sql);

          // start table
          if (mysqli_num_rows($result) > 0) {
            echo "<table class='p_result_table'>";
            echo "<tr><th class='name'>Name</th><th class='address'>Address</th><th class='p_licence'>Licence</th><th class='btn'></th>
                <th class='btn'></th></tr>"; // table header

            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>";
              echo "<td>" . $row["People_name"] . "</td>";
              echo "<td>" . $row["People_address"] . "</td>";
              echo "<td>" . $row["People_licence"] . "</td>";

              // Delete button executes JavaScript confirmDelete
              echo "<td><button onclick='confirmDelete(" . $row["People_ID"] . ")'>Delete</button></td>";
              // Edit button executes JavaScript Edit
              echo "<td><button onclick='Edit(" . $row["People_ID"] . ")'>Edit</button></td>";
              echo "</tr>";
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
    <div class="pagination">
    </div>
  </div>

  <script>
    // pagination javascript function
    function Page_move(p_category, p_search, page) {
      var form = document.P_search_form;
      form.p_category.value = p_category;
      form.p_search.value = p_search;
      form.page.value = page;
      form.submit();
    }
  </script>
</body>


</html>
