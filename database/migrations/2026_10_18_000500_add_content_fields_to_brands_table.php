<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول brands بحقول المحتوى الغني لصفحات الماركة (المرحلة 3).
 * كلها nullable ومبتمسّش أي بيانات موجودة — إضافة فقط.
 * النشر آلي حسب اكتمال المحتوى (شوف Brand::hasCompleteContent()).
 * ملاحظة: مفيش لوجوهات رسمية — الاسم نصّي، و logo حقل مكانه لرفع لوجو لاحقاً.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('excerpt')->nullable()->after('name_ar');            // ملخّص قصير للكارت والفهرس
            $table->longText('intro')->nullable()->after('excerpt');            // نبذة/مقدمة عن الماركة
            $table->json('common_faults')->nullable()->after('intro');          // الأعطال الشائعة
            $table->json('maintenance_tips')->nullable()->after('common_faults'); // نصايح صيانة
            $table->json('device_types')->nullable()->after('maintenance_tips'); // أنواع الأجهزة
            $table->json('suitable_services')->nullable()->after('device_types'); // slugs للخدمات المناسبة
            $table->json('faqs')->nullable()->after('suitable_services');       // أسئلة شائعة خاصة بالماركة

            // مكان لرفع اللوجو لاحقاً (اسم ملف في public/images/brands/). فاضي => كارت نصّي.
            $table->string('logo')->nullable()->after('faqs');

            // تجاوز عناوين/أوصاف الـ SEO (اختياري)
            $table->string('seo_title')->nullable()->after('logo');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn([
                'excerpt', 'intro', 'common_faults', 'maintenance_tips', 'device_types',
                'suitable_services', 'faqs', 'logo', 'seo_title', 'seo_description',
            ]);
        });
    }
};
