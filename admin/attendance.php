<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php include('../includes/header.php')?>
<?php require_once('attendance_reports.php'); ?>
<?php
// Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the user has the role of Manager or Admin
$userRole = $_SESSION['srole'];
$session_role   = $_SESSION['srole'];
$session_depart = $_SESSION['department'];

if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Get the view type from URL parameter, default to 'all' (original view)
$viewType = isset($_GET['view']) ? $_GET['view'] : 'all';
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
                 <?php $page_name = "attendance"; ?>
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
                                                    <h4>Attendance</h4>
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

                                        <!-- ================= VIEW SELECTOR ================= -->
<div class="card">
    <div class="card-block">
        <div class="btn-group" role="group">

            <a href="attendance.php?view=all"
               class="btn btn-primary <?= $viewType === 'all' ? 'active' : ''; ?>">
                All Records
            </a>

            <a href="attendance.php?view=weekly"
               class="btn btn-primary <?= $viewType === 'weekly' ? 'active' : ''; ?>">
                Weekly Hours
            </a>

            <a href="attendance.php?view=daysoff"
               class="btn btn-primary <?= $viewType === 'daysoff' ? 'active' : ''; ?>">
                Days Off Accumulated
            </a>

            <a href="attendance.php?view=detailed"
               class="btn btn-primary <?= $viewType === 'detailed' ? 'active' : ''; ?>">
                Detailed Breakdown
            </a>

        </div>
    </div>
</div>
<!-- ================= END VIEW SELECTOR ================= -->

<?php if ($viewType !== 'all'): ?>

    <!-- REPORT VIEWS -->
    <?php echo generateAttendanceReport($conn, $viewType); ?>

<?php else: ?>

    <!-- ================= ALL RECORDS TABLE ================= -->


                                            <!-- tab content start -->
                                            <div class="tab-content">
                                                <!-- tab pane contact start -->
                                                <div class="tab-pane active" id="contacts" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <!-- contact data table card start -->
                                                                     <?php
                                                                        // Helper function to format late time
                                                                        function formatLateTimeAttendance($lateMinutes) {
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
                                                                        
                                                                        // Query to fetch attendance records with late_minutes
                                                                        $stmt = mysqli_prepare($conn, "SELECT a.date, a.staff_id, 
                                                                                                            e.first_name, e.middle_name, e.last_name, a.attendance_id,
                                                                                                            a.time_in, a.time_out, a.clocked_out_offsite, a.late_minutes,
                                                                                                            a.recorded_hours, a.attendance_status
                                                                                                    FROM tblattendance a
                                                                                                    JOIN tblemployees e ON a.staff_id = e.staff_id
                                                                                                    ORDER BY a.date DESC");
                                                                        mysqli_stmt_execute($stmt);
                                                                        $result = mysqli_stmt_get_result($stmt);
                                                                     ?>
                                                                    <div class="card">
                                                                        <div class="card-header">
                                                                            <h5 class="card-header-text">Attendance Records</h5>
                                                                        </div>
                                                                        <div class="card-block contact-details">
                                                                            <div class="data_table_main table-responsive dt-responsive">
                                                                                <table id="simpletable" class="table  table-striped table-bordered nowrap">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th>Date</th>
                                                                                            <th>Staff ID</th>
                                                                                            <th>Full Name</th>
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Total Hours</th>
                                                                                            <th>Status(In/Out)</th>
                                                                                            <th>Location</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                                                            <?php
                                                                                                $time_in = new DateTime($row['time_in']);
                                                                                                $time_out = $row['time_out'] ? new DateTime($row['time_out']) : null;
                                                                                                // Calculate and format total hours
                                                                                                if ($row['recorded_hours'] !== null) {
                                                                                                    $total_hours = $row['recorded_hours'] . ' hrs (recorded)';
                                                                                                } elseif ($time_out) {
                                                                                                    $time_in = new DateTime($row['time_in']);
                                                                                                    $interval = $time_in->diff($time_out);
                                                                                                    
                                                                                                    $hours = $interval->h;
                                                                                                    $minutes = $interval->i;
                                                                                                    $seconds = $interval->s;

                                                                                                    $total_hours = '';
                                                                                                    if ($hours > 0) {
                                                                                                        $total_hours .= $hours . ' hr' . ($hours > 1 ? 's ' : ' ');
                                                                                                    }
                                                                                                    if ($minutes > 0) {
                                                                                                        $total_hours .= $minutes . ' min' . ($minutes > 1 ? 's ' : ' ');
                                                                                                    }
                                                                                                    if ($seconds > 0) {
                                                                                                        $total_hours .= $seconds . ' sec' . ($seconds > 1 ? 's' : '');
                                                                                                    }

                                                                                                    $total_hours = trim($total_hours);
                                                                                                } else {
                                                                                                    $total_hours = '-';
                                                                                                }
                                                                                                // Determine status
                                                                                                $status = $row['time_out'] ? 'In/Out' : 'In';

                                                                                                // Split and color the status
                                                                                                if ($status == 'In/Out') {
                                                                                                    $formatted_status = '<span style="color: green;">In</span>/<span style="color: orange;">Out</span>';
                                                                                                } else {
                                                                                                    $formatted_status = '<span style="color: green;">In</span>';
                                                                                                }
                                                                                                
                                                                                                // Determine location status
                                                                                                $location_status = '-';
                                                                                                if ($row['time_out'] && $row['clocked_out_offsite'] === 1) {
                                                                                                    $location_status = '<span style="color: red; font-weight: bold;">⚠ Clocked out offsite</span>';
                                                                                                } elseif ($row['time_out'] && $row['clocked_out_offsite'] === 0) {
                                                                                                    $location_status = '<span style="color: green;">At work</span>';
                                                                                                }
                                                                                                
                                                                                                // Format late time
                                                                                                $late_display = formatLateTimeAttendance($row['late_minutes']);
                                                                                                $late_class = ($row['late_minutes'] > 0) ? 'style="color: red; font-weight: bold;"' : '';
                                                                                            ?>
                                                                                            <tr>
                                                                                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                                                                <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                                                                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($row['time_in']))); ?></td>
                                                                                                <td><?php echo $time_out ? htmlspecialchars(date('h:i A', strtotime($row['time_out']))) : '-'; ?></td>
                                                                                                <td <?php echo $late_class; ?>><?php echo htmlspecialchars($late_display); ?></td>
                                                                                                <td><strong><?php echo htmlspecialchars($total_hours); ?></strong></td>
                                                                                                <td><?php echo $formatted_status; ?></td>
                                                                                                <td><?php echo $location_status; ?></td>
                                                                                                <td class="dropdown">
                                                                                                    <button id="btn_delete" type="submit" class="btn btn-primary" data-id="<?php echo $row['attendance_id']; ?>"><i class="icofont icofont-ui-delete" aria-hidden="true"></i>Delete</button>
                                                                                                </td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    </tbody>
                                                                                    <tfoot>
                                                                                        <tr>
                                                                                            <th>Date</th>
                                                                                            <th>Staff ID</th>
                                                                                            <th>Full Name</th>
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Total Hours</th>
                                                                                            <th>Status(In/Out)</th>
                                                                                            <th>Location</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </tfoot>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!-- contact data table card end -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- tab pane contact end -->
                                            </div>
                                            <!-- tab content end -->
                                          <?php endif; ?>

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
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-23581568-13');
        
        $(function() {
            var interval = setInterval(function() {
                var momentNow = moment();
                $('.date').html(momentNow.format('MMMM DD, YYYY'));  
                $('.time').html(momentNow.format('hh:mm:ss A'));
                $('.day').html(momentNow.format('dddd').toUpperCase());
            }, 100);
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#btn_delete').click(function(event){
                event.preventDefault();
                var attendanceId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete this record?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'attendance_function.php',
                            type: 'POST',
                            data: {
                                action: 'delete_attendance',
                                attendance_id: attendanceId
                            },
                            success: function(response) {
                                response = JSON.parse(response);
                                if(response.status === 'success') {
                                    Swal.fire(
                                        'Deleted!',
                                        'The record has been deleted.',
                                        'success'
                                    ).then(() => {
                                        location.reload(); // Refresh the page to reflect changes
                                    });
                                } else {
                                    Swal.fire(
                                        'Failed!',
                                        'Failed to delete record: ' + response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('Error:', errorThrown);
                                Swal.fire(
                                    'Error!',
                                    'Error deleting record',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>
