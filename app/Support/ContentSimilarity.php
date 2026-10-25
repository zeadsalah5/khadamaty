<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * قياس التشابه بين نصوص الصفحات (محتوى عربي).
 *
 * الطريقة: تطبيع النص (إزالة تشكيل/رموز/تطويل) => مجموعة shingles من 3 كلمات
 * => معامل Jaccard. بيرجّع نسبة مئوية صحيحة (0..100). بيُستخدم في:
 *   - تحذير النشر لو التشابه > 30% مع صفحة منشورة من نفس النوع (Section 2).
 *   - قياس التشابه بين صفحات الماركات الأساسية (Section 5).
 *
 * ملاحظة: هيوريستي بسيط بدون اعتماديات خارجية — الهدف إشارة تحذير، مش دقة أكاديمية.
 */
class ContentSimilarity
{
    /** حجم الـ shingle (عدد الكلمات المتتالية). */
    protected const SHINGLE = 3;

    /** نسبة التشابه (0..100) بين نصّين. */
    public function percent(?string $a, ?string $b): int
    {
        $sa = $this->shingles((string) $a);
        $sb = $this->shingles((string) $b);

        if (empty($sa) || empty($sb)) {
            return 0;
        }

        $intersect = count(array_intersect_key($sa, $sb));
        $union = count($sa + $sb); // اتحاد المفاتيح

        return $union === 0 ? 0 : (int) round($intersect / $union * 100);
    }

    /**
     * أعلى تشابه للسجل مقابل الصفحات المنشورة من نفس النوع (باستثناء نفسه).
     * بيرجّع ['pct' => int, 'other' => Model|null].
     */
    public function maxAgainstPublished(Model $record): array
    {
        $text = $this->recordText($record);
        $best = ['pct' => 0, 'other' => null];

        if ($text === '') {
            return $best;
        }

        $query = $record->newQuery()->where('is_published', true);
        if ($record->getKey()) {
            $query->whereKeyNot($record->getKey());
        }

        foreach ($query->get() as $other) {
            $pct = $this->percent($text, $this->recordText($other));
            if ($pct > $best['pct']) {
                $best = ['pct' => $pct, 'other' => $other];
            }
        }

        return $best;
    }

    /** مصفوفة أزواج التشابه بين مجموعة سجلات (لتقرير Section 5). */
    public function pairwise($records): array
    {
        $records = collect($records)->values();
        $pairs = [];

        for ($i = 0; $i < $records->count(); $i++) {
            for ($j = $i + 1; $j < $records->count(); $j++) {
                $a = $records[$i];
                $b = $records[$j];
                $pairs[] = [
                    'a'   => $a,
                    'b'   => $b,
                    'pct' => $this->percent($this->recordText($a), $this->recordText($b)),
                ];
            }
        }

        usort($pairs, fn ($x, $y) => $y['pct'] <=> $x['pct']);

        return $pairs;
    }

    /** نص السجل القابل للمراجعة (بيستخدم reviewText لو الموديل معاه سير المراجعة). */
    public function recordText(Model $record): string
    {
        if (method_exists($record, 'reviewText')) {
            return $this->normalize($record->reviewText());
        }

        return $this->normalize(implode("\n", array_filter(
            $record->getAttributes(),
            fn ($v) => is_string($v)
        )));
    }

    /** مجموعة shingles (مفتاح => 1) من النص المطبّع. */
    protected function shingles(string $text): array
    {
        $words = array_values(array_filter(explode(' ', $this->normalize($text)), fn ($w) => $w !== ''));
        $count = count($words);

        if ($count === 0) {
            return [];
        }

        // نص قصير جداً => كلمات مفردة بدل shingles.
        if ($count < self::SHINGLE) {
            return array_fill_keys($words, 1);
        }

        $set = [];
        for ($i = 0; $i <= $count - self::SHINGLE; $i++) {
            $gram = implode(' ', array_slice($words, $i, self::SHINGLE));
            $set[$gram] = 1;
        }

        return $set;
    }

    /** تطبيع النص العربي: توحيد الحروف، إزالة التشكيل والتطويل والرموز. */
    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        // إزالة التشكيل والتطويل (ـ).
        $text = preg_replace('/[\x{064B}-\x{0652}\x{0640}]/u', '', $text) ?? $text;

        // توحيد الألف والهمزات والتاء المربوطة والياء.
        $text = strtr($text, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا',
            'ى' => 'ي', 'ة' => 'ه',
        ]);

        // استبدال أي حرف مش عربي/لاتيني/رقم بمسافة.
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}]+/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
