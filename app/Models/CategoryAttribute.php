<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryAttribute extends Model
{
    protected $fillable = [
        'product_category_id', 'key', 'label_ar', 'type',
        'options', 'unit', 'is_required', 'position',
    ];

    protected $casts = [
        'options'     => 'array',
        'is_required' => 'boolean',
        'position'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
