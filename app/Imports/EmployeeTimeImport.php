<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\WorkSchedule;
use App\Services\AttendanceGapService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeTimeImport implements ToCollection
{
    protected $progressKey;

    public function __construct($progressKey = null)
    {
        $this->progressKey = $progressKey;
    }

    public function collection(Collection $rows)
    {
        $rows = $rows->filter(fn ($row) => isset($row[0]) && $row[0] !== 'Emp No.');
        $grouped = $rows->groupBy(fn ($row) => $row[1]);
        $total = $rows->count();
        $processed = 0;
        $workSchedule = WorkSchedule::first();
        $gaps = app(AttendanceGapService::class);

        foreach ($grouped as $acNo => $employeeRows) {
            $employee = Employee::where('acc_number', $acNo)->first();
            if (!$employee) {
                continue;
            }

            $dates = $employeeRows->map(fn ($row) => $this->parseDate($row[4] ?? null))
                ->filter()->unique()->sort()->values();
            if ($dates->isEmpty()) {
                continue;
            }

            $start = $dates->first();
            $end = $dates->last();
            $calendar = $gaps->calendar($employee, $start, $end);

            foreach ($employeeRows as $row) {
                $date = $this->parseDate($row[4] ?? null);
                if (!$date) {
                    continue;
                }
                if (($employee->start_date && Carbon::parse($date)->lt(Carbon::parse($employee->start_date)))
                    || ($employee->end_date && Carbon::parse($date)->gt(Carbon::parse($employee->end_date)))) {
                    continue;
                }

                [$clockIn, $clockOut, $totalTime] = $this->clockTimes($row);
                $details = $gaps->dayDetails($employee, $date, $calendar);

                // Preserve full-day leave/holiday handling, but never erase its reason.
                if (in_array($details['vacation_type'], ['Holiday', 'Vacation', 'Sick Leave', 'Unpaid', 'Attended'], true)) {
                    $clockIn = $clockOut = $totalTime = null;
                }

                if ($details['vacation_type'] === null) {
                    if ($clockIn === null && $clockOut === null) {
                        $details['off_day'] = true;
                        $details['reason'] = 'Unknown';
                    } else {
                        $details['vacation_type'] = 'Attended';
                    }
                }

                if (!$details['off_day'] && !$details['reason']) {
                    $details['reason'] = $this->lateEarlyReason($clockIn, $clockOut, $workSchedule);
                }

                $data = $details + [
                    'acc_number' => $employee->acc_number,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total_time' => $totalTime,
                ];
                $existing = EmployeeTime::where('employee_id', $employee->id)->where('date', $date)->first();

                if ($existing) {
                    if ($existing->isUnknownAbsence()) {
                        $existing->update($data);
                    } elseif ($existing->vacation_type === 'Half day vacation') {
                        $existing->update([
                            'clock_in' => $clockIn,
                            'clock_out' => $clockOut,
                            'total_time' => $totalTime,
                        ]);
                    }
                    // Keep other existing/manual attendance unchanged.
                    continue;
                }

                EmployeeTime::firstOrCreate(['employee_id' => $employee->id, 'date' => $date], $data);
                $processed++;
                $this->updateProgress($processed, $total);
            }

            // Use the same range/rules as machine calculation, after all imported
            // attendance is saved so old and new rows define one employee range.
            $processed += $gaps->fillBetweenAttendance($employee);
            $this->updateProgress($processed, $total);
        }

        if ($this->progressKey) {
            \Cache::put($this->progressKey, 100, 600);
        }
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        $parsed = \DateTime::createFromFormat('d/m/Y', $value);

        return $parsed && $parsed->format('d/m/Y') === $value ? $parsed->format('Y-m-d') : null;
    }

    private function clockTimes($row): array
    {
        $pairs = [];
        for ($i = 5; $i < count($row); $i += 2) {
            $in = $this->parseTime($row[$i] ?? null);
            $out = $this->parseTime($row[$i + 1] ?? null);
            if ($in || $out) {
                $pairs[] = [$in, $out];
            }
        }

        $clockIn = null;
        $lastOut = null;
        foreach ($pairs as [$in, $out]) {
            $clockIn = $clockIn ?: $in;
            $lastOut = $out ?: $lastOut;
        }
        if (!$clockIn || !$lastOut || strtotime($lastOut) <= strtotime($clockIn)) {
            return [$clockIn, null, null];
        }

        $gapSeconds = 0;
        for ($i = 0; $i < count($pairs) - 1; $i++) {
            if ($pairs[$i][1] && $pairs[$i + 1][0]) {
                $gapSeconds += max(0, strtotime($pairs[$i + 1][0]) - strtotime($pairs[$i][1]));
            }
        }
        $net = max(0, strtotime($lastOut) - strtotime($clockIn) - $gapSeconds);
        $total = sprintf('%02d:%02d:%02d', floor($net / 3600), floor(($net % 3600) / 60), $net % 60);

        // Retain the import's existing synthetic clock-out (clock-in + net time).
        return [$clockIn, date('H:i:s', strtotime($clockIn) + $net), $total];
    }

    private function parseTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return is_numeric($value)
            ? Date::excelToDateTimeObject($value)->format('H:i:s')
            : date('H:i:s', strtotime($value));
    }

    private function lateEarlyReason($clockIn, $clockOut, ?WorkSchedule $schedule): ?string
    {
        $reasons = [];
        if ($clockIn && $schedule?->start_time) {
            $threshold = Carbon::parse($schedule->start_time)->addMinutes(max(0, (int) $schedule->late_arrival));
            if (Carbon::parse($clockIn)->gt($threshold)) {
                $reasons[] = 'Late arrival';
            }
        }
        if ($clockOut && $schedule?->end_time) {
            $threshold = Carbon::parse($schedule->end_time)->subMinutes(max(0, (int) $schedule->early_leave));
            if (Carbon::parse($clockOut)->lt($threshold)) {
                $reasons[] = 'Early leave';
            }
        }

        return $reasons ? implode(' / ', $reasons) : null;
    }

    private function updateProgress(int $processed, int $total): void
    {
        if ($this->progressKey && $total > 0) {
            \Cache::put($this->progressKey, min(99, intval(($processed / $total) * 100)), 600);
        }
    }
}
