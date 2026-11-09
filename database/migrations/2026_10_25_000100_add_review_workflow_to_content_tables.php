<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سير مراجعة موحّد لكل أنواع صفحات المحتوى (خدمات/ماركات/مناطق/مقالات/ماركة×خدمة).
 * القاعدة: مفيش صفحة تتنشر قبل مراجعة الفني وصفر TODO (بيتفرض في الكود — شوف
 * App\Models\Concerns\HasReviewWorkflow و GuardsReviewWorkflow).
 * - content_status: draft | needs_tech_review | ready (الافتراضي draft).
 * - reviewed_by / reviewed_at: مين اعتمد المحتوى وإمتى.
 * - similarity_ack_* : تسجيل تأكيد النشر رغم تشابه > 30% مع صفحة منشورة من نفس النوع.
 * ملاحظة: الأعمدة FK منطقياً لـ users لكن من غير constrained() عشان ALTER على SQLite.
 */
return new class extends Migration
{
    /** الجداول اللي بياخدوا سير المراجعة. */
    protected array $tables = [
        'services',
        'brands',
        'areas',
        'posts',
        'brand_service_pages',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('content_status')->default('draft')->after('is_published');
                $table->unsignedBigInteger('reviewed_by')->nullable()->after('content_status');
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                $table->unsignedBigInteger('similarity_ack_by')->nullable()->after('reviewed_at');
                $table->timestamp('similarity_ack_at')->nullable()->after('similarity_ack_by');
                $table->unsignedTinyInteger('similarity_pct')->nullable()->after('similarity_ack_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn([
                    'content_status', 'reviewed_by', 'reviewed_at',
                    'similarity_ack_by', 'similarity_ack_at', 'similarity_pct',
                ]);
            });
        }
    }
};
