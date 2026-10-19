<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M4 — فهارس على أعمدة الاستعلام:
 *  - orders.customer_phone: التتبّع بالرقم + الموبايل.
 *  - products.price / products.sale_price: فلتر وترتيب السعر في المتجر.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_phone', 'orders_customer_phone_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('price', 'products_price_index');
            $table->index('sale_price', 'products_sale_price_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_phone_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_price_index');
            $table->dropIndex('products_sale_price_index');
        });
    }
};
