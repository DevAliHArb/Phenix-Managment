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
    // AC-No
    $acNo = isset($row[1]) ? $row[1] : null;

    // Handle Date (Excel date vs string)
    $date = null;
    if (!empty($row[4])) {
        $date = is_numeric($row[4]) 
            ? Date::excelToDateTimeObject($row[4])->format('Y-m-d')
            : date('Y-m-d', strtotime($row[4]));
    }

    // Handle Clock In
    $clockIn = null;
    if (!empty($row[5])) {
        $clockIn = is_numeric($row[5]) 
            ? Date::excelToDateTimeObject($row[5])->format('H:i:s')
            : date('H:i:s', strtotime($row[5]));
    }

    // Handle Clock Out
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
        $totalTime = $clockOutSeconds - $clockInSeconds;
    }

    return new EmployeeTime([
        'employee_id' => $acNo,
        'acc_number'  => $acNo,
        'date'        => $date,
        'clock_in'    => $clockIn,
        'clock_out'   => $clockOut,
        'total_time'  => $totalTime,
    ]);
}

}
