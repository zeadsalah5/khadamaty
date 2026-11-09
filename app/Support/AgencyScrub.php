<?php

namespace App\Support;

/**
 * تنظيف المحتوى من أي تصريح عن «وضع التوكيل» بتاعنا — من غير ما نقول توكيل
 * ولا مش توكيل (حياد تام، بناءً على طلب صاحب الموقع). بتشيل الجُمل اللي بتتكلم
 * عن استقلالنا/عدم توكيلنا، وبتسيب الصياغة الإيجابية «بنصلّح كل الماركات»،
 * وبتسيب نصيحة «لو الجهاز تحت الضمان ارجع للوكيل» (عن ضمان العميل، مش عن وضعنا).
 *
 * بتُستخدم في الـ seeders وفي تنظيف المحتوى المنشور.
 */
class AgencyScrub
{
    /** كلمات لو جملة فيها أي منها => الجملة بتتشال (تصريح عن وضعنا كتوكيل). */
    protected const SENTENCE_MARKERS = ['توكيل', 'وكيل معتمد', 'غير تابع', 'مركز مستقل', 'مركز صيانة مستقل'];

    /** استبدالات مباشرة بتحافظ على الجزء الإيجابي في نفس الجملة. */
    protected static function inlineReplacements(): array
    {
        return [
            '، وإحنا مش توكيل لماركة بذاتها' => '',  // excerpt: يفضل «بنصلّح كل الماركات.»
            '، وإحنا مش توكيل لأي ماركة' => '',
            ' إحنا مش توكيل لماركة بذاتها — ' => ' ',
            ' إحنا مش توكيل لأي ماركة. ' => ' ',
        ];
    }

    /** تنظيف نص (مقدمة/ملخّص/متن/وصف SEO). */
    public static function text(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        // 1) استبدالات تحافظ على الجزء الإيجابي داخل الجملة نفسها.
        $value = strtr($value, static::inlineReplacements());

        // 2) إزالة على مستوى الجملة: نقسّم على النقطة ونشيل أي جملة فيها علامة تصريح.
        //    (الفقرات مفصولة بسطرين؛ بنحافظ عليها.)
        $paragraphs = preg_split('/(\n\s*\n)/u', $value, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$value];
        foreach ($paragraphs as $i => $chunk) {
            if (preg_match('/^\s*$/u', $chunk) || str_contains($chunk, "\n")) {
                continue; // فاصل فقرات
            }
            $sentences = preg_split('/(?<=\.)\s+/u', $chunk) ?: [$chunk];
            $kept = array_filter($sentences, fn ($s) => ! static::hasAgency($s));
            $paragraphs[$i] = implode(' ', $kept);
        }
        $value = implode('', $paragraphs);

        // 3) تنظيف فراغات/نقط ناتجة عن الحذف.
        $value = preg_replace('/[ \t]{2,}/u', ' ', $value) ?? $value;
        $value = preg_replace('/ +\./u', '.', $value) ?? $value;
        $value = preg_replace('/\.\s*\./u', '.', $value) ?? $value;
        $value = preg_replace('/\n{3,}/u', "\n\n", $value) ?? $value;

        return trim($value);
    }

    /** الجملة فيها تصريح عن وضعنا كتوكيل؟ */
    protected static function hasAgency(string $sentence): bool
    {
        foreach (static::SENTENCE_MARKERS as $marker) {
            if (mb_strpos($sentence, $marker) !== false) {
                return true;
            }
        }

        return false;
    }

    /** تنظيف قايمة أسئلة شائعة: نشيل أي سؤال/جواب بيتكلم عن وضع التوكيل بتاعنا. */
    public static function faqs(?array $faqs): ?array
    {
        if (empty($faqs)) {
            return $faqs;
        }

        $out = [];
        foreach ($faqs as $faq) {
            $q = (string) ($faq['q'] ?? '');
            $a = (string) ($faq['a'] ?? '');

            if (static::hasAgency($q) || static::hasAgency($a)) {
                continue; // سؤال/جواب عن وضعنا => يتشال بالكامل
            }

            $out[] = $faq;
        }

        return array_values($out);
    }
}
