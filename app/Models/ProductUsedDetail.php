<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUsedDetail extends Model
{
    protected $fillable = [
        'product_id', 'condition_grade', 'inspection', 'work_done',
        'manufacture_year', 'inspected_by', 'inspected_at',
    ];

    protected $casts = [
        'inspection'       => 'array',
        'manufacture_year' => 'integer',
        'inspected_at'     => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** قايمة فحص «كاملة» = فيها عنصر واحد على الأقل بعنوان item. */
    public function hasCompleteInspection(): bool
    {
        return collect($this->inspection ?? [])
            ->filter(fn ($row) => filled($row['item'] ?? null))
            ->isNotEmpty();
    }
}
