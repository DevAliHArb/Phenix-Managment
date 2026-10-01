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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
