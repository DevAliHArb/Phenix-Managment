<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCalculationHistory extends Model
{
    use HasFactory;

    protected $table = 'attendance_calculation_history';
    protected $fillable = [
        'employee_id' ,
        'last_processed_date'
    ];

    protected $casts = [
        'last_processed_date' => 'date',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
