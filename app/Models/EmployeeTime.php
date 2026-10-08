<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTime extends Model
{
    protected $table = 'employee_times';

    protected $fillable = [
        'employee_id',
        'acc_number',
        'date',
        'clock_in',
        'clock_out',
        'total_time',
        'off_day',
        'reason',
        'vacation_type',
        'total_leave_diff',
        'total_break_diff',
        'flagged',
        'break_flag',
        
    ];

    public function isUnknownAbsence(): bool
    {
        return $this->reason === 'Unknown'
            && in_array($this->vacation_type, [null, 'Unknown'], true)
            && $this->clock_in === null && $this->clock_out === null;
    }

    public function attendanceResetAttributes(): array
    {
        return $this->isUnknownAbsence()
            ? ['off_day' => false, 'reason' => null, 'vacation_type' => null]
            : [];
    }

    public function machineAttendanceAttributes(): array
    {
        // A saved machine-event day is attended, unless an existing leave/off
        // label must be preserved. Unknown absence placeholders become attended.
        return [
            'vacation_type' => $this->isUnknownAbsence()
                ? 'Attended'
                : ($this->vacation_type ?: 'Attended'),
        ] + $this->attendanceResetAttributes();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
