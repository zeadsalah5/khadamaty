<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * زيارة صفحة واحدة (تحليلات الزوّار). بتتسجّل من middleware بعد الرد.
 * مفيش updated_at — بس created_at. بيتمسح تلقائياً بعد 120 يوم لو الـ
 * scheduler شغّال (model:prune) عشان الجدول مايكبرش من غير داعي.
 */
class Visit extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    protected $fillable = [
        'ip', 'path', 'method', 'referer', 'referer_host', 'user_agent',
        'browser', 'device', 'is_bot', 'bot_name', 'source_type', 'source_name',
    ];

    protected $casts = [
        'is_bot'     => 'boolean',
        'created_at' => 'datetime',
    ];

    /** الصفوف اللي بتتمسح في model:prune (أقدم من 120 يوم). */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(120));
    }

    /** تسميات عربية لنوع المصدر (للعرض في الداشبورد). */
    public const SOURCE_LABELS = [
        'search'   => 'محرك بحث',
        'ai'       => 'أداة AI',
        'ai_bot'   => 'زاحف AI',
        'social'   => 'سوشيال ميديا',
        'referral' => 'موقع آخر (منشن)',
        'direct'   => 'مباشر',
        'internal' => 'تنقّل داخلي',
    ];

    public function sourceLabel(): string
    {
        return self::SOURCE_LABELS[$this->source_type] ?? $this->source_type;
    }
}
