<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دور المستخدم في لوحة الإدارة (المرحلة C):
 * - admin: كل حاجة (المحتوى + الإعدادات + المستخدمين + الـ redirects + الميديا).
 * - editor: المحتوى فقط (من غير الإعدادات والمستخدمين).
 * الافتراضي admin عشان المستخدمين الحاليين ما يفقدوش صلاحية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
