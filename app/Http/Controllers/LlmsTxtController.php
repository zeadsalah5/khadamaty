<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Brand;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;

/**
 * ملف llms.txt — معيار صاعد بيساعد أنظمة الـ AI (ChatGPT/Claude/Perplexity…)
 * تفهم بنية الموقع وأهم صفحاته. بيتولّد ديناميكياً من المحتوى المنشور
 * فبيفضل متحدّث تلقائياً. بيتقدّم على /llms.txt.
 */
class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $base = rtrim((string) url('/'), '/');
        $name = (string) site('name');
        $tagline = trim((string) site('tagline'));

        $lines = [];
        $lines[] = '# ' . $name;
        $lines[] = '';
        $lines[] = '> ' . ($tagline !== ''
            ? $tagline
            : 'خدمات صيانة وتركيب وإصلاح التكييفات والأجهزة المنزلية، وقطع غيار — بأيدي فنيين متخصصين.');
        $lines[] = '';
        $lines[] = 'خدماتي منصّة مصرية لحجز خدمات صيانة التكييفات والأجهزة المنزلية في القاهرة الكبرى، '
            . 'مع دليل مناطق وماركات ومدوّنة إرشادية ومتجر قطع غيار. المحتوى عربي، والحجز أونلاين أو واتساب.';
        $lines[] = '';

        // الخدمات
        $this->section($lines, 'الخدمات', Service::query()->published()->orderBy('name_ar')->get(), '/services');

        // مناطق الخدمة
        $this->section($lines, 'مناطق الخدمة', Area::query()->published()->orderBy('name_ar')->get(), '/areas');

        // الماركات
        $this->section($lines, 'الماركات', Brand::query()->published()->orderBy('name_ar')->get(), '/brands');

        // المدونة (أحدث المقالات)
        $posts = Post::query()->published()->latest('published_at')->take(20)->get();
        if ($posts->isNotEmpty()) {
            $lines[] = '## المدونة';
            foreach ($posts as $p) {
                $lines[] = $this->item($p->title_ar, $base . '/blog/' . $p->slug, $p->excerpt);
            }
            $lines[] = '';
        }

        // روابط مهمة
        $lines[] = '## روابط مهمة';
        $lines[] = '- [كل الخدمات](' . $base . '/services)';
        $lines[] = '- [مناطق الخدمة](' . $base . '/areas)';
        $lines[] = '- [الماركات](' . $base . '/brands)';
        $lines[] = '- [المدونة](' . $base . '/blog)';
        $lines[] = '- [المتجر وقطع الغيار](' . $base . '/shop)';
        $lines[] = '- [من إحنا](' . $base . '/about)';
        $lines[] = '- [الأسعار](' . $base . '/prices)';
        $lines[] = '- [الأسئلة الشائعة](' . $base . '/faq)';
        $lines[] = '- [تواصل معنا](' . $base . '/contact)';
        $lines[] = '';

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /** يضيف قسم بعناصر منشورة لها slug + name_ar (+ excerpt اختياري). */
    private function section(array &$lines, string $title, $items, string $prefix): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $lines[] = '## ' . $title;
        foreach ($items as $it) {
            $lines[] = $this->item($it->name_ar, rtrim((string) url('/'), '/') . $prefix . '/' . $it->slug, $it->excerpt ?? null);
        }
        $lines[] = '';
    }

    /** سطر عنصر llms.txt: - [العنوان](الرابط): الوصف */
    private function item(?string $title, string $url, ?string $excerpt): string
    {
        $line = '- [' . trim((string) $title) . '](' . $url . ')';
        $excerpt = trim(preg_replace('/\s+/', ' ', (string) $excerpt));
        if ($excerpt !== '') {
            $line .= ': ' . mb_substr($excerpt, 0, 160);
        }

        return $line;
    }
}
