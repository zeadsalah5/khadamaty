<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6C — بنود الطلب.
 *
 * كل بند بياخد نسخة (snapshot) من بيانات المنتج وقت الشراء:
 * الاسم، الكود، الحالة (جديد/مستعمل)، وسعر الوحدة — عشان لو المنتج اتعدّل أو
 * اتحذف بعد كده، الطلب يفضل محتفظ ببياناته الأصلية.
 * product_id بيفضل مربوط (nullOnDelete) للرجوع للمنتج لو لسه موجود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // snapshots وقت الشراء
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('condition')->default('new'); // new | used
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('qty');
            $table->decimal('line_total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
