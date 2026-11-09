<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6E — مهلة الدفع الأونلاين.
 *
 * payment_due_at: آخر ميعاد لإتمام الدفع للطلبات pending_payment. أمر مجدول
 * بيلغي اللي فات ميعاده ويرجّع المخزون. فاضي للدفع عند الاستلام (COD).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('payment_due_at')->nullable()->after('payment_status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_due_at');
        });
    }
};
