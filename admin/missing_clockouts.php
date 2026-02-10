<?php include('../includes/header.php')?>
<?php
// Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the user has the role of Manager or Admin
$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

$currentUserId = $_SESSION['slogin'];

// Helper function to format late time
function formatLateTimeDisplayAdmin($lateMinutes) {
    if ($lateMinutes === null || $lateMinutes <= 0) {
        return '-';
    }
    $hours = floor($lateMinutes / 60);
    $minutes = $lateMinutes % 60;
    $result = '';
    if ($hours > 0) {
        $result .= $hours . ' hr' . ($hours > 1 ? 's ' : ' ');
    }
    if ($minutes > 0) {
        $result .= $minutes . ' min' . ($minutes > 1 ? 's' : '');
    }
    return trim($result);
}

// Helper function to format attendance status
function formatAttendanceStatus($status) {
    switch($status) {
        case 'normal':
            return '<span class="badge badge-success">Normal</span>';
        case 'missing_clockout':
            return '<span class="badge badge-danger">Missing Clock-out</span>';
        case 'forfeit':
            return '<span class="badge badge-secondary">Forfeit</span>';
        case 'worked':
            return '<span class="badge badge-warning">Pending Approval</span>';
        case 'half':
            return '<span class="badge badge-info">Half Day (4.5 hrs)</span>';
        case 'overnight':
            return '<span class="badge badge-primary">Overnight</span>';
        default:
            return '<span class="badge badge-light">' . ucfirst($status) . '</span>';
    }
}
?>
<body>
<!-- Pre-loader start -->
<?php include('../includes/loader.php')?>
<!-- Pre-loader end -->
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php')?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                 <?php $page_name = "missing_clockouts"; ?>
                <?php include('../includes/sidebar.php')?>

                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <!-- Main-body start -->
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- Page-header start -->
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>Missing Clock-out Records</h4>
                                                    <p class="text-muted">Review and action attendance records where employees did not clock out</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-header end -->

                                <!-- Page-body start -->
                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- tab content start -->
                                            <div class="tab-content">
                                                <!-- tab pane contact start -->
                                                <div class="tab-pane active" id="contacts" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <!-- Missing clock-out records table start -->
                                                                    <?php
                                                                        // Query to fetch missing clock-out records
                                                                        // A record is considered missing clock-out if:
                                                                        // 1. time_out is NULL AND the date is before today
                                                                        // 2. OR attendance_status is 'missing_clockout'
                                                                        $today = date('Y-m-d');
                                                                        $stmt = mysqli_prepare($conn, "
                                                                            SELECT a.attendance_id, a.date, a.staff_id, 
                                                                                e.first_name, e.middle_name, e.last_name, e.emp_id,
                                                                                a.time_in, a.time_out, a.late_minutes, 
                                                                                a.attendance_status, a.recorded_hours,
                                                                                a.manager_action_by, a.manager_action_date,
                                                                                a.manager_manager_approved_by, a.manager_manager_approved_date,
                                                                                m.first_name as manager_first_name, m.last_name as manager_last_name
                                                                            FROM tblattendance a
                                                                            JOIN tblemployees e ON a.staff_id = e.staff_id
                                                                            LEFT JOIN tblemployees m ON a.manager_action_by = m.emp_id
                                                                            WHERE (a.time_out IS NULL AND a.date < ?)
                                                                               OR a.attendance_status IN ('missing_clockout', 'worked')
                                                                            ORDER BY a.date DESC, e.first_name
                                                                        ");
                                                                        mysqli_stmt_bind_param($stmt, "s", $today);
                                                                        mysqli_stmt_execute($stmt);
                                                                        $result = mysqli_stmt_get_result($stmt);
                                                                    ?>
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="card-header-text">Records Requiring Action</h5>
                                                                        </div>
                                                                        <div class="card-block contact-details">
                                                                            <div class="data_table_main table-responsive dt-responsive">
                                                                                <table id="simpletable" class="table table-striped table-bordered nowrap">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Date</th>
                                                                                            <th>Staff ID</th>
                                                                                            <th>Employee Name</th>
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Status</th>
                                                                                            <th>Recorded Hours</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                                                            <?php
                                                                                                $time_out = $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-';
                                                                                                $late_display = formatLateTimeDisplayAdmin($row['late_minutes']);
                                                                                                $status_display = formatAttendanceStatus($row['attendance_status'] ?: 'missing_clockout');
                                                                                                $recorded_hours = $row['recorded_hours'] !== null ? $row['recorded_hours'] . ' hrs' : '-';
                                                                                                
                                                                                                // Determine if this record needs action or approval
                                                                                                // Only records with null or missing_clockout status need action (removed 'normal')
                                                                                                $needsAction = $row['attendance_status'] === null || $row['attendance_status'] === 'missing_clockout';
                                                                                                $needsApproval = $row['attendance_status'] === 'worked' && $row['manager_manager_approved_by'] === null;
                                                                                            ?>
                                                                                            <tr>
                                                                                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                                                                <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                                                                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($row['time_in']))); ?></td>
                                                                                                <td><?php echo htmlspecialchars($time_out); ?></td>
                                                                                                <td <?php echo ($row['late_minutes'] > 0) ? 'style="color: red; font-weight: bold;"' : ''; ?>><?php echo htmlspecialchars($late_display); ?></td>
                                                                                                <td><?php echo $status_display; ?></td>
                                                                                                <td><?php echo htmlspecialchars($recorded_hours); ?></td>
                                                                                                <td>
                                                                                                    <?php if ($needsAction && ($row['time_out'] === null)): ?>
                                                                                                        <div class="btn-group" role="group">
                                                                                                            <button type="button" class="btn btn-sm btn-secondary btn-action" data-id="<?php echo $row['attendance_id']; ?>" data-action="forfeit" title="Forfeit - No hours recorded">
                                                                                                                Forfeit
                                                                                                            </button>
                                                                                                            <button type="button" class="btn btn-sm btn-success btn-action" data-id="<?php echo $row['attendance_id']; ?>" data-action="worked" title="Worked - Requires manager approval">
                                                                                                                Worked
                                                                                                            </button>
                                                                                                            <button type="button" class="btn btn-sm btn-info btn-action" data-id="<?php echo $row['attendance_id']; ?>" data-action="half" title="Half Day - 4.5 hours">
                                                                                                                Half
                                                                                                            </button>
                                                                                                            <button type="button" class="btn btn-sm btn-primary btn-action" data-id="<?php echo $row['attendance_id']; ?>" data-action="overnight" title="Overnight - Count actual hours">
                                                                                                                Overnight
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    <?php elseif ($needsApproval): ?>
                                                                                                        <div class="btn-group" role="group">
                                                                                                            <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?php echo $row['attendance_id']; ?>" title="Approve - Record as 9 hours">
                                                                                                                <i class="fa fa-check"></i> Approve
                                                                                                            </button>
                                                                                                            <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $row['attendance_id']; ?>" title="Reject - Revert to missing clock-out">
                                                                                                                <i class="fa fa-times"></i> Reject
                                                                                                            </button>
                                                                                                        </div>
                                                                                                        <small class="text-muted d-block mt-1">
                                                                                                            Set by: <?php echo htmlspecialchars($row['manager_first_name'] . ' ' . $row['manager_last_name']); ?>
                                                                                                        </small>
                                                                                                    <?php else: ?>
                                                                                                        <span class="text-muted">Completed</span>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    </tbody>
                                                                                    <tfoot>
                                                                                        <tr>
                                                                                            <th>Date</th>
                                                                                            <th>Staff ID</th>
                                                                                            <th>Employee Name</th>
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Status</th>
                                                                                            <th>Recorded Hours</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- Missing clock-out records table end -->
                                                                    
                                                                    <!-- Legend Card -->
                                                                    <div class="card mt-4">
                                                                        <div class="card-header">
                                                                            <h5 class="card-header-text">Action Definitions</h5>
                                                                        </div>
                                                                        <div class="card-block">
                                                                            <ul class="list-group">
                                                                                <li class="list-group-item">
                                                                                    <strong>Forfeit:</strong> Employee's recorded time is disregarded. They are marked as present (not absent), but no working hours are recorded.
                                                                                </li>
                                                                                <li class="list-group-item">
                                                                                    <strong>Worked:</strong> Requires verification from your manager before the employee's hours can be recorded as 9 hours (full day).
                                                                                </li>
                                                                                <li class="list-group-item">
                                                                                    <strong>Half:</strong> Records the employee as having worked 4.5 hours for that day.
                                                                                </li>
                                                                                <li class="list-group-item">
                                                                                    <strong>Overnight:</strong> Counts hours from clock-in until clock-out (even if next day). Hours in excess of 9 are overtime.
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- tab pane contact end -->
                                            </div>
                                            <!-- tab content end -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-body end -->
                            </div>
                            <!-- Main body end -->
                            <div id="styleSelector">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <?php include('../includes/scripts.php')?>
    <script>
        $(document).ready(function() {
            // Handle action buttons (forfeit, worked, half, overnight)
            $('.btn-action').click(function(event) {
                event.preventDefault();
                var attendanceId = $(this).data('id');
                var action = $(this).data('action');
                var actionTitle = action.charAt(0).toUpperCase() + action.slice(1);
                
                var message = '';
                switch(action) {
                    case 'forfeit':
                        message = 'This will mark the employee as present but no working hours will be recorded.';
                        break;
                    case 'worked':
                        message = 'This will require approval from your manager before 9 hours can be recorded.';
                        break;
                    case 'half':
                        message = 'This will record 4.5 hours for the employee.';
                        break;
                    case 'overnight':
                        message = 'This will count all hours from clock-in to clock-out. Hours beyond 9 are overtime.';
                        break;
                }
                
                Swal.fire({
                    title: 'Confirm Action: ' + actionTitle,
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#01a9ac',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'attendance_function.php',
                            type: 'POST',
                            data: {
                                action: 'update_attendance_status',
                                attendance_id: attendanceId,
                                status: action
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonColor: '#01a9ac'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#eb3422'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Server error occurred. Please try again.',
                                    confirmButtonColor: '#eb3422'
                                });
                            }
                        });
                    }
                });
            });
            
            // Handle approve button (for manager's manager)
            $('.btn-approve').click(function(event) {
                event.preventDefault();
                var attendanceId = $(this).data('id');
                
                Swal.fire({
                    title: 'Approve Worked Status?',
                    text: 'This will record 9 hours for the employee.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'attendance_function.php',
                            type: 'POST',
                            data: {
                                action: 'approve_worked_status',
                                attendance_id: attendanceId
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Approved',
                                        text: response.message,
                                        confirmButtonColor: '#01a9ac'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#eb3422'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Server error occurred. Please try again.',
                                    confirmButtonColor: '#eb3422'
                                });
                            }
                        });
                    }
                });
            });
            
            // Handle reject button
            $('.btn-reject').click(function(event) {
                event.preventDefault();
                var attendanceId = $(this).data('id');
                
                Swal.fire({
                    title: 'Reject Worked Status?',
                    text: 'This will revert the record back to missing clock-out status.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'attendance_function.php',
                            type: 'POST',
                            data: {
                                action: 'reject_worked_status',
                                attendance_id: attendanceId
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Rejected',
                                        text: response.message,
                                        confirmButtonColor: '#01a9ac'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#eb3422'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Server error occurred. Please try again.',
                                    confirmButtonColor: '#eb3422'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
