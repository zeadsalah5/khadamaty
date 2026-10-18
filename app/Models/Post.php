<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'title_ar', 'excerpt', 'body', 'cover', 'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
