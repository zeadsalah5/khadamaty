<?php

namespace Database\Seeders;

use App\Support\Settings;
use Illuminate\Database\Seeder;

/**
 * نقل قيم config/site.php الحالية لجدول settings حرفياً (المرحلة C).
 * idempotent: بيحدّث لو موجود. القيم هي نفسها الـ fallback، فالموقع
 * بيفضل بنفس الشكل والمحتوى قبل وبعد النقل.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'company' => [
                'name', 'tagline', 'areas_label',
                'phone', 'phone_tel', 'whatsapp', 'email',
                'working_hours', 'social_links',
            ],
            'warranty' => [
                'warranty_months', 'warranty_scope', 'warranty_exclusions',
            ],
            'seo' => [
                'default_og_image', 'twitter_handle',
                'ga4_id', 'gtm_id', 'meta_pixel_id', 'custom_head',
            ],
            'home' => [
                'announcement_enabled', 'announcement_text', 'announcement_link', 'announcement_link_label',
                'hero_slides', 'service_sections', 'how_steps', 'why_us', 'home_sections',
            ],
            'booking' => [
                'booking_services', 'booking_appliances', 'areas',
            ],
            'pricing' => [
                'packages',
            ],
            'content' => [
                'faqs',
            ],
        ];

        foreach ($groups as $group => $keys) {
            $pairs = [];
            foreach ($keys as $key) {
                // القيمة الحالية من config (الـ default) — بتتنسخ زي ما هي.
                $pairs[$key] = config('site.' . $key);
            }
            Settings::setMany($pairs, $group);
        }

        Settings::flush();
    }
}
