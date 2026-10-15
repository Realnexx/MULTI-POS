<?php
  require_once("includes/mysqli_conn.php"); //http
  ob_start();
  error_reporting(1);
	if (session_status() == PHP_SESSION_NONE) {        
    session_start();
  }
  $username = $_SESSION['username'];
  $privilege = $_SESSION['privilege'];
  $userid = $_SESSION['user_id'];
  $_SESSION['page'] = 'students';

  if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "You have to login to access the database !";
    header("Location: login.php");}
$timezone = "Asia/Manila";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Population Summary</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="css/dataTables.bootstrap.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="css/_all-skins.min.css">
  <link rel="stylesheet" href="css/select2.min.css">

  <link rel="stylesheet" href="css/print_summaryEnrollment.css">
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper" style="background-color:white;">

    <div style="width:100%; padding:0px 115px;">
        <table style="text-align:center; background-color:white;  width:100%;">
            <tr>
                <td><img src="images/BNSC-logo.png" width="50" height="50" alt="BNSC logo"></td>
                <td style="padding:0px 5px;">
                    <h5><b>BOHOL NORTHERN STAR COLLEGE, INC.</b></h5>
                    <p style="line-height:3px; font-size: 12px;">Bohol Northeastern Education Foundation, Inc.</p>
                    <p style="line-height:4px; font-size: 12px;">Isaac Garces Street, Ubay, Bohol</p>
                    <p style="line-height:3px; font-size: 12px; margin-top: 15px; font-weight: bold;">List of Students</p>
                    <p style="line-height:4px; font-size: 12px; font-weight: bold;"><?php echo $_SESSION['pop-sem'].' Semester of S.Y. '.$_SESSION['pop-syear'].'-'.intval($_SESSION['pop-syear']+1); ?></p>
                </td>
                <td><img src="images/bnef%20logo.PNG" width="50" height="50" alt="BNSC logo"></td>
            </tr>
        </table>
    </div>


  <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="background-color:white;">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th style="text-align: center;">MI</th>
              <th style="text-align: center;">Course & Year</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if (isset($_SESSION['population-list'])) {
                echo $_SESSION['population-list'];
              }
            ?>
          </tbody>
        </table>

    </div>
<!-- ./wrapper -->

<!-- jQuery 2.2.3 -->
<script src="js/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="js/bootstrap.min.js"></script>
<!-- Select2 -->
<script src="js/select2.full.min.js"></script>
<!-- AdminLTE App -->
<script src="js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="js/demo.js"></script>
<script type="text/javascript">
 // function closeWindow() {
 //    setTimeout(function() {
 //    window.close();
 //    }, 60000);
 //    }
 //
 //    window.onload = closeWindow();
</script>
</body>
</html>
