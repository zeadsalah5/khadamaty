<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * سير مراجعة موحّد لكل أنواع صفحات المحتوى.
 *
 * القاعدة المفروضة بالكود: صفحة ماتتنشرش (is_published=true) غير لو:
 *   - الحالة "ready" (جاهزة للنشر).
 *   - reviewed_by معبّى (الفني/المسؤول راجعها).
 *   - مفيش أي "TODO" في حقول المحتوى (شامل الأسئلة الشائعة والـ SEO).
 *   - الحقول المطلوبة معبّية (hasCompleteContent()).
 *
 * الفرض الفعلي بيحصل في GuardsReviewWorkflow (صفحات Filament) عبر canPublish().
 * الموديل بيعرّف reviewableTextFields() = الحقول اللي بتتفحص للـ TODO وتُحسب في التشابه.
 */
trait HasReviewWorkflow
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_NEEDS_REVIEW = 'needs_tech_review';
    public const STATUS_READY = 'ready';

    /** العناوين العربية لحالات المراجعة (للعرض/الفلترة في الداشبورد). */
    public static function reviewStatusLabels(): array
    {
        return [
            self::STATUS_DRAFT        => 'مسودة',
            self::STATUS_NEEDS_REVIEW => 'محتاجة مراجعة الفني',
            self::STATUS_READY        => 'جاهزة للنشر',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه.
     * كل موديل المفروض يعرّفها؛ لو مش معرّفة بنرجّع للـ fillable (آمن لكن أوسع).
     */
    public function reviewableTextFields(): array
    {
        return array_values(array_diff($this->getFillable(), [
            'is_published', 'published_at', 'content_status', 'reviewed_by', 'reviewed_at',
            'similarity_ack_by', 'similarity_ack_at', 'similarity_pct',
            'slug', 'icon', 'logo', 'cover', 'category_id', 'brand_id', 'service_id',
            'booking_service', 'delivers', 'delivery_fee', 'delivery_note',
        ]));
    }

    /** كل نص المحتوى القابل للمراجعة مجمّع في سترينج واحد (للـ TODO/التشابه). */
    public function reviewText(): string
    {
        $parts = [];

        foreach ($this->reviewableTextFields() as $field) {
            $this->flattenValue($this->{$field}, $parts);
        }

        return trim(implode("\n", array_filter($parts, fn ($p) => $p !== '')));
    }

    /** يفرد القيم (نصوص + arrays متداخلة زي faqs/steps) في مصفوفة سطور. */
    protected function flattenValue($value, array &$parts): void
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                $this->flattenValue($v, $parts);
            }

            return;
        }

        if (is_string($value) || is_numeric($value)) {
            $parts[] = (string) $value;
        }
    }

    /** فيه "TODO" في أي حقل محتوى؟ (بيمنع النشر). */
    public function hasTodo(): bool
    {
        return stripos($this->reviewText(), 'TODO') !== false;
    }

    /** المحتوى المطلوب مكتمل؟ (بيرجّع للميثود الخاص بالموديل لو موجود). */
    public function reviewHasCompleteContent(): bool
    {
        return method_exists($this, 'hasCompleteContent') ? $this->hasCompleteContent() : true;
    }

    /** يصحّ نشرها؟ (كل الشروط). */
    public function canPublish(): bool
    {
        return $this->content_status === self::STATUS_READY
            && filled($this->reviewed_by)
            && ! $this->hasTodo()
            && $this->reviewHasCompleteContent();
    }

    /**
     * أسباب منع النشر كـ labels عربية (للعرض في الداشبورد).
     * فاضية => جاهزة للنشر.
     */
    public function publishBlockers(): array
    {
        $blockers = [];

        if ($this->content_status !== self::STATUS_READY) {
            $blockers[] = 'الحالة مش «جاهزة للنشر»';
        }

        if (blank($this->reviewed_by)) {
            $blockers[] = 'لسه متراجعتش (reviewed_by فاضي)';
        }

        if ($this->hasTodo()) {
            $blockers[] = 'فيه TODO في المحتوى';
        }

        if (method_exists($this, 'missingContent')) {
            foreach ((array) $this->missingContent() as $missing) {
                $blockers[] = 'ناقص: ' . $missing;
            }
        } elseif (! $this->reviewHasCompleteContent()) {
            $blockers[] = 'المحتوى المطلوب ناقص';
        }

        return array_values(array_unique($blockers));
    }

    /** نص قصير للنوع (للـ widget والرسائل). كل موديل ممكن يعرّفه. */
    public function reviewTypeLabel(): string
    {
        return class_basename($this);
    }

    /** قيمة reviewed_by كـ relation مختصر (مفيد في Arr::get على arrays). */
    public function reviewerName(): ?string
    {
        return $this->reviewer?->name ?? Arr::get($this->getAttributes(), 'reviewed_by');
    }
}
