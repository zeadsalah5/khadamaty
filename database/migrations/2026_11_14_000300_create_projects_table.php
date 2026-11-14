<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * معرض الأعمال (Portfolio): أعمال حقيقية للموقع بصور ووصف.
 * بتتدار من الداشبورد وتظهر في /portfolio — إشارة خبرة قوية للـ GEO/E-E-A-T.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('service_type')->nullable();  // نوع الخدمة (تركيب/صيانة/إصلاح…)
            $table->string('area_name')->nullable();      // المنطقة
            $table->string('summary', 300)->nullable();   // سطر وصف للكارت
            $table->text('body')->nullable();             // تفاصيل العمل (Markdown)
            $table->string('cover')->nullable();          // صورة الغلاف (WebP)
            $table->json('images')->nullable();           // معرض صور إضافية (WebP[])
            $table->date('completed_at')->nullable();     // تاريخ تنفيذ العمل
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
