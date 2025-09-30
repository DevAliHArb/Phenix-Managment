<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationDate extends Model
{
    use HasFactory;

    protected $table = 'vacation_dates';

    protected $fillable = [
        'date',
        'name',
    ];
}
