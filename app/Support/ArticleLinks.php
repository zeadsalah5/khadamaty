<?php

namespace App\Support;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Post;
use App\Models\Service;

/**
 * روابط داخلية «تحويلية» جوّه المقال: أنسب صفحة خدمة (ولو المقال عن منطقة: صفحة المنطقة×الخدمة)
 * + استخراج الأسئلة الشائعة من متن الـ Markdown لعمل FAQPage schema.
 */
class ArticleLinks
{
    /** كلمة في الـ slug => خدمة (الترتيب مهم: الأدق الأول). */
    protected const SERVICE_HINTS = [
        'piping' => 'ac-piping-installation',
        'relocation' => 'ac-relocation',
        'leak' => 'ac-leak-repair',
        'freon' => 'ac-gas-refill',
        'gas' => 'ac-gas-refill',
        'central' => 'central-ac-maintenance',
        'install' => 'ac-installation',
        'clean' => 'ac-cleaning',
        'wash' => 'ac-cleaning',
        'error' => 'ac-repair',
        'not-cooling' => 'ac-repair',
        'technician' => 'ac-repair',
        'repair' => 'ac-repair',
        'problem' => 'ac-repair',
        'shuts-off' => 'ac-repair',
        'remote' => 'ac-repair',
        'water' => 'ac-repair',
        'split' => 'split-ac-maintenance',
        'maintenance' => 'ac-maintenance',
    ];

    /** @return array{url:string,label:string}|null */
    public static function primary(Post $post): ?array
    {
        $service = self::service($post);
        if (! $service) {
            return null;
        }

        $area = Area::query()->published()->get(['id', 'slug', 'name_ar'])
            ->first(fn (Area $a) => str_contains($post->slug, $a->slug));

        if ($area) {
            $page = AreaServicePage::query()->published()
                ->where('area_id', $area->id)->where('service_id', $service->id)->first();
            if ($page) {
                return ['url' => $page->path(), 'label' => $service->name_ar.' في '.$area->name_ar];
            }

            return ['url' => '/areas/'.$area->slug, 'label' => $service->name_ar.' في '.$area->name_ar];
        }

        return ['url' => '/services/'.$service->slug, 'label' => $service->name_ar];
    }

    protected static function service(Post $post): ?Service
    {
        $slugs = (array) ($post->suitable_services ?? []);
        foreach (self::SERVICE_HINTS as $hint => $slug) {
            if (str_contains($post->slug, $hint)) {
                array_unshift($slugs, $slug);
                break;
            }
        }
        $slugs[] = 'ac-maintenance';

        foreach (array_unique($slugs) as $slug) {
            $s = Service::query()->published()->where('slug', $slug)->first();
            if ($s) {
                return $s;
            }
        }

        return null;
    }

    /**
     * الأسئلة من قسم H2 فيه «أسئلة»: صيغة «**سؤال؟** إجابة» أو «### سؤال» وبعده فقرة.
     *
     * @return array<int, array{q:string,a:string}>
     */
    public static function faqs(string $markdown): array
    {
        if (! preg_match('/^##\s+[^\n#]*(أسئلة|اسئلة|شائعة)[^\n]*\n(.*?)(?=^##\s|\z)/msu', $markdown, $m)) {
            return self::questionHeadings($markdown);
        }
        $section = $m[2];
        $items = [];

        if (preg_match_all('/^###\s+(.+?)\n+(.+?)(?=^###\s|\z)/msu', $section, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $x) {
                $items[] = ['q' => trim($x[1]), 'a' => trim($x[2])];
            }
        }
        if (! $items && preg_match_all('/^\s*(?:[-*]\s*)?\*\*(.+?)\*\*\s*:?\s*(.+)$/mu', $section, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $x) {
                $items[] = ['q' => trim($x[1]), 'a' => trim($x[2])];
            }
        }

        return collect($items)
            ->map(fn ($i) => ['q' => self::plain($i['q']), 'a' => self::plain($i['a'])])
            ->filter(fn ($i) => $i['q'] !== '' && $i['a'] !== '' && str_contains($i['q'], '؟'))
            ->values()->all();
    }

    /** بديل: عناوين H2/H3 على شكل سؤال (بتنتهي بـ ؟) + أول فقرة نصية بعدها. */
    protected static function questionHeadings(string $markdown): array
    {
        preg_match_all('/^#{2,3}\s+(.+؟)\s*\n+((?:(?!#)[^\n|>\-*\d][^\n]*\n?)+)/mu', $markdown, $mm, PREG_SET_ORDER);

        return collect($mm)
            ->map(fn ($x) => ['q' => self::plain($x[1]), 'a' => self::plain($x[2])])
            ->filter(fn ($i) => mb_strlen($i['a']) >= 40)
            ->values()->all();
    }

    protected static function plain(string $md): string
    {
        $t = preg_replace('/\[([^\]]+)\]\([^)]+\)/u', '$1', $md);
        $t = str_replace(['**', '__', '`'], '', (string) $t);

        return trim(preg_replace('/\s+/u', ' ', $t));
    }
}
