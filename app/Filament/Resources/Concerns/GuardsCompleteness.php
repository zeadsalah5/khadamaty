<?php

namespace App\Filament\Resources\Concerns;

/**
 * قاعدة النشر المعمّمة لموديلات المحتوى (خدمات/مناطق/ماركات...):
 * - العنصر مايتنشرش غير لو محتواه كامل (Model::hasCompleteContent()).
 * - النشر من غير تاريخ => تاريخ دلوقتي.
 * الناقص بيفضل مسوّدة (noindex + 404 + بره السايت ماب) بدل ما يظهر ناقص.
 * بتتطبّق في صفحات Create و Edit (شوف الـ Pages).
 */
trait GuardsCompleteness
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardCompleteness($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->guardCompleteness($data);
    }

    protected function guardCompleteness(array $data): array
    {
        $modelClass = static::getResource()::getModel();

        /** @var \Illuminate\Database\Eloquent\Model $temp */
        $temp = new $modelClass();
        $temp->forceFill($data);

        $complete = method_exists($temp, 'hasCompleteContent')
            ? $temp->hasCompleteContent()
            : true;

        // محتوى ناقص => مسوّدة إجبارياً.
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
