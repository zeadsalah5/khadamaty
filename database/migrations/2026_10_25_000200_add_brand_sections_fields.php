<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أقسام إضافية لصفحة الماركة (Section 3) — تتخفى لو فاضية، والمحتوى من صاحب الموقع:
 * - warranty_agent_note: شروط ضمان الوكيل (نص).
 * - error_codes: أكواد الأعطال [{code, meaning, safe_check, when_call}] مع ملاحظة
 *   «قد يختلف المعنى حسب الموديل».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->longText('warranty_agent_note')->nullable()->after('faqs');
            $table->json('error_codes')->nullable()->after('warranty_agent_note');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['warranty_agent_note', 'error_codes']);
        });
    }
};
