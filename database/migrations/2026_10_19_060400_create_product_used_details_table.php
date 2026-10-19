<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تفاصيل الأجهزة المستعملة (1:1). كل جهاز مستعمل صف منتج مستقل بكمية 1.
 * inspection = قايمة فحص json: عناصر {item, ok, note}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_used_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->string('condition_grade')->nullable(); // ممتاز | جيد جداً | جيد | مقبول
            $table->json('inspection')->nullable();
            $table->text('work_done')->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->string('inspected_by')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_used_details');
    }
};
