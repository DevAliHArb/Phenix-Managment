<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LateEarlyRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'time',
        'amount',
        'given_back',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
