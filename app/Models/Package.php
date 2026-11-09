<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'price', 'unit', 'featured', 'features', 'sort', 'is_active'];

    protected $casts = [
        'featured'  => 'boolean',
        'is_active' => 'boolean',
        'features'  => 'array',
    ];
}
