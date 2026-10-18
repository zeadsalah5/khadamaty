<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * تقييم عميل.
 * - الحالة pending لحد ما صاحب الموقع يراجعه ويعتمده (approved).
 * - المعتمد بس هو اللي بيظهر على /reviews وبيدخل الـ schema.
 * - مفيش تقييمات مفبركة في الـ seeder — الجدول بيبدأ فاضي.
 */
class Review extends Model
{
    protected $fillable = [
        'name', 'area', 'service', 'rating', 'body', 'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /** التقييمات المعتمدة بس (اللي تظهر للعامة). */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
