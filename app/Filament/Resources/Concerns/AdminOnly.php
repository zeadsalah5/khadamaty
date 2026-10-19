<?php

namespace App\Filament\Resources\Concerns;

/**
 * يقصر الوصول للـ Resource على الأدمن فقط (مش المحرّر).
 * بيخفيه من القائمة ويمنع الدخول المباشر.
 */
trait AdminOnly
{
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
