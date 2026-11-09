<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'sort', 'show_on_home', 'is_active'];

    protected $casts = [
        'show_on_home' => 'boolean',
        'is_active'    => 'boolean',
    ];
}
