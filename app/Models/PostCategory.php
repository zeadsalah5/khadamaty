<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * تصنيف المدونة (صيانة/أعطال/شراء وتركيب/توفير كهرباء).
 * الـ route binding بالـ slug. التصنيف متاح دايماً؛ صفحة التصنيف الفاضية
 * (من غير مقالات منشورة) بتبقى noindex وبره السايت ماب (بيتظبط في الكنترولر).
 */
class PostCategory extends Model
{
    protected $fillable = ['slug', 'name_ar', 'excerpt', 'sort'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    /** المقالات المنشورة لهذا التصنيف، الأحدث أولاً. */
    public function publishedPosts(): HasMany
    {
        return $this->posts()
            ->where('is_published', true)
            ->orderByDesc('published_at');
    }
}
