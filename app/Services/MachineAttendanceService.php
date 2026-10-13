<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class MachineAttendanceService
{
    public function __construct(private AttendanceGapService $gaps) {}

    public function allowedBreakSeconds(?WorkSchedule $schedule): int
    {
        // An explicit zero means no allowance; a missing setting defaults to one hour.
        return max(0, (int) ($schedule?->break_duration ?? 60)) * 60;
    }

    public function expectedWorkSeconds(Employee $employee, ?WorkSchedule $schedule): ?int
    {
        if (! $employee->working_hours_from || ! $employee->working_hours_to) {
            return null;
        }

        try {
            $start = Carbon::parse('2000-01-01 '.$employee->working_hours_from);
            $end = Carbon::parse('2000-01-01 '.$employee->working_hours_to);
            if ($end->lt($start)) {
                $end->addDay();
            }
            $seconds = $end->getTimestamp() - $start->getTimestamp() - $this->allowedBreakSeconds($schedule);

            return $seconds > 0 ? $seconds : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isHalfDayDuration(Employee $employee, ?int $workedSeconds, ?WorkSchedule $schedule): bool
    {
        $expected = $this->expectedWorkSeconds($employee, $schedule);

        return $workedSeconds !== null && $workedSeconds > 0 && $expected !== null
            && $workedSeconds >= $expected / 2 && $workedSeconds <= $expected / 2 + 1800;
    }

    /**
     * Worked seconds are the closed shift span minus clock-out/clock-in gaps,
     * before any break deduction. Half-days use that duration without breaks.
     */
    public function calculate(
        Employee $employee,
        string $date,
        ?EmployeeTime $existing,
        ?string $clockIn,
        ?string $clockOut,
        ?int $workedSeconds,
        int $recordedBreakSeconds,
        ?WorkSchedule $schedule
    ): array {
        $attendance = $existing ?? new EmployeeTime;
        $attributes = $attendance->machineAttendanceAttributes();
        $attributes += ['off_day' => (bool) $attendance->off_day, 'reason' => $attendance->reason];

        // Saved leave/off-day labels take precedence. Otherwise use the same
        // calendar and employee weekday flags as gap filling, even without a saved row.
        $hasSavedClassification = ! $attendance->isUnknownAbsence()
            && (($attendance->vacation_type !== null && $attendance->vacation_type !== 'Attended')
                || $attendance->off_day);
        if (! $hasSavedClassification) {
            $details = $this->gaps->dayDetails($employee, $date, $this->gaps->calendar($employee, $date, $date));
            if ($details['vacation_type'] !== null) {
                $attributes = $details;
            }
        }

        $complete = $clockIn !== null && $clockOut !== null && $workedSeconds !== null && $workedSeconds > 0;
        if ($complete && ! $attributes['off_day']
            && $attributes['vacation_type'] === 'Attended'
            && $this->isHalfDayDuration($employee, $workedSeconds, $schedule)) {
            $attributes['off_day'] = true;
            $attributes['vacation_type'] = 'Half day vacation';
        }

        $totalTime = null;
        $breakDiff = null;
        if ($complete) {
            $halfDay = strtolower($attributes['vacation_type'] ?? '') === 'half day vacation';
            $allowed = $this->allowedBreakSeconds($schedule);
            $breakDiff = $halfDay ? 0 : max(0, $recordedBreakSeconds - $allowed);
            $totalSeconds = $halfDay ? $workedSeconds : max(0, $workedSeconds - $allowed - $breakDiff);
            $totalTime = $this->duration($totalSeconds);
            $breakDiff = $this->duration($breakDiff);
        }

        return [
            'total_time' => $totalTime,
            'total_break_diff' => $breakDiff,
            'attendance_attributes' => $attributes,
        ];
    }

    private function duration(int $seconds): string
    {
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }
}
