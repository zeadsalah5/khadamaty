<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إعادة التوجيه (المرحلة C3) — تُدار من الداشبورد بدل config/redirects.php.
 * from: المسار القديم بدون / في الأول. to: المسار الجديد. status: 301/302.
 * بتتدمج مع config/redirects.php (الداتابيز بتاخد الأولوية).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from')->unique();
            $table->string('to');
            $table->unsignedSmallInteger('status')->default(301);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
