<?php

namespace App\Models;

use App\Models\Concerns\HasReviewWorkflow;
use App\Models\Concerns\Publishable;
use App\Support\ArticleContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Post extends Model
{
    use HasReviewWorkflow, Publishable;

    protected $fillable = [
        'slug', 'title_ar', 'excerpt', 'body', 'cover', 'category_id',
        'suitable_services', 'seo_title', 'seo_description',
        'is_published', 'published_at',
        'content_status', 'reviewed_by', 'reviewed_at',
        'similarity_ack_by', 'similarity_ack_at', 'similarity_pct',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'published_at'      => 'datetime',
        'reviewed_at'       => 'datetime',
        'similarity_ack_at' => 'datetime',
        'suitable_services' => 'array',
    ];

    /** الحقول اللي بتتفحص للـ TODO وتدخل في حساب التشابه. */
    public function reviewableTextFields(): array
    {
        return ['title_ar', 'excerpt', 'body', 'seo_title', 'seo_description'];
    }

    public function reviewTypeLabel(): string
    {
        return 'مقال';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    /**
     * محتوى المقال كامل كفاية إنه يتنشر؟
     * لازم: عنوان + ملخّص + متن + تصنيف. الناقص بيفضل مسوّدة => 404 + noindex + بره السايت ماب.
     */
    public function hasCompleteContent(): bool
    {
        return filled($this->title_ar)
            && filled($this->excerpt)
            && filled($this->body)
            && filled($this->category_id);
    }

    /** المحتوى المعالَج: HTML + فهرس محتويات من H2 + وقت القراءة. */
    public function content(): ArticleContent
    {
        return ArticleContent::fromMarkdown((string) $this->body);
    }

    /**
     * مقالات ذات صلة: نفس التصنيف أولاً (الأحدث)، وتكمّل من أي تصنيف لو ناقصة.
     */
    public function relatedPosts(int $limit = 3): Collection
    {
        $base = static::query()->published()->where('id', '!=', $this->id);

        $sameCat = (clone $base)->where('category_id', $this->category_id)
            ->orderByDesc('published_at')->limit($limit)->get();

        if ($sameCat->count() >= $limit) {
            return $sameCat;
        }

        $fill = (clone $base)->whereNotIn('id', $sameCat->pluck('id'))
            ->orderByDesc('published_at')->limit($limit - $sameCat->count())->get();

        return $sameCat->concat($fill);
    }
}
