<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حقول صفحة التركيب (Section 4) — تتخفى لو فاضية:
 * - excludes: «الخدمة ميشملش إيه».
 * - cost_factors: «إيه اللي بيأثر على التكلفة».
 * (المدة = duration_note الموجود، وخطوات «إزاي بنشتغل» = steps الموجود.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->json('excludes')->nullable()->after('includes');
            $table->json('cost_factors')->nullable()->after('excludes');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['excludes', 'cost_factors']);
        });
    }
};
