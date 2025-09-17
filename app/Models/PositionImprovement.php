<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionImprovement extends Model
{
    protected $fillable = [
        'position_id',
        'employee_id',
        'start_date',
        'end_date',
    ];

    protected $dates = ['start_date', 'end_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function position()
    {
        return $this->belongsTo(Lookup::class);
    }
}
