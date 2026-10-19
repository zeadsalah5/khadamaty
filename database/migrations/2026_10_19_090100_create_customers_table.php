<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6D — حسابات العملاء (منفصلة تماماً عن جدول users بتاع الأدمن).
 *
 * - الدخول/التسجيل اختياري بالموبايل أو الإيميل — واحد منهم على الأقل (enforced app-level).
 * - الموبايل بيتخزّن بعد تطبيع (شيل المسافات) عشان الربط بالطلبات القديمة يطابق.
 * - مفيش OTP في 6D (مفيش SMS gateway) — الدخول بباسورد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
