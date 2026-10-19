<?php

namespace App\Http\Controllers\Content;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends PageController
{
    protected string $modelClass = Product::class;
    protected string $basePath = '/shop';
    protected string $indexTitle = 'المتجر وقطع الغيار';
    protected string $indexSubtitle = 'تكييفات جديدة ومستعملة وقطع غيار ومستلزمات.';
    protected string $singleType = 'product';

    public function index(): View
    {
        $products = Product::query()->published()
            ->with(['primaryImage', 'category'])
            ->orderByDesc('updated_at')->get();

        // فهرس فاضي => noindex,follow ومش بيدخل السايت ماب.
        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots($products->isEmpty() ? 'noindex,follow' : 'index,follow');

        $items = $products->map(fn (Product $p) => [
            'name' => $p->name_ar,
            'url'  => $this->basePath . '/' . $p->getRouteKey(),
        ])->all();

        return view('pages.shop-index', [
            'title'        => $this->indexTitle,
            'subtitle'     => $this->indexSubtitle,
            'itemListName' => $this->indexTitle,
            'products'     => $products,
            'items'        => $items,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category.attributes', 'brand', 'images', 'primaryImage', 'usedDetails']);

        $path = $this->basePath . '/' . $product->getRouteKey();

        $this->seo()
            ->title($product->seo_title ?: $product->name_ar)
            ->description($product->seo_description ?: $product->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->robots('index,follow');

        return view('pages.single', [
            'title'       => $product->name_ar,
            'subtitle'    => $product->excerpt,
            'type'        => $this->singleType,
            'model'       => $product,
            'canonical'   => $this->seo()->getCanonical(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $product->name_ar, 'url' => $path],
            ],
        ]);
    }
}
