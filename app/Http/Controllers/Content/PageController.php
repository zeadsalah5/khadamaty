<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

/**
 * أساس مشترك لصفحات المحتوى (فهرس + عنصر مفرد).
 * الصفحات placeholder خفيفة بتضبط الـ SEO و الـ schema المناسب.
 */
abstract class PageController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelClass;

    protected string $basePath;      // مثال: '/services'
    protected string $indexTitle;    // عنوان الفهرس
    protected string $indexSubtitle; // وصف الفهرس
    protected string $singleType;    // service|post|product|area|appliance|brand|vendor
    protected string $nameField = 'name_ar';

    /** عنوان الـ SEO لصفحة الفهرس (لو فاضي بيستخدم indexTitle). الـ H1 ومسار التصفّح بيفضلوا indexTitle. */
    protected string $indexSeoTitle = '';

    /**
     * قسم محتواه لسه خفيف/خارج تركيز الموقع (زي التجار والأجهزة غير التكييف):
     * صفحاته بتفضل شغّالة للزائر لكن noindex وبره السايت ماب لحد ما يتبني له محتوى حقيقي.
     */
    protected bool $thinSection = false;

    protected function indexSeoTitle(): string
    {
        return $this->indexSeoTitle !== '' ? $this->indexSeoTitle : $this->indexTitle;
    }

    /** عنصر اسمه لسه فيه TODO (بيانات تجريبية) => مايظهرش للزائر. */
    protected function isPlaceholderName(?string $name): bool
    {
        return stripos((string) $name, 'TODO') !== false;
    }

    protected function seo(): Seo
    {
        return app(Seo::class);
    }

    /** صفحة الفهرس. */
    protected function renderIndex(): View
    {
        $models = $this->modelClass::query()->published()->orderByDesc('updated_at')->get()
            ->reject(fn (Model $m) => $this->isPlaceholderName($m->{$this->nameField}))
            ->values();

        $items = $models->map(fn (Model $m) => [
            'name'    => $m->{$this->nameField},
            'url'     => $this->basePath . '/' . $m->getRouteKey(),
            'excerpt' => $m->excerpt ?? null,
        ])->all();

        // فهرس فاضي => noindex,follow ومش بيدخل السايت ماب.
        $this->seo()
            ->title($this->indexSeoTitle())
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots(empty($items) || $this->thinSection ? 'noindex,follow' : 'index,follow');

        return view('pages.listing', [
            'title'        => $this->indexTitle,
            'subtitle'     => $this->indexSubtitle,
            'itemListName' => $this->indexTitle,
            'items'        => $items,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    /** صفحة عنصر مفرد (الموديل متربط منشور فقط، غير كده 404). */
    protected function renderShow(Model $model): View
    {
        $name = $model->{$this->nameField};
        abort_if($this->isPlaceholderName($name) && ! can_preview_drafts(), 404);
        $desc = $model->excerpt ?? null;
        $path = $this->basePath . '/' . $model->getRouteKey();

        $this->seo()
            ->title($name)
            ->description($desc)
            ->canonical($path)
            ->ogType($this->singleType === 'post' ? 'article' : 'website')
            ->robots($this->thinSection ? 'noindex,follow' : 'index,follow');

        return view('pages.single', [
            'title'       => $name,
            'subtitle'    => $desc,
            'type'        => $this->singleType,
            'model'       => $model,
            'canonical'   => $this->seo()->getCanonical(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $name, 'url' => $path],
            ],
        ]);
    }
}
