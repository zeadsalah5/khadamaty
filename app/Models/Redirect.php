<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = ['from', 'to', 'status', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'status'    => 'integer',
    ];
}
