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
                 <?php $page_name = "my_attendance"; ?>
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
                                                    <h4>My Attendance</h4>
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
                                                        <div class="col-xl-3">
                                                            <!-- user contact card left side start -->
                                                            <div class="card">
                                                                <div class="tabbed-modal m-b-20 m-t-10 m-r-10 m-l-10">
                                                                    <!-- Nav tabs -->
                                                                    <ul class="nav nav-tabs nav-justified" role="tablist">
                                                                        <li class="nav-item">
                                                                            <a class="nav-link active" data-toggle="tab" href="#clock_in" role="tab">
                                                                                <h6>Clock In</h6>
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <a class="nav-link" data-toggle="tab" href="#clock_out" role="tab">
                                                                                <h6>Clock Out</h6>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                    <!-- Tab panes -->
                                                                    <div class="tab-content">
                                                                        <div class="tab-pane active" id="clock_in" role="tabpanel">
                                                                            <div class="auth-box col-xl-12">
                                                                                <div class="row m-t-20" style="display: flex; justify-content: center; align-items: center;">
                                                                                    <div class="col-md-9">
                                                                                        <div class="card text-center text-white" style="background-color: #404E67;">
                                                                                            <div class="card-block">
                                                                                                <h6 class="day m-b-0"></h6>
                                                                                                <h4 class="time m-t-10 m-b-10"><i class="feather icon-arrow-down m-r-15"></i></h4>
                                                                                                <p class="date m-b-0"></p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="input-group">
                                                                                    <input type="text" class="form-control" placeholder="Staff ID" id="clock_in_id" name="clock_in_id" oninput="this.value = this.value.toUpperCase()" value="<?php echo isset($_SESSION['sstaff_id']) ? $_SESSION['sstaff_id'] : ''; ?>">
                                                                                    <span class="md-line"></span>
                                                                                </div>
                                                                                <div class="row m-t-15">
                                                                                    <div class="col-md-12">
                                                                                        <button id="btn_clock_in" type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center">CONFIRM</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="tab-pane" id="clock_out" role="tabpanel">
                                                                            <div class="auth-box col-xl-12">
                                                                                <div class="row m-t-20" style="display: flex; justify-content: center; align-items: center;">
                                                                                    <div class="col-md-9">
                                                                                        <div class="card text-center text-white" style="background-color: #404E67;">
                                                                                            <div class="card-block">
                                                                                                <h6 class="day m-b-0"></h6>
                                                                                                <h4 class="time m-t-10 m-b-10"></h4>
                                                                                                <p class="date m-b-0"></p>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="input-group">
                                                                                    <input type="text" class="form-control" placeholder="Staff ID" id="clock_out_id" name="clock_out_id" oninput="this.value = this.value.toUpperCase()" value="<?php echo isset($_SESSION['sstaff_id']) ? $_SESSION['sstaff_id'] : ''; ?>">
                                                                                    <span class="md-line"></span>
                                                                                </div>
                                                                                <div class="row m-t-15">
                                                                                    <div class="col-md-12">
                                                                                        <button id="btn_clock_out" type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center">CONFIRM</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- user contact card left side end -->
                                                        </div>
                                                        <div class="col-xl-9">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <!-- contact data table card start -->
                                                                   <?php
                                                                        // Query to fetch attendance records
                                                                       $staff_id = $_SESSION['sstaff_id']; // get logged-in employee

                                                                    $stmt = mysqli_prepare($conn, "
                                                                        SELECT 
                                                                            a.date,
                                                                            a.staff_id,
                                                                            e.first_name,
                                                                            e.middle_name,
                                                                            e.last_name,
                                                                            a.total_hours,
                                                                            a.time_in,
                                                                            a.time_out,
                                                                              a.late_minutes
                                                                        FROM tblattendance a
                                                                        JOIN tblemployees e ON a.staff_id = e.staff_id
                                                                        WHERE a.staff_id = ?
                                                                        ORDER BY a.date DESC
                                                                    ");

                                                                    if (!$stmt) {
                                                                        die("Prepare failed: " . mysqli_error($conn));
                                                                    }

                                                                    mysqli_stmt_bind_param($stmt, "s", $staff_id); 
                                                                    mysqli_stmt_execute($stmt);
                                                                    $result = mysqli_stmt_get_result($stmt);

                                                                      // Helper function to format late time
                                                                        function formatLateTimeDisplay($lateMinutes) {
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
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Total Hours</th>
                                                                                            <th>Status(In/Out)</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>

                                                                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                                                                <?php
                                                                                                    $time_in = new DateTime($row['time_in']);
                                                                                                    $time_out = $row['time_out'] ? new DateTime($row['time_out']) : null;

                                                                                                    // ---- LATE LOGIC (ADD THIS) ----
                                                                                                    $lateMinutes = isset($row['late_minutes']) ? (int)$row['late_minutes'] : 0;
                                                                                                    $late_display = formatLateTimeDisplay($lateMinutes);
                                                                                                    $late_class = ($lateMinutes > 0)
                                                                                                        ? 'style="color: red; font-weight: bold;"'
                                                                                                        : '';

                                                                                                    // ---- TOTAL HOURS ----
                                                                                                    if ($time_out) {
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

                                                                                                    // Status formatting
                                                                                                    $status = $row['time_out'] ? 'In/Out' : 'In';

                                                                                                    if ($status == 'In/Out') {
                                                                                                        $formatted_status = '<span style="color: green;">In</span>/<span style="color: orange;">Out</span>';
                                                                                                    } else {
                                                                                                        $formatted_status = '<span style="color: green;">In</span>';
                                                                                                    }
                                                                                                ?>

                                                                                       
                                                                                            <tr>
                                                                                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                                                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($row['time_in']))); ?></td>
                                                                                                <td><?php echo $time_out ? htmlspecialchars(date('h:i A', strtotime($row['time_out']))) : '-'; ?></td>
                                                                                                <td <?php echo $late_class; ?>><?php echo htmlspecialchars($late_display); ?></td>
                                                                                                <td><strong><?php echo htmlspecialchars($total_hours); ?></strong></td>
                                                                                                <td><?php echo $formatted_status; ?></td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    </tbody>
                                                                                    <tfoot>
                                                                                        <tr>
                                                                                            <th>Date</th>
                                                                                            <th>Time In</th>
                                                                                            <th>Time Out</th>
                                                                                            <th>Late By</th>
                                                                                            <th>Total Hours</th>

                                                                                            <th>Status(In/Out)</th>
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
            $('#btn_clock_in').click(function(event){
                event.preventDefault();
                clockIn();
            });

            $('#btn_clock_out').click(function(event){
                event.preventDefault();
                clockOut();
            });

            // Get location with 2-minute timeout for clock-in (required)
            function getLocationForClockIn(callback) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            callback(null, {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            });
                        },
                        function(error) {
                            var errorMessage = 'Unable to retrieve your location. ';
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage += 'Please enable location services in your browser.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage += 'Location information is unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage += 'The request to get your location timed out after 2 minutes.';
                                    break;
                                default:
                                    errorMessage += 'An unknown error occurred.';
                                    break;
                            }
                            callback(errorMessage, null);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 120000, // 2 minutes timeout
                            maximumAge: 0
                        }
                    );
                } else {
                    callback('Geolocation is not supported by your browser.', null);
                }
            }

            // Get location for clock-out (optional - continue even if it fails)
            function getLocationForClockOut(callback) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            callback(null, {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            });
                        },
                        function(error) {
                            // Location failed but we still allow clock-out
                            callback(null, { latitude: null, longitude: null });
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 120000, // 2 minutes timeout
                            maximumAge: 0
                        }
                    );
                } else {
                    // Geolocation not supported but we still allow clock-out
                    callback(null, { latitude: null, longitude: null });
                }
            }

            function clockIn() {
                var staffId = $('#clock_in_id').val().trim();
                if (staffId === '') {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Please enter your Staff ID to clock in.',
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                console.log("STAFF ID HERE: " + staffId);

                // Show loading indicator while getting location
                Swal.fire({
                    title: 'Getting your location...',
                    text: 'Please wait while we verify your location.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Get user's location before clocking in (required for clock-in)
                getLocationForClockIn(function(error, location) {
                    if (error) {
                        Swal.fire({
                            icon: 'error',
                            text: error,
                            confirmButtonColor: '#eb3422',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    $.ajax({
                        url: 'attendance_function.php',
                        type: 'post',
                        data: {
                            action: 'clock_in', 
                            staff_id: staffId,
                            latitude: location.latitude,
                            longitude: location.longitude
                        },
                        success: function(response) {
                            
                            console.log("Response: " + response);
                            response = JSON.parse(response);
                            if (response.status === 'success') {
                                // Check if user is late
                                if (response.is_late && response.late_time) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Late Clock-in',
                                        html: '<p>You have clocked in successfully.</p><p><strong>Note:</strong> You are late for work by <strong>' + response.late_time + '</strong>.</p>',
                                        confirmButtonColor: '#ffc107',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        text: response.message,
                                        confirmButtonColor: '#01a9ac',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    text: response.message,
                                    confirmButtonColor: '#eb3422',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            console.log("Response Text:", jqXHR.responseText);
                            Swal.fire({
                                icon: 'error',
                                text: 'Server error occurred. Please try again.',
                                confirmButtonColor: '#eb3422',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            }

            function clockOut() {
                var staffId = $('#clock_out_id').val().trim();
                if (staffId === '') {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Please enter your Staff ID to clock out.',
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Show loading indicator
                Swal.fire({
                    title: 'Processing clock-out...',
                    text: 'Please wait.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Get user's location for clock-out (optional - proceed even if unavailable)
                getLocationForClockOut(function(error, location) {
                    $.ajax({
                        url: 'attendance_function.php',
                        type: 'post',
                        data: {
                            action: 'clock_out', 
                            staff_id: staffId,
                            latitude: location.latitude,
                            longitude: location.longitude
                        },
                        success: function(response) {
                            response = JSON.parse(response);
                            if (response.status === 'success') {
                                // Check if clocked out offsite and show alert
                                if (response.clocked_out_offsite) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Clocked Out Successfully',
                                        html: '<p>' + response.message + '</p><p><strong>Note:</strong> You have clocked out while outside the office.</p>',
                                        confirmButtonColor: '#01a9ac',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        text: response.message,
                                        confirmButtonColor: '#01a9ac',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    text: response.message,
                                    confirmButtonColor: '#eb3422',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            console.log("Response Text:", jqXHR.responseText);
                            Swal.fire({
                                icon: 'error',
                                text: 'Server error occurred. Please try again.',
                                confirmButtonColor: '#eb3422',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>
