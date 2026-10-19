<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * عنصر مفضلة لعميل مسجّل (المرحلة 6D).
 * المفضلة للضيف بتتخزّن في الـ session؛ ده التخزين الدائم للمسجّل.
 */
class Wishlist extends Model
{
    protected $fillable = ['customer_id', 'product_id'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
