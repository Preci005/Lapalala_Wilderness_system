<?php
require_once('attendance_calculations.php');

/**
 * Attendance Reports
 * 
 * This file contains functions for generating different attendance report views
 */

/**
 * Generate Weekly Hours View (Default)
 * Shows all users' total hours worked per week based on 5 9-hour workdays
 * 
 * @param mysqli $conn Database connection
 * @return string HTML output for the report
 */
function generateWeeklyHoursView($conn) {
    $attendanceData = getWeeklyAttendanceByStaff($conn);
    
    $html = '<div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">Weekly Hours Report</h5>
                    <p class="text-muted">Expected: ' . EXPECTED_WEEKLY_HOURS . ' hours per week (' . STANDARD_WORK_DAYS . ' days × ' . STANDARD_WORK_HOURS . ' hours)</p>
                </div>
                <div class="card-block">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Staff ID</th>
                                    <th>Employee Name</th>
                                    <th>Week</th>
                                    <th>Total Hours Worked</th>
                                    <th>Expected Hours</th>
                                    <th>Variance</th>
                                </tr>
                            </thead>
                            <tbody>';
    
    if (empty($attendanceData)) {
        $html .= '<tr><td colspan="6" class="text-center">No attendance records found</td></tr>';
    } else {
        foreach ($attendanceData as $staffData) {
            foreach ($staffData['weeks'] as $weekKey => $weekData) {
                $variance = $weekData['total_hours'] - EXPECTED_WEEKLY_HOURS;
                $varianceClass = $variance >= 0 ? 'text-success' : 'text-danger';
                $varianceSign = $variance >= 0 ? '+' : '';
                
                $html .= '<tr>
                            <td>' . htmlspecialchars($staffData['staff_id']) . '</td>
                            <td>' . htmlspecialchars($staffData['full_name']) . '</td>
                            <td>' . htmlspecialchars($weekData['week_label']) . '</td>
                            <td><strong>' . formatHours($weekData['total_hours']) . '</strong></td>
                            <td>' . EXPECTED_WEEKLY_HOURS . ' hrs</td>
                            <td class="' . $varianceClass . '"><strong>' . $varianceSign . formatHours($variance) . '</strong></td>
                          </tr>';
            }
        }
    }
    
    $html .= '      </tbody>
                        </table>
                    </div>
                </div>
            </div>';
    
    return $html;
}

/**
 * Generate Days Off Accumulated View
 * Shows weekly hours PLUS days off accumulated from extra hours
 * 
 * @param mysqli $conn Database connection
 * @return string HTML output for the report
 */
function generateDaysOffView($conn) {
    $attendanceData = getWeeklyAttendanceByStaff($conn);
    
    $html = '<div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">Days Off Accumulated Report</h5>
                    <p class="text-muted">Extra hours beyond ' . STANDARD_WORK_HOURS . ' hours/day accumulate towards days off (9 extra hours = 1 day off)</p>
                </div>
                <div class="card-block">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Staff ID</th>
                                    <th>Employee Name</th>
                                    <th>Total Extra Hours</th>
                                    <th>Days Off Accumulated</th>
                                </tr>
                            </thead>
                            <tbody>';
    
    if (empty($attendanceData)) {
        $html .= '<tr><td colspan="4" class="text-center">No attendance records found</td></tr>';
    } else {
        foreach ($attendanceData as $staffData) {
            $daysOffData = calculateStaffDaysOff($staffData['weeks']);
            
            $html .= '<tr>
                        <td>' . htmlspecialchars($staffData['staff_id']) . '</td>
                        <td>' . htmlspecialchars($staffData['full_name']) . '</td>
                        <td><span class="badge badge-success">' . formatHours($daysOffData['total_extra_hours']) . '</span></td>
                        <td><strong>' . number_format($daysOffData['days_off_accumulated'], 2) . ' days</strong></td>
                      </tr>';
        }
    }
    
    $html .= '      </tbody>
                        </table>
                    </div>
                </div>
            </div>';
    
    return $html;
}

/**
 * Generate Detailed Hours Breakdown View
 * Shows daily breakdown with extra hours (green) and deficit hours (red)
 * 
 * @param mysqli $conn Database connection
 * @return string HTML output for the report
 */
function generateDetailedBreakdownView($conn) {
    $attendanceData = getWeeklyAttendanceByStaff($conn);
    
    $html = '<div class="card">
                <div class="card-header">
                    <h5 class="card-header-text">Detailed Hours Breakdown</h5>
                    <p class="text-muted">Daily breakdown showing hours worked, extra hours (green), and deficit hours (red)</p>
                </div>
                <div class="card-block">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Staff ID</th>
                                    <th>Employee Name</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours Worked</th>
                                    <th>Extra Hours</th>
                                    <th>Deficit Hours</th>
                                </tr>
                            </thead>
                            <tbody>';
    
    if (empty($attendanceData)) {
        $html .= '<tr><td colspan="8" class="text-center">No attendance records found</td></tr>';
    } else {
        foreach ($attendanceData as $staffData) {
            foreach ($staffData['weeks'] as $weekKey => $weekData) {
                // Add week header row
                $html .= '<tr class="table-info">
                            <td colspan="8"><strong>' . htmlspecialchars($weekData['week_label']) . '</strong></td>
                          </tr>';
                
                foreach ($weekData['days'] as $dayData) {
                    $extraHoursDisplay = $dayData['extra_hours'] > 0 
                        ? '<span class="badge badge-success">+' . formatHours($dayData['extra_hours']) . '</span>' 
                        : '<span class="text-muted">-</span>';
                    
                    $deficitHoursDisplay = $dayData['deficit_hours'] > 0 
                        ? '<span class="badge badge-danger">-' . formatHours($dayData['deficit_hours']) . '</span>' 
                        : '<span class="text-muted">-</span>';
                    
                    $timeIn = $dayData['time_in'] ? date('h:i A', strtotime($dayData['time_in'])) : '-';
                    $timeOut = $dayData['time_out'] ? date('h:i A', strtotime($dayData['time_out'])) : '-';
                    
                    $html .= '<tr>
                                <td>' . htmlspecialchars($staffData['staff_id']) . '</td>
                                <td>' . htmlspecialchars($staffData['full_name']) . '</td>
                                <td>' . date('M d, Y', strtotime($dayData['date'])) . '</td>
                                <td>' . $timeIn . '</td>
                                <td>' . $timeOut . '</td>
                                <td><strong>' . formatHours($dayData['hours_decimal'], true) . '</strong></td>
                                <td>' . $extraHoursDisplay . '</td>
                                <td>' . $deficitHoursDisplay . '</td>
                              </tr>';
                }
            }
        }
    }
    
    $html .= '      </tbody>
                        </table>
                    </div>
                </div>
            </div>';
    
    return $html;
}

/**
 * Generate the appropriate report based on view type
 * 
 * @param mysqli $conn Database connection
 * @param string $viewType View type: 'weekly', 'daysoff', or 'detailed'
 * @return string HTML output for the selected report
 */
function generateAttendanceReport($conn, $viewType = 'weekly') {
    switch ($viewType) {
        case 'daysoff':
            return generateDaysOffView($conn);
        case 'detailed':
            return generateDetailedBreakdownView($conn);
        case 'weekly':
        default:
            return generateWeeklyHoursView($conn);
    }
}
?>
