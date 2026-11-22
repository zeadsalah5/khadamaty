<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\User;
use App\Support\AgencyScrub;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * ينشر محتوى خدمة على الموقع (للاستخدام على الإنتاج من غير SSH تفاعلي).
 *
 * - خدمة ليها «إضافات» في service_additions.php: بتتضاف الفقرات/الأسئلة الناقصة بس
 *   (النص الأصلي وتعديلات الداشبورد بتفضل)، وبيتحدّث عنوان/وصف الـ SEO.
 * - --replace: المحتوى كله بيتاخد من services.php (لخدمة جديدة أو مسودة لسه متعدّلتش).
 * بعدها الخدمة بتتنشر ومسجّل إنها متراجعة بواسطة الأدمن. لو لسه فيها TODO أو
 * محتوى ناقص الأمر بيفشل ومابينشرش. حقلا السعر/المدة اللي فيهم TODO بيتفضّوا
 * (القسم بيتخفى لحد ما صاحب الموقع يكتبهم) — الأمر مابيألّفش أسعار.
 */
class PublishService extends Command
{
    protected $signature = 'khadamaty:publish-service {slug} {--replace : استبدال المحتوى كله من بيانات الـ seed}';

    protected $description = 'تطبيق إضافات محتوى خدمة (أو استبداله) ونشرها';

    private const META = [
        'ac-relocation' => ['icon' => 'move', 'booking_service' => 'فك ونقل'],
    ];

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $service = Service::withoutGlobalScopes()->where('slug', $slug)->first();

        if ($this->option('replace')) {
            $data = require database_path('seeders/data/services.php');
            if (! isset($data[$slug])) {
                $this->error("الخدمة «{$slug}» مش موجودة في ملف البيانات.");

                return self::FAILURE;
            }

            $service ??= tap(new Service, fn (Service $s) => $s->slug = $slug);
            $service->fill($this->scrub($data[$slug]) + (self::META[$slug] ?? []));
        }

        if (! $service) {
            $this->error("الخدمة «{$slug}» مش موجودة.");

            return self::FAILURE;
        }

        $additions = (require database_path('seeders/data/service_additions.php'))[$slug] ?? [];
        $this->applyAdditions($service, $this->scrub($additions));

        // TODO في الحقول الاختيارية = تذكير قديم؛ بيتفضّى بدل ما يفضل متخزّن.
        foreach (['price_note', 'duration_note'] as $field) {
            if (filled($service->{$field}) && public_note($service->{$field}) === null) {
                $service->{$field} = null;
            }
        }

        $service->content_status = Service::STATUS_READY;
        $service->reviewed_by ??= User::where('role', 'admin')->orderBy('id')->value('id');
        $service->reviewed_at ??= now();

        if (! $service->canPublish()) {
            $this->error('ماينفعش تتنشر: ' . implode('، ', $service->publishBlockers()));

            return self::FAILURE;
        }

        $service->forceFill([
            'is_published' => true,
            'published_at' => $service->published_at ?? now(),
        ])->save();

        ContentCache::flush();

        $this->info("اتنشرت الخدمة «{$service->name_ar}» (/services/{$slug}).");

        return self::SUCCESS;
    }

    private function applyAdditions(Service $service, array $additions): void
    {
        $body = (string) $service->body;
        // فقرة افتتاحية (للكلمة المفتاحية في أول 100 كلمة) — بتتضاف مرة واحدة بس.
        if (filled($additions['body_prepend'] ?? null) && ! str_contains($body, trim($additions['body_prepend']))) {
            $body = trim($additions['body_prepend']) . "\n\n" . ltrim($body);
        }
        foreach ($additions['body_append'] ?? [] as $paragraph) {
            // العنوان اللي قبل «:» هو مفتاح عدم التكرار.
            $key = trim(explode(':', $paragraph, 2)[0]);
            if (! str_contains($body, $key)) {
                $body = rtrim($body) . "\n\n" . $paragraph;
            }
        }
        $service->body = $body;

        $faqs = (array) $service->faqs;
        $known = array_map(fn ($f) => trim((string) ($f['q'] ?? '')), $faqs);
        foreach ($additions['faqs_append'] ?? [] as $faq) {
            if (! in_array(trim($faq['q']), $known, true)) {
                $faqs[] = $faq;
            }
        }
        $service->faqs = $faqs;

        // عوامل التكلفة: بتتكتب بس لو الخدمة مالهاش (تعديلات الداشبورد بتفضل).
        if (empty($service->cost_factors) && ! empty($additions['cost_factors'])) {
            $service->cost_factors = $additions['cost_factors'];
        }

        foreach (['seo_title', 'seo_description', 'excerpt', 'price_note'] as $field) {
            if (isset($additions[$field])) {
                $service->{$field} = $additions[$field];
            }
        }
    }

    private function scrub(array $fields): array
    {
        foreach (['excerpt', 'body', 'body_prepend', 'seo_description'] as $key) {
            if (isset($fields[$key])) {
                $fields[$key] = AgencyScrub::text($fields[$key]);
            }
        }
        foreach (['faqs', 'faqs_append'] as $key) {
            if (isset($fields[$key])) {
                $fields[$key] = AgencyScrub::faqs($fields[$key]);
            }
        }
        if (isset($fields['body_append'])) {
            $fields['body_append'] = array_map([AgencyScrub::class, 'text'], $fields['body_append']);
        }

        return $fields;
    }
}
