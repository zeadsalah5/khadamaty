<?php

namespace App\Filament\Widgets;

use App\Models\ContactClick;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

/**
 * عدّاد ضغطات أزرار التواصل: كام حد ضغط «اتصل» وكام حد ضغط «واتساب»
 * من الموقع (النهارده + آخر ٧ أيام). أدمن فقط.
 */
class ContactClicksOverview extends BaseWidget
{
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return (auth()->user()?->isAdmin() ?? false) && Schema::hasTable('contact_clicks');
    }

    protected function getStats(): array
    {
        return [
            $this->stat('call', 'اتصالات (ضغط «اتصل») النهارده', '📞', 'success'),
            $this->stat('whatsapp', 'رسايل واتساب (ضغط «واتساب») النهارده', '💬', 'primary'),
        ];
    }

    private function stat(string $type, string $label, string $icon, string $color): Stat
    {
        $today = ContactClick::where('type', $type)->whereDate('created_at', today())->count();
        $week  = ContactClick::where('type', $type)->where('created_at', '>=', now()->subDays(7))->count();

        $spark = [];
        for ($i = 6; $i >= 0; $i--) {
            $spark[] = ContactClick::where('type', $type)
                ->whereDate('created_at', today()->subDays($i))->count();
        }

        return Stat::make($icon . ' ' . $label, $today)
            ->description('آخر ٧ أيام: ' . $week)
            ->descriptionIcon('heroicon-m-cursor-arrow-ripple')
            ->color($color)
            ->chart($spark);
    }
}
