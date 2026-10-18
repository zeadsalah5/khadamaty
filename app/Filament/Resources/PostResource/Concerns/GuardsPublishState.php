<?php

namespace App\Filament\Resources\PostResource\Concerns;

/**
 * يضمن قاعدة النشر: المقال مايتنشرش غير لو محتواه كامل
 * (عنوان + ملخّص + متن + تصنيف)، ولو اتنشر من غير تاريخ بيتحط تاريخ دلوقتي.
 * بتتطبّق قبل الإنشاء وقبل الحفظ.
 */
trait GuardsPublishState
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardPublishState($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->guardPublishState($data);
    }

    protected function guardPublishState(array $data): array
    {
        $complete = filled($data['title_ar'] ?? null)
            && filled($data['excerpt'] ?? null)
            && filled($data['body'] ?? null)
            && filled($data['category_id'] ?? null);

        // محتوى ناقص => مسوّدة إجبارياً (noindex + بره السايت ماب).
        if (! $complete) {
            $data['is_published'] = false;
        }

        // نشر من غير تاريخ => تاريخ دلوقتي.
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
