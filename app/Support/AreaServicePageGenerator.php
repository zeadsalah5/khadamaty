<?php

namespace App\Support;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Service;

/**
 * مولّد صفحات (منطقة × خدمة) من قالب (K2) — بحد أقصى صارم.
 *
 * بياخد مناطق × خدمات ويولّد **مسودة** لكل تركيبة **غير موجودة** من قالب فيه محتوى
 * مؤقت معلّم TODO. **حد أقصى 5 تركيبات في المرة** (مفيش توليد بالجملة) — بيرمي
 * استثناء لو العدد أكبر. القالب (h1/المقدمة) بيتعدّل من الداشبورد (ManagePageTemplates).
 */
class AreaServicePageGenerator
{
    public const MAX_COMBINATIONS = 5;

    /**
     * @return array{created:int, skipped:int}
     *
     * @throws \InvalidArgumentException لو التركيبات أكتر من الحد الأقصى.
     */
    public function generate(array $areaIds, array $serviceIds): array
    {
        $combinations = count($areaIds) * count($serviceIds);

        if ($combinations > self::MAX_COMBINATIONS) {
            throw new \InvalidArgumentException(
                'الحد الأقصى ' . self::MAX_COMBINATIONS . ' تركيبات في المرة (اخترت ' . $combinations . ').'
            );
        }

        $areas = Area::query()->whereIn('id', $areaIds)->get()->keyBy('id');
        $services = Service::query()->whereIn('id', $serviceIds)->get()->keyBy('id');

        $created = 0;
        $skipped = 0;

        foreach ($areaIds as $areaId) {
            foreach ($serviceIds as $serviceId) {
                $area = $areas[$areaId] ?? null;
                $service = $services[$serviceId] ?? null;

                if (! $area || ! $service) {
                    $skipped++;

                    continue;
                }

                $exists = AreaServicePage::query()
                    ->where('area_id', $areaId)
                    ->where('service_id', $serviceId)
                    ->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                AreaServicePage::create($this->draftFrom($area, $service));
                $created++;
            }
        }

        return compact('created', 'skipped');
    }

    protected function draftFrom(Area $area, Service $service): array
    {
        $repl = [
            '{{area}}'    => $area->name_ar,
            '{{service}}' => $service->name_ar,
        ];

        $h1 = strtr((string) Settings::get('asp_tpl_h1', self::defaultH1()), $repl);
        $intro = strtr((string) Settings::get('asp_tpl_intro', self::defaultIntro()), $repl);

        return [
            'area_id'        => $area->id,
            'service_id'     => $service->id,
            'h1'             => $h1,
            'intro'          => $intro,
            'steps'          => [['title' => 'TODO: عنوان الخطوة', 'desc' => 'TODO: وصف الخطوة']],
            'faqs'           => [['q' => 'TODO: سؤال من عملاء ' . $area->name_ar, 'a' => 'TODO: الإجابة']],
            'price_note'     => 'TODO: حدّد السعر المبدئي',
            'duration_note'  => 'TODO: حدّد المدة التقريبية',
            'content_status' => AreaServicePage::STATUS_DRAFT,
            'is_published'   => false,
        ];
    }

    public static function defaultH1(): string
    {
        return '{{service}} تكييف في {{area}}';
    }

    public static function defaultIntro(): string
    {
        return "{{service}} لتكييفات {{area}} — فني بيوصلك، معاينة قبل الشغل، والدفع بعد الخدمة.\n\n"
            . 'TODO: صاحب الموقع/الفني يكتب مقدمة فريدة عن {{service}} في {{area}} (تجنّب تكرار نص الصفحات التانية).';
    }
}
