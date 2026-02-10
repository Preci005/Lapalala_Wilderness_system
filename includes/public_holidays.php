<?php
/**
 * South African Public Holidays Configuration
 * Source: https://www.gov.za/about-sa/public-holidays
 * 
 * This file contains public holidays for 2026 and 2027
 */

/**
 * Get all South African public holidays for the given years
 * Uses static caching to avoid recreating the array on each call
 * 
 * @return array Array of public holidays in 'Y-m-d' format
 */
function getPublicHolidays() {
    static $holidays = null;
    
    if ($holidays === null) {
        // South African Public Holidays for 2026
        $holidays_2026 = [
            '2026-01-01', // New Year's Day
            '2026-03-21', // Human Rights Day
            '2026-04-03', // Good Friday
            '2026-04-06', // Family Day
            '2026-04-27', // Freedom Day
            '2026-05-01', // Workers' Day
            '2026-06-16', // Youth Day
            '2026-08-09', // National Women's Day (Sunday, observed Monday 10th)
            '2026-08-10', // National Women's Day (observed)
            '2026-09-24', // Heritage Day
            '2026-12-16', // Day of Reconciliation
            '2026-12-25', // Christmas Day
            '2026-12-26', // Day of Goodwill (Saturday, observed Monday 28th)
            '2026-12-28', // Day of Goodwill (observed)
        ];
        
        // South African Public Holidays for 2027
        $holidays_2027 = [
            '2027-01-01', // New Year's Day
            '2027-03-21', // Human Rights Day (Sunday, observed Monday 22nd)
            '2027-03-22', // Human Rights Day (observed)
            '2027-03-26', // Good Friday
            '2027-03-29', // Family Day
            '2027-04-27', // Freedom Day
            '2027-05-01', // Workers' Day (Saturday, observed Monday 3rd)
            '2027-05-03', // Workers' Day (observed)
            '2027-06-16', // Youth Day
            '2027-08-09', // National Women's Day
            '2027-09-24', // Heritage Day
            '2027-12-16', // Day of Reconciliation
            '2027-12-25', // Christmas Day (Saturday, observed Monday 27th)
            '2027-12-26', // Day of Goodwill (Sunday, observed Tuesday 28th)
            '2027-12-27', // Christmas Day (observed)
            '2027-12-28', // Day of Goodwill (observed)
        ];
        
        $holidays = array_merge($holidays_2026, $holidays_2027);
    }
    
    return $holidays;
}

/**
 * Check if a given date is a public holiday
 * 
 * @param string $date Date in 'Y-m-d' format
 * @return bool True if the date is a public holiday, false otherwise
 */
function isPublicHoliday($date) {
    $holidays = getPublicHolidays();
    return in_array($date, $holidays);
}

/**
 * Check if a given date is a weekend (Saturday or Sunday)
 * 
 * @param string $date Date in 'Y-m-d' format
 * @return bool True if the date is a weekend, false otherwise
 */
function isWeekend($date) {
    // Validate date format
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        error_log("Invalid date format provided to isWeekend(): $date");
        return false;
    }
    
    $dayOfWeek = date('N', $timestamp); // 1 (Monday) through 7 (Sunday)
    return ($dayOfWeek == 6 || $dayOfWeek == 7); // 6 = Saturday, 7 = Sunday
}

/**
 * Get the hour multiplier for a given date
 * - Public holidays: 2.0x
 * - Weekends: 1.5x
 * - Weekdays: 1.0x
 * 
 * @param string $date Date in 'Y-m-d' format
 * @return float The multiplier to apply to hours worked
 */
function getHourMultiplier($date) {
    // Validate date format
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        error_log("Invalid date format provided to getHourMultiplier(): $date");
        return 1.0; // Default to weekday multiplier for invalid dates
    }
    
    if (isPublicHoliday($date)) {
        return 2.0;
    } elseif (isWeekend($date)) {
        return 1.5;
    } else {
        return 1.0;
    }
}
?>
