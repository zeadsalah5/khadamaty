<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Content\Concerns\LinksServices;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class BlogController extends PageController
{
    use LinksServices;

    protected string $modelClass = Post::class;

    protected string $basePath = '/blog';

    protected string $indexTitle = 'المدونة';

    protected string $indexSubtitle = 'نصايح ومقالات عن صيانة التكييفات وأعطالها وتركيبها وتوفير الكهرباء.';

    protected string $singleType = 'post';

    protected string $nameField = 'title_ar';

    protected int $perPage = 9;

    /** هب المدونة — المقالات المنشورة الأحدث أولاً + تصنيفات + pagination. */
    public function index(): View
    {
        $posts = Post::query()->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->paginate($this->perPage);

        // canonical: الصفحة الأولى /blog، والصفحات بعدها rel canonical لنفسها (?page=N).
        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->paginatedCanonical($this->basePath, $posts))
            ->robots($posts->total() > 0 ? 'index,follow' : 'noindex,follow');

        return view('pages.blog-index', [
            'title' => $this->indexTitle,
            'subtitle' => $this->indexSubtitle,
            'posts' => $posts,
            'categories' => $this->categoriesWithCounts(),
            'activeCat' => null,
            'items' => $this->itemList($posts),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    /** صفحة تصنيف — مقالات التصنيف المنشورة + pagination. فاضي => noindex. */
    public function category(PostCategory $category): View
    {
        $path = $this->basePath.'/category/'.$category->getRouteKey();

        $posts = $category->publishedPosts()
            ->with('category')
            ->paginate($this->perPage);

        $title = $category->name_ar;
        $subtitle = $category->excerpt ?: ('مقالات عن '.$category->name_ar.' للتكييفات.');

        $this->seo()
            ->title('مقالات '.$title)
            ->description($subtitle)
            ->canonical($this->paginatedCanonical($path, $posts))
            ->robots($posts->total() > 0 ? 'index,follow' : 'noindex,follow');

        return view('pages.blog-index', [
            'title' => $title,
            'subtitle' => $subtitle,
            'posts' => $posts,
            'categories' => $this->categoriesWithCounts(),
            'activeCat' => $category,
            'items' => $this->itemList($posts),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $title, 'url' => $path],
            ],
        ]);
    }

    /** صفحة المقال — متن، فهرس محتويات، ذات صلة، روابط خدمات، CTA حجز، schema. */
    public function show(Post $post): View
    {
        $post->loadMissing('category');
        $path = $this->basePath.'/'.$post->getRouteKey();

        $this->seo()
            ->title($post->seo_title ?: $post->title_ar)
            ->description($post->seo_description ?: $post->excerpt)
            ->canonical($path)
            ->ogType('article')
            ->ogImage($post->cover ? 'images/blog/'.$post->cover : null)
            ->robots($post->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        $crumbs = [
            ['label' => 'الرئيسية', 'url' => '/'],
            ['label' => $this->indexTitle, 'url' => $this->basePath],
        ];
        if ($post->category) {
            $crumbs[] = [
                'label' => $post->category->name_ar,
                'url' => $this->basePath.'/category/'.$post->category->slug,
            ];
        }
        $crumbs[] = ['label' => $post->title_ar, 'url' => $path];

        return view('pages.post', [
            'post' => $post,
            'title' => $post->title_ar,
            'subtitle' => $post->excerpt,
            'canonical' => $this->seo()->getCanonical(),
            'content' => $post->content(),
            'related' => $post->relatedPosts(3),
            'services' => $this->publishedServices($post->suitable_services ?? []),
            'serviceIcons' => $this->serviceIcons(),
            'breadcrumbs' => $crumbs,
        ]);
    }

    /** canonical لصفحة مفهرسة: /path للأولى، و/path?page=N لما بعدها (rel canonical لنفسها). */
    protected function paginatedCanonical(string $path, LengthAwarePaginator $posts): string
    {
        return $posts->currentPage() > 1
            ? $path.'?page='.$posts->currentPage()
            : $path;
    }

    /** عناصر ItemList schema من صفحة المقالات الحالية (روابط نسبية). */
    protected function itemList(LengthAwarePaginator $posts): array
    {
        return collect($posts->items())->map(fn (Post $p) => [
            'name' => $p->title_ar,
            'url' => $this->basePath.'/'.$p->getRouteKey(),
        ])->all();
    }

    /** التصنيفات مرتّبة + عدد المقالات المنشورة في كل واحد (للتنقّل). */
    protected function categoriesWithCounts()
    {
        return PostCategory::query()
            ->withCount(['posts as published_count' => fn ($q) => $q->where('is_published', true)])
            ->orderBy('sort')
            ->orderBy('name_ar')
            ->get();
    }
}
