<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول posts بحقول المدونة الغنية (المرحلة 5):
 * - category_id: ربط بتصنيف (صيانة/أعطال/شراء وتركيب/توفير كهرباء).
 * - suitable_services: slugs لخدمات مناسبة للروابط الداخلية جوّه المقال.
 * - seo_title / seo_description: تجاوز عناوين/أوصاف الـ SEO (اختياري).
 * كلها nullable — إضافة فقط، مبتمسّش بيانات موجودة.
 * body بيتخزّن Markdown ويترندر عن طريق league/commonmark (مدمج في الفريمورك).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('cover')
                ->constrained('post_categories')->nullOnDelete();
            $table->json('suitable_services')->nullable()->after('category_id'); // slugs خدمات مناسبة
            $table->string('seo_title')->nullable()->after('suitable_services');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['suitable_services', 'seo_title', 'seo_description']);
        });
    }
};
