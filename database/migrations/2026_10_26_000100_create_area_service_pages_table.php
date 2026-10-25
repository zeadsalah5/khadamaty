<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول صفحات (منطقة × خدمة) — K2 المناطق (SEO عضوي).
 * صفحة مخصّصة لكل تقاطع منطقة/خدمة (مثال: /areas/nasr-city/ac-maintenance).
 * النشر عبر سير المراجعة الموحّد (أعمدة المراجعة inline). فهرس فريد على (area_id, service_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_service_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->string('h1');
            $table->longText('intro')->nullable();
            $table->json('steps')->nullable();            // إزاي بنشتغل [{title, desc}]
            $table->json('faqs')->nullable();             // الأسئلة الشائعة [{q, a}]
            $table->string('price_note')->nullable();     // اختياري (TODO صاحب الموقع)
            $table->string('duration_note')->nullable();  // اختياري (TODO)

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            // سير المراجعة الموحّد.
            $table->string('content_status')->default('draft');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('similarity_ack_by')->nullable();
            $table->timestamp('similarity_ack_at')->nullable();
            $table->unsignedTinyInteger('similarity_pct')->nullable();

            $table->timestamps();

            $table->unique(['area_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_service_pages');
    }
};
