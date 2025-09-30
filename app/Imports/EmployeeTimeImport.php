<?php

namespace App\Imports;

use App\Models\EmployeeTime;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeTimeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
{
    // Skip header row
    if ($row[0] === 'Emp No.' || $row[0] === null) {
        return null;
    }

    // Emp No. and AC-No. are in columns 0 and 1
    $acNo = isset($row[1]) ? $row[1] : null;

    // Handle Date (Excel date vs string) from column 4
    $date = null;
    if (!empty($row[4])) {
        if (is_numeric($row[4])) {
            $date = Date::excelToDateTimeObject($row[4])->format('Y-m-d');
        } else {
            $parsed = \DateTime::createFromFormat('d/m/Y', $row[4]);
            if ($parsed && $parsed->format('d/m/Y') === $row[4]) {
                $date = $parsed->format('Y-m-d');
            } else {
                // fallback only if d/m/Y is not valid
                $date = null;
            }
        }
    }

    // Handle Clock In from column 5
    $clockIn = null;
    if (!empty($row[5])) {
        $clockIn = is_numeric($row[5]) 
            ? Date::excelToDateTimeObject($row[5])->format('H:i:s')
            : date('H:i:s', strtotime($row[5]));
    }

    // Handle Clock Out from column 6
    $clockOut = null;
    if (!empty($row[6])) {
        $clockOut = is_numeric($row[6]) 
            ? Date::excelToDateTimeObject($row[6])->format('H:i:s')
            : date('H:i:s', strtotime($row[6]));
    }

    // Calculate total time in seconds
    $totalTime = null;
    if ($clockIn && $clockOut) {
        $clockInSeconds = strtotime($clockIn);
        $clockOutSeconds = strtotime($clockOut);
        if ($clockOutSeconds !== false && $clockInSeconds !== false) {
            $diffSeconds = $clockOutSeconds - $clockInSeconds;
            $hours = floor($diffSeconds / 3600);
            $minutes = floor(($diffSeconds % 3600) / 60);
            $seconds = $diffSeconds % 60;
            $totalTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
    }

    // Detect if the date is Saturday or Sunday, or in SickLeave/YearlyVacation
    $offDay = false;
    $reason = null;
    $excelDateValue = $row[4] ?? null;
    $checkDate = null;
    if (!empty($excelDateValue)) {
        if (is_numeric($excelDateValue)) {
            $carbonDate = Date::excelToDateTimeObject($excelDateValue);
            $checkDate = $carbonDate->format('Y-m-d');
        } else {
            $carbonDate = new \DateTime($excelDateValue);
            $checkDate = $carbonDate->format('Y-m-d');
        }
        $dayOfWeek = $carbonDate->format('N'); // 6 = Saturday, 7 = Sunday
        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
            $offDay = true;
            $reason = 'Weekend';
        }
    }

    // Check SickLeave and YearlyVacation for this employee and date (after $employeeId is set)


    $employeeId = null;
    if ($acNo) {
        // Skip if date already exists for this acc_number
        if ($date && EmployeeTime::where('acc_number', $acNo)->where('date', $date)->exists()) {
            return null;
        }
        $employee = \App\Models\Employee::where('acc_number', $acNo)->first();
        if ($employee) {
            $employeeId = $employee->id;
        }
    }
    // Check VacationDate table for this date (takes precedence)
    if ($checkDate) {
        $vacationDate = \App\Models\VacationDate::where('date', $checkDate)->first();
        if ($vacationDate) {
            $offDay = true;
            $reason = $vacationDate->name ?? 'vacationdate';
        } else if ($employeeId) {
            // Check SickLeave and YearlyVacation for this employee and date
            $sickLeave = \App\Models\SickLeave::where('employee_id', $employeeId)
                ->where('date', $checkDate)
                ->first();
            if ($sickLeave) {
                $offDay = true;
                $reason = 'Sick Leave';
            } else {
                $vacation = \App\Models\YearlyVacation::where('employee_id', $employeeId)
                    ->where('date', $checkDate)
                    ->first();
                if ($vacation) {
                    $offDay = true;
                    $reason = 'Vacation';
                }
            }
        }
    }
    return new EmployeeTime([
        'employee_id' => $employeeId,
        'acc_number'  => $acNo,
        'date'        => $date,
        'clock_in'    => $clockIn,
        'clock_out'   => $clockOut,
        'total_time'  => $totalTime,
        'off_day'     => $offDay,
        'reason'      => $reason,
    ]);
}

}
