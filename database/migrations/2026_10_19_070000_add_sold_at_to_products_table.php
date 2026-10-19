<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المرحلة 6B — تاريخ البيع للمنتجات (المستعمل خصوصاً).
 * المنتج المباع (status=sold) بيفضل صفحته شغّالة وتعرض "تم البيع"،
 * وبعد ٦٠ يوم من sold_at بيبقى noindex وبيخرج من السايت ماب.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('sold_at')->nullable()->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sold_at');
        });
    }
};
