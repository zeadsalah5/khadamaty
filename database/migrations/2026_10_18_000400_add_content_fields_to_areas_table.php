<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول areas بحقول المحتوى الغني لصفحات المنطقة (المرحلة 3).
 * كلها nullable ومبتمسّش أي بيانات موجودة — إضافة فقط.
 * النشر آلي حسب اكتمال المحتوى (شوف Area::hasCompleteContent()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->string('excerpt')->nullable()->after('name_ar');       // ملخّص قصير للكارت والفهرس
            $table->longText('intro')->nullable()->after('excerpt');       // طبيعة المنطقة والمباني (مقدمة)
            $table->json('common_problems')->nullable()->after('intro');   // مشاكل التكييف الشائعة في المنطقة
            $table->json('neighborhoods')->nullable()->after('common_problems'); // أشهر الأحياء
            $table->json('top_services')->nullable()->after('neighborhoods');    // slugs لأكثر الخدمات طلباً
            $table->string('eta_note')->nullable()->after('top_services'); // زمن الوصول التقريبي (placeholder/TODO)
            $table->json('faqs')->nullable()->after('eta_note');           // أسئلة شائعة خاصة بالمنطقة

            // تجاوز عناوين/أوصاف الـ SEO (اختياري)
            $table->string('seo_title')->nullable()->after('faqs');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt', 'intro', 'common_problems', 'neighborhoods',
                'top_services', 'eta_note', 'faqs', 'seo_title', 'seo_description',
            ]);
        });
    }
};
