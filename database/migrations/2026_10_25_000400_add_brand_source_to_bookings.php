<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حقول مخفية في فورم الحجز (Section 4):
 * - brand: الماركة اللي العميل جه منها (لو الحجز اتفتح من صفحة ماركة/ماركة×خدمة).
 * - source_url: رابط الصفحة اللي اتبعت منها الطلب (لمعرفة مصدر الحجز).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('service');
            $table->string('source_url')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['brand', 'source_url']);
        });
    }
};
