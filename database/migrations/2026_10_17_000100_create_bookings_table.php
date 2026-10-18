<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('area');
            $table->string('service');
            $table->unsignedSmallInteger('devices_count')->default(1);
            $table->string('preferred_time')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('new'); // new | contacted | done | cancelled
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
