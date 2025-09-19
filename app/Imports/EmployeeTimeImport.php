<?php

namespace App\Imports;

use App\Models\EmployeeTime;
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeTimeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
            // Assuming the Excel columns are: AC-No, Date, Clock In, Clock Out
            // Convert times to seconds for calculation
            $clockIn = isset($row[2]) ? strtotime($row[2]) : null;
            $clockOut = isset($row[3]) ? strtotime($row[3]) : null;
            $totalTime = ($clockIn && $clockOut) ? ($clockOut - $clockIn) : null;

            return new EmployeeTime([
                'employee_id' => $row[0], // AC-No
                'acc_number' => $row[0],  // AC-No (if acc_number is same as employee_id)
                'date' => isset($row[1]) ? $row[1] : null, // Date
                'clock_in' => isset($row[2]) ? $row[2] : null, // Clock In
                'clock_out' => isset($row[3]) ? $row[3] : null, // Clock Out
                'total_time' => $totalTime,
            ]);
    }
}
