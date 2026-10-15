<?php
require_once("includes/mysqli_conn.php");
require_once("php-file/formatter.php");
require("includes/user_access.php");
require("includes/gradesheet-eval-handler.php");
ob_start();
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
error_reporting(1);
$username = $_SESSION['username'];
$privilege = $_SESSION['privilege'];
$userid = $_SESSION['user_id'];
$_SESSION['page'] = 'students';
if (!isset($_SESSION['user_id'])) {
  $_SESSION['message'] = "You have to login to access the database !";
  header("Location: login.php");
}
$timezone = "Asia/Manila";
if (function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
$today = date('Y-m-d');

if (isset($_GET['id']) and $_GET['id'] != "") {
  $studID = $_GET['id'];
  $_SESSION['studID'] = $studID;
}

if (!$studID) {
  $studID = $_SESSION['studID'];
}

$sql_maxstud = "SELECT MAX(id) max_studid
        FROM enrolment
        WHERE studID='{$studID}'
        AND status NOT IN('delete', 'cancel')";
$qry_maxstud = $conn_i->query($sql_maxstud);
$rs_maxstud = $qry_maxstud->fetch_assoc();
$max_enrol_id = $rs_maxstud['max_studid'];

if ($_POST['save_pic']) {
  $sql = "UPDATE students
          SET picture='{$_SESSION['image']}'
          WHERE id='{$studID}'";
  $conn_i->query($sql);
}
$sql_stud = "SELECT * FROM students
          WHERE deleted!='yes'
          AND id='{$studID}'";
$qry_stud = $conn_i->query($sql_stud);
while ($info = $qry_stud->fetch_assoc()) {
  $picture = $info['picture'];
  $college = $info['college'];
  // echo $info['deleted'].' --- ';
  $type = $info['type'];
  $fname = strtoupper($info['fname']);
  $lname = strtoupper($info['lname']);
  $mname = strtoupper($info['mname']);
  $_SESSION['stud_fullname'] = $lname . ', ' . $fname . ' ; ' . $mname;
  $gender = $info['sex'];
  $birthdate = $info['bmonth'] . " " . $info['bday'] . ", " . $info['byear'];
  $bday = strtotime($info['bmonth'] . " " . $info['bday'] . " " . $info['byear']);
  $bdate = date('Y-m-d', $bday);
  $civilStatus = $info['civilStatus'];
  $religion = $info['religion'];
  $nationality = $info['nationality'];
  $contact = $info['contact'];
  $emailAdd = $info['emailAdd'];
  $street = $info['street'];
  $town = $info['town'];
  $province = $info['province'];
  $lastSchool = $info['lastSchool'];
  $lastSchoolYear = $info['lastSchoolYear'];
  $elementary = $info['elementary'];
  $elemYear = $info['elemYear'];
  $hs = $info['hs'];
  $hsYear = $info['hsYear'];
  $prim = $info['prim'];
  $primYear = $info['primYear'];
  $parents = $info['parents'];
  $parentsAddress = $info['parentsAddress'];
  $occupation = $info['occupation'];
  $contactHome = $info['contactHome'];
  $contactOffice = $info['contactOffice'];
  $prev_balance = $info['prev_balance'];
  //Check If Image Exists
  if (!file_exists('saved_images/' . $picture)) {
    if ($gender == 'male') {
      $picture = 'avatarMale.jpg';
    } else {
      $picture = 'avatarFemale.jpg';
    }
  }


  if ($picture == "") {
    if ($gender == 'male') {
      $picture = 'avatarMale.jpg';
    } else {
      $picture = 'avatarFemale.jpg';
    }
  }


  // END
}
if ($_GET['addon_id']) {
  $addon_id = $_GET['addon_id'];
  $sql = "DELETE FROM enroladdons
          WHERE id='{$addon_id}'";
  $conn_i->query($sql);
}

if ($college == 'yes') {
  $sql_req = "SELECT COUNT(id) count_req
          FROM requirements
          WHERE studID='{$studID}'";
  $qry_req = $conn_i->query($sql_req);
  $rs_req = $qry_req->fetch_assoc();
  $totalreq = $rs_req['count_req'];
  if ($type == 'yes') {
    $percent = ($totalreq / 5) * 100;
  } else {
    $percent = ($totalreq / 4) * 100;
  }
} else {
  $sql_countreq = "SELECT COUNT(id) count_req
          FROM requirements
          WHERE studID='{$studID}'
          AND syear=(SELECT syear FROM enrolment WHERE studID='{$studID}' AND status!='delete' ORDER BY id DESC)";
  $qry_countreq = $conn_i->query($sql_countreq);
  $rs_countreq = $qry_countreq->fetch_assoc();
  $totalreq = $rs_countreq['count_req'];
  if ($enrolCourse == 'Grade 11' or $enrolCourse == 'Grade 12') {
    $percent = ($totalreq / 6) * 100;
  } elseif ($enrolCourse == 'Grade 7' || $enrolCourse == 'Grade 8' || $enrolCourse == 'Grade 9' || $enrolCourse == 'Grade 10') {
    $percent = ($totalreq / 5) * 100;
  } elseif ($enrolCourse == 'Grade 1' || $enrolCourse == 'Grade 2' || $enrolCourse == 'Grade 3' || $enrolCourse == 'Grade 4' || $enrolCourse == 'Grade 5' || $enrolCourse == 'Grade 6') {
    $percent = ($totalreq / 3) * 100;
  } elseif ($enrolCourse == 'Kinder 2') {
    $percent = ($totalreq / 2) * 100;
  } else {
    $percent = ($totalreq / 1) * 100;
  }
}

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>BNSC - Profile</title>
  <link rel="icon" type="image/x-icon" href="../favicon.png">
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
  <link rel="stylesheet" href="css/profile.css" type="text/css">
  <!-- Added modules Css -->
  <link rel="stylesheet" href="php-file/css/print-billing-style.css" type="text/css">
  <!-- JQuery Confirm CSS -->
  <link rel="stylesheet" href="php-file/jquery-confirm/jquery-confirm.min.css" type="text/css">
  <!-- Stud Ledger CSS -->
  <link rel="stylesheet" href="php-file/stud-ledger/css/style.css" type="text/css">
  <link rel="stylesheet" href="css/font-awesome.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<style>
  .box {
    margin-top: 0px !important;
  }

  #camera_wrapper {
    float: center;
    width: 250px;
  }

  #show_saved_img {
    float: center;
    width: 250px;
  }

  @font-face {
    font-family: myFirstFont;
    src: url(fonts/Antonio-Regular.ttf);
  }

  td {
    padding: 1px;
    margin: 1px;
  }

  .profile-user-img-custom {
    position: relative;
    width: 200px;
    height: 200px;
    border: 3px solid #d2d6de;
    border-radius: 50%;
  }

  .img-div {
    position: relative;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
  }

  .capturebtn {
    position: absolute;
    top: 200px;
    left: 300px;
    transform: translate(-45%, -45%);
    display: block;
    color: white;
    font-weight: bold;
    text-shadow: 1px 1px gray;
  }

  /* @media only screen and (max-width: 600px) {
  .capturebtn {
    left: 250px;
    }
  }*/
  @media (min-width: 501px) and (max-width: 600px) {
    .capturebtn {
      left: 340px;
    }
  }

  @media (min-width: 400px) and (max-width: 500px) {
    .capturebtn {
      left: 275px;
    }
  }

  @media (min-width: 300px) and (max-width: 399px) {
    .capturebtn {
      left: 270px;
    }
  }

  @media (min-width: 200px) and (max-width: 299px) {
    .capturebtn {
      left: 195px;
    }
  }

  @media (min-width: 1000px) and (max-width: 1100px) {
    .capturebtn {
      left: 240px;
    }
  }

  @media (min-width: 900px) and (max-width: 999px) {
    .capturebtn {
      left: 530px;
    }
  }

  @media (min-width: 800px) and (max-width: 899px) {
    .capturebtn {
      left: 475px;
    }
  }

  @media (min-width: 700px) and (max-width: 799px) {
    .capturebtn {
      left: 470px;
    }
  }

  /*Added Module*/
  .loader {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 9999;
    background: url('php-file/img/loader.svg') 50% 50% no-repeat rgb(249, 249, 249);
    opacity: 0.9;
    cursor: wait;
    background-size: 20% 20%;
  }

  .loader font {
    bottom: 35%;
    position: absolute;
    width: 100%;
    text-align: center;
  }

  /*End*/
</style>

<script src="js/jquery-1.5.2.min.js"></script>
<?php if ($_SESSION['medEntry'] == 'on') { ?>
  <script type="text/javascript">
    $(document).ready(function() {
      $("#newMed").modal('show');
    });
  </script>
<?php } ?>

<body class="hold-transition skin-blue layout-top-nav">
  <!-- Added Module -->
  <div class="loader">
    <font>- L O A D I N G -<br><br>P L E A S E W A I T</font>
  </div>
  <!-- End -->
  <div class="wrapper">
    <?php require 'includes/menu.php'; ?>

    <!-- ALERTS -->
    <div class="alert alert-success" role="alert" id="alert-success" style="position: fixed;bottom:20px;right:20px;z-index:1051;display:none;box-shadow: 0px 1px 3px #000;font-family:Arial, Helvetica, sans-serif;">
      <i class="fa fa-check-circle" aria-hidden="true" style="display: inline-flex;"></i>
      <div class="msg" style="display: inline-flex;"></div>
    </div>
    <div class="alert alert-danger" role="alert" id="alert-danger" style="position: fixed;bottom:20px;right:20px;z-index:1051;display:none;box-shadow: 0px 1px 3px #000;font-family:Arial, Helvetica, sans-serif;">
      <i class="fa fa-times-circle" aria-hidden="true" style="display: inline-flex;"></i>
      <div class="msg" style="display: inline-flex;"></div>
    </div>
    <div class="alert alert-warning" role="alert" id="alert-warning" style="position: fixed;bottom:20px;right:20px;z-index:1051;display:none;box-shadow: 0px 1px 3px #000;font-family:Arial, Helvetica, sans-serif;">
      <i class="fa fa-exclamation-triangle" aria-hidden="true" style="display: inline-flex;"></i>
      <div class="msg" style="display: inline-flex;"></div>
    </div>
    <?php
    require 'php-file/modal/confirm-class-modal.php';
    ?>
    <!-- END ALERTS -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Student's Record
        </h1>
        <div class="breadcrumb">
          <div class="dropdown">
            <?php if ($privilege != 'dean') { ?>
              <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-folder-open"></i> Show Documents
              </button>
            <?php } ?>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="border: solid 1px black;">
              <?php if ($privilege == 'superadmin' or $privilege == 'registrar') { ?>
                <li><a href="#" onClick='window.open("print_Enrollment_dean.php?enrol_ID=<?php echo $max_enrol_id; ?>","targetWindow", "toolbar=no, location=no, ummtatus=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show Student Copy">
                    <i class="fa fa-file"></i> Enrollment Form
                  </a> </li>
              <?php } ?>
              <!--?php if ($privilege == 'superadmin' OR $privilege == 'accounting'){ ?>
                  <li><a href="#" onClick='window.open("SOAindi.php?id=<?php //echo $studID; 
                                                                        ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left"  title="Show Statement of Accounts">
                    SOA
                    </a> </li>
                  <?php //if (date('m')=='04' OR date('m')=='05'){ 
                  ?>
                      <li><a href="#" onClick='window.open("SOA_summer1.php?id=<?php //echo $studID; 
                                                                                ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left"  title="Show Statement of Accounts">
                    SOA Summer 1
                    </a> </li>
                  <li><a href="#" onClick='window.open("SOA_summer2.php?id=<?php //echo $studID; 
                                                                            ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left"  title="Show Statement of Accounts">
                    SOA Summer 2
                    </a> </li>
                  <li><a href="#" onClick='window.open("SOA_summer3.php?id=<?php //echo $studID; 
                                                                            ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left"  title="Show Statement of Accounts">
                    SOA Summer 3
                    </a> </li>
                 ?php //} ? -->

              <li><a href="#" onClick='window.open("all_payments.php?studID=<?php echo $studID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1220, height=654"); return false;' data-toggle="tooltip" data-placement="left" title="Show All Payments Record">
                  <i class="fa fa-receipt"></i> All Payments
                </a> </li>
              <?php //} 
              ?>
              <?php if (in_array('soa_stud_ledger', $_SESSION['user-access'])) { ?>
                <li>
                  <a href="javascript:void(0)" class="btn-stud-ledger" data-id="<?php echo $studID; ?>" data-toggle="tooltip" data-placement="left" title="Show Student Ledger">
                    <i class="fa fa-folder"></i> Ledger
                  </a>
                </li>
              <?php } ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Main content -->
      <section class="content" style="margin-top: 20px;">
        <div class="row">
          <div class="col-md-4">
            <!-- Profile Image -->
            <div class="box box-primary">
              <div class="box-body box-profile">
                <center>
                  <div class="img-div">
                    <img class="profile-user-img-custom" src="saved_images/<?php echo $picture; ?>" alt="User profile picture">
                  </div>
                </center>
                <!--   <img class="profile-user-img img-responsive img-circle" src="saved_images/<?php echo $picture; ?>" alt="User profile picture"> -->
                <p class="capturebtn">
                  <?php if ($privilege == 'superadmin') { ?>
                    <button class="btn btn-link btn-xs" data-toggle="modal" data-target="#takePicture"><i class="fa fa-camera" aria-hidden="true"></i> Take Picture</button>
                  <?php } ?>
                </p>

                <h3 class="profile-username text-center"><?php echo strtoupper($lname) . ', ' . $fname;
                                                          if ($mname != "") {
                                                            echo ' <i>y</i> ' . strtoupper($mname);
                                                          } ?></h3>

                <!-- p class="text-muted text-center"><?php echo ucwords($occupation); ?></p -->
                <div class="row">
                  <div class="col-md-3">ID No: </div>
                  <div class="col-md-9"><b><?php echo $studID; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Gender: </div>
                  <div class="col-md-4"><b><?php echo ucwords($gender); ?></b></div>
                  <div class="col-md-1">Age: </div>
                  <div class="col-md-4"><b><?php $age = floor((time() - strtotime($bdate)) / 31556926);
                                            $_SESSION['age'] = $age;
                                            echo $age; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Birth Date: </div>
                  <div class="col-md-6"><b><?php echo $birthdate; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Address: </div>
                  <div class="col-md-9"><b><?php echo ucwords($street) . ', ' . ucwords($town) . ', ' . ucwords($province); ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Contact: </div>
                  <div class="col-md-6"><b><?php echo $contact; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Email Add: </div>
                  <div class="col-md-6"><b><?php echo $emailAdd; ?></b></div>
                </div>
              </div>
              <!-- /.box-body -->
            </div>
            <!-- /.box -->

            <!-- About Me Box -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Personal Background:</h3>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-3">Nationality: </div>
                  <div class="col-md-9"><b><?php echo ucwords($nationality); ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Religion: </div>
                  <div class="col-md-9"><b><?php echo ucwords($religion); ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">P/G Name: </div>
                  <div class="col-md-9"><b><?php echo ucwords($parents); ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Occupation: </div>
                  <div class="col-md-9"><b><?php echo ucwords($occupation); ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">P/G Address: </div>
                  <div class="col-md-9"><b><?php echo $parentsAddress; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">P/G Contact: </div>
                  <div class="col-md-9"><b><?php echo $contactHome; ?></b></div>
                </div>
              </div>
              <div class="box-header with-border">
                <h3 class="box-title">Education:</h3>
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <div class="row">
                  <div class="col-md-3">Elementary: </div>
                  <div class="col-md-9"><b><?php echo $elementary . ', ' . $elemYear; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Junior High School: </div>
                  <div class="col-md-9"><b><?php echo $hs . ', ' . $hsYear; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3">Senior High School: </div>
                  <div class="col-md-9"><b><?php echo $prim . ', ' . $primYear; ?></b></div>
                </div>
                <div class="row">
                  <div class="col-md-3" style="padding-right:0px;margin-right:0px;">Prev's School: </div>
                  <div class="col-md-9"><b><?php echo $lastSchool . ', ' . $lastSchoolYear; ?></b></div>
                </div>
              </div>
              <!-- /.box-body if-->
            </div>
            <!-- /.box -->
          </div>
          <!-- /.col -->

          <div class="col-md-8">
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#financial" data-toggle="tab">Financial Reports</a></li>
                  
                <li><a href="#academic" data-toggle="tab">Academic Reports</a></li>
                <li id="require_color"><a>Requirements:</a>
                  <?php
                  if ($percent >= 100) {
                    echo '<span class="fas fa-check-circle"></span>';
                  } else {
                    echo '<span class="fas fa-times-circle"></span>';
                  }
                  ?>
                </li>
              </ul>

              <div class="tab-content">



                <div class="tab-pane" id="academic">

                  <div class="box box-solid">

                    <?php
                    $countSY = 0;
                    $sql_enrol = "SELECT *
          FROM enrolment
          WHERE studID='{$studID}'
          AND status!='delete'
          ORDER BY id DESC";
                    $qry_enrol = $conn_i->query($sql_enrol);



                    while ($info2 = $qry_enrol->fetch_assoc()) {

                      $countSY++;
                      //$enrolID[$countSY] = $info2['id'];
                      $enrolID = $info2['id'];
                      $level = $info2['level'];
                      $status = $info2['status'];
                      $semester = $info2['semester'];
                      $sem[$countSY] = $semester;
                      $syear = $info2['syear'];
                      $enrolFee[$countSY] = $info2['enrol_fee'];
                      $miscFee[$countSY] = $info2['misc_fee'];
                      $library_Fee[$countSY] = $info2['library_fee'];
                      $course[$countSY] = $info2['course'];
                      $tuition[$countSY] = $info2['tuition'];
                      $less_amount[$countSY] = $info2['less_amount'];
                      $less_reason[$countSY] = $info2['less_reason'];
                      $discount[$countSY] = explode('@@@@', $info2['discount']);
                      $discount[$countSY][0];
                      $discount[$countSY][1];
                      $discount[$countSY][2];
                      $discount_1 = explode('@@@', $discount[$countSY][0]);
                      $discount_2 = explode('@@@', $discount[$countSY][1]);
                      $discount_3 = explode('@@@', $discount[$countSY][2]);
                      if ($status == '') {
                        $_SESSION['enrol-status'] = 'PENDING';
                        $_SESSION['enrol_stat'] = 'PENDING';
                      } else if ($status == 'cancel') {
                        $_SESSION['enrol-status'] = '<span style="color:red;">WITHDRAWN</span>';
                        $_SESSION['enrol_stat'] = 'cancel';
                      } else {
                        $_SESSION['enrol-status'] = $status;
                        $_SESSION['enrol_stat'] = '$status';
                        strtoupper($_SESSION['enrol-status']);
                      }

                      include 'includes/student_evaluation.php';

                      if ($level >= 1) {
                        $title_fot = 'Show Student FOT';
                        $title_pre = 'Show Student Prelim';
                        $title_mid = 'Show Student Midterm';
                        $title_sfin = 'Show Student Semi-Finals';
                        $title_fin = 'Show Student Finals';
                      } else {
                        $title_1st = 'Show Student 1st Grading';
                        $title_2nd = 'Show Student 2nd Grading';
                        $title_3rd = 'Show Student 3rd Grading';
                        $title_4th = 'Show Student 4th Grading';
                      }



                    ?>
                      <div class="panel box box-primary">
                        <div class="box-header with-border" style="background-color:seashell;">
                          <div class="col-md-7" style="padding-left:0px;">
                            <h5 class="box-title" <?php if ($status == 'temporary') {
                                                    echo 'style="color:lightgray;"';
                                                  } ?>>
                              <?php echo ucwords($semester);
                              if ($semester == '1st' or $semester == '2nd') {
                                echo ' Semester';
                              };
                              if ($semester == 'summer') {
                                echo ' of ' . $syear . '-' . ($syear + 1);
                                //echo ' of '.($syear-1).'-'.$syear;
                              } else {
                                echo ' of ' . $syear . '-' . ($syear + 1);
                              }
                              ?> | <b><?php echo str_replace('BACHELOR OF SCIENCE', 'BS', $info2['course']) . '<br/><font size="2">STATUS: ' . strtoupper($_SESSION['enrol-status']) . '</font>'; ?></b>
                            </h5>
                          </div>
                          <div class="col-md-4" style="text-align:right; padding-right:1px;">
                            <h5 class="box-title" <?php if ($status == 'temporary') {
                                                    echo 'style="color:lightgray;"';
                                                  } ?>><?php echo $info2['major']; ?>

                            </h5>
                          </div>
                          <div class="col-md-1" style="text-align:right; padding-right:1px;">
                            <?php if ($privilege == 'superadmin' or $privilege == 'registrar' or $userid == '29' or $privilege == 'dean' or $userid == '51') { ?>
                              <!-- START Semester MENU -->
                              <div class="dropdown">
                                <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="background-color:lightgray; border:solid 1px;">

                                  <!-- ALTERED BY DOMZ START-->
                                  <?php if ($privilege == 'superadmin' or $privilege == 'registrar' or $userid == '29') { ?>
                                    <?php if ($_SESSION['enrol-status'] != 'CANCELLED') { ?>
                                      <li>
                                        <a href="#" onClick='window.open("print_studentCopy.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=900, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show Student Copy" style="color:black; padding-left: 18px;"><i class="fa fa-print" aria-hidden="true"></i> Print Student Copy </a>
                                      </li>
                                      <!-- force print enrolment for ADMIN -->
                                      <?php if (in_array('print_enrolment_form', $_SESSION['user-access'])) { ?>
                                        <li>
                                          <a href="#" onClick='window.open("print_Enrollment_dean.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=7000"); return false;' data-toggle="tooltip" data-placement="left" title="Print Enrollment Form" style="color:black; padding-left: 18px;"><i class="fa fa-print text-danger" aria-hidden="true"></i> Enrollment Form </a>
                                        </li>
                                      <?php } ?>
                                      <!-- end -->
                                      <li>
                                        <a href="#" onClick='window.open("print_studentCoE.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Certificate of Enrollment" style="color:black; padding-left: 20px;"><i class="far fa-file" aria-hidden="true"></i> Certificate of Enrollment </a>
                                      </li>
                                      <li>
                                        <a href="#" onClick='window.open("print_studentCoR.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Certificate of Registration" style="color:black;"><i class="fas fa-file-contract" aria-hidden="true"></i> Certificate of Registration </a>
                                      </li>
                                      <li>
                                        <a href="#" onClick='window.open("print_studentToR.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes , width=816, height=700" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Transcript of Records" style="color:black;"><i class="fa fa-file" aria-hidden="true"></i> Transcript of Records </a>
                                      </li>

                                      <?php if ($info2['course'] == 'Grade 10') { ?>
                                        <li>
                                          <a href="#" onClick='window.open("print_JhighschoolDiploma.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=1040, height=700" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Diploma" style="color:black; padding-left: 20px;"><i class="far fa-file-code space" aria-hidden="true"></i> Diploma </a>
                                        </li>
                                      <?php } ?>
                                      <?php if ($info2['course'] == 'Grade 12') { ?>
                                        <li>
                                          <a href="#" onClick='window.open("print_highschoolDiploma.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=1040, height=700" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Diploma" style="color:black; padding-left: 20px;"><i class="far fa-file-code space" aria-hidden="true"></i> Diploma </a>
                                        </li>
                                        <li>
                                          <a href="#" onClick='window.open("highSchoolAlumni.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=1000, height=1100" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Alumni ID" style="color:black; padding-left: 20px;"><i class="far fa-id-card" aria-hidden="true" style="margin-right: 5px;"></i> Alumni ID </a>
                                        </li>
                                      <?php } ?>
                                      <?php if ($level >= 4) { ?>
                                        <li>
                                          <a href="#" onClick='window.open("print_collegeDiploma.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=1040, height=700" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Diploma" style="color:black; padding-left: 20px;"><i class="far fa-file-code space" aria-hidden="true"></i> Diploma </a>
                                        </li>
                                        <li>
                                          <a href="#" onClick='window.open("collegeAlumni.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=1280, height=510" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Alumni ID" style="color:black; padding-left: 20px;"><i class="far fa-id-card" aria-hidden="true" style="margin-right: 5px;"></i> Alumni ID </a>
                                        </li>
                                      <?php } ?>

                                      <li>
                                        <?php if ($level >= 1) { ?>
                                          <a href="#" onClick='window.open("print_collegeGMC.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes" ); return false;' data-toggle="tooltip" data-placement="left" title="Show GMC" style="color:black; padding-left: 20px;"><i class="far fa-file-archive space" aria-hidden="true"></i> Good Moral Certificate </a>
                                        <?php } else { ?>
                                          <a href="#" onClick='window.open("print_highschoolGMC.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes" ); return false;' data-toggle="tooltip" data-placement="left" title="Show GMC" class="logo_bnef" style="color:black; padding-left: 20px;"><i class="far fa-file-archive space" aria-hidden="true"></i> Good Moral Certificate </a>
                                        <?php } ?>
                                      </li>

                                      <li>
                                        <a href="#" onClick='window.open("<?php echo "print_studentEvaluation.php" ?>?enrol_ID=<?php echo $enrolID; ?>&course=<?php echo $info2['course']; ?>&studid=<?php echo $studID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes width=620, height=700" ); return false;' data-toggle="tooltip" data-placement="left" title="Show Student Evaluation" style="color:black;"><i class="far fa-file-alt" aria-hidden="true"></i> Student Evaluation </a>
                                      </li>
                                      <li class="rating_dropdown">
                                        <button class="rating_dropbtn"><i class="fa fa-file-alt space" aria-hidden="true"></i> Student Rating </button>
                                        <div class="rating_dropdown-content">
                                          <?php if ($semester == 'Summer') { ?>
                                              <a href="#" onClick='window.open("print_studAllRating.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show All Terms">ALL TERMS</a>
                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=midterm","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_mid . $title_2nd; ?>'><?php echo $level >= 1 ? 'Midterm' : '2nd Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=finals","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_fin . $title_4th; ?>'><?php echo $level >= 1 ? 'Finals' : '4th Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studFinalRating.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show Student Final Rating">Final Rating</a>
                                            <?php } else {
                                            if ($syear > 2022 and strtolower($semester) != 'summer' and $level >= 1 and $syear < 2024) { ?>
                                             
                                              <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=fot","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_fot ?>'><?php echo $level >= 1 ? 'FOT' : ''; ?></a>
                                            <?php } ?>
                                           <a href="#" onClick='window.open("print_studAllRating.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show All Terms">ALL TERMS</a>

                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=prelim","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_pre . $title_1st; ?>'><?php echo $level >= 1 ? 'Prelim' : '1st Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=midterm","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_mid . $title_2nd; ?>'><?php echo $level >= 1 ? 'Midterm' : '2nd Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=semi-finals","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_sfin . $title_3rd; ?>'><?php echo $level >= 1 ? 'Semi-Finals' : '3rd Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studRating.php?enrol_ID=<?php echo $enrolID; ?>&term=finals","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title='<?php echo $title_fin . $title_4th; ?>'><?php echo $level >= 1 ? 'Finals' : '4th Grading'; ?></a>
                                            <a href="#" onClick='window.open("print_studFinalRating.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=900"); return false;' data-toggle="tooltip" data-placement="left" title="Show Student Final Rating">Final Rating</a>
                                          <?php } ?>
                                        </div>
                                      </li>

                                    <?php } ?>
                                  <?php } ?>

                                  <?php if ($privilege == 'registrar' || $privilege == 'superadmin') { ?>
                                    <li>
                                      <a href="#" onClick='window.open("grade_entry.php?enrol_id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=600"); return false;' data-toggle="tooltip" data-placement="left" title="Update Grades" style="color:black;"><i class="fa fa-fire" aria-hidden="true"></i> Grade Entry</a>
                                    </li>
                                  <?php } ?>
                                  <!-- ALTERED BY DOMZ END-->
                                  <!-- Disabled Options Function -->
                                  <?php if ($countSY == 1 || $userid == '29' || $userid == '41' || $userid == '10' || $userid == '51') { ?>
                                    <?php if ($_SESSION['enrol-status'] != 'CANCELLED') { ?>
                                      <?php if ($_SESSION['enrol-status'] != 'CONFIRM' && $privilege == 'registrar' || $privilege == 'superadmin') { ?>
                                        <li>
                                          <a href="#" onClick='window.open("edit_enrolment.php?enrol_id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=700"); return false;' data-toggle="tooltip" data-placement="left" title="Edit Enrolment" style="color:black;"><i class="fa fa-edit" aria-hidden="true"></i> Edit Enrolment</a>
                                        </li>

                                      <?php } else if ($_SESSION['enrol-status'] == 'confirm' && $privilege == 'dean') { ?>
                                        <li>
                                          <a href="javascript:void(0)" onClick='alert("Unable to proceed actions.")' data-toggle="tooltip" data-placement="left" title="No Actions Required" style="color:black;"><i class="fa fa-times" aria-hidden="true"></i> No Action Privilege</a>
                                        </li>
                                      <?php } else { ?>
                                        <li>
                                          <a href="#" onClick='window.open("edit_enrolment.php?enrol_id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=700"); return false;' data-toggle="tooltip" data-placement="left" title="Edit Enrolment" style="color:black;"><i class="fa fa-edit" aria-hidden="true"></i> Edit Enrolment</a>
                                        </li>
                                      <?php } ?>

                                    <?php } ?>
                                    <!-- ALTERED BY DOMZ START-->
                                    <?php if ($privilege == 'superadmin' || $privilege == 'registrar' || $userid == '29' || $userid == '51' || $privilege == 'dean') {
                                      if ($status != 'confirm') {
                                    ?>
                                        <li>
                                          <a href="delete_enrolment.php?enrol_id=<?php echo $enrolID; ?>" onClick="return confirm('DELETE Enrollment ?');" data-toggle="tooltip" data-placement="left" title="Delete Enrolment" style="color:black;"><i class="fa fa-trash" aria-hidden="true"></i> Delete Enrolment</a>
                                        </li>
                                        <!-- Cancel Enrolment -->
                                      <?php } ?>
                                      <!-- <li>
                    <a href="" onClick='window.open("transfer_block.php?enrol_id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=700"); return false;' title="Transfer Block" style="color:black;"><i class="fa fa-arrow-right" aria-hidden="true"></i>Transfer Block</a>
                  </li> -->
                                      <?php if ($privilege != 'dean' && $_SESSION['enrol_stat'] != 'cancel') { ?>
                                        <li>
                                          <a href="cancel_enrolment.php?enrol_id=<?php echo $enrolID; ?>" onClick="return confirm('CANCEL Enrollment ?');" data-toggle="tooltip" data-placement="left" title="Withdraw Enrolment" style="color:black;"><i class="fa fa-ban" aria-hidden="true"></i> Withdraw Enrolment</a>
                                        </li>
                                      <?php } ?>
                                    <?php } ?>
                                    <!-- ALTERED BY DOMZ END-->

                                  <?php } else { ?>
                                    <li style="color:gray; padding-left:20px; padding-bottom:5px;"><i class="fa fa-edit" aria-hidden="true"></i> &#160; &#160;Edit Enrolment</li>
                                    <li style="color:gray; padding-left:20px;"><i class="fa fa-trash" aria-hidden="true"></i> &#160; &#160; Delete Enrolment</li>
                                  <?php } ?>

                                </div>
                              </div>
                              <!-- END Semester MENU-->
                            <?php } ?>
                          </div>
                        </div>
                        <table class="table table-condensed" style="font-family: 'Arial',sans-serif; font-size:12px;">
                          <?php if ($status == 'temporary') { ?>
                            <tr>
                              <th colspan="10" style="background-color:indianred; color:white;">
                                <div class="row">
                                  <div class="col-md-6">
                                    STATUS: <?php echo strtoupper($_SESSION['enrol-status']); ?>
                                  </div>
                                  <div class="col-md-6" style="text-align:right;">

                                    <!-- Altered By Domz -->
                                    <?php if ($privilege == 'superadmin' || $_SESSION['privilege'] == 'registrar') { ?>

                                      <?php if ($_SESSION['enrol-status'] != 'CANCELLED') {
                                      ?>
                                        <a href="#" onClick='window.open("print_Enrollment_dean.php?enrol_ID=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=720, height=7000"); return false;' data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-info" title="Show Student's Copy"><i class="fa fa-file-alt text-red" aria-hidden="true"></i> Enrollment Form</a>
                                      <?php } ?><!--enrol status -->

                                    <?php } ?>

                                    <!-- Altered By Domz -->

                                    &#160; &#160;
                                    <?php if ($_SESSION['enrol-status'] != 'CANCELLED') { ?>
                                      <a href="#" onClick='window.open("add_subjects.php?enrol_id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1000, height=700"); return false;' data-toggle="tooltip" data-placement="top" title="Add Subjects" class="btn btn-xs btn-info"><i class="fa fa-folder-plus text-red" aria-hidden="true"></i> Add Subject</a>
                                    <?php } ?><!--enrol status -->

                                    <?php
                                    if ($privilege == 'superadmin' || $_SESSION['privilege'] == 'registrar') {
                                    ?>
                                      &#160; &#160;
                                      <?php if ($_SESSION['enrol-status'] != 'CANCELLED') { ?>
                                        <a href="javascript:void(0)" en-id="<?php echo $enrolID; ?>" stud-id="<?php echo $studID; ?>" data-toggle="tooltip" data-placement="top" title="Confirm Enrollment" class="btn btn-xs btn-info btn-confirm-enrol"><i class="fa fa-exclamation-triangle text-red" aria-hidden="true"></i> Confirm</a>
                                      <?php } ?> <!--enrol status -->
                                    <?php } ?>
                                  </div>
                                </div>
                              </th>
                            </tr>
                          <?php } ?>
                          <tr style="background-color: lightgray;">
                            <th>Subject</th>
                            <th>Description</th>
                            <?php
                            if ($semester == 'Summer' || stripos($semester, 'Summer') !== false) {
                              echo '<th class="output_grades" style="text-align:right; width: 60px;">Units</th>
                                <th class="output_grades" style="width: 65px;">Midterm</th>
                                <th class="output_grades" style="width: 55px;">Final</th>
                                <th class="output_grades" style="width: 70px;">F-Rating</th>
                                <th class="output_grades" style="width: 100px;">Remarks</th>';
                            } else {
                              if ($syear > 2022 and $syear < 2024) {
                                echo '<th class="output_grades" style="text-align:right; width: 60px;">Units</th>
                                <th class="output_grades" style="width: 60px;">FOT</th>
                                <th class="output_grades" style="width: 60px;">Prelim</th>
                                <th class="output_grades" style="width: 65px;">Midterm</th>
                                <th class="output_grades" style="width: 60px;">S-Final</th>
                                <th class="output_grades" style="width: 55px;">Final</th>
                                <th class="output_grades" style="width: 70px;">F-Rating</th>
                                <th class="output_grades" style="width: 100px;">Remarks</th>';
                              } elseif ($level >= 1) {
                                echo '<th class="output_grades" style="text-align:right; width: 60px;">Units</th>
                                <th class="output_grades" style="width: 60px;">Prelim</th>
                                <th class="output_grades" style="width: 65px;">Midterm</th>
                                <th class="output_grades" style="width: 60px;">S-Final</th>
                                <th class="output_grades" style="width: 55px;">Final</th>
                                <th class="output_grades" style="width: 70px;">F-Rating</th>
                                <th class="output_grades" style="width: 100px;">Remarks</th>';
                              } else {
                                echo '<th class="output_grades" style="text-align:right; width: 60px;">Units</th>
                                <th class="output_grades" style="width: 60px;">1st</th>
                                <th class="output_grades" style="width: 60px;">2nd</th>
                                <th class="output_grades" style="width: 60px;">3rd</th>
                                <th class="output_grades" style="width: 60px;">4th</th>
                                <th class="output_grades" style="width: 70px;">F-Rating</th>
                                <th class="output_grades" style="width: 70px;">Remarks</th>';
                              }
                            }
                            ?>

                          </tr>
                          <?php
                          $units_bracketed = 0;
                          $totalUnits = 0;
                          $sql_enrolsubj = "SELECT *
                    FROM enroll_subjects
                    WHERE enrol_id='{$enrolID}'
                    AND status=''";
                          $qry_enrolsubj = $conn_i->query($sql_enrolsubj);
                          while ($info3 = $qry_enrolsubj->fetch_assoc()) {
                            $subject_id = $info3['id'];
                            $subject = $info3['title'];
                            $description = $info3['description'];
                            $units = $info3['units'];
                            if ($units[0] == '(') {
                              $units_bracketed = $units_bracketed + unit_format($units);
                            }

                            //$units = preg_replace('/[^A-Za-z0-9\-]/', '', $info3['units']);
                            //$amountPerUnit[$countSY] = $info3['amount'];
                            $totalUnit[$countSY] = $totalUnit[$countSY] + $units;
                            //$amount[$countSY] = $amount[$countSY] + $info3['amount'];
                            $fot = $info3['fot'];
                            $prelim = $info3['prelim'];
                            $midterm = $info3['midterm'];
                            $sfinal = $info3['sfinal'];
                            $final = $info3['final'];
                            $remarks = $info3['remarks'];
                            $gr = 0;
                            //new module
                            $enrol_syear = $info3['syear'];
                            if ($enrol_syear >= 2024) {
                              $enroll_subjid = $info3['enrol_id']; //enrolment id
                              $subjid = $info3['subject_id'];
                              $adm_stat = adm_stat($enroll_subjid, $conn_i); //admission
                              $current_remarks = $remarks; //INC,INCOMPLETE,NG,NO GRADE
                              $remarks_arr = ['INC', 'INCOMPLETE', 'NG', 'NO GRADE'];
                              $gsub_stat = gsub_stat($subjid, $conn_i); //submission stat
                            }
                          ?>
                            <tr>
                              <td><?php echo $subject; ?></td>
                              <td><?php echo $description; ?></td>
                              <td style="text-align:right;"><?php echo $units;
                                                            $totalUnits = $totalUnits + $units; ?></td>
                              <?php if ($semester == 'Summer' || stripos($semester, 'Summer') !== false) { ?>
                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($midterm > 0) {
                                      if ($gsub_stat[1] == '' || $gsub_stat[1] == 'pending') {
                                        
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        echo number_format($midterm, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($midterm > 0) { ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($midterm, 1);
                                        } else {
                                          echo $midterm;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>
                                </td>
                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($final > 0) {
                                      if ($gsub_stat[3] == '' || $gsub_stat[3] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        echo number_format($final, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($final > 0) { ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($final, 1);
                                        } else {
                                          echo $final;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>

                                </td>
                                <?php } else {
                                if ($syear > 2022 and $syear < 2024) { ?>
                                  <td class="output_grades">
                                    <?php if ($fot > 0) {
                                      $gr++; ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($fot, 1);
                                        } else {
                                          echo $fot;
                                        }
                                        ?>
                                      </a>
                                    <?php } else {
                                      echo '--';
                                    } ?>
                                  </td>

                                <?php } ?>

                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($prelim > 0) {
                                      $gr++;
                                     if ($gsub_stat[0] == '' || $gsub_stat[0] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        echo number_format($prelim, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($prelim > 0) {
                                      $gr++; ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($prelim, 1);
                                        } else {
                                          echo $prelim;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>

                                </td>
                                <!-- onClick="return confirm('Are you sure you want to Reset Grade in Midterm ?'); -->
                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($midterm > 0) {
                                      if ($gsub_stat[1] == '' || $gsub_stat[1] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                       
                                      } else {
                                        echo number_format($midterm, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($midterm > 0) { ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($midterm, 1);
                                        } else {
                                          echo $midterm;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>

                                </td>
                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($sfinal > 0) {
                                      if ($gsub_stat[2] == '' || $gsub_stat[2] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        echo number_format($sfinal, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($sfinal > 0) { ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($sfinal, 1);
                                        } else {
                                          echo $sfinal;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>

                                </td>
                                <td class="output_grades">
                                  <?php if ($enrol_syear >= 2024) {
                                    if ($final > 0) {
                                      if ($gsub_stat[3] == '' || $gsub_stat[3] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        echo number_format($final, 1);
                                      }
                                    } else {
                                      echo '--';
                                    }
                                  } else { ?>
                                    <?php if ($final > 0) { ?>
                                      <a>
                                        <?php
                                        if ($level >= 1) {
                                          echo number_format($final, 1);
                                        } else {
                                          echo $final;
                                        }
                                        ?>
                                      </a>
                                  <?php } else {
                                      echo '--';
                                    }
                                  } ?>

                                </td>

                              <?php } ?>

                              <td class="output_grades">
                                <?php
                                if ($level >= 1) {
                                  if ($syear > 2022 and strtolower($semester) != 'summer' and $syear < 2024 and stripos($semester, 'summer') === false) {
                                    if ($fot > 0 and $prelim > 0 and $midterm > 0 and $sfinal > 0 and $final > 0) {
                                      $final_rating = ($fot + $prelim + $midterm + $sfinal + $final) / 5;
                                      echo '<b>' . number_format($final_rating, 1) . '</b>';
                                    } else {
                                      echo '--';
                                      $final_rating = "";
                                    }
                                  } elseif (
                                    $semester == '1st' || $semester == '2nd' ||
                                    stripos($semester, '1st') !== false || stripos($semester, '2nd') !== false ||
                                    stripos($semester, 'First') !== false || stripos($semester, 'Second') !== false
                                  ) {
                                    if ($enrol_syear >= 2024) {
                                      if (in_array('', $gsub_stat) || in_array('pending', $gsub_stat)) {
                                        echo '<i class="fa-solid fa-table-cells-row-lock text-danger" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        if ($prelim > 0 and $midterm > 0 and $sfinal > 0 and $final > 0) {
                                          $final_rating = ($prelim + $midterm + $sfinal + $final) / 4;
                                          echo '<b>' . number_format($final_rating, 1) . '</b>';
                                        } else {
                                          echo '--';
                                          $final_rating = "";
                                        }
                                      }
                                    } else {
                                      if ($prelim > 0 and $midterm > 0 and $sfinal > 0 and $final > 0) {
                                        $final_rating = ($prelim + $midterm + $sfinal + $final) / 4;
                                        echo '<b>' . number_format($final_rating, 1) . '</b>';
                                      } else {
                                        echo '--';
                                        $final_rating = "";
                                      }
                                    }
                                  } else {
                                    if ($enrol_syear >= 2024) {
                                      if ($gsub_stat[1] == '' || $gsub_stat[1] == 'pending' || $gsub_stat[3] == '' || $gsub_stat[3] == 'pending') {
                                        echo '<i class="fa-solid fa-table-cells-row-lock text-danger" data-toggle="tooltip" title="Not yet submitted."></i>';
                                      } else {
                                        if ($midterm > 0 and $final > 0) {
                                          $final_rating = ($midterm + $final) / 2;
                                          echo '<b>' . number_format($final_rating, 1) . '</b>';
                                        } else {
                                          echo '--';
                                          $final_rating = "";
                                        }
                                      }
                                    } else {
                                      if ($midterm > 0 and $final > 0) {
                                        $final_rating = ($midterm + $final) / 2;
                                        echo '<b>' . number_format($final_rating, 1) . '</b>';
                                      } else {
                                        echo '--';
                                        $final_rating = "";
                                      }
                                    }
                                  }
                                } else {
                                  if ($enrol_syear >= 2024) {
                                    if (in_array('', $gsub_stat) || in_array('pending', $gsub_stat)) {
                                      echo '<i class="fa-solid fa-table-cells-row-lock text-danger" data-toggle="tooltip" title="Not yet submitted."></i>';
                                    } else {
                                      if ($prelim > 0 and $midterm > 0 and $sfinal > 0 and $final > 0) {
                                        $final_rating = ($prelim + $midterm + $sfinal + $final) / 4;
                                        echo '<b>' . number_format($final_rating, 1) . '</b>';
                                      } else {
                                        echo '--';
                                      }
                                    }
                                  } else {
                                    if ($prelim > 0 and $midterm > 0 and $sfinal > 0 and $final > 0) {
                                      $final_rating = ($prelim + $midterm + $sfinal + $final) / 4;
                                      echo '<b>' . number_format($final_rating, 1) . '</b>';
                                    } else {
                                      echo '--';
                                    }
                                  }
                                }
                                ?>
                              </td>
                              <td class="output_grades">
                                <?php
                                if ($level >= 1) {
                                  if ($syear > 2022 and $syear < 2024 and strtolower($semester) != 'summer') {
                                    if ($remarks != 'NO ADMISSION') {
                                      if ($fot <= 0 and $prelim <= 0 and $midterm <= 0 and $sfinal <= 0 and $final <= 0) {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } elseif ($fot < 1 or $prelim < 1 or $midterm < 1 or $sfinal < 1 or $final < 1) {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } elseif ($final_rating > 3.04) {
                                        echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                      } elseif ($final_rating == " ") {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } else {
                                        echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                      }
                                    } else {
                                      echo "<span style='color: #3AC;'><b>$remarks</b></span>";
                                    }
                                    } else {
                                      echo "<span style='color: #3AC;'><b>$remarks</b></span>";
                                    }
                                  } elseif (
                                    $semester == '1st' || $semester == '2nd' ||
                                    stripos($semester, '1st') !== false || stripos($semester, '2nd') !== false ||
                                    stripos($semester, 'First') !== false || stripos($semester, 'Second') !== false
                                  ) {
                                    if ($enrol_syear >= 2024) {
                                      if (in_array('', $gsub_stat) || in_array('pending', $gsub_stat)) {
                                        echo '<label class="text-danger" style="font-size: 10px !important;">NOT SUBMITTED</label>';
                                      } else if (in_array('', $adm_stat) || in_array('cancelled', $adm_stat) && in_array($remarks, ['',' ', null]) ) {
                                        echo '<label class="text-danger" style="font-size: 10px !important;">NO ADMISSION</label>';
                                      } else if ($remarks == 'INC' || $remarks == 'INCOMPLETE') {
                                        echo '<label class="text-danger">INC</label>';
                                      } else if ($remarks == 'NG' || $remarks == 'NO GRADE') {
                                        echo '<label class="text-danger">NG</label>';
                                      } else if ($remarks == 'DR' || $remarks == 'DROPPED') {
                                        echo '<label class="text-danger">DR</label>';
                                      } else {
                                        if ($prelim <= 0 and $midterm <= 0 and $sfinal <= 0 and $final <= 0) {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } elseif ($prelim < 1 or $midterm < 1 or $sfinal < 1 or $final < 1) {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } elseif ($final_rating > 3.04) {
                                          echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                        } elseif ($final_rating == " ") {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } else {
                                          echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                        }
                                      }
                                    } else {
                                      if ($remarks != 'NO ADMISSION') {
                                        if ($prelim <= 0 and $midterm <= 0 and $sfinal <= 0 and $final <= 0) {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } elseif ($prelim < 1 or $midterm < 1 or $sfinal < 1 or $final < 1) {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } elseif ($final_rating > 3.04) {
                                          echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                        } elseif ($final_rating == " ") {
                                          echo "<span style='color:#ae1316;'><b>--</b></span>";
                                        } else {
                                          echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                        }
                                      } else {
                                        echo "<span style='color: #3AC;'><b>$remarks</b></span>";
                                      }
                                    }
                                  } else {
                                    if ($enrol_syear >= 2024) {
                                      if ($gsub_stat[1] == '' || $gsub_stat[1] == 'pending' || $gsub_stat[3] == '' || $gsub_stat[3] == 'pending') {
                                        echo '<label class="text-danger" style="font-size: 10px !important;">NOT SUBMITTED</label>';
                                      } else if ($adm_stat[1] == '' || $adm_stat[1] == 'cancelled' || $adm_stat[3] == '' || $adm_stat[3] == 'cancelled') {
                                        echo '<label class="text-danger" style="font-size: 10px !important;">NO ADMISSION</label>';
                                      } else if ($remarks == 'INC' || $remarks == 'INCOMPLETE') {
                                        echo '<label class="text-danger">INC</label>';
                                      } else if ($remarks == 'NG' || $remarks == 'NO GRADE') {
                                        echo '<label class="text-danger">NG</label>';
                                      } else if ($remarks == 'DR' || $remarks == 'DROPPED') {
                                        echo '<label class="text-danger">DR</label>';
                                      } else {
                                        if ($remarks == "" or $remarks == 0) {
                                          if ($midterm < 1 or $final < 1) {
                                            echo "<span style='color:#ae1316;'><b>--</b></span>";
                                          } elseif ($final_rating == " ") {
                                            echo "<span style='color:#ae1316;'><b>--</b></span>";
                                          } elseif ($final_rating > 3.04) {
                                            echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                          } else {
                                            echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                          }
                                        } else {
                                          echo $remarks;
                                        }
                                      }
                                    } else {
                                      if ($remarks != 'NO ADMISSION') {
                                        if ($remarks == "" or $remarks == 0) {
                                          if ($midterm < 1 or $final < 1) {
                                            echo "<span style='color:#ae1316;'><b>--</b></span>";
                                          } elseif ($final_rating == " ") {
                                            echo "<span style='color:#ae1316;'><b>--</b></span>";
                                          } elseif ($final_rating > 3.04) {
                                            echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                          } else {
                                            echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                          }
                                        } else {
                                          echo $remarks;
                                        }
                                      } else {
                                        echo "<span style='color: #3AC;'><b>$remarks</b></span>";
                                      }
                                    }
                                  }
                                } else {
                                  if ($enrol_syear >= 2024) {
                                    if (in_array('', $gsub_stat) || in_array('pending', $gsub_stat)) {
                                      echo '<label class="text-danger" style="font-size: 10px !important;">NOT SUBMITTED</label>';
                                    } else if (in_array('', $adm_stat) || in_array('cancelled', $adm_stat)) {
                                      echo '<label class="text-danger" style="font-size: 10px !important;">NO ADMISSION</label>';
                                    } else if ($remarks == 'INC' || $remarks == 'INCOMPLETE') {
                                      echo '<label class="text-danger">INC</label>';
                                    } else if ($remarks == 'NG' || $remarks == 'NO GRADE') {
                                      echo '<label class="text-danger">NG</label>';
                                    } else if ($remarks == 'DR' || $remarks == 'DROPPED') {
                                      echo '<label class="text-danger">DR</label>';
                                    } else {
                                      if ($prelim <= 0 and $midterm <= 0 and $sfinal <= 0 and $final <= 0) {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } elseif ($prelim < 1 or $midterm < 1 or $sfinal < 1 or $final < 1) {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } elseif ($final_rating < 75 || $final_rating > 3.04) {
                                        echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                      } elseif ($final_rating == " ") {
                                        echo "<span style='color:#ae1316;'><b>--</b></span>";
                                      } else {
                                        echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                      }
                                    }
                                  } else {
                                    if ($prelim <= 0 and $midterm <= 0 and $sfinal <= 0 and $final <= 0) {
                                      echo "<span style='color:#ae1316;'><b>--</b></span>";
                                    } elseif ($prelim < 1 or $midterm < 1 or $sfinal < 1 or $final < 1) {
                                      echo "<span style='color:#ae1316;'><b>--</b></span>";
                                    } elseif ($final_rating < 75) {
                                      echo "<span style='color:#ae1316;'><b>FAILED</b></span>";
                                    } elseif ($final_rating == " ") {
                                      echo "<span style='color:#ae1316;'><b>--</b></span>";
                                    } else {
                                      echo "<span style='color:#3f7e3d;'><b>PASSED</b></span>";
                                    }
                                  }
                                }
                                ?>
                              </td>
                            </tr>
                          <?php } ?>
                          <tr>
                            <td colspan="2"><b>Total Units</b></td>
                            <td style="text-align:right;">
                              <b><?php echo $totalUnits . '/' . ($totalUnits + $units_bracketed);
                                  $totalUnits = 0; ?></b>
                            </td>
                            <td colspan="7"></td>
                          </tr>
                        </table>
                        <table class="table table-condensed">
                          <tr style="background-color:seashell;">
                            <th colspan="5"><b>Addon List</b><?php //echo ' '..$privilege; 
                                                              ?>
                            </th>
                            <th style="text-align: right;"><a href="#" onClick='window.open("addons_segregation.php?id=<?php echo $enrolID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=920, height=900"); return false;'>Segregate</a></th>
                          </tr>
                          <tr>
                            <th>Qty</th>
                            <th>Item/Services</th>
                            <th>Description</th>
                            <th style="text-align:right;">Amount</th>
                            <th style="text-align:right;">Total</th>
                            <?php
                            if ($privilege == 'superadmin' || $_SESSION['privilege'] == 'accounting') {
                            ?>
                              <th style="text-align:center;">Options</th>
                            <?php } ?>
                          </tr>
                          <?php

                          $sql_addons = "SELECT *
                FROM enroladdons
                WHERE enrol_ID='{$enrolID}'
                AND status!='deleted'";
                          $qry_addons = $conn_i->query($sql_addons);
                          while ($infoAddon = $qry_addons->fetch_assoc()) {
                            $addon_id = $infoAddon['id']; ?>
                            <tr>
                              <td><?php echo $infoAddon['pieces']; ?></td>
                              <td><?php echo $infoAddon['item']; ?></td>
                              <td><?php echo $infoAddon['description']; ?></td>
                              <td style="text-align:right;"><?php echo number_format($infoAddon['amount'], 2); ?></td>
                              <td style="text-align:right;"><?php echo number_format(($infoAddon['pieces'] * $infoAddon['amount']), 2);
                                                            $totalAddon = $totalAddon + ($infoAddon['pieces'] * $infoAddon['amount']); ?></td>
                              <?php
                              if ($privilege == 'superadmin' || $_SESSION['privilege'] == "accounting") {
                              ?>
                                <td style="text-align:center;">
                                  <a href="profile.php?addon_id=<?php echo $addon_id; ?>" onClick="return confirm('Remove <?php echo strtoupper($infoAddon['item']); ?> from Addon list ?');" data-toggle="tooltip" data-placement="left" title="Delete Addon"><i class="fa fa-trash"></i></a>
                                </td>
                              <?php } ?>
                            </tr>
                          <?php } ?>
                          <tr>
                            <td colspan="2">Total Addons</td>
                            <td colspan="3" style="text-align:right;"><b><?php echo number_format($totalAddon, 2); ?></b></td>
                          </tr>
                        </table>
                      </div>
                    <?php $totalAddon = 0;
                    } ?>
                  </div>
                  <!-- /.post -->
                </div>
                <!-- /.tab-pane -->
                 
                <div class="tab-pane active" id="financial">
                  <div class="box box-info">
                    <div class="box-header with-border">
                      <h3 class="box-title">Statement of Accounts</h3>

                      <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                      </div>
                    </div>
                    <!-- /.box-header -->

                    <div class="box-body">
                      <div class="table-responsive">
                        <?php
                        //$countSY = 0;
                        $totalUnits = 0;
                        $units_bracketed = 0;
                        $units = 0;
                        $sql_scholar = "SELECT A.*, B.studsc_code
            FROM enrolment A
            LEFT JOIN stud_scholarship B ON(A.id=B.studsc_enrolid)
            WHERE A.studID='{$studID}'
            AND A.status NOT IN('delete','cancel')
            ORDER BY A.id ASC";
                        $qry_scholar = $conn_i->query($sql_scholar);
                        while ($info2 = $qry_scholar->fetch_assoc()) {
                          //$countSY++;
                          $enrol_ID = $info2['id'];
                          $level = $info2['level'];
                          $status = $info2['status'];
                          $sc_status = $info2['studsc_code'];
                          $semester = strtolower($info2['semester']);
                          $syear = $info2['syear'];
                          $enrolFee = $info2['enrol_fee'];
                          $miscFee = $info2['misc_fee'];
                          $course = $info2['course'];
                          $tuition = $info2['tuition'];
                          $less_amount = $info2['less_amount'];
                          $less_reason = $info2['less_reason'];
                          $discount = explode('@@@@', $info2['discount']);
                          $discount[0];
                          $discount[1];
                          $discount[2];
                          $discount_1 = explode('@@@', $discount[0]);
                          $discount_2 = explode('@@@', $discount[1]);
                          $discount_3 = explode('@@@', $discount[2]);
                          if (intval($less_amount) > 200000) {
                            $less_amount = 0;
                          }

                          if (strpos(strtolower($course), 'bachelor') !== false || strpos(strtolower($course), 'cpe') !== false) {
                            $label = 'Units';
                          } else {
                            $label = 'Hours';
                          }

                        ?>
                          <div class="row">
                            <div class="col-md-8">
                              Course: <b><?php echo str_replace('BACHELOR OF SCIENCE', 'BS', $course) . '-' . $info2['major'] . ' | ' . ucwords($semester);
                                          if ($semester == '1st' or $semester == '2nd') {
                                            echo ' Semester';
                                          };
                                          //echo ' of ' .$syear;
                                          if ($semester == 'summer') {
                                            //echo ' of '.($syear-1).'-'.$syear;
                                            echo ' of ' . $syear . '-' . ($syear + 1);
                                          } else {
                                            echo ' of ' . $syear . '-' . ($syear + 1);
                                          } ?></b>
                            </div>
                            <div class="col-md-4" style="text-align: right;display: inherit;">
                              <a href="#" class="btn btn-danger btn-xs" onClick='window.open("SOAindi.php?id=<?php echo $enrol_ID; ?>&studID=<?php echo $studID; ?>","targetWindow", "toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=900, height=900"); return false;' data-toggle="tooltip" data-placement="top" title="Show Statement of Accounts"><i class="far fa-sticky-note"></i>
                              </a>
                              <!--  Added Module-->
                              <?php if (in_array('soa_stud_billing', $_SESSION['user-access']) && in_array('accounting', $_SESSION['user-access'])) { ?>
                                <a href="javascript:void(0);" class="btn btn-info btn-xs show-bill" en-id="<?php echo $enrol_ID; ?>" stud-id="<?php echo $studID; ?>" data-toggle="tooltip" data-placement="top" title="Show Billing Statement">
                                  <i class="fas fa-print"></i>
                                </a>
                              <?php } ?>
                              <?php if (in_array('soa_stud_ledger', $_SESSION['user-access']) && in_array('accounting', $_SESSION['user-access'])) { ?>
                                <a href="javascript:void(0);" class="btn btn-warning btn-xs save-ledger" en-id="<?php echo $enrol_ID; ?>" stud-id="<?php echo $studID; ?>" data-toggle="tooltip" data-placement="top" title="Save to Ledger">
                                  <i class="far fa-save"></i>
                                </a>
                              <?php } ?>
                              <!-- End -->
                            </div>
                          </div>
                          <p style="line-height: 70%;">Level: <b>
                              <?php
                              if ($level == '1') {
                                echo '1st Year';
                              } elseif ($level == '2') {
                                echo '2nd Year';
                              } elseif ($level == '3') {
                                echo '3rd Year';
                              } elseif ($level == '4') {
                                echo '4th Year';
                              } elseif ($level == '5') {
                                echo '5th Year';
                              } else {
                                echo $level;
                              }
                              ?>
                            </b>&nbsp;|&nbsp;
                            Scholarship:
                            <?php
                            if ($sc_status == 'Y') {
                              echo '<b style="padding: 0px 3px 0px 3px;background-color:lightgreen;border-radius:2px;">YES</b>';
                            } elseif ($sc_status == 'N') {
                              echo '<b style="padding: 0px 3px 0px 3px;background-color:indianred;color:#fff;border-radius:2px;">NONE</b>';
                            } elseif ($sc_status == 'T') {
                              echo '<b style="padding: 0px 3px 0px 3px;background-color:#00c0ef;color:#fff;border-radius:2px;">TENTATIVE</b>';
                            } else {
                              echo '<b style="padding: 0px 3px 0px 3px;background-color:lightgray;border-radius:2px;">N/A</b>';
                            }


                            ?>
                          </p>
                          <?php if (isset($_SESSION['enrol-status']) && $_SESSION['enrol-status'] == 'CANCELLED') { ?>
                            <!-- <p style="line-height: 50%;">Status: <b>Cancelled</b>
      </p> -->
                          <?php } ?>
                          <?php
                          $qryEnrol = "SELECT *
                   FROM enroll_subjects
                   WHERE enrol_id='{$enrol_ID}'
                   AND status=''";
                          $qry_enrol = $conn_i->query($qryEnrol);
                          while ($info3 = $qry_enrol->fetch_assoc()) {
                            $subject = $info3['title'];
                            $description = $info3['description'];
                            $units = $info3['units'];
                            // if($units[0] == '('){$units_bracketed = $units_bracketed + preg_replace('/[^A-Za-z0-9\-]/', '', $units);}
                            //$amount = $info3['amount'];
                            //$totalTuition = $totalTuition + $amount;
                            $days = $info3['sked_days'];
                            $time = $info3['sked_time'];
                            $room = $info3['room'];
                            $instructor = $info3['instructor'];
                            //$tuition_fee = $info3['amount'];
                            $totalUnits += unit_format($units);
                          }
                          $qryEnroll = "SELECT *
                          FROM enrolment
                          WHERE status NOT IN('delete', 'cancel')
                          AND id='{$enrol_ID}'";
                          $qry_qryEnroll = $conn_i->query($qryEnroll);
                          while ($infoEnroll = $qry_qryEnroll->fetch_assoc()) {
                            $course = $infoEnroll['course'];
                            $enrol_fee = $infoEnroll['enrol_fee'];
                            $misc_fee = $infoEnroll['misc_fee'];
                            $library_fee = $infoEnroll['library_fee'];
                            $tuition_fee = $infoEnroll['tuition'];
                          }
                          $wholeyearFee = array("Grade 11", "Grade 12");
                          //$withRegFee = array("Grade 8","Grade 9","Grade 10");
                          if (in_array($info2['course'], $wholeyearFee) && $semester == '2nd' && $syear >= 2021) {
                            $enrol_fee = 0;
                            $misc_fee = 0;
                            $library_fee = 0;
                            $tuition_fee = 0;
                          }
                          //Check if Million and Over Units
                          if (($totalUnits * $tuition_fee) > 200000 && $totalUnits >= 40) {
                            $total_payable = $enrol_fee + $misc_fee + $tuition_fee;
                          } else {
                            $total_payable = $enrol_fee + $misc_fee + ($totalUnits * $tuition_fee);
                          }
                          //
                          $qryAddons = "SELECT *
                          FROM enroladdons
                          WHERE enrol_ID='{$enrol_ID}'
                          AND status!='deleted'";
                          $qry_qryAddons = $conn_i->query($qryAddons);
                          while ($infoAddons = $qry_qryAddons->fetch_assoc()) {
                            $piece = $infoAddons['pieces'];
                            if ($piece < 1) {
                              $piece = 1;
                            }
                            $total_addons = $total_addons + ($infoAddons['amount'] * $piece);
                          }
                          $qryPayment = "SELECT *
                           FROM payments
                           WHERE deleted!='yes'
                           AND enrol_ID='{$enrol_ID}'";
                          $qry_qryPayment = $conn_i->query($qryPayment);
                          while ($infoPayment = $qry_qryPayment->fetch_assoc()) {
                            $total_payment = $total_payment + $infoPayment['amount'];
                          }
                          ?>
                          <!-- /.tab-content -->
                          <table width="100%">
                            <tr>
                              <td colspan="3" style="padding-left:25px;">Registration Fee</td>
                              <td style="text-align:right; padding-right:100px;"><?php echo ' ' . number_format($enrol_fee, 2); ?></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td colspan="3" style="padding-left:25px;">Miscellaneous Fee</td>
                              <td style="text-align:right; padding-right:100px;"><?php echo ' ' . number_format($misc_fee, 2); ?></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td colspan="3" style="padding-left:25px;">Library Fee</td>
                              <td style="text-align:right; padding-right:100px;"><?php echo ' ' . number_format($library_fee, 2); ?></td>
                              <td></td>
                            </tr>
                            <tr style="border-bottom: 1px solid gray;">
                              <td colspan="3" style="padding-left:25px;">

                                Tuition (<?php echo $totalUnits + $units_bracketed . ' ' . $label . ' &#64; ' . number_format($tuition_fee, 2); ?>)

                              </td>
                              <td style="text-align:right; padding-right:100px;">
                                <?php
                                //Check If Million
                                $totalTuition = ($totalUnits + $units_bracketed) * $tuition_fee;
                                if ($totalTuition == '' || $totalTuition == 0) {
                                  $totalTuition = $tuition_fee;
                                } elseif ($totalTuition > 200000 && ($totalUnits + $units_bracketed) >= 40) {
                                  $totalTuition = $tuition_fee;
                                }
                                //
                                echo ' ' . number_format($totalTuition, 2);
                                $totalAmount = $enrol_fee + $totalTuition + $misc_fee + $library_fee;
                                ?>
                              </td>
                              <td style="text-align:right; padding-right:300px;">
                                <?php echo number_format($totalAmount, 2) ?>
                              </td>
                            </tr>
                            <!-- tr>
                    <td style="padding-left:7px; border-top: 1px solid gray;">Total Fees &#160; &#160;</td>
                    <td style="border-top: 1px solid gray;text-align:right;padding-right:50px;"><?php //echo ' '.number_format($totalAmount,2); 
                                                                                                ?></td>
                    <td style="text-align:right; padding-right:50px; border-top: 1px solid gray;"><?php //echo ' '.number_format($totalAmount,2); 
                                                                                                  ?></td>
                </tr -->
                            <!--===================================================START/ OLD PREVIOUS BALANCE -->

                            <!--===================================================END/ OLD PREVIOUS BALANCE -->
                            <?php
                            if ($total_addons > 0) {
                              echo '
                            <tr>
                                <td colspan="5" style="padding-left:25px;"><i>Add:</i> Addons</td>
                            </tr>';
                              $qryAddons = "SELECT *
                      FROM enroladdons
                      WHERE enrol_ID='{$enrol_ID}'
                      AND status!='deleted'";
                              $qry_qryAddons = $conn_i->query($qryAddons);
                              while ($infoAddons = $qry_qryAddons->fetch_assoc()) {
                                $item = $infoAddons['item'];
                                $price = $infoAddons['amount'];
                                $piece = $infoAddons['pieces'];
                                if ($piece < 1) {
                                  $piece = 1;
                                }
                            ?>
                                <tr> <!--
                    <td colspan="3" style="padding-left:45px;"><?php echo $item . ' x ' . $piece; ?></td> -->
                                  <td colspan="3" style="padding-left:45px;"><?php echo $item; ?></td>
                                  <td style="text-align:right; padding-right:100px;">
                                    <?php echo number_format($price * $piece, 2);
                                    $total_price = $total_price + ($price * $piece); ?>
                                  </td>
                                  <td style="text-align:right; padding-right:300px;">
                                    <?php if ($total_price == $total_addons) {
                                      echo number_format($total_addons, 2);
                                    } ?>
                                  </td>
                                  <td></td>
                                </tr>

                            <?php }
                              $total_price = 0;
                            } ?>
                            <tr style="border-top: 1px solid gray;">
                              <td colspan="3">Total Amount Payables</td>
                              <td style="text-align:right;  <?php if ((intval($balance) == 0 && $prev_balance > 0) || (intval($balance) > 0 && $prev_balance == 0)) {
                                                              echo "visibility: visible;";
                                                            } else {
                                                              echo "visibility: hidden;";
                                                            } ?> ">
                                <?php
                                //gidelete ang previous balance
                                echo number_format($totalAmount + $total_addons, 2);  //echo "<br><b>[BAL: ".intval($balance)."][PREV: ".$prev_balance."]</b>";
                                
                                ?>
                              </td>
                              <td style="text-align:right; padding-right:80px; <?php if ((intval($balance) == 0 && $prev_balance > 0) || (intval($balance) > 0 && $prev_balance == 0)) {
                                                                                  echo "visibility: hidden;";
                                                                                } else {
                                                                                  echo "visibility: visible;";
                                                                                } ?>">
                                <?php
                                echo number_format($totalAmount + $total_addons, 2);
                                $totalAmount = $totalAmount + $total_addons;
                                //echo "<br><b>[BAL: ".intval($balance)."][PREV: ".$prev_balance."]</b>"
                                ?>
                              </td>
                            </tr>

                            <!--====================================================START/ NEW OVER PAYMENT -->

                            <?php if ($prev_balance > 0) {
                              $totalAmount = $totalAmount + $prev_balance; ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Add:</i> Old Accounts</td>
                                <td style="text-align:right; padding-right:100px;">
                                  <?php
                                  echo number_format($prev_balance, 2);
                                  ?>
                                </td>
                                <td style="text-align:right; padding-right:80px;">
                                  <?php
                                  echo number_format($totalAmount, 2);
                                  $prev_bal = $prev_balance;
                                  $prev_balance = 0;
                                  ?>
                                </td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <?php if ((intval($balance) > 0 || $balance > 0) && (round($balance, 2) != 0)) {
                              $totalAmount = $totalAmount + $balance; ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Add:</i> Old Accounts</td>
                                <td style="text-align:right; padding-right:100px;">
                                  <?php
                                  echo number_format($balance, 2);
                                  ?>
                                </td>
                                <td style="text-align:right; padding-right:80px;">
                                  <?php echo number_format($totalAmount, 2); ?>
                                </td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <?php if (number_format($balance, 2) < 0) {
                              $totalAmount = $totalAmount + $balance; ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> Over Payment</td>
                                <td style="text-align:right; padding-right:100px;">
                                  <?php
                                  echo number_format(abs($balance), 2);
                                  //Less Overpayment Value
                                  $less_overpayment = abs($balance);
                                  ?>
                                </td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <!--====================================================END/ NEW OVER PAYMENT -->

                            <?php if ($discount_1[0] > 0) { ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> Discount <?php echo $discount_1[0] . '% of ' . number_format($totalTuition, 2) . ' - ' . $discount_1[1]; ?>
                                </td>

                                <td style="text-align:right;">
                                  <?php
                                  $discount1 = $totalTuition * ($discount_1[0] / 100);
                                  echo number_format($discount1, 2);
                                  $total_less = $total_less + $discount1;
                                  ?>
                                </td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <?php if ($discount_2[0] > 0) { ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> Discount <?php echo $discount_2[0] . '% of ' . number_format($totalTuition, 2); ?> </td>
                                <td>
                                  <?php
                                  echo ' - ' . $discount_2[1];
                                  //$totalTuition = $totalTuition-($totalTuition * ($discount_2[0]/100));
                                  ?>
                                </td>
                                <td style="text-align:right; padding-right:200px;">
                                  <?php
                                  $discount2 = $totalTuition * ($discount_2[0] / 100);
                                  echo number_format($discount2, 2);
                                  $total_less = $total_less + $discount2;
                                  ?>
                                </td>
                              </tr>
                            <?php } ?>
                            <?php if ($discount_3[0] > 0) { ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> Discount <?php echo $discount_3[0] . '% of ' . number_format($totalTuition, 2); ?> </td>
                                <td>
                                  <?php
                                  echo ' - ' . $discount_3[1];
                                  //$totalTuition = $totalTuition-($totalTuition * ($discount_3[0]/100));
                                  ?>
                                </td>
                                <td style="text-align:right;">
                                  <?php
                                  $discount3 = $totalTuition * ($discount_3[0] / 100);
                                  echo number_format($discount3, 2);
                                  $total_less = $total_less + $discount3;
                                  ?>
                                </td>
                              </tr>
                            <?php } ?>
                            <?php if ($less_amount < 0 || $less_amount > 0) { ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> <?php echo $less_reason; ?> </td>

                                <td style="text-align:right;">
                                  <?php echo number_format($less_amount, 2); ?>
                                </td>
                                <td></td>
                              </tr>
                            <?php } ?>
                            <?php if (intval($total_payment) > 0) { ?>
                              <tr>
                                <td colspan="3" style="padding-left:25px;"><i>Less:</i> Payments</td>

                                <td style="text-align:right;">
                                  <?php
                                  echo number_format($total_payment, 2);
                                  $total_less = $total_less + $total_payment;
                                  ?>
                                </td>
                                <td style="text-align:right; padding-right:80px;">
                                  <?php echo number_format($total_payment + $less_amount + $discount1 + $discount2 + $discount3 + $less_overpayment, 2);
                                  $less_overpayment = 0; ?>
                                </td>
                              </tr>
                            <?php } ?>
                            <tr style="border-top: 1px solid gray;">
                              <td colspan="4" style="padding-left:7px;"><b>Balance: </b></td>
                              <td style="text-align:right; padding-right:80px;">
                                <b>
                                  <?php
                                  $balance = ($totalAmount) - ($total_payment + $less_amount + round($discount1, 2) + round($discount2, 2) + round($discount3, 2));


                                  // echo $balance;
                                  //if($balance<0.09){$balance=0.00;}
                                  if (abs($balance) == 0) {
                                    $display_balance = abs($balance);
                                  } else {
                                    $display_balance = $balance;
                                  }
                                  echo number_format($display_balance, 2);                                  

                                  if (date('m') == '09' || date('m') == '10' || date('m') == '11' || date('m') == '12') {
                                    $term_balance = $balance / 4;
                                  }
                                  if (date('m') == '01') {
                                    $term_balance = $balance / 3;
                                  }
                                  if (date('m') == '02') {
                                    $term_balance = $balance / 2;
                                  }
                                  if (date('m') == '03') {
                                    $term_balance = $balance;
                                  }

                                  // RESET VALUES - - - - - -
                                  $total_payment = 0;
                                  $totalUnits = 0;
                                  $units_bracketed = 0;
                                  $discount1 = 0;
                                  $discount2 = 0;
                                  $discount3 = 0;
                                  ?>
                                </b>
                              </td>
                            </tr>
                            <tr>
                              <td colspan="2">
                                <p>&nbsp;</p>
                              </td>
                            </tr>
                          </table>
                        <?php $total_addons = 0;
                        } ?>
                      </div>
                      <!-- /.table-responsive -->
                    </div>
                    <!-- /.box-body -->
                  </div>
                  <div class="box box-info">
                    <div class="box-header with-border">
                      <h3 class="box-title">Payments History</h3>
                    </div>
                    <div class="box-body">
                      <table class="table table-condensed table-hover" style="font-size: 14px;">
                        <thead>
                          <tr>
                            <th style="width: 10%;">OR. No.</th>
                            <th style="width: 49%;">Description</th>
                            <th style="width: 13%;">Exam Term</th>
                            <th style="width: 13%;">Sem / Fee Type</th>
                            <th style="width: 10%;">Date Paid</th>
                            <th style="text-align:right; padding-right:35px;">Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          //$payment_enrol_id = '@@@@@';
                          $qryPayments = "SELECT *
                    FROM payments
                    WHERE deleted!='yes'
                    AND studID='{$studID}'
                    ORDER BY enrol_ID ASC";
                          $qry_qryPayments = $conn_i->query($qryPayments);
                          while ($infoPayment = $qry_qryPayments->fetch_assoc()) {
                            if ($infoPayment['enrol_ID'] != $payment_enrol_id) {
                              $payment_enrol_id = $infoPayment['enrol_ID'];
                              echo '<tr><td colspan="6" style="background-color:lightgray;"></td></tr>';
                            }
                            $payment_id = $infoPayment['id'];
                            $OR_no = $infoPayment['OR_no'];
                            $paymentDate = $infoPayment['paymentDate'];
                            $amountPaid = $infoPayment['amount'];
                            $exam_term = $infoPayment['term'];
                            $exam_term = str_replace(',', ' / ', $exam_term);

                            if ($exam_term == 'Pre') {
                              $exam_term = 'Prelim';
                            }
                            if ($exam_term == 'Mid') {
                              $exam_term = 'Midterm';
                            }
                            if ($exam_term == '') {
                              $exam_term = 'NA';
                            }
                            $item = ucwords($infoPayment['item']);
                            $PaymentDesc = $infoPayment['annotation'];
                            //Get Semester for the payment // Domz Altered
                            $PaymentDesc = str_replace('`', ' / ', $PaymentDesc);
                            $PaymentDesc = str_replace('|', '', $PaymentDesc);
                            $DSem = '';
                            if ($infoPayment['enrol_ID'] != '' || $infoPayment['enrol_ID'] != 0) {
                              $qrySem = "SELECT semester
                               FROM enrolment
                               WHERE id='" . $infoPayment['enrol_ID'] . "'";
                              $qry_qrySem = $conn_i->query($qrySem);
                              $qrySemRes = $qry_qrySem->fetch_assoc();
                              $DSem = $qrySemRes['semester'];
                              if ($DSem != 'Summer' && $DSem != '') {
                                //If equal to summer
                                if ($DSem == "summer" || strtolower($DSem) == 'whole year') {
                                  $DSem = ucwords($DSem);
                                } else {
                                  $DSem = $DSem . ' ' . 'Sem';
                                }
                                //


                              } else if ($DSem == '') {
                                $DSem = 'Other Fees';
                              }
                            } else {
                              $DSem = 'Other Fees';
                            }
                            //Get Semester for the payment // Domz Altered // END
                          ?>
                            <tr>
                              <td><?php echo $OR_no; ?></td>
                              <!-- <td><?php echo $item; ?></td> -->
                              <td><?php echo $PaymentDesc; ?></td>
                              <td><?php echo $exam_term; ?></td>
                              <td><?php echo $DSem; ?></td>
                              <td><?php echo date('M d, Y', strtotime($paymentDate)); ?></td>
                              <td style="text-align:right; padding-right:35px;">
                                <?php echo number_format($amountPaid, 2); ?>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.tab-content -->
            </div>
            <!-- /.nav-tabs-custom -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <?php require 'includes/footer.php'; ?>
  </div>


  <style type="text/css">
    .opt-col-center {
      margin-left: 25px;
      margin-right: 25px;
    }

    @media (min-width: 200px) and (max-width: 299px) {

      /*.div_center{
    width:120px;height: 190px;
    border:1px solid blue;
    position: absolute;left:32.5%;top:95px;border-radius: 48%;
  }*/
      #canvas {
        width: 225px;
        height: 225px;
      }

      #image {
        width: 225px;
        height: 225px;
      }

      .opt-col-center {
        margin-left: 5px;
        margin-right: 5px;
      }
    }

    @media (min-width: 370px) and (max-width: 380px) {
      .div_center {
        width: 120px;
        height: 190px;
        border: 1px solid blue;
        position: absolute;
        left: 32.5%;
        top: 95px;
        border-radius: 48%;
      }

      #canvas {
        width: 318px;
        height: 318px;
      }

      #image {
        width: 318px;
        height: 318px;
      }

      .opt-col-center {
        margin-left: 45px;
        margin-right: 45px;
      }
    }

    @media (min-width: 340px) and (max-width: 369px) {
      .div_center {
        width: 120px;
        height: 190px;
        border: 1px solid blue;
        position: absolute;
        left: 32.5%;
        top: 95px;
        border-radius: 48%;
      }

      #canvas {
        width: 305px;
        height: 305px;
      }

      #image {
        width: 305px;
        height: 305px;
      }

      .opt-col-center {
        margin-left: 45px;
        margin-right: 45px;
      }
    }

    @media (min-width: 381px) and (max-width: 399px) {
      .div_center {
        width: 120px;
        height: 190px;
        border: 1px solid blue;
        position: absolute;
        left: 32.5%;
        top: 95px;
        border-radius: 48%;
      }

      #canvas {
        width: 330px;
        height: 330px;
      }

      #image {
        width: 318px;
        height: 318px;
      }

      .opt-col-center {
        margin-left: 55px;
        margin-right: 55px;
      }
    }

    @media (min-width: 400px) and (max-width: 500px) {
      .div_center {
        width: 120px;
        height: 190px;
        border: 1px solid blue;
        position: absolute;
        left: 33.5%;
        top: 95px;
        border-radius: 48%;
      }

      #canvas {
        width: 355px;
        height: 355px;
      }

      #image {
        width: 355px;
        height: 355px;
      }

      .opt-col-center {
        margin-left: 65px;
        margin-right: 65px;
      }
    }

    @media (min-width: 501px) and (max-width: 600px) {

      /*.div_center{
    width:120px;height: 190px;
    border:1px solid blue;
    position: absolute;left:32.5%;top:95px;border-radius: 48%;
  }*/
      #canvas {
        width: 480px;
        height: 480px;
      }

      #image {
        width: 480px;
        height: 480px;
      }

      .opt-col-center {
        margin-left: 125px;
        margin-right: 125px;
      }
    }

    .table-responsive {
      overflow-x: hidden !important;
    }
  </style>
  <!-- CAMERA Modal -->
  <div class="modal fade bs-example-modal-sm" tabindex="-1" id="takePicture" role="dialog" aria-labelledby="mySmallModalLabel">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content"> <!-- style="width:270px;" -->
        <div class="modal-header">
          <button type="button" class="close" onclick="vidOff()" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Capture Image - <label id="cameraStatus" style="font-size: 10px;color:gray;"></label></h4>
        </div>
        <div id="camera_wrapper" style="padding: 10px 0px 0px 10px;padding-left: 17px;">
          <p style="position: absolute;top:200px;text-align: center;left:25px;" id="camera_msg" hidden>Allow your camera to capture an image</p>


          <div class="div_center" id="cntr"></div>

          <canvas id="canvas" width="267" height="267" style="border:1px solid gray;">

          </canvas>

          <img src="" id="image" style="display: none;">
        </div>
        <div class="modal-footer" style="text-align: center;">
          <div class="row">
            <div class="opt-col" style="display: inline-block;">
              <button class="btn btn-info" data-toggle="modal" data-target="#patientImage"> Upload Image </button>
            </div>
            <div class="opt-col-center" style="display: inline-block;">
              <button class="btn btn-default" id="capture_btn" onclick="snap()"><span class="fa fa-camera"></span></button>
              <button class="btn btn-default" id="take_another" style="display: none;"><span class="glyphicon glyphicon-refresh"></span></button>
            </div>
            <div class="opt-col" style="display: inline-block;">
              <form action="saveimage.php" method="post">
                <input type="hidden" name="picture" id="img">
                <button class="btn btn-danger"> Save </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--     <script type="text/javascript" src="scripts/webcam.min.js"></script> -->
  <script language="JavaScript" id="script">
    let video = null;
    let canvas = null;
    let context = null;
    let scaler = 0.8;
    let size = {
      x: 0,
      y: 0,
      width: 0,
      height: 0
    };
    // let status = false;
    // var status = document.getElementById('status').innerHTML;
    // console.log(status);
    $(function() {
      //start
      $(window).on('load', function() {
        main();
      });

      function main() {

        canvas = document.getElementById('canvas');
        context = canvas.getContext('2d');

        let promise = navigator.mediaDevices.getUserMedia({
          video: {
            width: {
              exact: 267
            },
            height: {
              exact: 267
            },
            facingMode: 'environment'
          }
        });
        promise.then(function(signal) {
          video = document.createElement('video');
          video.srcObject = signal;
          video.play();

          video.onloadeddata = function() {
            handleResize();
            window.addEventListener('resize', handleResize);
            updateCanvas();
          }
          document.getElementById('camera_msg').setAttribute('hidden', '');
          document.getElementById('cameraStatus').innerHTML = '<span style="color:green;">Camera is ready</span>';
        }).catch(function(err) {
          // alert('Camera error: '+err);
          document.getElementById('camera_msg').removeAttribute('hidden', '');
          document.getElementById('cameraStatus').innerHTML = '<span style="color:red;">Camera is not ready</span>';
        });
      }

      function handleResize() {
        // canvas.width=window.innerWidth;
        // canvas.height=window.innerHeight;

        let resizer = scaler *
          Math.min(
            window.innerWidth / video.videoWidth,
            window.innerHeight / video.videoHeight
          );
        size.width = resizer * video.videoWidth;
        size.height = resizer * video.videoHeight;
        size.x = window.innerWidth / 2 - size.width / 2;
        size.y = window.innerHeight / 2 - size.height / 2;
      }

      function updateCanvas() {
        context.drawImage(video, 0, 0);
        window.requestAnimationFrame(updateCanvas);
      }

      function snap() {
        const URI = document.querySelector('#canvas').toDataURL();
        document.getElementById('image').removeAttribute('style', 'display:none;');
        document.querySelector('#image').src = URI;
        document.querySelector('#img').value = URI;
        document.getElementById('canvas').setAttribute('style', 'display:none;');
        document.getElementById('capture_btn').setAttribute('style', 'display:none;');
        document.getElementById('take_another').removeAttribute('style', 'display:none;');
        document.getElementById('cntr').setAttribute('style', 'display:none;');
      }
      $('#take_another').click(function() {
        document.getElementById('canvas').removeAttribute('style', 'display:none;');
        document.querySelector('#image').src = '';
        document.querySelector('#img').value = '';
        document.getElementById('image').setAttribute('style', 'display:none;');
        document.getElementById('capture_btn').removeAttribute('style', 'display:none;');
        document.getElementById('take_another').setAttribute('style', 'display:none;');
        document.getElementById('cntr').removeAttribute('style', 'display:none;');
      });


      //end
    });
  </script>

  <!-- Student Image -->
  <div class="modal fade" id="patientImage" tabindex="-1" role="dialog" aria-labelledby="newMedLabel">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="attachments" style="font-weight:bolder; color:#A29F9F;">Upload Student Picture</h4>
        </div>
        <form action="upload_stud_Image.php" method="post" enctype="multipart/form-data">
          <div class="modal-body" style="text-align:center;">

            <input type="file" name="pic" accept="image/*" onchange="loadFile(event)" id='file' /><br>
            <p id="dimension"></p>
            <p id="response" style="color:red;"></p>
            <img id="output" width="270" style="border-radius: 50%; border: 3px solid #d2d6de;" />

            <script>
              var loadFile = function(event) {
                var reader = new FileReader();
                reader.onload = function() {
                  var output = document.getElementById('output');
                  output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
              };
              $(document).ready(function() {
                var _URL = window.URL || window.webkitURL;

                $('#file').change(function() {
                  var file = $(this)[0].files[0];

                  img = new Image();
                  var imgwidth = 0;
                  var imgheight = 0;
                  // var maxwidth = 267;
                  // var maxheight = 200;

                  img.src = _URL.createObjectURL(file);
                  img.onload = function() {
                    imgwidth = this.width;
                    imgheight = this.height;


                    if (imgwidth == imgheight) {

                      // var formData = new FormData();
                      // formData.append('fileToUpload', $('#file')[0].files[0]);
                      $('#uploadbtn').removeAttr('disabled', '');
                      $("#response").text("");

                    } else {
                      $('#uploadbtn').attr('disabled', '');
                      var s = 'File Dimension: ' + imgwidth + 'X' + imgheight;
                      $("#dimension").text(s);
                      $("#response").text("Image width and height should be the same!");
                    }
                  };
                  img.onerror = function() {

                    $("#response").text("not a valid file: " + file.type);
                  }

                });
              });
            </script>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary btn-sm" id="uploadbtn" disabled> Upload Picture </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Added Modules Modal -->
  <?php
  include 'php-file/modal/modal-profile-bill.php';
  include 'php-file/modal/alert-modal.php';
  include 'php-file/stud-ledger/modal/view-exist-ledger.php';
  include 'php-file/stud-ledger/modal/view-ledger-records.php';
  include 'php-file/stud-ledger/modal/preview-ledger-records.php';
  ?>

  <!-- ./wrapper -->
  <!-- jQuery 2.2.3 -->
  <script src="js/jquery-2.2.3.min.js"></script>
  <!-- Bootstrap 3.3.6 -->
  <script src="js/bootstrap.min.js"></script>
  <!-- Select2 -->
  <script src="js/select2.full.min.js"></script>
  <!-- InputMask -->
  <script src="js/jquery.inputmask.js"></script>
  <script src="js/jquery.inputmask.date.extensions.js"></script>
  <script src="js/jquery.inputmask.extensions.js"></script>
  <!-- date-range-picker -->
  <script src="js/moment.min.js"></script>
  <script src="js/daterangepicker.js"></script>
  <script src="js/jquery.dataTables.min.js"></script>
  <script src="js/dataTables.bootstrap.min.js"></script>
  <!-- SlimScroll -->
  <!-- bootstrap datepicker -->
  <script src="js/bootstrap-datepicker.js"></script>
  <!-- bootstrap color picker
<script src="js/bootstrap-colorpicker.min.js"></script> -->
  <!-- bootstrap time picker
<script src="js/bootstrap-timepicker.min.js"></script> -->
  <!-- bootstrap time picker Domz Altered -->
  <script src="js/bootstrap-timepicker.js"></script>
  <!-- SlimScroll 1.3.0 -->
  <script src="js/jquery.slimscroll.min.js"></script>
  <!-- iCheck 1.0.1 -->
  <script src="js/icheck.min.js"></script>
  <!-- FastClick -->
  <script src="js/fastclick.js"></script>
  <!-- AdminLTE App -->
  <script src="js/app.min.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="js/demo.js"></script>
  <!-- Added Module -->
  <!-- Billing Statement Functions -->
  <script src="php-file/js/profile-bill.js"></script>
  <!-- Print This Snippet -->
  <script src="php-file/js/printThis.js"></script>
  <!-- Jquery Confirm JS -->
  <script src="php-file/jquery-confirm/jquery-confirm.min.js"></script>
  <!-- Custom ConfirmJS Method -->
  <script src="php-file/jquery-confirm/jquery-confirm-method.js"></script>
  <!-- Custom JS Functions -->
  <script src="php-file/js/student-ledger.js"></script>
  <!-- Custom Profile JS -->
  <script type="module" src="php-file/js/profile-control.js"></script>
  <!-- End -->
  <!-- Page script -->
  <script>
    $(function() {
      //Initialize Select2 Elements
      $(".select2").select2();

      //Datemask dd/mm/yyyy
      $("#datemask").inputmask("dd/mm/yyyy", {
        "placeholder": "dd/mm/yyyy"
      });
      //Datemask2 mm/dd/yyyy
      $("#datemask2").inputmask("mm/dd/yyyy", {
        "placeholder": "mm/dd/yyyy"
      });
      //Money Euro
      $("[data-mask]").inputmask();

      //iCheck for checkbox and radio inputs
      $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
      });
      //Red color scheme for iCheck
      $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
        checkboxClass: 'icheckbox_minimal-red',
        radioClass: 'iradio_minimal-red'
      });
      //Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-green'
      });

    });
  </script>
  <script>
    $(function() {
      $("#example1").DataTable();
    });
  </script>
</body>

</html>