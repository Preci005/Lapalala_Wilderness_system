<?php
/**
 * Attendance Calculations
 * 
 * This file contains reusable functions for attendance reporting calculations
 */

// Standard work hours per day
define('STANDARD_WORK_HOURS', 9);
// Standard work days per week (Monday-Friday)
define('STANDARD_WORK_DAYS', 5);
// Expected hours per week
define('EXPECTED_WEEKLY_HOURS', STANDARD_WORK_HOURS * STANDARD_WORK_DAYS); // 45 hours

/**
 * Calculate total hours worked from time string (HH:MM:SS format)
 * 
 * @param string $timeString Time in HH:MM:SS format
 * @return float Hours as decimal
 */
function calculateHoursFromTimeString($timeString) {
    if (empty($timeString)) {
        return 0;
    }
    
    $parts = explode(':', $timeString);
    if (count($parts) !== 3) {
        return 0;
    }
    
    $hours = (int)$parts[0];
    $minutes = (int)$parts[1];
    $seconds = (int)$parts[2];
    
    return $hours + ($minutes / 60) + ($seconds / 3600);
}

/**
 * Calculate extra hours beyond standard work hours
 * 
 * @param float $hoursWorked Total hours worked
 * @return float Extra hours (0 if less than standard)
 */
function calculateExtraHours($hoursWorked) {
    $extra = $hoursWorked - STANDARD_WORK_HOURS;
    return $extra > 0 ? $extra : 0;
}

/**
 * Calculate deficit hours (hours below standard)
 * 
 * @param float $hoursWorked Total hours worked
 * @return float Deficit hours (0 if more than standard)
 */
function calculateDeficitHours($hoursWorked) {
    $deficit = STANDARD_WORK_HOURS - $hoursWorked;
    return $deficit > 0 ? $deficit : 0;
}

/**
 * Calculate days off accumulated from extra hours
 * 
 * @param float $totalExtraHours Total extra hours accumulated
 * @return float Days off (9 extra hours = 1 day off)
 */
function calculateDaysOffAccumulated($totalExtraHours) {
    return $totalExtraHours / STANDARD_WORK_HOURS;
}

/**
 * Format hours to readable string (e.g., "8.5 hrs" or "8 hrs 30 mins")
 * 
 * @param float $hours Hours as decimal
 * @param bool $detailed Whether to show minutes separately
 * @return string Formatted hours string
 */
function formatHours($hours, $detailed = false) {
    if ($hours == 0) {
        return "0 hrs";
    }
    
    $wholeHours = floor($hours);
    $minutes = round(($hours - $wholeHours) * 60);
    
    if (!$detailed || $minutes == 0) {
        return number_format($hours, 1) . " hrs";
    }
    
    $result = "";
    if ($wholeHours > 0) {
        $result .= $wholeHours . " hr" . ($wholeHours > 1 ? "s" : "");
    }
    if ($minutes > 0) {
        if ($wholeHours > 0) {
            $result .= " ";
        }
        $result .= $minutes . " min" . ($minutes > 1 ? "s" : "");
    }
    
    return $result;
}

/**
 * Get week number and year for a given date
 * 
 * @param string $date Date string
 * @return array Array with 'week' and 'year' keys
 */
function getWeekInfo($date) {
    $dateObj = new DateTime($date);
    return [
        'week' => (int)$dateObj->format('W'),
        'year' => (int)$dateObj->format('Y'),
        'week_label' => 'Week ' . $dateObj->format('W') . ', ' . $dateObj->format('Y')
    ];
}

/**
 * Get all attendance records grouped by staff and week
 * 
 * @param mysqli $conn Database connection
 * @param string|null $startDate Optional start date filter
 * @param string|null $endDate Optional end date filter
 * @return array Multi-dimensional array of attendance data
 */
function getWeeklyAttendanceByStaff($conn, $startDate = null, $endDate = null) {
    $query = "SELECT a.date, a.staff_id, a.time_in, a.time_out, a.total_hours,
                     e.first_name, e.middle_name, e.last_name, e.emp_id
              FROM tblattendance a
              JOIN tblemployees e ON a.staff_id = e.staff_id";
    
    $conditions = [];
    $params = [];
    $param_types = "";
    
    if ($startDate) {
        $conditions[] = "a.date >= ?";
        $params[] = $startDate;
        $param_types .= "s";
    }
    if ($endDate) {
        $conditions[] = "a.date <= ?";
        $params[] = $endDate;
        $param_types .= "s";
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " ORDER BY e.first_name, a.date";
    
    // Use prepared statement if there are parameters, otherwise use regular query
    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    $attendanceData = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $staffKey = $row['staff_id'];
            $weekInfo = getWeekInfo($row['date']);
            $weekKey = $weekInfo['year'] . '-W' . str_pad($weekInfo['week'], 2, '0', STR_PAD_LEFT);
            
            if (!isset($attendanceData[$staffKey])) {
                $attendanceData[$staffKey] = [
                    'staff_id' => $row['staff_id'],
                    'emp_id' => $row['emp_id'],
                    'full_name' => trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']),
                    'weeks' => []
                ];
            }
            
            if (!isset($attendanceData[$staffKey]['weeks'][$weekKey])) {
                $attendanceData[$staffKey]['weeks'][$weekKey] = [
                    'week_label' => $weekInfo['week_label'],
                    'total_hours' => 0,
                    'extra_hours' => 0,
                    'days' => []
                ];
            }
            
            $hoursWorked = calculateHoursFromTimeString($row['total_hours']);
            $extraHours = calculateExtraHours($hoursWorked);
            $deficitHours = calculateDeficitHours($hoursWorked);
            
            $attendanceData[$staffKey]['weeks'][$weekKey]['days'][] = [
                'date' => $row['date'],
                'time_in' => $row['time_in'],
                'time_out' => $row['time_out'],
                'total_hours' => $row['total_hours'],
                'hours_decimal' => $hoursWorked,
                'extra_hours' => $extraHours,
                'deficit_hours' => $deficitHours
            ];
            
            $attendanceData[$staffKey]['weeks'][$weekKey]['total_hours'] += $hoursWorked;
            $attendanceData[$staffKey]['weeks'][$weekKey]['extra_hours'] += $extraHours;
        }
    }
    
    return $attendanceData;
}

/**
 * Calculate total extra hours and days off for a staff member
 * 
 * @param array $staffWeekData Staff's week data from getWeeklyAttendanceByStaff
 * @return array Array with 'total_extra_hours' and 'days_off_accumulated' keys
 */
function calculateStaffDaysOff($staffWeekData) {
    $totalExtraHours = 0;
    
    foreach ($staffWeekData as $weekData) {
        $totalExtraHours += $weekData['extra_hours'];
    }
    
    return [
        'total_extra_hours' => $totalExtraHours,
        'days_off_accumulated' => calculateDaysOffAccumulated($totalExtraHours)
    ];
}
?>
