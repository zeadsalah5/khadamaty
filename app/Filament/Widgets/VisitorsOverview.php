<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

/**
 * ملخّص تحليلات الزوّار: زيارات النهارده، زوّار مميزين، بشر/بوت،
 * ومصادر الأسبوع (بحث / AI / سوشيال / مواقع أخرى). أدمن فقط.
 */
class VisitorsOverview extends BaseWidget
{
    protected static ?int $sort = -2;

    public static function canView(): bool
    {
        return (auth()->user()?->isAdmin() ?? false) && Schema::hasTable('visits');
    }

    protected function getStats(): array
    {
        $today     = Visit::whereDate('created_at', today());
        $todayCount = (clone $today)->count();
        $humansToday = (clone $today)->where('is_bot', false)->count();
        $botsToday   = (clone $today)->where('is_bot', true)->count();
        $uniqueToday = (clone $today)->distinct('ip')->count('ip');

        $week = fn () => Visit::where('created_at', '>=', now()->subDays(7));
        $search   = (clone $week())->where('source_type', 'search')->count();
        $ai       = (clone $week())->where('source_type', 'ai')->count();
        $aiBot    = (clone $week())->where('source_type', 'ai_bot')->count();
        $social   = (clone $week())->where('source_type', 'social')->count();
        $referral = (clone $week())->where('source_type', 'referral')->count();

        // sparkline آخر ٧ أيام (زيارات البشر فقط).
        $spark = [];
        for ($i = 6; $i >= 0; $i--) {
            $spark[] = Visit::whereDate('created_at', today()->subDays($i))
                ->where('is_bot', false)->count();
        }

        return [
            Stat::make('زيارات النهارده', $todayCount)
                ->description("👤 بشر: {$humansToday}  ·  🤖 بوت: {$botsToday}")
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary')
                ->chart($spark),

            Stat::make('زوّار مميزين النهارده (IP)', $uniqueToday)
                ->description('عدد عناوين الـ IP المختلفة النهارده')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('من محركات البحث (٧ أيام)', $search)
                ->description('Google / Bing / غيرهم')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('info'),

            Stat::make('من أدوات AI (٧ أيام)', $ai)
                ->description("زوّار جايين من شات AI  ·  🤖 زواحف AI: {$aiBot}")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),

            Stat::make('من سوشيال ميديا (٧ أيام)', $social)
                ->description('Facebook / Instagram / غيرهم')
                ->descriptionIcon('heroicon-m-share')
                ->color('primary'),

            Stat::make('من مواقع أخرى — منشن (٧ أيام)', $referral)
                ->description('مواقع عملت لينك للموقع')
                ->descriptionIcon('heroicon-m-link')
                ->color('warning'),
        ];
    }
}
