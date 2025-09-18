<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_improvement_id',
        'salary',
        'status',
    ];

    public function positionImprovement()
    {
        return $this->belongsTo(PositionImprovement::class);
    }
}
