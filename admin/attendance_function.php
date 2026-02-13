<?php 
date_default_timezone_set('Africa/Accra');
session_start();
include('../includes/config.php');

// Work location center coordinates
define('WORK_CENTER_LAT', -23.936882);
define('WORK_CENTER_LNG', 28.362950);
define('ALLOWED_DISTANCE_METERS', 100);

// Working hours configuration
define('WORK_START_TIME', '07:00:00');
define('WORK_END_TIME', '17:00:00');
define('FRIDAY_EARLY_END_TIME', '13:00:00');
define('LUNCH_BREAK_HOURS', 1);
define('STANDARD_WORK_HOURS_DAILY', 9);

/**
 * Calculate late minutes if clock-in is after work start time (07:00)
 * Only applies to weekdays (Monday-Friday)
 * 
 * @param string $clockInTime Time in H:i:s format
 * @param string $date Date in Y-m-d format
 * @return int|null Late minutes or null if not late/not a weekday


 */



 
function calculateLateMinutes($clockInTime, $date) {
    // Check if it's a weekday
    $dayOfWeek = date('N', strtotime($date)); // 1 = Monday, 7 = Sunday
    if ($dayOfWeek > 5) {
        return null; // Weekend, no late tracking
    }
    
    $workStart = strtotime($date . ' ' . WORK_START_TIME);
    $clockIn = strtotime($date . ' ' . $clockInTime);
    
    if ($clockIn > $workStart) {
        $lateSeconds = $clockIn - $workStart;
        return ceil($lateSeconds / 60); // Return late minutes
    }
    
    return 0; // Not late
}

/**
 * Format late time for display
 * 
 * @param int $lateMinutes Total late minutes
 * @return string Formatted late time (e.g., "1 hour 30 minutes")
 */
function formatLateTime($lateMinutes) {
    if ($lateMinutes <= 0) {
        return '';
    }
    
    $hours = floor($lateMinutes / 60);
    $minutes = $lateMinutes % 60;
    
    $result = '';
    if ($hours > 0) {
        $result .= $hours . ' hour' . ($hours > 1 ? 's' : '');
    }
    if ($minutes > 0) {
        if ($hours > 0) {
            $result .= ' ';
        }
        $result .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }
    
    return $result;
}

/**
 * Check if it's Friday and clock-out is between 13:00 and 17:00
 * If so, record as full 9 hours work day
 * 
 * @param string $clockOutTime Time in H:i:s format
 * @param string $date Date in Y-m-d format
 * @return bool True if Friday early clock-out applies
 */
function isFridayEarlyClockOut($clockOutTime, $date) {
    $dayOfWeek = date('N', strtotime($date)); // 5 = Friday
    if ($dayOfWeek != 5) {
        return false;
    }
    
    $clockOut = strtotime($date . ' ' . $clockOutTime);
    $earlyEnd = strtotime($date . ' ' . FRIDAY_EARLY_END_TIME);
    $normalEnd = strtotime($date . ' ' . WORK_END_TIME);
    
    return ($clockOut >= $earlyEnd && $clockOut <= $normalEnd);
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param float $lat1 Latitude of first point
 * @param float $lon1 Longitude of first point
 * @param float $lat2 Latitude of second point
 * @param float $lon2 Longitude of second point
 * @return float Distance in meters
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Earth's radius in meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    $distance = $earthRadius * $c;
    
    return $distance;
}

function clockIn($staff_id, $latitude = null, $longitude = null) {
    global $conn;

    error_log("Clock in attempt for staff_id: " . $staff_id);
    
    // Debug session
    error_log("Session staff_id: " . (isset($_SESSION['sstaff_id']) ? $_SESSION['sstaff_id'] : 'not set'));

    if ($staff_id !== $_SESSION['sstaff_id']) {
        $response = array('status' => 'error', 'message' => 'Staff ID does not match session ID');
        echo json_encode($response);
        exit;
    }

    // Check if geolocation is provided
    if ($latitude === null || $longitude === null) {
        $response = array('status' => 'error', 'message' => 'Location data is required to clock in. Please enable location services.');
        echo json_encode($response);
        exit;
    }

    // Validate coordinate ranges
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        $response = array('status' => 'error', 'message' => 'Invalid location coordinates received.');
        echo json_encode($response);
        exit;
    }

    // Calculate distance from work center
    $distance = calculateDistance($latitude, $longitude, WORK_CENTER_LAT, WORK_CENTER_LNG);
    
    // Check if user is within allowed distance for clock-in
    if ($distance > ALLOWED_DISTANCE_METERS) {
        $distanceKm = round($distance / 1000, 2);
        $response = array(
            'status' => 'error', 
            'message' => 'You can only clock in when you are at work. You are currently ' . $distanceKm . ' km away from the office.'
        );
        echo json_encode($response);
        exit;
    }
$timeResult = mysqli_query($conn, "SELECT CURDATE() AS db_date, CURTIME() AS db_time");
$timeRow = mysqli_fetch_assoc($timeResult);

$dbDate = $timeRow['db_date'];
$dbTime = $timeRow['db_time'];

  
    // Calculate late minutes for weekdays
   $lateMinutes = calculateLateMinutes($dbTime, $dbDate);

     // Check if staff_id exists in tblemployees
    $stmt = mysqli_prepare($conn, "SELECT * FROM tblemployees WHERE staff_id = ?");
    if (!$stmt) {
        $response = array('status' => 'error', 'message' => 'Query preparation failed: ' . mysqli_error($conn));
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 's', $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        $response = array('status' => 'error', 'message' => 'Invalid Staff ID');
        echo json_encode($response);
        exit;
    }


    
    $stmt = mysqli_prepare($conn, "
    SELECT attendance_id 
    FROM tblattendance
    WHERE staff_id = ?
    AND date = CURDATE()
    AND time_out IS NULL
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, 's', $staff_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $response = array(
        'status' => 'error',
        'message' => 'You must clock out from your previous session before clocking in again.'
    );
    echo json_encode($response);
    exit;
}

    // Check if already clocked in today
   /*$stmt = mysqli_prepare($conn, "
    SELECT * FROM tblattendance
    WHERE staff_id = ?
    AND date = CURDATE()
    AND time_out IS NULL
");
mysqli_stmt_bind_param($stmt, 's', $staff_id);


    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $response = array('status' => 'error', 'message' => 'You have already clocked in today.');
        echo json_encode($response);
        exit;
    }*/



                // Check how many sessions already exist today
        $stmt = mysqli_prepare($conn, "
            SELECT COUNT(*) as total_sessions 
            FROM tblattendance
            WHERE staff_id = ?
            AND date = CURDATE()
        ");
        mysqli_stmt_bind_param($stmt, 's', $staff_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row['total_sessions'] >= 2) {
            $response = array(
                'status' => 'error',
                'message' => 'You have reached the maximum of 2 clock-in sessions for today.'
            );
            echo json_encode($response);
            exit;
        }


            // Insert clock in time with location data and late minutes
        $stmt = mysqli_prepare($conn, "
        INSERT INTO tblattendance
        (staff_id, time_in, date, clock_in_latitude, clock_in_longitude, late_minutes)
        VALUES (?, CURTIME(), CURDATE(), ?, ?, ?)
        ");


   mysqli_stmt_bind_param(
    $stmt,
    'sddi',
    $staff_id,
    $latitude,
    $longitude,
    $lateMinutes
);

    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $message = 'Clocked in successfully.';
        $isLate = false;
        $lateTimeFormatted = '';
        
        // Check if late and add to response for frontend display
        if ($lateMinutes !== null && $lateMinutes > 0) {
            $isLate = true;
            $lateTimeFormatted = formatLateTime($lateMinutes);
        }
        
        $response = array(
            'status' => 'success', 
            'message' => $message,
            'is_late' => $isLate,
            'late_time' => $lateTimeFormatted,
            'late_minutes' => $lateMinutes
        );
        echo json_encode($response);
        exit;
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to clock in.');
        echo json_encode($response);
        exit;
    }
}

function clockOut($staff_id, $latitude = null, $longitude = null) {
    global $conn;

    error_log("Clock out attempt for staff_id: " . $staff_id);
    
    // Debug session
    error_log("Session staff_id: " . (isset($_SESSION['sstaff_id']) ? $_SESSION['sstaff_id'] : 'not set'));

    if ($staff_id !== $_SESSION['sstaff_id']) {
        $response = array('status' => 'error', 'message' => 'Staff ID does not match session ID');
        echo json_encode($response);
        exit;
    }


  

    // Location is optional for clock-out - users can clock out from anywhere
    $clockedOutOffsite = 0;
    
    // If location is provided, check if offsite
    if ($latitude !== null && $longitude !== null) {
        // Validate coordinate ranges
        if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
            // Calculate distance from work center
            $distance = calculateDistance($latitude, $longitude, WORK_CENTER_LAT, WORK_CENTER_LNG);
            // Determine if clocking out offsite
            $clockedOutOffsite = ($distance > ALLOWED_DISTANCE_METERS) ? 1 : 0;
        }
    }
    
    /*$currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');*/

     // Check if staff_id exists in tblemployees
    $stmt = mysqli_prepare($conn, "SELECT * FROM tblemployees WHERE staff_id = ?");
    if (!$stmt) {
        $response = array('status' => 'error', 'message' => 'Query preparation failed: ' . mysqli_error($conn));
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 's', $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        $response = array('status' => 'error', 'message' => 'Invalid Staff ID');
        echo json_encode($response);
        exit;
    }

    

    // Check if clocked in today
   $stmt = mysqli_prepare($conn, "
    SELECT * FROM tblattendance 
    WHERE staff_id = ? 
    AND date = CURDATE() 
    AND time_out IS NULL
    ");
    mysqli_stmt_bind_param($stmt, 's', $staff_id);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        $response = array('status' => 'error', 'message' => 'You must clock in before clocking out.');
        echo json_encode($response);
        exit;
    }
    
    // Check for Friday early clock-out (13:00-17:00 counts as 9 hours)
   $timeResult = mysqli_query($conn, "SELECT CURDATE() AS db_date, CURTIME() AS db_time");
$timeRow = mysqli_fetch_assoc($timeResult);

$isFridayEarly = isFridayEarlyClockOut($timeRow['db_time'], $timeRow['db_date']);

    $recordedHours = null;
    if ($isFridayEarly) {
        $recordedHours = STANDARD_WORK_HOURS_DAILY; // Record as 9 hours
    }

    // Update clock out time with location data
  $stmt = mysqli_prepare($conn, "
    UPDATE tblattendance 
    SET 
        time_out = CURTIME(),
        clock_out_latitude = ?, 
        clock_out_longitude = ?, 
        clocked_out_offsite = ?, 
        recorded_hours = ?
    WHERE attendance_id = (
        SELECT attendance_id FROM (
            SELECT attendance_id FROM tblattendance
            WHERE staff_id = ?
            AND date = CURDATE()
            AND time_out IS NULL
            ORDER BY attendance_id DESC
            LIMIT 1
        ) as temp
    )
");



mysqli_stmt_bind_param(
    $stmt,
    'ddids',
    $latitude,
    $longitude,
    $clockedOutOffsite,
    $recordedHours,
    $staff_id
);


    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $message = 'Clocked out successfully.';
        $showOffsiteAlert = false;
        
        // Only alert about off-site clock-out (no distance shown)
        if ($clockedOutOffsite) {
            $showOffsiteAlert = true;
        }
        
        $response = array(
            'status' => 'success', 
            'message' => $message,
            'clocked_out_offsite' => $showOffsiteAlert,
            'friday_early_clockout' => $isFridayEarly
        );
        echo json_encode($response);
        exit;
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to clock out.');
        echo json_encode($response);
        exit;
    }
}

function deleteAttendance($attendanceId) {
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM tblattendance WHERE attendance_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $attendanceId);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $response = array('status' => 'success', 'message' => 'Attendance record deleted successfully');
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to delete attendance record');
    }
    echo json_encode($response);
    exit;
}

/**
 * Update attendance status for missing clock-out records
 * Manager actions: forfeit, worked, half, overnight
 * 
 * @param int $attendanceId Attendance record ID
 * @param string $status New attendance status
 * @param int $managerId Manager's employee ID
 * @return void
 */
function updateAttendanceStatus($attendanceId, $status, $managerId) {
    global $conn;
    
    $validStatuses = ['forfeit', 'worked', 'half', 'overnight'];
    if (!in_array($status, $validStatuses)) {
        $response = array('status' => 'error', 'message' => 'Invalid attendance status');
        echo json_encode($response);
        exit;
    }
    
    // Get the attendance record to calculate hours for overnight
    $stmt = mysqli_prepare($conn, "SELECT * FROM tblattendance WHERE attendance_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $attendanceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attendance = mysqli_fetch_assoc($result);
    
    if (!$attendance) {
        $response = array('status' => 'error', 'message' => 'Attendance record not found');
        echo json_encode($response);
        exit;
    }
    
    // Calculate recorded hours based on status
    $recordedHours = null;
    $currentTime = date('Y-m-d H:i:s');
    
    switch ($status) {
        case 'forfeit':
            $recordedHours = 0; // No hours recorded
            break;
        case 'worked':
            // Needs manager's manager approval - don't set hours yet
            $recordedHours = null;
            break;
        case 'half':
            $recordedHours = 4.5; // Half day
            break;
        case 'overnight':
            // For overnight, we need to calculate actual hours if clock-out exists
            // If no clock-out, this will be handled when they eventually clock out
            if ($attendance['time_out']) {
                // Handle overnight shifts properly - time_out may be on a different day
                $timeIn = new DateTime($attendance['date'] . ' ' . $attendance['time_in']);
                // For overnight shifts, if time_out is earlier than time_in, assume next day
                $timeOutStr = $attendance['date'] . ' ' . $attendance['time_out'];
                $timeOut = new DateTime($timeOutStr);
                
                // If time_out appears to be before time_in, add a day (overnight shift)
                if ($timeOut < $timeIn) {
                    $timeOut->modify('+1 day');
                }
                
                $interval = $timeIn->diff($timeOut);
                // Include days in calculation for shifts spanning multiple days
                $recordedHours = ($interval->d * 24) + $interval->h + ($interval->i / 60);
            }
            break;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE tblattendance SET attendance_status = ?, manager_action_by = ?, manager_action_date = ?, recorded_hours = ? WHERE attendance_id = ?");
    mysqli_stmt_bind_param($stmt, 'sisdi', $status, $managerId, $currentTime, $recordedHours, $attendanceId);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        $message = 'Attendance status updated to ' . $status . '.';
        if ($status === 'worked') {
            $message .= ' Awaiting manager\'s manager approval.';
        }
        $response = array('status' => 'success', 'message' => $message);
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to update attendance status');
    }
    echo json_encode($response);
    exit;
}

/**
 * Approve 'worked' status by manager's manager
 * This records the employee as having worked 9 hours
 * 
 * @param int $attendanceId Attendance record ID
 * @param int $approverManagerId Manager's manager employee ID
 * @return void
 */
function approveWorkedStatus($attendanceId, $approverManagerId) {
    global $conn;
    
    // Verify the record is in 'worked' status awaiting approval
    $stmt = mysqli_prepare($conn, "SELECT * FROM tblattendance WHERE attendance_id = ? AND attendance_status = 'worked' AND manager_manager_approved_by IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $attendanceId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        $response = array('status' => 'error', 'message' => 'Record not found or already approved');
        echo json_encode($response);
        exit;
    }
    
    $currentTime = date('Y-m-d H:i:s');
    $recordedHours = STANDARD_WORK_HOURS_DAILY; // 9 hours
    
    $stmt = mysqli_prepare($conn, "UPDATE tblattendance SET manager_manager_approved_by = ?, manager_manager_approved_date = NOW()
, recorded_hours = ? WHERE attendance_id = ?");
    mysqli_stmt_bind_param($stmt, 'idi', $approverManagerId, $recordedHours, $attendanceId);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        $response = array('status' => 'success', 'message' => 'Worked status approved. 9 hours recorded.');
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to approve worked status');
    }
    echo json_encode($response);
    exit;
}

/**
 * Reject 'worked' status by manager's manager
 * This reverts the status back to missing_clockout
 * 
 * @param int $attendanceId Attendance record ID
 * @return void
 */
function rejectWorkedStatus($attendanceId) {
    global $conn;
    
    $stmt = mysqli_prepare($conn, "UPDATE tblattendance SET attendance_status = 'missing_clockout', manager_action_by = NULL, manager_action_date = NULL WHERE attendance_id = ? AND attendance_status = 'worked' AND manager_manager_approved_by IS NULL");
    mysqli_stmt_bind_param($stmt, 'i', $attendanceId);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result && mysqli_affected_rows($conn) > 0) {
        $response = array('status' => 'success', 'message' => 'Worked status rejected. Record reverted to missing clock-out.');
    } else {
        $response = array('status' => 'error', 'message' => 'Failed to reject worked status or record not found');
    }
    echo json_encode($response);
    exit;
}

if(isset($_POST['action'])) {
    if ($_POST['action'] === 'clock_in') {
        $staff_id = $_POST['staff_id'];
        $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        clockIn($staff_id, $latitude, $longitude);

    } elseif ($_POST['action'] === 'clock_out') {
        $staff_id = $_POST['staff_id'];
        $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        clockOut($staff_id, $latitude, $longitude);

    } elseif ($_POST['action'] === 'delete_attendance') {
        $attendanceId = $_POST['attendance_id'];
        deleteAttendance($attendanceId);
        
    } elseif ($_POST['action'] === 'update_attendance_status') {
        $attendanceId = intval($_POST['attendance_id']);
        $status = $_POST['status'];
        $managerId = intval($_SESSION['slogin']); // Current logged-in user as manager
        updateAttendanceStatus($attendanceId, $status, $managerId);
        
    } elseif ($_POST['action'] === 'approve_worked_status') {
        $attendanceId = intval($_POST['attendance_id']);
        $approverId = intval($_SESSION['slogin']); // Current logged-in user as approver
        approveWorkedStatus($attendanceId, $approverId);
        
    } elseif ($_POST['action'] === 'reject_worked_status') {
        $attendanceId = intval($_POST['attendance_id']);
        rejectWorkedStatus($attendanceId);
    }
}
?>