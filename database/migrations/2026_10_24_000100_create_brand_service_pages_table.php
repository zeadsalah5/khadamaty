<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول صفحات (ماركة × خدمة) — المرحلة K1 (SEO عضوي).
 * صفحة مخصّصة لكل تقاطع ماركة/خدمة (مثال: /brands/carrier/ac-maintenance)،
 * بمحتوى فريد يتدار من داشبورد Filament. النشر آلي حسب اكتمال المحتوى
 * (BrandServicePage::hasCompleteContent) — الناقصة تفضل noindex وبرّه السايت ماب.
 * فهرس فريد على (brand_id, service_id) => صفحة واحدة لكل تقاطع.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_service_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->string('h1');                         // عنوان الصفحة (H1)
            $table->longText('intro')->nullable();        // المقدمة / أول فقرة
            $table->json('common_faults')->nullable();    // الأعطال الشائعة (فريدة للتقاطع)
            $table->json('symptoms')->nullable();         // الأعراض
            $table->json('steps')->nullable();            // إزاي بنشتغل [{title, desc}]
            $table->string('price_note')->nullable();     // السعر (TODO صاحب الموقع)
            $table->string('duration_note')->nullable();  // المدة التقريبية (TODO)
            $table->json('faqs')->nullable();             // الأسئلة الشائعة [{q, a}]

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['brand_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_service_pages');
    }
};
