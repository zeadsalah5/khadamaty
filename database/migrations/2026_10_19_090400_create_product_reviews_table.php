<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6D — تقييمات المنتجات (منفصلة عن تقييمات الخدمة في جدول reviews).
 *
 * - العميل يقدر يقيّم بس منتج اشتراه واتسلّم (طلب حالته delivered) — عبر order_item_id.
 * - كل تقييم بيبدأ pending لحد ما صاحب الموقع يراجعه ويعتمده (approved).
 * - المعتمد بس هو اللي بيظهر على صفحة المنتج وبيدخل schema (AggregateRating/Review).
 * - تقييم واحد لكل عميل لكل منتج (unique) — التعديل بيرجّعه pending تاني.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // بند الطلب اللي أثبت الشراء (للتحقق + «شراء موثّق»).
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();

            $table->unsignedTinyInteger('rating'); // 1..5
            $table->string('title')->nullable();
            $table->text('body');
            // pending = مستني مراجعة | approved = معتمد ويظهر | rejected = مرفوض
            $table->string('status')->default('pending')->index();

            $table->timestamps();

            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
