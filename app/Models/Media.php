<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['disk', 'path', 'alt', 'title', 'size', 'mime'];

    /** الرابط العام للصورة. */
    public function url(): string
    {
        return Storage::disk($this->disk ?: 'media')->url($this->path);
    }
}
