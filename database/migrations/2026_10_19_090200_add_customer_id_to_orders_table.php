<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6D — ربط الطلب بحساب عميل (اختياري).
 *
 * - الطلب كضيف بيفضل customer_id = null (الضيف شغال زي ما هو).
 * - عند التسجيل/الدخول بتتربط الطلبات القديمة اللي موبايلها يطابق موبايل الحساب.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('status')
                ->constrained('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
