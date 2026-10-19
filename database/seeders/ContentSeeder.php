<?php

namespace Database\Seeders;

use App\Models\Appliance;
use App\Models\Area;
use App\Models\Brand;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\Service;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        | الخدمات (المرحلة 2) — 9 خدمات بـ slugs إنجليزي ثابتة ومحتوى عربي من
        | database/seeders/data/services.php. الـ slug إنجليزي والمحتوى عربي.
        |
        | النشر آلي: الخدمة اللي محتواها كامل بتتنشر (is_published=true)،
        | والناقصة بتفضل غير منشورة => 404 + noindex + بره السايت ماب.
        */
        $servicesData = require __DIR__.'/data/services.php';
        foreach ($servicesData as $slug => $fields) {
            $service = Service::updateOrCreate(['slug' => $slug], $fields);

            // النشر بناءً على اكتمال المحتوى (مش بنلغي نشر حاجة اتنشرت يدوياً بمحتوى كامل).
            $complete = $service->hasCompleteContent();
            $service->forceFill([
                'is_published' => $complete,
                'published_at' => $complete ? ($service->published_at ?? $now) : null,
            ])->save();
        }

        /*
        | تنظيف الـ slugs القديمة (المرحلة 1) اللي اتعوّضت بالخدمات الجديدة.
        | ده شغل seeder (مش migration) فمش بيكسر قاعدة "متمسحش بيانات في الـ migration".
        | الـ 301 من القديم للجديد متظبّط في config/redirects.php.
        */
        Service::whereIn('slug', [
            'maintenance', 'cleaning', 'freon-charge', 'ac-install', 'move-install', 'repair',
        ])->delete();

        /*
        | أيقونة الخدمة ونوع خدمة الحجز (المرحلة C2) — بتتعدّل من الداشبورد.
        | بنملا الناقص بس (null) عشان ما نكتبش فوق تعديلات لوحة التحكم.
        */
        $icons = [
            'ac-maintenance' => 'maintenance', 'ac-repair' => 'repair', 'ac-cleaning' => 'cleaning',
            'ac-installation' => 'install', 'ac-gas-refill' => 'freon', 'ac-leak-repair' => 'diagnose',
            'split-ac-maintenance' => 'maintenance', 'central-ac-maintenance' => 'contract',
            'ac-piping-installation' => 'pipes',
        ];
        $bookingMap = [
            'ac-maintenance' => 'صيانة دورية', 'ac-repair' => 'إصلاح عطل', 'ac-cleaning' => 'غسيل وتنظيف',
            'ac-installation' => 'تركيب تكييف', 'ac-gas-refill' => 'شحن فريون', 'ac-leak-repair' => 'إصلاح عطل',
            'split-ac-maintenance' => 'صيانة دورية', 'central-ac-maintenance' => 'صيانة دورية',
            'ac-piping-installation' => 'تركيب تكييف',
        ];
        foreach ($icons as $slug => $icon) {
            Service::where('slug', $slug)->whereNull('icon')->update([
                'icon' => $icon,
                'booking_service' => $bookingMap[$slug] ?? null,
            ]);
        }

        /*
        | المناطق (المرحلة 3) — من database/seeders/data/areas.php.
        | نفس منطق الخدمات: المنطقة اللي محتواها كامل بتتنشر تلقائياً،
        | والناقصة بتفضل غير منشورة => 404 + noindex + بره السايت ماب.
        */
        $areasData = require __DIR__.'/data/areas.php';
        foreach ($areasData as $slug => $fields) {
            $area = Area::updateOrCreate(['slug' => $slug], $fields);

            $complete = $area->hasCompleteContent();
            $area->forceFill([
                'is_published' => $complete,
                'published_at' => $complete ? ($area->published_at ?? $now) : null,
            ])->save();
        }

        /*
        | تنظيف slugs مناطق قديمة اتشالت من القايمة (المرحلة 1/2).
        | الـ 301 من القديم لـ /areas متظبّط في config/redirects.php.
        */
        Area::whereIn('slug', ['cairo', 'giza', 'heliopolis', 'helwan', 'shubra'])->delete();

        /*
        | الماركات (المرحلة 3) — من database/seeders/data/brands.php.
        | نفس منطق النشر الآلي حسب اكتمال المحتوى. بدون لوجوهات، بدون ادّعاء وكالة.
        */
        $brandsData = require __DIR__.'/data/brands.php';
        foreach ($brandsData as $slug => $fields) {
            $brand = Brand::updateOrCreate(['slug' => $slug], $fields);

            $complete = $brand->hasCompleteContent();
            $brand->forceFill([
                'is_published' => $complete,
                'published_at' => $complete ? ($brand->published_at ?? $now) : null,
            ])->save();
        }

        // تنظيف ماركات placeholder قديمة.
        Brand::whereIn('slug', ['brand-placeholder-1', 'brand-placeholder-2'])->delete();

        /*
        | ما تحت ده كله placeholders غير منشورة (is_published=false):
        | بترجّع 404، noindex، ومش بتدخل السايت ماب. صاحب الموقع ينشرها بعد التعبئة.
        | TODO: استبدل البيانات دي بمحتوى حقيقي وانشرها.
        */
        $appliances = [
            ['air-conditioners', 'تكييفات'],
            ['refrigerators',    'ثلاجات'],
            ['washing-machines', 'غسالات'],
            ['home-appliances',  'أجهزة منزلية'],
        ];
        foreach ($appliances as [$slug, $name]) {
            Appliance::updateOrCreate(['slug' => $slug], [
                'name_ar' => $name, 'is_published' => false,
            ]);
        }

        /*
        | المدونة (المرحلة 5) — تكييفات بس.
        | التصنيفات الأربعة أول حاجة، وبعدين المقالات (3 كاملة بتتنشر + مسوّدات).
        | النشر آلي حسب اكتمال المحتوى (Post::hasCompleteContent).
        */
        $categoriesData = require __DIR__.'/data/post_categories.php';
        foreach ($categoriesData as $slug => $fields) {
            PostCategory::updateOrCreate(['slug' => $slug], $fields);
        }

        // تنظيف مقال placeholder قديم (المرحلة 1) اتعوّض بمحتوى حقيقي.
        Post::where('slug', 'post-placeholder-1')->delete();

        $catIds = PostCategory::pluck('id', 'slug');

        $postsData = require __DIR__.'/data/posts.php';
        foreach ($postsData as $slug => $fields) {
            $category = $fields['category'] ?? null;
            unset($fields['category']);
            $fields['category_id'] = $category ? ($catIds[$category] ?? null) : null;

            $post = Post::updateOrCreate(['slug' => $slug], $fields);

            // المقال الكامل بيتنشر تلقائياً؛ المسوّدة بتفضل غير منشورة => 404 + noindex + بره السايت ماب.
            $complete = $post->hasCompleteContent();
            $post->forceFill([
                'is_published' => $complete,
                'published_at' => $complete ? ($post->published_at ?? $now) : null,
            ])->save();
        }

        Product::updateOrCreate(['slug' => 'product-placeholder-1'], [
            'name_ar' => 'منتج تجريبي (TODO)',
            'excerpt' => 'وصف المنتج هيتكتب هنا.',
            'price' => null, // لا سعر => مفيش offers في الـ schema
            'is_published' => false,
        ]);

        Vendor::updateOrCreate(['slug' => 'vendor-placeholder-1'], [
            'name_ar' => 'تاجر تجريبي (TODO)',
            'about' => 'نبذة عن التاجر هتتكتب هنا.',
            'is_published' => false,
        ]);
    }
}
