<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * نقل الأسئلة من config/site.faqs للجدول (المرحلة C3).
 * أول 4 بـ show_on_home=true (نفس سلوك الرئيسية الحالي).
 */
class FaqsSeeder extends Seeder
{
    public function run(): void
    {
        // تنظيف أي سؤال عن وضع التوكيل بتاعنا (حياد تام — طلب صاحب الموقع).
        $faqs = \App\Support\AgencyScrub::faqs((array) config('site.faqs', [])) ?? [];

        foreach ($faqs as $i => $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['q']],
                [
                    'answer'       => $faq['a'],
                    'sort'         => $i,
                    'show_on_home' => $i < 4,
                    'is_active'    => true,
                ],
            );
        }
    }
}
