<?php

namespace App\Filament\Resources\Concerns;

use Filament\Notifications\Notification;

/**
 * قاعدة نشر المنتجات — نسخة واعية بالعلاقات (صور/تفاصيل مستعمل).
 * الصور وتفاصيل المستعمل بتتحفظ كـ relationships بعد السجل الأساسي، علشان كده
 * بنطبّق القاعدة في afterCreate/afterSave (مش mutateFormDataBeforeSave):
 *   - بنعيد تحميل السجل بعلاقاته ونحسب الناقص (Product::missingContent()).
 *   - ناقص => مسوّدة إجبارياً (is_published=false, status=draft) + Notification بالناقص.
 *   - status=sold => مخفي من المتجر (is_published=false).
 *   - نشر مكتمل من غير تاريخ => تاريخ دلوقتي + status=published.
 *   - بنظبّط الصورة الرئيسية (واحدة بالظبط).
 */
trait GuardsProductCompleteness
{
    protected function afterCreate(): void
    {
        $this->enforceProductPublishRules();
    }

    protected function afterSave(): void
    {
        $this->enforceProductPublishRules();
    }

    protected function enforceProductPublishRules(): void
    {
        /** @var \App\Models\Product $record */
        $record = $this->record;
        $record->load(['images', 'usedDetails']);

        $this->normalizePrimaryImage($record);

        $complete = $record->hasCompleteContent();
        $status   = $record->status;
        $publish  = (bool) $record->is_published;

        if ($status === 'sold') {
            $publish = false;
        } elseif (! $complete) {
            $publish = false;
            $status  = 'draft';
        } elseif ($publish) {
            $status = 'published';
        }

        $publishedAt = $record->published_at;
        if ($publish && ! $publishedAt) {
            $publishedAt = now();
        }

        $record->forceFill([
            'is_published' => $publish,
            'status'       => $status,
            'published_at' => $publishedAt,
        ]);

        if ($record->isDirty(['is_published', 'status', 'published_at'])) {
            $record->save();
        }

        // لو المستخدم طلب نشر لكن المحتوى ناقص — نبّهه بالناقص.
        if (! $complete && ! empty($record->missingContent())) {
            Notification::make()
                ->warning()
                ->title('المنتج اتحفظ كمسوّدة')
                ->body('المحتوى ناقص للنشر: ' . implode('، ', $record->missingContent()))
                ->send();
        }
    }

    /** صورة رئيسية واحدة بالظبط: لو مفيش، أول صورة؛ لو أكتر من واحدة، نسيب الأولى بس. */
    protected function normalizePrimaryImage(\App\Models\Product $record): void
    {
        $images = $record->images;

        if ($images->isEmpty()) {
            return;
        }

        $primaries = $images->where('is_primary', true);

        if ($primaries->isEmpty()) {
            $images->first()->update(['is_primary' => true]);

            return;
        }

        if ($primaries->count() > 1) {
            $keep = $primaries->first()->id;
            $images->where('is_primary', true)
                ->reject(fn ($img) => $img->id === $keep)
                ->each(fn ($img) => $img->update(['is_primary' => false]));
        }
    }
}
