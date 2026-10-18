<?php

namespace App\Http\Controllers\Content;

use App\Models\Post;
use Illuminate\View\View;

class BlogController extends PageController
{
    protected string $modelClass = Post::class;
    protected string $basePath = '/blog';
    protected string $indexTitle = 'المدونة';
    protected string $indexSubtitle = 'نصايح ومقالات عن صيانة التكييفات والأجهزة.';
    protected string $singleType = 'post';
    protected string $nameField = 'title_ar';

    public function index(): View
    {
        return $this->renderIndex();
    }

    public function show(Post $post): View
    {
        return $this->renderShow($post);
    }
}
