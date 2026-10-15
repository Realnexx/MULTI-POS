<?php
	require_once("includes/connection.php"); //http
	ob_start();
	session_start();
	$username = $_SESSION['username'];
	$privilege = $_SESSION['privilege'];
	$userid = $_SESSION['user_id'];	
	$_SESSION['page'] = 'students';

	if (!isset($_SESSION['user_id'])) {
		$_SESSION['message'] = "You have to login to access the database !";
		header("Location: login.php");}
$timezone = "Asia/Manila";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);		

$today = date('Y-m-d');
$syear = $_SESSION['syear'];
$semester = $_SESSION['semester'];
$level = $_SESSION['level'];

if($level == 1)
  {
      $slevel = "1st";
      $star = "AND level ='".$level."'";
  }
  elseif($level == 2)
  {
      $slevel = "2nd";
      $star = "AND level ='".$level."'";
  }
  elseif($level == 3)
  {
      $slevel = "3rd";
      $star = "AND level ='".$level."'";
  }
  elseif($level == 4)
  {
      $slevel = "4th";
      $star = "AND level ='".$level."'";
  }
  else
  {
      $slevel = "All";
      $star = "AND level !='".$level."'";
  }


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
<!--style>    
@font-face {
    font-family: compact_font;
    src: url(fonts/Antonio-Regular.ttf);
    }    
</style -->     
    
</head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper" style="background-color:white; padding-top:10px;">
<?php require'includes/letter_head.html'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="background-color:white;">
             
            <div style="padding:0px 15px;">
<h5>Population Summary: <?php echo ucwords($semester); if ($semester == '1st' OR $semester == '2nd'){echo ' Semester';}; echo ' of '.$syear.'~'.intval($syear+1).' as of '.date('F d, Y').' of '.$slevel.' Year'; ?></h5>
 <table class="table table-striped">
     <style>
         th {padding: 0px 5px;}
         td {padding: 0px 5px; margin: 0px 5px;}
     </style>
                <thead>
                <tr>
                    <th>Course</th>
                    <th style="text-align:right;">Male</th>
                    <th style="text-align:right;">Female</th>
                    <th style="text-align:right;">Total</th>
                </tr>
                </thead>
                <tbody>
<?php 
    $male = 0;
    $female = 0;
    $courseTemp = 'temp';
    $qry_course = mysql_query("SELECT course FROM courses WHERE status != 'delete' ORDER BY course ASC") or die(mysql_error()); 
		while($info_course = mysql_fetch_array($qry_course)){
			if($info_course['course'] != $courseTemp)
            {
                $courseTemp = $info_course['course'];                
   
	$qryEnroll = mysql_query("SELECT studID, status FROM enrolment WHERE syear = '".$syear."' AND course = '".$courseTemp."' AND semester = '".$semester."'$star ORDER BY studID ASC") or die(mysql_error()); 
		while($infoEnroll = mysql_fetch_array($qryEnroll)){
			if ($infoEnroll['status'] == 'confirm')
            {
        if($studID != $infoEnroll['studID']){ 
            $studID = $infoEnroll['studID'];             
     $qry_gender = mysql_query("SELECT sex FROM students WHERE id = '".$studID."'") or die(mysql_error()); 
		while($info_gender = mysql_fetch_array($qry_gender))
        {
            if($info_gender['sex'] == 'male'){$male++;}
            if($info_gender['sex'] == 'female'){$female++;}
        }   
	       }}
        if ($infoEnroll['status'] == 'temporary'){$count_temp++;}   
        }
?>                
                <tr>
                  <td style="padding: 0px 3px; font-weight: bold;"><?php echo $courseTemp; ?></td>
                  <td style="text-align:right;padding: 0px 3px; font-weight: bold;">
                      <?php echo $male; $total_male = $total_male+$male;  ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px; font-weight: bold;">
                        <?php echo $female; $total_female = $total_female+$female; ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px; font-weight: bold;"><?php echo $male + $female; $total_college = $total_college +($male + $female); $male = 0; $female = 0;?>
                    </td>
                    
<?php 
    $male2=0;
    $female2=0;
                $studID2='999999999';
     $qry_major = mysql_query("SELECT major FROM courses WHERE course = '".$courseTemp."' ORDER BY major ASC") or die(mysql_error()); 
		while($info_major = mysql_fetch_array($qry_major)){
            if($info_major['major']){
                if($info_major['major'] != $majorTemp)
                {  
                    
                    $majorTemp = $info_major['major'];
                    
        $qryEnroll2 = mysql_query("SELECT studID, status FROM enrolment WHERE syear = '".$syear."' AND major = '".$majorTemp."' AND semester = '".$semester."' $star ORDER BY studID ASC") or die(mysql_error()); 
		while($infoEnroll2 = mysql_fetch_array($qryEnroll2)){
			if ($infoEnroll2['status'] == 'confirm')
            {
            if($studID2 != $infoEnroll2['studID']){
                $studID2 = $infoEnroll2['studID'];
            
     $qry_gender2 = mysql_query("SELECT sex FROM students WHERE id = '".$studID2."'") or die(mysql_error()); 
		while($info_gender2 = mysql_fetch_array($qry_gender2))
        {
            if($info_gender2['sex'] == 'male'){$male2++;}
            if($info_gender2['sex'] == 'female'){$female2++;}
        }   
	       } }  }         
?>
     <tr>
                  <td style="padding: 0px 3px 0px 25px;"><?php echo $majorTemp; ?></td>
                  <td style="text-align:right;padding: 0px 3px;">
                      <?php echo $male2; $total_male2 = $total_male2+$male2; ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px;">
                        <?php echo $female2; $total_female2 = $total_female2+$female2;  ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px;"><?php echo $male2+$female2; $male2=0; $female2=0; ?>
                    </td>
                </tr>               
<?php 
                }
            } }
?>                    
<?php } } ?>  
              <tr>
                  <td><b>TOTAL COLLEGE</b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total_male; $total2_male = $total_male; $total_population =  $total_male + $total_female; $total_male = 0; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total_female; $total2_female = $total_female; $total_female = 0; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo number_format($total_college); ?></b></td>
                    </tr> 
<?php 
    $count = 0;
    $count_temp = 0;
    $male = 0;
    $female = 0;
    $courseTemp = 'temp';
    $qry_course = mysql_query("SELECT course FROM courses_lower ORDER BY course ASC") or die(mysql_error()); 
		while($info_course = mysql_fetch_array($qry_course)){
			if($info_course['course'] != $courseTemp)
            {
                $courseTemp = $info_course['course'];                
                    
	$qryEnroll = mysql_query("SELECT studID, status FROM enrolment WHERE syear = '".$syear."' AND course = '".$courseTemp."' AND semester = '".$semester."'") or die(mysql_error()); 
		while($infoEnroll = mysql_fetch_array($qryEnroll)){
			if ($infoEnroll['status'] == 'confirm')
            {
            $count++;
            $studID = $infoEnroll['studID'];
			//$enrolID = $infoEnroll['id'];
			//$semester = $infoEnroll['semester']; $_SESSION['semester'] = $semester;
            
     $qry_gender = mysql_query("SELECT sex FROM students WHERE id = '".$studID."'") or die(mysql_error()); 
		while($info_gender = mysql_fetch_array($qry_gender))
        {
            if($info_gender['sex'] == 'male'){$male++;}
            if($info_gender['sex'] == 'female'){$female++;}
        }   
	       }
        if ($infoEnroll['status'] == 'temporary'){$count_temp++;}   
        }
?>                
                <tr>
                  <td style="padding: 0px 3px;"><?php echo $courseTemp; ?></td>
                  <td style="text-align:right;padding: 0px 3px;">
                      <?php echo $male; $total_male = $total_male+$male; $male=0; ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px;">
                        <?php echo $female; $total_female = $total_female+$female; $female=0; ?>
                    </td>
                  <td style="text-align:right;padding: 0px 3px;"><?php echo $count; $total_population=$total_population+$count;$count=0; ?>
                    </td>
                </tr>
<?php } } ?>  
              <tr>
                  <td><b>TOTAL LOWER</b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total_male; $total2_male = $total2_male+$total_male; $total_population =  $total_male + $total_female; $total_male = 0; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total_female; $total2_female = $total2_female+$total_female; $total_female = 0; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo number_format($total_population); $total_pop = $total_pop+$total_population; //$total_population = 0; ?></b></td>
                </tr> 
                    <tr>
                  <td><b>TOTAL POPULATION</b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total2_male; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo $total2_female; ?></b></td>
                  <td style="text-align:right;padding-right: 3px;"><b><?php echo number_format($total2_male + $total2_female); ?></b></td>
                </tr>
                </tbody>
              </table>
              
          </div>
    <!-- /.content -->
  </div>
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
 function closeWindow() {
    setTimeout(function() {
    window.close();
    }, 60000);
    }

    window.onload = closeWindow();
</script>    
</body>
</html>
