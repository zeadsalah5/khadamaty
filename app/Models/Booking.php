<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'area',
        'service',
        'appliance',
        'devices_count',
        'preferred_time',
        'notes',
        'status',
        'admin_notes',
        'ip',
    ];
}
