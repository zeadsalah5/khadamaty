<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * نقل قوائم الهيدر والفوتر من الـ blade للجدول (المرحلة C3) — بنفس العناصر والترتيب.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $header = [
            ['الخدمات', '/services'],
            ['المناطق', '/areas'],
            ['الماركات', '/brands'],
            ['المدونة', '/blog'],
            ['الأسعار', '/prices'],
            ['مين إحنا', '/about'],
            ['الأسئلة الشائعة', '/faq'],
            ['اتصل بينا', '/contact'],
        ];

        $footer = [
            ['الخدمات', '/services'],
            ['المناطق', '/areas'],
            ['الماركات', '/brands'],
            ['الأسعار', '/prices'],
            ['مين إحنا', '/about'],
            ['الأسئلة الشائعة', '/faq'],
            ['شخّص عطلك', '/diagnose'],
            ['آراء العملاء', '/reviews'],
            ['اتصل بينا', '/contact'],
            ['اطلب خدمة', '/request-service'],
        ];

        foreach (['header' => $header, 'footer' => $footer] as $location => $items) {
            foreach ($items as $i => [$label, $url]) {
                MenuItem::updateOrCreate(
                    ['location' => $location, 'label' => $label],
                    ['url' => $url, 'sort' => $i, 'is_active' => true],
                );
            }
        }
    }
}
