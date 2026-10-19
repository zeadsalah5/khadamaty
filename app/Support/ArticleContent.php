<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * معالجة متن المقال (Markdown):
 *  - يحوّله لـ HTML آمن (الـ HTML الخام بيتهرّب — المصدر Markdown بس).
 *  - يحقن id على كل <h2> (section-1, section-2, ...) لبناء فهرس محتويات.
 *  - يستخرج فهرس المحتويات (نص العنوان + id) من عناوين H2.
 *  - يحسب وقت القراءة التقريبي من عدد الكلمات.
 *
 * بيستخدم league/commonmark المدمج في الفريمورك عن طريق Str::markdown().
 */
class ArticleContent
{
    /** @param list<array{id:string,text:string}> $toc */
    public function __construct(
        public readonly string $html,
        public readonly array $toc,
        public readonly int $readingMinutes,
    ) {}

    public static function fromMarkdown(string $markdown): self
    {
        $markdown = trim($markdown);

        if ($markdown === '') {
            return new self('', [], 1);
        }

        // الافتراضي في league/commonmark هو html_input=allow + allow_unsafe_links=true،
        // يعني الـ HTML الخام (<script>، <img onerror>...) وروابط javascript: بتتعرض زي ما هي.
        // بنمرّر options صريحة عشان الـ HTML الخام يتشال والروابط الخطرة تتمنع — المصدر Markdown بس.
        $html = Str::markdown($markdown, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        [$html, $toc] = self::injectHeadingIds($html);

        return new self($html, $toc, self::readingMinutes($html));
    }

    /**
     * يحقن id تسلسلي على كل <h2> ويرجّع [html المعدّل, فهرس المحتويات].
     *
     * @return array{0:string,1:list<array{id:string,text:string}>}
     */
    protected static function injectHeadingIds(string $html): array
    {
        $toc = [];
        $i = 0;

        $html = preg_replace_callback(
            '/<h2\b([^>]*)>(.*?)<\/h2>/is',
            function (array $m) use (&$toc, &$i) {
                $i++;
                $id = 'section-'.$i;
                $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES, 'UTF-8'));
                $toc[] = ['id' => $id, 'text' => $text];

                return '<h2 id="'.$id.'"'.$m[1].'>'.$m[2].'</h2>';
            },
            $html
        );

        return [$html, $toc];
    }

    /** وقت القراءة بالدقايق (≈ 200 كلمة/دقيقة، الحد الأدنى دقيقة). */
    protected static function readingMinutes(string $html): int
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text) ?: []);

        return max(1, (int) ceil($words / 200));
    }
}
