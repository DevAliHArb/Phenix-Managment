<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineRecord extends Model
{
use HasFactory;

    protected $table = 'machine_records';

    protected $fillable = [
        'machine_id',
        'emp_id', // Machine account number, matched against employees.acc_number.
        'login_via',
        'event_type_id',
        'timestamp',
        'sync_cycle',
        'calculated',
        'employee_time_id'
    ];

   
    public function loginVia()
    {
        return $this->belongsTo(Lookup::class, 'login_via');
    }

    public function eventType()
    {
        return $this->belongsTo(Lookup::class, 'event_type_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'acc_number');
    }

    public function employeeTime()
    {
        return $this->belongsTo(EmployeeTime::class, 'employee_time_id');
    }

    }
