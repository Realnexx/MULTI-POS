<?php
    session_start();
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $filter = $_GET['filter'] ?? 'all';
    $sex = $_GET['sex'] ?? 'all';
    $custom_date = $_GET['custom_date'] ?? '';
    $custom_month = $_GET['custom_month'] ?? '';
    
    // Determine Reference Date (Default to today)
    $ref_date = date('Y-m-d');
    if ($filter == 'date' && !empty($custom_date)) {
        $ref_date = sanitize($custom_date);
    } elseif ($filter == 'custom_month' && !empty($custom_month)) {
        $ref_date = sanitize($custom_month) . "-01";
    }

    $where_clause = "WHERE 1=1";
    $title_period = "All Attendance Logs";

    if ($filter === 'today') {
        $where_clause .= " AND a.timestamp = CURDATE()";
        $title_period = "Attendance for Today (" . date('M d, Y') . ")";
    } elseif ($filter === 'week') {
        $where_clause .= " AND a.timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $title_period = "Attendance for This Week";
    } elseif ($filter === 'month') {
        $where_clause .= " AND MONTH(a.timestamp) = MONTH(CURDATE()) AND YEAR(a.timestamp) = YEAR(CURDATE())";
        $title_period = "Attendance for " . date('F Y');
    } elseif ($filter === 'date') {
        $where_clause .= " AND a.timestamp = '$ref_date'";
        $title_period = "Attendance for " . date('M d, Y', strtotime($ref_date));
    } elseif ($filter === 'custom_month') {
        $where_clause .= " AND MONTH(a.timestamp) = MONTH('$ref_date') AND YEAR(a.timestamp) = YEAR('$ref_date')";
        $title_period = "Attendance for " . date('F Y', strtotime($ref_date));
    }

    if ($sex !== 'all') {
        $s_sex = sanitize($sex);
        $where_clause .= " AND s.sex = '$s_sex'";
    }

    $query = "SELECT a.*, s.fname, s.mname, s.lname, s.student_id, s.course, s.sex 
              FROM ojt_attendance a 
              JOIN ojt_students s ON a.studID = s.student_id 
              $where_clause 
              ORDER BY a.timestamp DESC, s.lname ASC";
    
    $result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Attendance Logs - <?php echo date('Ymd'); ?></title>
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
          padding: 4px 2px !important;
          font-size: 10px;
          text-transform: uppercase;
          -webkit-print-color-adjust: exact;
      }
      .table > tbody > tr > td { 
          font-size: 10px; 
          vertical-align: middle; 
          border: 1px solid #ddd !important;
          padding: 3px 2px !important;
      }
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
                        <h5 style="margin-top: 10px; font-weight: bold; text-transform: uppercase;">OJT ATTENDANCE LOGS REPORT</h5>
                        <p style="line-height:1.2; font-size: 11px; font-weight: bold;">Filter: <?php echo $title_period; ?> | Gender: <?php echo ucfirst($sex); ?></p>
                    </td>
                    <td width="15%"><img src="src/assets/images/logos/cot.png" width="60" height="60" alt="COT logo" onerror="this.src='../../assets/images/logos/aurora-logo.png'"></td>
                </tr>
            </table>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" width="12%">Date</th>
                            <th rowspan="2">Student Name</th>
                            <th colspan="2" class="text-center">Morning</th>
                            <th colspan="2" class="text-center">Afternoon</th>
                            <th rowspan="2" class="text-center" width="10%">Total</th>
                        </tr>
                        <tr>
                            <th class="text-center" width="8%">In</th>
                            <th class="text-center" width="8%">Out</th>
                            <th class="text-center" width="8%">In</th>
                            <th class="text-center" width="8%">Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
                                $fullname = mb_convert_case($row['lname'] . ', ' . $row['fname'] . ' ' . $mi, MB_CASE_TITLE, "UTF-8");
                                
                                // Format times with AM/PM if they aren't '--:--'
                                $formatTime = function($time) {
                                    if ($time === '--:--' || empty($time)) return '--:--';
                                    return date('h:i A', strtotime($time));
                                };
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['timestamp'])); ?></td>
                                    <td><div class="text-bold"><?php echo $fullname; ?></div></td>
                                    <td class="text-center"><?php echo $formatTime($row['check_in']); ?></td>
                                    <td class="text-center"><?php echo $formatTime($row['break_out']); ?></td>
                                    <td class="text-center"><?php echo $formatTime($row['break_in']); ?></td>
                                    <td class="text-center"><?php echo $formatTime($row['check_out']); ?></td>
                                    <td class="text-center text-bold"><?php echo $row['total_hours']; ?> hrs</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No attendance logs found with the current filters.</td></tr>
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

        <table style="width: 100%; margin-top: 50px; text-align: center;">
            <tr>
                <td style="width: 33.33%; padding: 10px;">
                    <p style="margin-bottom: 40px;">Prepared by:</p>
                    <div style="border-bottom: 1px solid #000; display: inline-block; width: 180px;"></div>
                    <p style="font-size: 10px; margin-top: 5px;">OJT Coordinator</p>
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
