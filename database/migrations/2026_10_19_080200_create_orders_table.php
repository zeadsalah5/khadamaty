<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6C — جدول الطلبات (الدفع عند الاستلام).
 *
 * - order_number: رقم طلب مقروء وفريد (مثال: KH-261019-4821) — بيستخدم في التتبع.
 * - الحالات: new | confirmed | out_for_delivery | delivered | cancelled | returned.
 * - area_id + area_name: ربط بالمنطقة + نسخة من الاسم وقت الطلب (snapshot).
 * - المبالغ (subtotal/delivery_fee/total) كلها snapshot بتتحسب server-side.
 * - payment_method ثابت 'cod' في 6C (abstraction جاهز لأونلاين في 6E).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('status')->default('new')->index();

            // بيانات العميل (بدون إلزام تسجيل)
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('address');
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('area_name'); // snapshot لاسم المنطقة وقت الطلب
            $table->text('notes')->nullable();

            // المبالغ (server-side)
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // الدفع
            $table->string('payment_method')->default('cod'); // cod | (online لاحقاً)
            $table->string('payment_status')->default('pending'); // pending | paid | refunded

            // إدارة
            $table->text('admin_notes')->nullable();
            $table->string('ip', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
