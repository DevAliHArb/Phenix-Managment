<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineSyncData extends Model
{
     protected $table = 'machine_sync_data';

    protected $fillable = [
        'last_machine_id',
        'last_attendance_date',
        'created_by',
        'updated_by',
        'added',
        'flagged',
        'ignored'
    ];
   
}
