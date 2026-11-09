<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Seo;
use Illuminate\View\View;

/**
 * صفحة ثابتة (سياسات/تعريفية) — متن Markdown يتعدّل من الداشبورد.
 * غير المنشورة بترجّع 404 (Publishable).
 */
class StaticPageController extends Controller
{
    public function show(Page $page): View
    {
        $path = '/pages/' . $page->getRouteKey();

        app(Seo::class)
            ->title($page->seo_title ?: $page->title)
            ->description($page->seo_description)
            ->canonical($path)
            ->robots('index,follow');

        return view('pages.static-page', [
            'page'        => $page,
            'content'     => $page->content(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $page->title, 'url' => $path],
            ],
        ]);
    }
}
