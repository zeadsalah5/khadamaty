<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ماركات متوافقة مع المنتج (many-to-many) — مفيدة لقطع الغيار اللي تنفع أكتر من ماركة.
 * منفصلة عن products.brand_id (الماركة الأساسية).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_brand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'brand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brand');
    }
};
