<?php

namespace App\Filament\Resources\Concerns;

use App\Support\ContentSimilarity;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * زرّار نشر صريح على صفحة Edit بيفرض سير المراجعة + تحذير التشابه.
 *
 * - بيظهر بس لما الصفحة لسه مش منشورة.
 * - لو مش جاهزة (canPublish=false) بيرفض ويوضّح الأسباب.
 * - بيحسب أعلى تشابه مع صفحة منشورة من نفس النوع؛ لو > 30% بيعرض تحذير صريح
 *   في مودال التأكيد ويطلب تأكيد، ويسجّل مين أكّد وإمتى والنسبة (similarity_ack_*).
 *
 * بيُستخدم مع GuardsReviewWorkflow في صفحات EditRecord.
 */
trait PublishesWithReview
{
    protected function publishAction(): Action
    {
        return Action::make('publishWithReview')
            ->label('نشر مع تأكيد التشابه')
            ->icon('heroicon-o-rocket-launch')
            ->color('success')
            ->visible(fn () => $this->record && ! $this->record->is_published)
            ->requiresConfirmation()
            ->modalHeading('تأكيد نشر الصفحة')
            ->modalDescription(function () {
                $record = $this->record;

                if (! $record->canPublish()) {
                    return '⛔ مش جاهزة للنشر: ' . implode(' • ', $record->publishBlockers());
                }

                $sim = app(ContentSimilarity::class)->maxAgainstPublished($record);

                if ($sim['pct'] > 30) {
                    $other = $sim['other'] ? ' («' . $this->recordTitle($sim['other']) . '»)' : '';

                    return '⚠️ تحذير: تشابه المحتوى مع صفحة منشورة وصل ' . $sim['pct'] . '%' . $other
                        . '. المحتوى المكرر ممكن يضرّ الـ SEO. متأكد إنك عايز تنشرها؟';
                }

                return 'التشابه مع الصفحات المنشورة ' . $sim['pct'] . '% (ضمن الحد). تأكيد النشر؟';
            })
            ->action(function () {
                $record = $this->record;

                if (! $record->canPublish()) {
                    Notification::make()->danger()
                        ->title('مش جاهزة للنشر')
                        ->body(implode(' • ', $record->publishBlockers()))
                        ->send();

                    return;
                }

                $sim = app(ContentSimilarity::class)->maxAgainstPublished($record);
                $high = $sim['pct'] > 30;

                $record->forceFill([
                    'is_published'      => true,
                    'published_at'      => $record->published_at ?? now(),
                    'similarity_pct'    => $sim['pct'],
                    'similarity_ack_by' => $high ? auth()->id() : null,
                    'similarity_ack_at' => $high ? now() : null,
                ])->save();

                Notification::make()->success()
                    ->title('اتنشرت الصفحة')
                    ->body($high ? 'اتسجّل تأكيد التشابه (' . $sim['pct'] . '%).' : 'التشابه ' . $sim['pct'] . '%.')
                    ->send();

                $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
            });
    }

    /** عنوان مختصر لسجل (للعرض في رسالة التشابه). */
    protected function recordTitle(Model $record): string
    {
        foreach (['h1', 'name_ar', 'title_ar'] as $field) {
            if (filled($record->{$field} ?? null)) {
                return (string) $record->{$field};
            }
        }

        return '#' . $record->getKey();
    }
}
