<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Post;
use App\Models\Service;
use App\Support\Site;
use Illuminate\Console\Command;

/**
 * بوابة إطلاق (preflight): بتفحص إعدادات الإنتاج قبل ما الموقع يروح live.
 * بتفشل (exit 1) وتطبع قايمة المشاكل لو APP_ENV=production وفيه أي مشكلة من دول:
 *   - APP_DEBUG=true
 *   - canonical_host فاضي/placeholder
 *   - رقم الهاتف أو الواتساب فاضي/placeholder
 *   - favicon أو og-image ناقصين
 *   - TRUSTED_PROXIES='*'
 *   - أي صفحة منشورة فيها TODO
 * بره الإنتاج: بتطبع التحذيرات بس وترجّع نجاح (أداة فحص مش بوابة).
 */
class SitePreflight extends Command
{
    protected $signature = 'site:preflight';

    protected $description = 'فحص ما قبل الإطلاق: بيفشل في production لو فيه إعداد ناقص أو صفحة منشورة فيها TODO';

    public function handle(): int
    {
        $issues = $this->collectIssues();

        if (empty($issues)) {
            $this->info('✅ preflight: مفيش مشاكل. الموقع جاهز للإطلاق.');

            return self::SUCCESS;
        }

        $isProd = app()->isProduction();

        $this->{$isProd ? 'error' : 'warn'}(
            ($isProd ? '⛔' : '⚠️') . ' preflight: لقينا ' . count($issues) . ' مشكلة:'
        );
        foreach ($issues as $issue) {
            $this->line('  • ' . $issue);
        }

        if ($isProd) {
            $this->error('الإطلاق متوقف — صلّح المشاكل دي الأول.');

            return self::FAILURE;
        }

        $this->warn('(تحذير بس — مش production. في الإنتاج ده كان هيفشل.)');

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    protected function collectIssues(): array
    {
        $issues = [];

        if (config('app.debug') === true) {
            $issues[] = 'APP_DEBUG=true (لازم false في الإنتاج).';
        }

        $host = trim((string) config('site.canonical_host'));
        if ($host === '' || str_contains($host, 'example')) {
            $issues[] = 'canonical_host فاضي أو placeholder (حطّ الدومين الحقيقي في .env).';
        }

        if (! Site::hasPhone()) {
            $issues[] = 'رقم الهاتف فاضي أو placeholder (حطّه من الداشبورد).';
        }

        if (! Site::hasWhatsapp()) {
            $issues[] = 'رقم الواتساب فاضي أو placeholder (حطّه من الداشبورد).';
        }

        if (! file_exists(public_path('favicon.ico'))) {
            $issues[] = 'ملف favicon ناقص (public/favicon.ico).';
        }

        $ogImage = ltrim((string) config('site.default_og_image', 'images/og-image.png'), '/');
        if (! file_exists(public_path($ogImage))) {
            $issues[] = 'صورة og ناقصة (public/' . $ogImage . ').';
        }

        if (trim((string) env('TRUSTED_PROXIES')) === '*') {
            $issues[] = "TRUSTED_PROXIES='*' ممنوع في الإنتاج.";
        }

        foreach ($this->publishedTodoPages() as $label) {
            $issues[] = 'صفحة منشورة فيها TODO: ' . $label;
        }

        return $issues;
    }

    /** الصفحات المنشورة اللي فيها TODO (كل الأنواع). @return array<int, string> */
    protected function publishedTodoPages(): array
    {
        $labels = [];

        $types = [Service::class, Brand::class, Area::class, Post::class, BrandServicePage::class, AreaServicePage::class];

        foreach ($types as $class) {
            foreach ($class::query()->where('is_published', true)->get() as $record) {
                if (method_exists($record, 'hasTodo') && $record->hasTodo()) {
                    $labels[] = $record->reviewTypeLabel() . ' #' . $record->getKey();
                }
            }
        }

        return $labels;
    }
}
