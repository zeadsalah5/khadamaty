<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أقسام المتجر (شجرة). كل قسم له حقول مواصفات خاصة (category_attributes).
 * يستخدم نفس نمط المحتوى: slug + is_published + published_at (trait Publishable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()
                ->constrained('product_categories')->nullOnDelete();
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('excerpt')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->unsignedInteger('position')->default(0)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
