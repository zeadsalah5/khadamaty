<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Support\ArticleContent;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'title', 'body', 'seo_title', 'seo_description',
        'show_in_footer', 'is_published',
    ];

    protected $casts = [
        'show_in_footer' => 'boolean',
        'is_published'   => 'boolean',
    ];

    /** المتن المعالَج (HTML من Markdown). */
    public function content(): ArticleContent
    {
        return ArticleContent::fromMarkdown((string) $this->body);
    }
}
