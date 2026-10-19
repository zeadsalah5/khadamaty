<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6C — رسوم التوصيل على مستوى المنطقة (للدفع عند الاستلام).
 * - delivers: المنطقة داخل نطاق التوصيل؟ (false => "غير متاح حالياً" في الـ checkout).
 * - delivery_fee: رسوم التوصيل بالجنيه (0 = توصيل مجاني لو delivers=true).
 * - delivery_note: ملاحظة اختيارية تظهر للعميل (مثال: "التوصيل خلال ٢٤ ساعة").
 * كلها إضافة فقط ومبتمسّش بيانات المناطق الموجودة.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->boolean('delivers')->default(false)->after('eta_note')->index();
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivers');
            $table->string('delivery_note')->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['delivers', 'delivery_fee', 'delivery_note']);
        });
    }
};
