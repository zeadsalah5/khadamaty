<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Support\AgencyScrub;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * يطبّق إضافات صفحات الماركات من database/seeders/data/brand_additions.php:
 * - warranty_agent_note بيتكتب بس لو الحقل فاضي (تعديلات الداشبورد بتفضل).
 * - faqs_append: الأسئلة اللي مش موجودة بنفس النص بتتضاف في الآخر.
 * مابيغيّرش حالة النشر، وبيفشل للماركة لو الإضافة هتطلّع TODO.
 */
class ApplyBrandAdditions extends Command
{
    protected $signature = 'khadamaty:brand-additions {slugs? : slugs مفصولة بفاصلة (الافتراضي: كل اللي في الملف)}';

    protected $description = 'إضافة «بعد الضمان» وأسئلة شائعة لصفحات الماركات';

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
            if (blank($brand->warranty_agent_note) && filled($add['warranty_agent_note'] ?? null)) {
                $brand->warranty_agent_note = AgencyScrub::text($add['warranty_agent_note']);
                $notes[] = 'بعد الضمان';
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
