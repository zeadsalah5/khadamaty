<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تصنيفات المدونة (المرحلة 5) — 4 تصنيفات ثابتة حسب نية البحث:
 * صيانة، أعطال، شراء وتركيب، توفير كهرباء.
 * التصنيف بيظهر في /blog/category/{slug} ولو مفيهوش مقالات منشورة بيبقى noindex.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_ar');
            $table->string('excerpt')->nullable();  // وصف قصير للتصنيف (هيدر الصفحة + SEO)
            $table->unsignedSmallInteger('sort')->default(0); // ترتيب العرض
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_categories');
    }
};
