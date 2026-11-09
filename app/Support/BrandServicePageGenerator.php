<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;

/**
 * مولّد صفحات (ماركة × خدمة) من قالب (Section 1).
 *
 * بياخد ماركات × خدمات ويولّد **مسودة** لكل تركيبة **غير موجودة** من قالب فيه
 * محتوى مؤقت معلّم TODO (من غير تكرار لتركيبة موجودة). القالب (h1/المقدمة) بيتعدّل
 * من الداشبورد (ManagePageTemplates) — والباقي scaffolding فيه TODO عشان الصفحة
 * تفضل مسودة لحد ما الفني يملاها ويراجعها.
 */
class BrandServicePageGenerator
{
    /** @return array{created:int, skipped:int} */
    public function generate(array $brandIds, array $serviceIds): array
    {
        $brands = Brand::query()->whereIn('id', $brandIds)->get()->keyBy('id');
        $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');

        $created = 0;
        $skipped = 0;

        foreach ($brandIds as $brandId) {
            foreach ($serviceIds as $serviceId) {
                $brand = $brands[$brandId] ?? null;
                $service = $services[$serviceId] ?? null;

                if (! $brand || ! $service) {
                    $skipped++;

                    continue;
                }

                $exists = BrandServicePage::query()
                    ->where('brand_id', $brandId)
                    ->where('service_id', $serviceId)
                    ->exists();

                if ($exists) {
                    $skipped++; // تركيبة موجودة => مانكرّرش

                    continue;
                }

                BrandServicePage::create($this->draftFrom($brand, $service));
                $created++;
            }
        }

        return compact('created', 'skipped');
    }

    /** مسودة من القالب لماركة/خدمة محدّدة. */
    protected function draftFrom(Brand $brand, Service $service): array
    {
        $repl = [
            '{{brand}}'   => $brand->name_ar,
            '{{service}}' => $service->name_ar,
        ];

        $h1 = strtr((string) Settings::get('bsp_tpl_h1', self::defaultH1()), $repl);
        $intro = strtr((string) Settings::get('bsp_tpl_intro', self::defaultIntro()), $repl);

        return [
            'brand_id'       => $brand->id,
            'service_id'     => $service->id,
            'h1'             => $h1,
            'intro'          => $intro,
            // scaffolding بمحتوى مؤقت (TODO) => الصفحة تفضل مسودة لحد المراجعة.
            'common_faults'  => ['TODO: اكتب الأعطال الشائعة لـ ' . $service->name_ar . ' ' . $brand->name_ar],
            'symptoms'       => ['TODO: اكتب الأعراض اللي بتستدعي الخدمة'],
            'steps'          => [['title' => 'TODO: عنوان الخطوة', 'desc' => 'TODO: وصف الخطوة']],
            'faqs'           => [['q' => 'TODO: سؤال شائع', 'a' => 'TODO: الإجابة']],
            'price_note'     => 'TODO: حدّد السعر المبدئي',
            'duration_note'  => 'TODO: حدّد المدة التقريبية',
            'content_status' => BrandServicePage::STATUS_DRAFT,
            'is_published'   => false,
        ];
    }

    public static function defaultH1(): string
    {
        return '{{service}} {{brand}}';
    }

    public static function defaultIntro(): string
    {
        return "صفحة {{service}} لأجهزة {{brand}}. بنصلّح كل الماركات وإحنا مش توكيل لماركة بذاتها.\n\n"
            . 'TODO: صاحب الموقع/الفني يكتب مقدمة فريدة عن {{service}} لـ {{brand}} (تجنّب تكرار نص الصفحات التانية).';
    }
}
