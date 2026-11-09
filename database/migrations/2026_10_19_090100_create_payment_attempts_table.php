<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6E — سجلّ محاولات الدفع (callbacks البوابة + الاسترجاع اليدوي).
 *
 * - سجل لكل حدث دفع (زي ما طلب التكليف): منه نراجع ونتبع أي محاولة.
 * - gateway_txn_id فريد => منع تكرار تطبيق نفس المعاملة (idempotency).
 * - hmac_valid: نسجّل حتى الكول باكس المرفوضة بتوقيع غلط (للتدقيق) من غير ما
 *   نغيّر حالة الطلب.
 * - payload: الحمولة الخام من البوابة (Paymob بترجّع آخر 4 أرقام والشبكة بس —
 *   مفيش بيانات كروت حساسة).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('gateway')->default('paymob');
            $table->string('gateway_txn_id')->nullable()->unique(); // null بيسمح بتكرار (المرفوضة/اليدوية)
            $table->string('status'); // paid | failed | refunded | rejected
            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('hmac_valid')->default(false);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
