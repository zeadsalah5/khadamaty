<?php

namespace Database\Seeders;

use App\Models\Appliance;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // مراجِع البيانات الأولية = أول أدمن (موجود في التطوير — شوف DatabaseSeeder).
        // المحتوى اللي بيتزرع منشور بيتسجّل متراجَع بواسطته عشان يفضل قابل للنشر
        // تحت سير المراجعة الجديد (reviewed_by مطلوب). فاضي => بيفضل مسوّدة.
        $reviewerId = User::where('role', 'admin')->value('id');

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

            // النشر بناءً على اكتمال المحتوى + صفر TODO + مراجَع (سير المراجعة الموحّد).
            $service->forceFill($this->reviewFill($service, $reviewerId, $now))->save();
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
        | المناطق (المرحلة 3 / K2) — من database/seeders/data/areas.php.
        | **المعلومات المحلية (الأحياء ونوع المباني والمشاكل) اتكتبت من معرفة عامة**،
        | فكل صفحات المناطق بتتزرع **«محتاجة مراجعة الفني» وغير منشورة** (المحتوى محفوظ
        | كمسودة) — لازم الفني يراجعها قبل النشر. المنطقة الناقصة (زي فيصل) بتفضل draft.
        */
        $areasData = require __DIR__.'/data/areas.php';
        foreach ($areasData as $slug => $fields) {
            $area = Area::updateOrCreate(['slug' => $slug], $fields);

            // محتوى مكتمل وصفر TODO => «محتاجة مراجعة»؛ غير كده مسودة. مفيش نشر تلقائي.
            $complete = $area->hasCompleteContent() && ! $area->hasTodo();

            $fill = [
                'is_published'   => false,
                'published_at'   => null,
                'content_status' => $complete ? Area::STATUS_NEEDS_REVIEW : Area::STATUS_DRAFT,
                'reviewed_by'    => null,
                'reviewed_at'    => null,
            ];

            // توصيل المتجر: placeholder للمناطق المكتملة (مستقل عن نشر صفحة المنطقة).
            if ($complete && $area->delivery_note === null && (float) $area->delivery_fee === 0.0) {
                $fill['delivers'] = true;
                $fill['delivery_note'] = 'TODO: صاحب الموقع يحدد رسوم التوصيل لهذه المنطقة.';
            }

            $area->forceFill($fill)->save();
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

            $brand->forceFill($this->reviewFill($brand, $reviewerId, $now))->save();
        }

        // تنظيف ماركات placeholder قديمة.
        Brand::whereIn('slug', ['brand-placeholder-1', 'brand-placeholder-2'])->delete();

        /*
        | صفحات (ماركة × خدمة) — المرحلة K1 (SEO عضوي).
        | من database/seeders/data/brand_services.php، المفتاح 'brand|service'.
        | **بتتزرع دايماً كمسوّدة (is_published=false)** — عكس باقي المحتوى — عشان
        | تتراجع وتتنشر يدوياً من الداشبورد بعد ملء السعر/المدة (TODO صاحب الموقع).
        | الصفحة الناقصة أو غير المنشورة => 404 + noindex + برّه السايت ماب.
        */
        $brandServicesData = require __DIR__.'/data/brand_services.php';
        $brandIds = Brand::pluck('id', 'slug');
        $serviceIds = Service::pluck('id', 'slug');
        foreach ($brandServicesData as $key => $fields) {
            [$brandSlug, $serviceSlug] = explode('|', $key);
            $brandId = $brandIds[$brandSlug] ?? null;
            $serviceId = $serviceIds[$serviceSlug] ?? null;
            if (! $brandId || ! $serviceId) {
                continue; // ماركة/خدمة مش موجودة => نتخطّى بأمان
            }

            BrandServicePage::updateOrCreate(
                ['brand_id' => $brandId, 'service_id' => $serviceId],
                $fields + ['is_published' => false, 'published_at' => null],
            );
        }

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

            // مقالات K3 (من أسئلة الناس) بتتزرع مسودة إجبارياً — لازم مراجعة قبل النشر.
            $forceDraft = ! empty($fields['force_draft']);
            unset($fields['force_draft']);

            $post = Post::updateOrCreate(['slug' => $slug], $fields);

            if ($forceDraft) {
                $post->forceFill([
                    'is_published'   => false,
                    'published_at'   => null,
                    'content_status' => Post::STATUS_DRAFT,
                    'reviewed_by'    => null,
                    'reviewed_at'    => null,
                ])->save();
            } else {
                // المقال الكامل + صفر TODO + مراجَع بيتنشر؛ غير كده مسوّدة (404 + noindex + بره السايت ماب).
                $post->forceFill($this->reviewFill($post, $reviewerId, $now))->save();
            }
        }

        // المنتجات التجريبية بقت في StoreSeeder (المرحلة 6A) مع الأقسام والمواصفات.

        Vendor::updateOrCreate(['slug' => 'vendor-placeholder-1'], [
            'name_ar' => 'تاجر تجريبي (TODO)',
            'about' => 'نبذة عن التاجر هتتكتب هنا.',
            'is_published' => false,
        ]);
    }

    /**
     * قيم النشر + سير المراجعة لعنصر محتوى:
     * بيتنشر بس لو المحتوى مكتمل + صفر TODO (canPublish فعلياً)، ويتسجّل متراجَع
     * بواسطة الأدمن. غير كده بيفضل مسوّدة (content_status=draft, غير منشور).
     * مابيلغيش تاريخ/مراجعة سابقة لو كانت موجودة.
     *
     * @param  \App\Models\Concerns\HasReviewWorkflow  $model
     */
    private function reviewFill($model, ?int $reviewerId, $now): array
    {
        $publishable = $model->hasCompleteContent() && ! $model->hasTodo();

        return [
            'is_published'   => $publishable,
            'published_at'   => $publishable ? ($model->published_at ?? $now) : null,
            'content_status' => $publishable ? $model::STATUS_READY : $model::STATUS_DRAFT,
            'reviewed_by'    => $publishable ? ($model->reviewed_by ?? $reviewerId) : null,
            'reviewed_at'    => $publishable ? ($model->reviewed_at ?? $now) : null,
        ];
    }
}
