<?php

namespace App\Imports;

use App\Models\EmployeeTime;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use Illuminate\Support\Collection;

class EmployeeTimeImport implements ToCollection
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $progressKey;

    public function __construct($progressKey = null)
    {
        $this->progressKey = $progressKey;
    }

    public function collection(Collection $rows)
    {
        // Skip header row
        $rows = $rows->filter(function($row) {
            return isset($row[0]) && $row[0] !== 'Emp No.';
        });

        // Group rows by employee acc_number
        $grouped = $rows->groupBy(function($row) {
            return $row[1]; // AC-No.
        });

    $total = $rows->count();
    $processed = 0;


    // Load global work schedule once
    $workSchedule = \App\Models\WorkSchedule::first();

    foreach ($grouped as $acNo => $employeeRows) {
            // Sort by date
            $dates = $employeeRows->map(function($row) {
                if (!empty($row[4])) {
                    if (is_numeric($row[4])) {
                        return Date::excelToDateTimeObject($row[4])->format('Y-m-d');
                    } else {
                        $parsed = \DateTime::createFromFormat('d/m/Y', $row[4]);
                        if ($parsed && $parsed->format('d/m/Y') === $row[4]) {
                            return $parsed->format('Y-m-d');
                        }
                    }
                }
                return null;
            })->filter()->sort()->values();

            if ($dates->count() === 0) continue;

            $start = $dates->first();
            $end = $dates->last();
            $period = new \DatePeriod(
                new \DateTime($start),
                new \DateInterval('P1D'),
                (new \DateTime($end))->modify('+1 day')
            );

            $dateSet = $dates->flip();

            // Add missing dates
            foreach ($period as $dateObj) {
                $dateStr = $dateObj->format('Y-m-d');
                if (!$dateSet->has($dateStr)) {
                    $employee = \App\Models\Employee::where('acc_number', $acNo)->first();
                    $employeeId = $employee ? $employee->id : null;
                    // Skip if already exists
                    if ($employeeId && EmployeeTime::where('employee_id', $employeeId)->where('date', $dateStr)->exists()) {
                        continue;
                    }
                    // Default missing dates to off day with no vacation type
                    $offDay = true;
                    $reason = null;
                    $vacationType = null;
                    // Determine weekend/off-day based on employee working days (columns: monday..sunday).
                    // If employee record available, use its boolean flags; otherwise fallback to Sat/Sun.
                    $dayName = strtolower($dateObj->format('l')); // monday, tuesday, ... sunday
                    if ($employee) {
                        $isWorking = (bool) ($employee->{$dayName} ?? true);
                        if (!$isWorking) {
                            $offDay = true;
                            $reason = 'Weekend';
                            $vacationType = 'Off';
                        }
                    } else {
                        $dayOfWeek = $dateObj->format('N');
                        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
                            $offDay = true;
                            $reason = 'Weekend';
                            $vacationType = 'Off';
                        }
                    }
                    $vacationDate = \App\Models\VacationDate::where('date', $dateStr)->first();
                    if ($vacationDate) {
                        $offDay = true;
                        $reason = $vacationDate->name ?? 'vacationdate';
                        $vacationType = 'Holiday';
                    } else if ($employeeId) {
                        $employeeVacation = \App\Models\EmployeeVacation::where('employee_id', $employeeId)
                            ->where('date', $dateStr)
                            ->first();
                        if ($employeeVacation) {
                            $offDay = true;
                            $reason = $employeeVacation->reason ?? 'Employee Vacation';
                            $vacationType = $employeeVacation->lookup_type_id === 31 ? 'Vacation' : ($employeeVacation->lookup_type_id === 32 ? 'Sick Leave' : 'Attended');
                        }
                    }
                    EmployeeTime::create([
                        'employee_id' => $employeeId,
                        'acc_number'  => $acNo,
                        'date'        => $dateStr,
                        'clock_in'    => null,
                        'clock_out'   => null,
                        'total_time'  => null,
                        'off_day'     => $offDay,
                        'reason'      => $reason,
                        'vacation_type' => $vacationType,
                    ]);
                    // Progress update for each inserted row (missing date)
                    $processed++;
                    if ($this->progressKey && $total > 0) {
                        $percent = intval(($processed / $total) * 100);
                        \Cache::put($this->progressKey, $percent, 600); // 10 min expiry
                    }
                }
            }

            // Process present dates (imported rows)
            foreach ($employeeRows as $row) {
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
                }else if (!empty($row[7])) {
                    $clockIn = is_numeric($row[7])
                        ? Date::excelToDateTimeObject($row[7])->format('H:i:s')
                        : date('H:i:s', strtotime($row[7]));
                }else if (!empty($row[9])) {
                    $clockIn = is_numeric($row[9])
                        ? Date::excelToDateTimeObject($row[9])->format('H:i:s')
                        : date('H:i:s', strtotime($row[9]));
                }

                // Handle Clock Out from column 6
                $clockOut = null;
                if (!empty($row[10])) {
                    $clockOut = is_numeric($row[10])
                        ? Date::excelToDateTimeObject($row[10])->format('H:i:s')
                        : date('H:i:s', strtotime($row[10]));
                }else if (!empty($row[8])) {
                    $clockOut = is_numeric($row[8])
                        ? Date::excelToDateTimeObject($row[8])->format('H:i:s')
                        : date('H:i:s', strtotime($row[8]));
                }else if (!empty($row[6])) {
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
                $vacationType = 'Attended';
                $excelDateValue = $row[4] ?? null;
                $checkDate = null;
                if (!empty($excelDateValue)) {
                    if (is_numeric($excelDateValue)) {
                        $carbonDate = Date::excelToDateTimeObject($excelDateValue);
                        $checkDate = $carbonDate->format('Y-m-d');
                    } else {
                        $parsed = \DateTime::createFromFormat('d/m/Y', $excelDateValue);
                        if ($parsed && $parsed->format('d/m/Y') === $excelDateValue) {
                            $carbonDate = $parsed;
                            $checkDate = $carbonDate->format('Y-m-d');
                        } else {
                            try {
                                $carbonDate = new \DateTime($excelDateValue);
                                $checkDate = $carbonDate->format('Y-m-d');
                            } catch (\Exception $e) {
                                $carbonDate = null;
                                $checkDate = null;
                            }
                        }
                    }
                    if ($carbonDate) {
                        // Determine weekend based on employee working days if we can resolve the employee later.
                        // We'll check employee flags after resolving $employeeId below; for now keep $carbonDate available.
                        $dayNameForCheck = strtolower($carbonDate->format('l'));
                        // Temporarily store dayName to be used after resolving employee
                        $dayName = $dayNameForCheck;
                    }
                }

                $employeeId = null;
                if ($acNo) {
                    $employee = \App\Models\Employee::where('acc_number', $acNo)->first();
                    if ($employee) {
                        $employeeId = $employee->id;
                    }
                }
                // If we have the $carbonDate dayName from above, determine weekend/off-day using employee flags
                if (isset($dayName)) {
                    if (isset($employee) && $employee) {
                        $isWorkingDay = (bool) ($employee->{$dayName} ?? true);
                        if (!$isWorkingDay) {
                            $offDay = true;
                            $reason = 'Weekend';
                            $vacationType = 'Off';
                        }
                    } else {
                        // fallback to Sat/Sun
                        $dow = $carbonDate->format('N');
                        if ($dow == 6 || $dow == 7) {
                            $offDay = true;
                            $reason = 'Weekend';
                            $vacationType = 'Off';
                        }
                    }
                }

                // Determine late-arrival and early-leave thresholds from work schedule
                // WorkSchedule stores start_time/end_time as 'H:i:s' and late_arrival/early_leave as minutes
                $lateThreshold = null; // time string H:i:s
                $earlyThreshold = null; // time string H:i:s
                if ($workSchedule && $workSchedule->start_time) {
                    $baseStart = $workSchedule->start_time; // e.g. '09:00:00'
                    $lateMinutes = intval($workSchedule->late_arrival ?? 0);
                    try {
                        $dt = new \DateTime($baseStart);
                        if ($lateMinutes > 0) {
                            $dt->modify("+{$lateMinutes} minutes");
                        }
                        $lateThreshold = $dt->format('H:i:s');
                    } catch (\Exception $e) {
                        $lateThreshold = $baseStart;
                    }
                }
                if ($workSchedule && $workSchedule->end_time) {
                    $baseEnd = $workSchedule->end_time; // e.g. '17:00:00'
                    $earlyMinutes = intval($workSchedule->early_leave ?? 0);
                    try {
                        $dt2 = new \DateTime($baseEnd);
                        if ($earlyMinutes > 0) {
                            // early leave threshold is end_time minus earlyMinutes
                            $dt2->modify("-{$earlyMinutes} minutes");
                        }
                        $earlyThreshold = $dt2->format('H:i:s');
                    } catch (\Exception $e) {
                        $earlyThreshold = $baseEnd;
                    }
                }
                if ($checkDate) {
                    $vacationDate = \App\Models\VacationDate::where('date', $checkDate)->first();
                    if ($vacationDate) {
                        $offDay = true;
                        $reason = $vacationDate->name ?? 'vacationdate';
                        $vacationType = 'Holiday';
                    } else if ($employeeId) {
                        $employeeVacation = \App\Models\EmployeeVacation::where('employee_id', $employeeId)
                            ->where('date', $checkDate)
                            ->first();
                        if ($employeeVacation) {
                            $offDay = true;
                            $reason = $employeeVacation->reason ?? 'Employee Vacation';
                            $vacationType = $employeeVacation->lookup_type_id === 31 ? 'Vacation' : ($employeeVacation->lookup_type_id === 32 ? 'Sick Leave' : 'Attended');
                        }
                    }
                }

                // If both clock_in and clock_out are null, treat as potential day off.
                if ($clockIn === null && $clockOut === null) {
                    $vacationType = null;
                    $reason = null;
                }

                // Apply late arrival / early leave reason logic if not already off day or vacation
                $reasons = [];
                if (!$offDay && !$reason) {
                    // Late arrival: clockIn exists and is after lateThreshold
                    if ($clockIn && $lateThreshold) {
                        // compare times using DateTime
                        try {
                            $ci = new \DateTime($clockIn);
                            $lt = new \DateTime($lateThreshold);
                            if ($ci > $lt) {
                                $reasons[] = 'Late arrival';
                            }
                        } catch (\Exception $e) {
                            // ignore parse errors
                        }
                    }

                    // Early leave: clockOut exists and is before earlyThreshold
                    if ($clockOut && $earlyThreshold) {
                        try {
                            $co = new \DateTime($clockOut);
                            $et = new \DateTime($earlyThreshold);
                            if ($co < $et) {
                                $reasons[] = 'Early leave';
                            }
                        } catch (\Exception $e) {
                            // ignore parse errors
                        }
                    }

                    if (count($reasons) === 1) {
                        $reason = $reasons[0];
                    } else if (count($reasons) > 1) {
                        $reason = implode(' / ', $reasons); // e.g. 'Late arrival / Early leave'
                    }
                }

                // Skip if already exists
                if ($employeeId && $date && EmployeeTime::where('employee_id', $employeeId)->where('date', $date)->exists()) {
                    continue;
                }

                EmployeeTime::create([
                    'employee_id' => $employeeId,
                    'acc_number'  => $acNo,
                    'date'        => $date,
                    'clock_in'    => $clockIn,
                    'clock_out'   => $clockOut,
                    'total_time'  => $totalTime,
                    'off_day'     => $offDay,
                    'reason'      => $reason,
                    'vacation_type' => $vacationType,
                ]);
                // Progress update for each inserted row (present date)
                $processed++;
                if ($this->progressKey && $total > 0) {
                    $percent = intval(($processed / $total) * 100);
                    \Cache::put($this->progressKey, $percent, 600); // 10 min expiry
                }
            }
        }

        if ($this->progressKey) {
            \Cache::put($this->progressKey, 100, 600);
        }
    }

}
