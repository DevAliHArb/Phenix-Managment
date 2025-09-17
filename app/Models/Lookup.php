<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    protected $table = 'lookup';


    
    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function positionImprovements()
    {
        return $this->hasMany(PositionImprovement::class);
    }
}
