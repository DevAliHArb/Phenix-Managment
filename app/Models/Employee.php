<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'date_of_birth',
        'phone',
        'image',
        'current_position_id',
        'start_date',
        'end_date',
        'status',
        'working_hours_from',
        'working_hours_to',
        'working_days',
        'yearly_vacations_total',
        'yearly_vacations_used',
        'yearly_vacations_left',
        'sick_leave_used',
        'last_salary',
    ];

    protected $dates = ['birthdate', 'start_date', 'end_date', 'deleted_at'];

    public function position()
    {
        return $this->belongsTo(Lookup::class);
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
}
