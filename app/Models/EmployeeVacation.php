<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeVacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reason',
        'type',
        'attachment',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function lookupType()
    {
        return $this->belongsTo(Lookup::class, 'lookup_type_id');
    }
}
