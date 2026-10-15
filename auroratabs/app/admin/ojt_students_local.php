<?php
    ob_start();
    session_start();
    
    // Auth check
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $timezone = "Asia/Manila";
    if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    $can_view = has_permission('Manage Students', 'view', $conn);
    $can_manage = has_permission('Manage Students', 'manage', $conn);
    $can_export = has_permission('Manage Students', 'export', $conn);
    $can_delete = has_permission('Manage Students', 'delete', $conn);

    if (!$can_view) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managed Students - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .table-responsive {
            overflow-x: hidden !important;
        }
        @media (max-width: 991px) {
            .table-responsive {
                overflow-x: auto !important;
            }
        }
        #studentsLocalTable {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <?php include 'components/sidebar.php'; ?>
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">

            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <!--  Header Start -->
                    <?php include 'components/header.php'; ?>
                    <!--  Header End -->

                    <div class="top-part card bg-light-success shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h4 class="fw-semibold mb-8">Managed OJT Students</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Student Management</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Local OJT Registry</h5>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm w-auto" id="genderFilter">
                                        <option value="all">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover" id="studentsLocalTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Student Details</th>
                                            <th>Gender</th>
                                            <th>Program</th>
                                            <?php if ($can_manage || $can_delete): ?>
                                            <th class="text-end">Actions</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Student Modal -->
                    <div class="modal fade" id="modalEditStudent" tabindex="-1" aria-labelledby="modalEditStudentLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-semibold" id="modalEditStudentLabel">Edit Student Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="formEditStudent">
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_student_id" name="student_id">
                                        <div class="mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="edit_fname" name="fname" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" id="edit_mname" name="mname">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="edit_lname" name="lname" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Course</label>
                                            <input type="text" class="form-control" id="edit_course" name="course" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Sex</label>
                                                <select class="form-select" id="edit_sex" name="sex">
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="edit_status" name="status">
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Dropped">Dropped</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Hours Modal -->
                    <div class="modal fade" id="modalManualHours" tabindex="-1" aria-labelledby="modalManualHoursLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-bottom bg-light-warning">
                                    <h5 class="modal-title fw-semibold text-warning" id="modalManualHoursLabel"><i class="ti ti-clock-edit me-2"></i>Manual Monthly Hours</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-light-warning text-warning mb-3">
                                        <i class="ti ti-alert-circle me-1"></i> This will record total hours for the selected month on the last day of that month.
                                    </div>
                                    <h6 class="mb-3">Student: <span id="manual_student_name" class="text-primary fw-bold"></span></h6>
                                    <input type="hidden" id="manual_student_id">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Select Month</label>
                                        <input type="month" class="form-control" id="manual_month">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Total Hours Rendered</label>
                                        <input type="number" class="form-control" id="manual_hours" step="0.01" min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description / Notes</label>
                                        <textarea class="form-control" id="manual_description" rows="3" placeholder="Reason for manual entry..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top bg-light">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-warning" id="btnSaveManualHours">
                                        <i class="ti ti-device-floppy me-1"></i>Save Manual Hours
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Modal -->
                    <div class="modal fade" id="modalAttendance" tabindex="-1" aria-labelledby="modalAttendanceLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-bottom bg-light-primary">
                                    <h5 class="modal-title fw-semibold text-primary" id="modalAttendanceLabel"><i class="ti ti-calendar-event me-2"></i>Monthly Attendance Record</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="p-3 border-bottom bg-white sticky-top" style="z-index: 10;">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="mb-0">Student: <span id="attn_student_name" class="text-primary fw-bold"></span></h5>
                                                <input type="hidden" id="attn_student_id">
                                            </div>
                                            <div class="col-md-6 d-flex justify-content-md-end align-items-center gap-2">
                                                <label class="form-label mb-0 fw-bold">Select Month:</label>
                                                <input type="month" class="form-control w-auto" id="attn_month" value="<?php echo date('Y-m'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="max-height: 60vh;">
                                        <table class="table table-bordered table-sm align-middle mb-0" id="tableMonthlyAttendance">
                                            <thead class="bg-light sticky-top" style="top: 0; z-index: 5;">
                                                <tr>
                                                    <th class="text-center" style="width: 15%;">Date</th>
                                                    <th class="text-center text-success" style="width: 18%;"><i class="ti ti-login me-1"></i>AM In</th>
                                                    <th class="text-center text-warning" style="width: 18%;"><i class="ti ti-logout me-1"></i>AM Out</th>
                                                    <th class="text-center text-info" style="width: 18%;"><i class="ti ti-login me-1"></i>PM In</th>
                                                    <th class="text-center text-danger" style="width: 18%;"><i class="ti ti-logout me-1"></i>PM Out</th>
                                                    <th class="text-center" style="width: 13%;">Total/Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attn_rows_container">
                                                <!-- Row generation via JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer border-top bg-light">
                                    <button type="button" class="btn btn-light-danger text-danger" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary px-4" id="btnSaveMonthlyAttendance">
                                        <i class="ti ti-device-floppy me-1"></i>Save All Records
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // --- DEBUG: INITIAL STATE ---
            console.group('🔍 OJT Students Local Debug');
            console.log('✅ Page Loaded');
            console.log('Session User ID:', <?php echo json_encode($_SESSION['user_id'] ?? null); ?>);
            console.log('Permissions:', {
                can_view: <?php echo json_encode($can_view); ?>,
                can_manage: <?php echo json_encode($can_manage); ?>,
                can_delete: <?php echo json_encode($can_delete); ?>,
                can_export: <?php echo json_encode($can_export); ?>
            });
            console.groupEnd();

            const table = $('#studentsLocalTable').DataTable({
                "ajax": {
                    "url": "endpoints/ojt_handler.php",
                    "type": "POST",
                    "data": function(d) {
                        d.action = "get_local_students";
                        d.sex = $('#genderFilter').val();
                        console.log('📤 DataTable Request:', d);
                    },
                    "dataSrc": function(json) {
                        console.log('📥 DataTable Response:', json);
                        if (json.status !== 'success') {
                            console.error('❌ Data Load Error:', json.message);
                        }
                        return json.status === 'success' ? json.data : [];
                    }
                },
                "columns": [
                    // Column 1: Student Details
                    { "data": null, "render": function(data, type, row) {
                        let sexIcon = row.sex === 'Male' ? 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-primary fs-2"></iconify-icon>' : 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-danger fs-2"></iconify-icon>';
                        return `
                            <div class="d-flex align-items-center gap-2">
                                ${sexIcon}
                                <div>
                                    <h6 class="fw-semibold mb-0">${row.fname} ${row.mname ? row.mname + ' ' : ''}${row.lname}</h6>
                                    <p class="mb-0 fs-2 text-muted">${row.student_id}</p>
                                </div>
                            </div>
                        `;
                    }},
                    // Column 2: Gender
                    { "data": "sex" },
                    // Column 3: Program
                    { "data": "course" }
                    <?php if ($can_manage || $can_delete): ?>
                    ,
                    // Column 4: Actions
                    { "data": null, "sortable": false, "className": "text-end", "render": function(data, type, row) {
                        let btns = '';
                        <?php if ($can_manage): ?>
                        btns += `
                            <button class="btn btn-sm btn-light-primary text-primary me-1 btnAttendance" 
                                    data-id="${row.student_id}" data-name="${row.fname} ${row.lname}" 
                                    title="Record Attendance"><i class="ti ti-calendar-event"></i></button>
                            <button class="btn btn-sm btn-light-warning text-warning me-1 btnManualHours" 
                                    data-id="${row.student_id}" data-name="${row.fname} ${row.lname}" 
                                    title="Manual Monthly Hours"><i class="ti ti-clock-edit"></i></button>
                            <button class="btn btn-sm btn-light-info text-info me-1 btnEdit" 
                                    data-id="${row.id}" data-sid="${row.student_id}" 
                                    data-fname="${row.fname}" data-mname="${row.mname}" 
                                    data-lname="${row.lname}" data-course="${row.course}" 
                                    data-sex="${row.sex}" title="Edit Student"><i class="ti ti-edit"></i></button>
                        `;
                        <?php endif; ?>
                       
                        return `<div class="d-flex justify-content-end">${btns}</div>`;
                    }}
                    <?php endif; ?>
                ],
                "pageLength": 10,
                "ordering": true,
                "processing": true
            });

            // Gender Filter Change
            $('#genderFilter').change(function() {
                console.log('🔄 Gender Filter Changed to:', $(this).val());
                table.ajax.reload();
            });

            // Print Click
            $('#btnPrint').click(function() {
                const sex = $('#genderFilter').val();
                console.log('🖨️ Print Clicked. Filter:', sex);
                window.open(`print_ojt_students.php?sex=${sex}`, 'PrintWindow', 'width=1000,height=800,resizable=yes,scrollbars=yes');
            });

            // Edit Click
            $(document).on('click', '.btnEdit', function() {
                const id = $(this).data('id');
                console.log('✏️ Edit Clicked. ID:', id);
                
                $.post('endpoints/ojt_handler.php', { action: 'get_student', student_id: id }, function(res) {
                    console.log('📥 Get Student Details Response:', res);
                    if (res.status === 'success') {
                        $('#edit_student_id').val(res.data.student_id);
                        $('#edit_fname').val(res.data.fname);
                        $('#edit_mname').val(res.data.mname);
                        $('#edit_lname').val(res.data.lname);
                        $('#edit_course').val(res.data.course);
                        $('#edit_sex').val(res.data.sex);
                        $('#edit_status').val(res.data.status);
                        $('#modalEditStudent').modal('show');
                    } else {
                        console.error('❌ Failed to fetch student details:', res.message);
                    }
                });
            });

            // Manual Hours Click
            $(document).on('click', '.btnManualHours', function() {
                const sid = $(this).data('id');
                const name = $(this).data('name');
                console.log('🕒 Manual Hours Clicked. Student:', name, 'ID:', sid);

                $('#manual_student_id').val(sid);
                $('#manual_student_name').text(name);
                $('#manual_month').val(new Date().toISOString().substring(0, 7)); // Default to current month
                $('#manual_hours').val('');
                $('#manual_description').val('');
                
                $('#modalManualHours').modal('show');
            });

            // Save Manual Hours
            $('#btnSaveManualHours').click(function() {
                const sid = $('#manual_student_id').val();
                const month = $('#manual_month').val();
                const hours = $('#manual_hours').val();
                const desc = $('#manual_description').val();

                if (!month || !hours) {
                    Swal.fire('Error', 'Please select a month and enter total hours.', 'warning');
                    return;
                }

                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.post('endpoints/ojt_handler.php', {
                    action: 'save_manual_monthly_hours',
                    student_id: sid,
                    month: month,
                    total_hours: hours,
                    description: desc
                }, function(res) {
                    $('#btnSaveManualHours').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Save Manual Hours');
                    if (res.status === 'success') {
                        $('#modalManualHours').modal('hide');
                        Swal.fire('Success', res.message, 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            });

            // Update Form Submit
            $('#formEditStudent').submit(function(e) {
                e.preventDefault();
                console.log('💾 Saving Student Changes...');
                const formData = $(this).serialize() + '&action=update_student';
                
                $.post('endpoints/ojt_handler.php', formData, function(res) {
                    console.log('📥 Update Student Response:', res);
                    if (res.status === 'success') {
                        $('#modalEditStudent').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Updated!', res.message, 'success');
                    } else {
                        console.error('❌ Update Failed:', res.message);
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            });


            // Attendance Click (Monthly)
            $(document).on('click', '.btnAttendance', function() {
                const sid = $(this).data('id');
                const name = $(this).data('name');
                console.log('📅 Monthly Attendance Clicked. Student:', name, 'ID:', sid);

                $('#attn_student_id').val(sid);
                $('#attn_student_name').text(name);
                
                // Keep current month if already set, otherwise set to today's month
                if (!$('#attn_month').val()) {
                    $('#attn_month').val(new Date().toISOString().substring(0, 7));
                }
                
                loadMonthlyData();
                $('#modalAttendance').modal('show');
            });

            // Month Change
            $('#attn_month').change(function() {
                loadMonthlyData();
            });

            function loadMonthlyData() {
                const sid = $('#attn_student_id').val();
                const month = $('#attn_month').val();
                if (!sid || !month) return;

                const [year, mon] = month.split('-').map(Number);
                const weekdays = getWeekdays(year, mon);
                
                $('#attn_rows_container').html('<tr><td colspan="6" class="text-center p-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading records...</td></tr>');

                $.post('endpoints/ojt_handler.php', {
                    action: 'get_monthly_attendance',
                    student_id: sid,
                    month: month
                }, function(res) {
                    if (res.status === 'success') {
                        renderAttendanceRows(weekdays, res.data);
                    } else {
                        Swal.fire('Error', 'Could not load data: ' + res.message, 'error');
                    }
                });
            }

            function getWeekdays(year, month) {
                const dates = [];
                const firstDay = new Date(year, month - 1, 1);
                const lastDay = new Date(year, month, 0);

                for (let d = new Date(firstDay); d <= lastDay; d.setDate(d.getDate() + 1)) {
                    const day = d.getDay();
                    if (day !== 0 && day !== 6) { // Not Sunday (0) or Saturday (6)
                        dates.push(new Date(d));
                    }
                }
                return dates;
            }

            function renderAttendanceRows(weekdays, existingData) {
                let html = '';
                weekdays.forEach(date => {
                    const dateStr = date.toISOString().split('T')[0];
                    const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
                    const displayDate = `${dayName}, ${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
                    
                    const record = existingData[dateStr] || {};
                    const hours = record.total_hours || '0.00';
                    const status = record.status || '--';
                    
                    let statusClass = 'text-muted';
                    if (status === 'Present') statusClass = 'text-success fw-bold';
                    else if (status === 'Late') statusClass = 'text-warning fw-bold';
                    else if (status === 'Half-day') statusClass = 'text-info fw-bold';

                    html += `
                        <tr class="attn-row" data-date="${dateStr}">
                            <td class="bg-light fw-semibold text-center">${displayDate}</td>
                            <td><input type="time" class="form-control form-control-sm border-0 t-in" value="${record.check_in || ''}"></td>
                            <td><input type="time" class="form-control form-control-sm border-0 t-bout" value="${record.break_out || ''}"></td>
                            <td><input type="time" class="form-control form-control-sm border-0 t-bin" value="${record.break_in || ''}"></td>
                            <td><input type="time" class="form-control form-control-sm border-0 t-out" value="${record.check_out || ''}"></td>
                            <td class="text-center small">
                                <span class="d-block">${hours} hrs</span>
                                <span class="${statusClass}">${status}</span>
                            </td>
                        </tr>
                    `;
                });
                $('#attn_rows_container').html(html);
            }

            // Save All Records
            $('#btnSaveMonthlyAttendance').click(function() {
                const sid = $('#attn_student_id').val();
                const month = $('#attn_month').val();
                const data = [];

                $('.attn-row').each(function() {
                    const row = $(this);
                    const cin = row.find('.t-in').val();
                    const bout = row.find('.t-bout').val();
                    const bin = row.find('.t-bin').val();
                    const cout = row.find('.t-out').val();

                    // Only push if there's some data (or if we want to allow clearing via save)
                    // Let's always push so we can handle clearing on backend if needed, 
                    // but we can optimize by only sending rows that HAVE data if we want.
                    data.push({
                        date: row.data('date'),
                        check_in: cin,
                        break_out: bout,
                        break_in: bin,
                        check_out: cout
                    });
                });

                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.post('endpoints/ojt_handler.php', {
                    action: 'save_monthly_attendance',
                    student_id: sid,
                    month: month,
                    attendance_data: JSON.stringify(data)
                }, function(res) {
                    $('#btnSaveMonthlyAttendance').prop('disabled', false).html('<i class="ti ti-device-floppy me-1"></i>Save All Records');
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#modalAttendance').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>
