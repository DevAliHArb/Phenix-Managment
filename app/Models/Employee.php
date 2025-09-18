<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'position_id',
        'birthdate',
        'start_date',
        'end_date',
        'employment_type',
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
