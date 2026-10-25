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
        'brand',
        'appliance',
        'devices_count',
        'preferred_time',
        'notes',
        'source_url',
        'status',
        'admin_notes',
        'ip',
    ];
}
