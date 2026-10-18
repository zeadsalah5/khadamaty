<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول services بحقول المحتوى الغني لصفحات الخدمة (المرحلة 2).
 * كلها nullable ومبتمسّش أي بيانات موجودة — إضافة فقط.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // محتوى منظّم (JSON)
            $table->json('symptoms')->nullable()->after('body');          // الأعراض اللي بتقول إنك محتاج الخدمة
            $table->json('steps')->nullable()->after('symptoms');          // خطوات "إزاي بنشتغل"
            $table->json('includes')->nullable()->after('steps');          // الخدمة بتشمل إيه
            $table->json('faqs')->nullable()->after('includes');           // أسئلة شائعة خاصة بالخدمة
            $table->json('related_services')->nullable()->after('faqs');   // slugs لخدمات مرتبطة

            // ملاحظات placeholders (يعدّلها صاحب الموقع — TODO)
            $table->string('price_note')->nullable()->after('related_services');
            $table->string('duration_note')->nullable()->after('price_note');

            // تجاوز عناوين/أوصاف الـ SEO (اختياري)
            $table->string('seo_title')->nullable()->after('duration_note');
            $table->string('seo_description')->nullable()->after('seo_title');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'symptoms', 'steps', 'includes', 'faqs', 'related_services',
                'price_note', 'duration_note', 'seo_title', 'seo_description',
            ]);
        });
    }
};
