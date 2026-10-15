<?php
    session_start();
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $sex = $_GET['sex'] ?? 'all';
    $where_sex = "";
    $title_sex = "All Genders";
    if ($sex !== 'all') {
        $s_sex = sanitize($sex);
        $where_sex = " WHERE sex = '$s_sex'";
        $title_sex = ucfirst($s_sex);
    }

    $query = "SELECT student_id, fname, mname, lname, course, sex, status FROM ojt_students $where_sex ORDER BY lname ASC";
    $result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>OJT Students List - <?php echo $title_sex; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="src/assets/libs/bootstrap/dist/css/bootstrap.min.css">
  <style>
      @media print {
          .no-print { display: none !important; }
          body { margin: 0; padding: 20px; color: #000; background-color: white !important; }
          .wrapper { background-color: white !important; }
      }
      .print-header { text-align: center; margin-bottom: 30px; }
      .table > thead > tr > th { 
          border-bottom: 2px solid #333 !important; 
          background-color: #f5f5f5 !important; 
          font-size: 11px;
          text-transform: uppercase;
          -webkit-print-color-adjust: exact;
      }
      .table > tbody > tr > td { font-size: 11px; vertical-align: middle; border: 1px solid #ddd !important; }
      .text-bold { font-weight: bold; }
  </style>
</head>
<body class="hold-transition skin-blue layout-top-nav" onload="window.print();">
<div class="wrapper" style="background-color:white;">
    <div class="container-fluid">
        
        <!-- Header copied from print_template.php -->
        <div style="width:100%; padding:20px 0px;">
            <table style="text-align:center; background-color:white;  width:100%;">
                <tr>
                    <td width="15%"><img src="src/assets/images/logos/BNSC-logo.png" width="60" height="60" alt="BNSC logo" onerror="this.src='../../assets/images/logos/logo.png'"></td>
                    <td style="padding:0px 5px;">
                        <h4 style="margin: 0; font-weight: bold;">BOHOL NORTHERN STAR COLLEGE, INC.</h4>
                        <p style="line-height:1.2; font-size: 11px; margin: 2px 0;">Bohol Northeastern Education Foundation, Inc.</p>
                        <p style="line-height:1.2; font-size: 11px; margin: 2px 0;">Isaac Garces Street, Ubay, Bohol</p>
                        <h5 style="margin-top: 10px; font-weight: bold; text-transform: uppercase;">MANAGED OJT STUDENTS LIST</h5>
                        <p style="line-height:1.2; font-size: 11px; font-weight: bold;">Filter: <?php echo $title_sex; ?> | Date: <?php echo date('F d, Y'); ?></p>
                    </td>
                    <td width="15%"><img src="src/assets/images/logos/cot.png" width="60" height="60" alt="Aurora logo" onerror="this.src='../../assets/images/logos/aurora-logo.png'"></td>
                </tr>
            </table>
        </div>

        <!-- Table Content -->
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="20%">Student ID</th>
                            <th>Full Name</th>
                            <th width="30%">Course</th>
                            <th width="10%">Sex</th>
                            <th width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $count = 1; while($row = $result->fetch_assoc()): 
                                $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
                                $fullname = mb_convert_case($row['lname'] . ', ' . $row['fname'] . ' ' . $mi, MB_CASE_TITLE, "UTF-8");
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><strong><?php echo $row['student_id']; ?></strong></td>
                                    <td><?php echo $fullname; ?></td>
                                    <td><?php echo $row['course']; ?></td>
                                    <td><?php echo $row['sex']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No students found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row" style="margin-top: 10px; font-size: 11px;">
            <div class="col-xs-12">
                <?php date_default_timezone_set('Asia/Manila'); ?>
                <p>This report is generated on <?php echo date('F d, Y h:i A'); ?>.</p>
            </div>
        </div>

        <!-- Footer / Signatories -->
        <table style="width: 100%; margin-top: 50px; text-align: center;">
            <tr>
                <td style="width: 33.33%; padding: 10px;">
                    <p style="margin-bottom: 40px;">Prepared by:</p>
                    <div style="border-bottom: 1px solid #000; display: inline-block; width: 180px;"></div>
                    <p style="font-size: 10px; margin-top: 5px;">OJT Coordinator / Instructor</p>
                </td>
                <td style="width: 33.33%; padding: 10px;">
                    <p style="margin-bottom: 40px;">Verified by:</p>
                    <div style="border-bottom: 1px solid #000; display: inline-block; width: 180px;"></div>
                    <p style="font-size: 10px; margin-top: 5px;">Program Chairperson</p>
                </td>
                <td style="width: 33.33%; padding: 10px;">
                    <p style="margin-bottom: 40px;">Noted by:</p>
                    <div style="border-bottom: 1px solid #000; display: inline-block; width: 180px;"></div>
                    <p style="font-size: 10px; margin-top: 5px;">Dean</p>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
