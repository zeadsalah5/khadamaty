<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * بند طلب — نسخة (snapshot) من بيانات المنتج وقت الشراء.
 * السعر هنا هو اللي اتحسب عليه الطلب (server-side)، مش بيتأثر بأي تعديل لاحق للمنتج.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'name', 'sku', 'condition', 'unit_price', 'qty', 'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'qty'        => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
