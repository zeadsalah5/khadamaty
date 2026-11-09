<?php

namespace App\Filament\Resources\Concerns;

use App\Support\ContentSimilarity;
use Filament\Notifications\Notification;

/**
 * قاعدة النشر الموحّدة لكل موديلات المحتوى (خدمات/ماركات/مناطق/مقالات/ماركة×خدمة).
 *
 * is_published=true مابيتحفظش غير لو canPublish():
 *   جاهزة (content_status=ready) + متراجعة (reviewed_by) + صفر TODO + المحتوى مكتمل.
 * غير كده بيتفرض مسوّدة (noindex + 404 للزوار + بره السايت ماب).
 *
 * + تحذير التشابه: لو صفحة لسه مش منشورة وبتتنشر وتشابهها مع صفحة منشورة من نفس
 *   النوع > 30% ومفيش تأكيد (similarity_ack) => بيتمنع النشر ويتوجّه المستخدم
 *   لاستخدام «نشر مع تأكيد التشابه» (action بيسجّل مين أكّد وإمتى — شوز PublishesWithReview).
 *
 * بيُستخدم في صفحات Create و Edit للموديلات الخمسة.
 */
trait GuardsReviewWorkflow
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardReviewWorkflow($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->guardReviewWorkflow($data);
    }

    protected function guardReviewWorkflow(array $data): array
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = static::getResource()::getModel();

        // اختيار «جاهزة» من غير مراجع => نسجّل المستخدم الحالي كمراجِع.
        if (($data['content_status'] ?? null) === $modelClass::STATUS_READY && empty($data['reviewed_by'])) {
            $data['reviewed_by'] = auth()->id();
            $data['reviewed_at'] = now();
        }

        // ابنِ نسخة مؤقتة من كل بيانات السجل (الموجود + المُرسل) لفحص TODO/الاكتمال/التشابه.
        $existing = property_exists($this, 'record') && $this->record ? $this->record->getAttributes() : [];
        $temp = (new $modelClass())->forceFill(array_merge($existing, $data));

        if (! empty($data['is_published'])) {
            if (! $temp->canPublish()) {
                // محتوى ناقص / مش متراجع / فيه TODO => مسوّدة إجبارياً.
                $data['is_published'] = false;
            } elseif (! ($this->record->is_published ?? false)) {
                // أول نشر: بوابة التشابه (الصفحة المنشورة بالفعل مابتتبوّظش عند إعادة الحفظ).
                $sim = app(ContentSimilarity::class)->maxAgainstPublished($temp);
                $ackFresh = ! empty($data['similarity_ack_at']);

                if ($sim['pct'] > 30 && ! $ackFresh) {
                    $data['is_published'] = false;
                    Notification::make()
                        ->warning()
                        ->title('التشابه عالي — النشر متوقّف')
                        ->body('تشابه المحتوى مع صفحة منشورة وصل ' . $sim['pct'] . '%. استخدم زرّار «نشر مع تأكيد التشابه» للتأكيد الصريح.')
                        ->persistent()
                        ->send();
                }
            }
        }

        // نشر من غير تاريخ => دلوقتي.
        if (! empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
