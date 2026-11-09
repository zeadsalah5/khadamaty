<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حقول المواصفات لكل قسم — يتدار من الداشبورد.
 * قيم المنتج بتتخزّن في products.specs (json) مفهرسة بـ key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')
                ->constrained('product_categories')->cascadeOnDelete();
            $table->string('key');          // مفتاح الماكينة (horsepower, gas_type...)
            $table->string('label_ar');     // الاسم المعروض (القدرة بالحصان...)
            $table->string('type')->default('text'); // text | number | select | boolean
            $table->json('options')->nullable();      // قيم select
            $table->string('unit')->nullable();       // وحدة اختيارية (حصان، سنة...)
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();

            $table->unique(['product_category_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attributes');
    }
};
