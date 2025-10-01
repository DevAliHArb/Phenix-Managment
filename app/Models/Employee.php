<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'mid_name',
        'last_name',
        'address',
        'date_of_birth',
        'phone',
        'image',
        'position_id',
        'start_date',
        'end_date',
        'status',
        'working_hours_from',
        'working_hours_to',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'yearly_vacations_total',
        'yearly_vacations_used',
        'yearly_vacations_left',
        'sick_leave_used',
        'last_salary',
        'lookup_employee_type_id',
        'acc_number',
        'email',
        'city',
        'province',
        'building_name',
        'floor',
        'housing_type',
        'owner_name',
        'owner_mobile_number',
    ];

    protected $dates = ['birthdate', 'start_date', 'end_date', 'deleted_at'];

    public function position()
    {
        return $this->belongsTo(Lookup::class, 'position_id');
    }
    public function EmployeeType()
    {
        return $this->belongsTo(Lookup::class, 'lookup_employee_type_id');
    }

    public function positionImprovements()
    {
        return $this->hasMany(PositionImprovement::class);
    }

    public function attachments()
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    public function yearlyVacations()
    {
        return $this->hasMany(YearlyVacation::class);
    }

    public function sickLeaves()
    {
        return $this->hasMany(SickLeave::class);
    }

    public function lateEarlyRecords()
    {
        return $this->hasMany(LateEarlyRecord::class);
    }

    public function employeeTimes()
    {
        return $this->hasMany(EmployeeTime::class);
    }

    public function employeeVacations()
    {
        return $this->hasMany(EmployeeVacation::class);
    }
}
