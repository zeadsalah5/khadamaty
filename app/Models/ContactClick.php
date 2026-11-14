<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * ضغطة زر تواصل (اتصال / واتساب). created_at بس — بتتمسح بعد 120 يوم
 * في model:prune لو الـ scheduler شغّال.
 */
class ContactClick extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    public const TYPES = ['call', 'whatsapp'];

    protected $fillable = ['type', 'path', 'ip'];

    protected $casts = ['created_at' => 'datetime'];

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(120));
    }
}
