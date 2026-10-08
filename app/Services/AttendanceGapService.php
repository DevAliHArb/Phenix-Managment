<?php

namespace App\Services;

use App\Models\AttendanceCalculationHistory;
use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\EmployeeVacation;
use App\Models\MachineRecord;
use App\Models\SickLeave;
use App\Models\VacationDate;
use App\Models\YearlyVacation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceGapService
{
    /**
     * Fill only between this active employee's own attendance rows, including
     * previously saved rows and rows just calculated/imported. Since filling
     * stays inside those bounds, generated rows cannot expand the range.
     */
    public function fillBetweenAttendance(Employee $employee): int
    {
        return DB::transaction(function () use ($employee) {
            $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            if ($employee->status !== 'active') {
                return 0;
            }

            $range = EmployeeTime::where('employee_id', $employee->id)
                ->selectRaw('MIN(date) as first_date, MAX(date) as last_date')->first();

            if (!$range->first_date || !$range->last_date) {
                return 0;
            }

            $from = Carbon::parse($range->first_date)->startOfDay();
            $to = Carbon::parse($range->last_date)->startOfDay();
            if ($employee->start_date) {
                $from = $from->max(Carbon::parse($employee->start_date)->startOfDay());
            }
            if ($employee->end_date) {
                $to = $to->min(Carbon::parse($employee->end_date)->startOfDay());
            }
            if ($from->gt($to)) {
                return 0;
            }

            // Unresolved punches exclude their own dates, not unrelated gaps.
            $punchDates = MachineRecord::where('emp_id', $employee->acc_number)
                ->whereBetween('timestamp', [$from->toDateTimeString(), $to->copy()->endOfDay()->toDateTimeString()])
                ->pluck('timestamp')->map(fn ($timestamp) => Carbon::parse($timestamp)->toDateString())
                ->unique()->values()->all();

            // Always recheck the combined range: an older import or newly resolved
            // attendance can reveal gaps before the previous history cursor.
            $created = $this->fillMissingDays(
                $employee, $from, $to,
                includeNonWorkingDays: true,
                excludedDates: $punchDates
            );
            AttendanceCalculationHistory::updateOrCreate(
                ['employee_id' => $employee->id],
                ['last_processed_date' => $to->toDateString()]
            );

            return $created;
        });
    }

    // Load once per employee/range, rather than querying every missing date.
    public function calendar(Employee $employee, $fromDate, $toDate): array
    {
        $range = [Carbon::parse($fromDate)->toDateString(), Carbon::parse($toDate)->toDateString()];

        return [
            'holidays' => VacationDate::whereBetween('date', $range)->get()->keyBy('date'),
            'vacations' => EmployeeVacation::where('employee_id', $employee->id)
                ->whereBetween('date', $range)->get()->keyBy('date'),
            'sick_leaves' => SickLeave::where('employee_id', $employee->id)
                ->whereBetween('date', $range)->get()->keyBy('date'),
            'yearly_vacations' => YearlyVacation::where('employee_id', $employee->id)
                ->whereBetween('date', $range)->get()->keyBy('date'),
        ];
    }

    public function dayDetails(Employee $employee, $date, array $calendar): array
    {
        $date = Carbon::parse($date);
        $key = $date->toDateString();
        $isWorking = $this->isWorkingDay($employee, $date);

        $details = [
            'off_day' => !$isWorking,
            'reason' => $isWorking ? null : 'Weekend',
            'vacation_type' => $isWorking ? null : 'Off',
        ];

        if ($holiday = $calendar['holidays']->get($key)) {
            return ['off_day' => true, 'reason' => $holiday->name ?: 'Holiday', 'vacation_type' => 'Holiday'];
        }

        if ($vacation = $calendar['vacations']->get($key)) {
            $type = match ((int) $vacation->lookup_type_id) {
                31 => 'Vacation',
                32 => 'Sick Leave',
                33 => 'Holiday',
                34 => 'Unpaid',
                35 => 'Half day vacation',
                default => 'Attended',
            };

            return [
                'off_day' => $type !== 'Half day vacation',
                'reason' => $vacation->reason ?: 'Employee Vacation',
                'vacation_type' => $type,
            ];
        }

        if ($leave = $calendar['sick_leaves']->get($key)) {
            return ['off_day' => true, 'reason' => $leave->reason ?: 'Sick Leave', 'vacation_type' => 'Sick Leave'];
        }

        if ($leave = $calendar['yearly_vacations']->get($key)) {
            return ['off_day' => true, 'reason' => $leave->reason ?: 'Vacation', 'vacation_type' => 'Vacation'];
        }

        return $details;
    }

    /**
     * All callers use the employee's saved weekday flags. Included non-working
     * days retain Off/Weekend labels unless a known holiday or leave applies.
     */
    public function fillMissingDays(
        Employee $employee,
        $fromDate,
        $toDate,
        bool $includeNonWorkingDays = false,
        bool $onlyKnownDays = false,
        array $excludedDates = []
    ): int {
        if ($employee->status !== 'active') {
            return 0;
        }

        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->startOfDay();

        if ($employee->start_date) {
            $from = $from->max(Carbon::parse($employee->start_date)->startOfDay());
        }
        if ($employee->end_date) {
            $to = $to->min(Carbon::parse($employee->end_date)->startOfDay());
        }
        if ($from->gt($to)) {
            return 0;
        }

        $calendar = $this->calendar($employee, $from, $to);
        $existingDates = EmployeeTime::where('employee_id', $employee->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->pluck('date')->all();
        $skipDates = array_fill_keys(array_merge($existingDates, $excludedDates), true);
        $created = 0;

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $key = $date->toDateString();
            if (isset($skipDates[$key])) {
                continue;
            }

            $isNonWorkingDay = !$this->isWorkingDay($employee, $date);
            if (!$includeNonWorkingDays && $isNonWorkingDay) {
                continue;
            }

            $details = $this->dayDetails($employee, $date, $calendar);
            if ($onlyKnownDays && $details['vacation_type'] === null) {
                continue;
            }

            if ($details['vacation_type'] === null) {
                $details['off_day'] = true;
                $details['reason'] = 'Unknown';
            }

            $record = EmployeeTime::firstOrCreate(
                ['employee_id' => $employee->id, 'date' => $key],
                $details + [
                    'acc_number' => $employee->acc_number,
                    'clock_in' => null,
                    'clock_out' => null,
                    'total_time' => null,
                ]
            );
            $created += (int) $record->wasRecentlyCreated;
        }

        return $created;
    }

    private function isWorkingDay(Employee $employee, Carbon $date): bool
    {
        return (bool) $employee->{strtolower($date->format('l'))};
    }
}
