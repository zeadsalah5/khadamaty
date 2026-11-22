<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Support\AgencyScrub;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * يطبّق إضافات صفحات الماركات من database/seeders/data/brand_additions.php:
 * - مابيكتبش warranty_agent_note خالص (اتشالت بطلب صاحب الموقع)؛ --clear-warranty بيمسح نصّنا القديم بس.
 * - faqs_append: الأسئلة اللي مش موجودة بنفس النص بتتضاف في الآخر.
 * مابيغيّرش حالة النشر، وبيفشل للماركة لو الإضافة هتطلّع TODO.
 */
class ApplyBrandAdditions extends Command
{
    protected $signature = 'khadamaty:brand-additions {slugs? : slugs مفصولة بفاصلة (الافتراضي: كل اللي في الملف)}
        {--clear-warranty : مسح فقرة «بعد الضمان» اللي اتضافت قبل كده (النص بتاعنا بس)}';

    protected $description = 'إضافة أسئلة شائعة لصفحات الماركات (و--clear-warranty لمسح فقرة الضمان القديمة)';

    public function handle(): int
    {
        $data = require database_path('seeders/data/brand_additions.php');
        $slugs = $this->argument('slugs')
            ? array_filter(array_map('trim', explode(',', (string) $this->argument('slugs'))))
            : array_keys($data);

        $failed = 0;
        foreach ($slugs as $slug) {
            $add = $data[$slug] ?? null;
            $brand = Brand::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $add || ! $brand) {
                $this->error("«{$slug}»: مفيش إضافات أو الماركة مش موجودة.");
                $failed++;

                continue;
            }

            $notes = [];
            // بطلب صاحب الموقع: فقرة «بعد الضمان» اتشالت. بنمسح النص اللي ضفناه بس،
            // ومابنلمسش أي نص كتبه صاحب الموقع من الداشبورد.
            if ($this->option('clear-warranty')
                && str_contains((string) $brand->warranty_agent_note, 'بعد ما ضمان الجهاز يخلص')) {
                $brand->warranty_agent_note = null;
                $notes[] = 'اتمسحت فقرة الضمان';
            }

            $faqs = (array) $brand->faqs;
            $known = array_map(fn ($f) => trim((string) ($f['q'] ?? '')), $faqs);
            $added = 0;
            foreach (AgencyScrub::faqs($add['faqs_append'] ?? []) as $faq) {
                if (! in_array(trim($faq['q']), $known, true)) {
                    $faqs[] = $faq;
                    $added++;
                }
            }
            $brand->faqs = $faqs;
            if ($added) {
                $notes[] = "{$added} سؤال";
            }

            if ($brand->hasTodo()) {
                $this->error("«{$slug}»: فيه TODO — ماتحفظش.");
                $failed++;

                continue;
            }

            $brand->save();
            $this->info("«{$slug}»: " . ($notes ? implode(' + ', $notes) : 'مفيش جديد'));
        }

        ContentCache::flush();

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
