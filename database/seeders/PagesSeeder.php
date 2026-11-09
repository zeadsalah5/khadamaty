<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * صفحات ثابتة مبدئية (المرحلة C3) — سياسة الخصوصية والشروط.
 * بتتزرع كمسوّدات (is_published=false) عشان صاحب الموقع يملاها وينشرها.
 * غير المنشورة => 404 ومش بتظهر في الفوتر.
 */
class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['slug' => 'privacy-policy', 'title' => 'سياسة الخصوصية'],
            ['slug' => 'terms', 'title' => 'الشروط والأحكام'],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                [
                    'title'          => $page['title'],
                    'body'           => '',
                    'show_in_footer' => true,
                    'is_published'   => false,
                ],
            );
        }
    }
}
