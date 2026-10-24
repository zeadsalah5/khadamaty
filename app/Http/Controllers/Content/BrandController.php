<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Content\Concerns\LinksServices;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandServicePage;
use Illuminate\View\View;

class BrandController extends PageController
{
    use LinksServices;

    protected string $modelClass = Brand::class;
    protected string $basePath = '/brands';
    protected string $indexTitle = 'الماركات';
    protected string $indexSubtitle = 'بنصلّح كل الماركات. اختر ماركتك واعرف الأعطال الشائعة والخدمات المناسبة ليها.';
    protected string $singleType = 'brand';

    /** هب الماركات — الماركات المنشورة بكروت نصّية (من غير لوجوهات رسمية). */
    public function index(): View
    {
        $brands = Brand::query()->published()->orderBy('name_ar')->get();

        $items = $brands->map(fn (Brand $b) => [
            'name' => $b->name_ar,
            'url'  => $this->basePath . '/' . $b->getRouteKey(),
        ])->all();

        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots(empty($items) ? 'noindex,follow' : 'index,follow');

        return view('pages.brands-index', [
            'title'        => $this->indexTitle,
            'subtitle'     => $this->indexSubtitle,
            'itemListName' => $this->indexTitle,
            'brands'       => $brands,
            'items'        => $items,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    public function show(Brand $brand): View
    {
        $path = $this->basePath . '/' . $brand->getRouteKey();

        $this->seo()
            ->title($brand->seo_title ?: ('صيانة وإصلاح تكييفات ' . $brand->name_ar))
            ->description($brand->seo_description ?: $brand->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->robots($brand->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        // الخدمات المناسبة للماركة (المنشورة بس) + المناطق المنشورة للروابط الداخلية.
        $services = $this->publishedServices($brand->suitable_services ?? []);
        $areas = Area::query()->published()->orderBy('name_ar')->get();

        // slugs الخدمات اللي ليها صفحة (ماركة × خدمة) منشورة — عشان الكارت يوصّل للصفحة المخصّصة.
        $brandServiceSlugs = BrandServicePage::query()->published()
            ->where('brand_id', $brand->id)
            ->with('service:id,slug')
            ->get()
            ->map(fn (BrandServicePage $p) => $p->service?->slug)
            ->filter()->values()->all();

        // منتجات المتجر التابعة للماركة (المنشورة) — قسم «منتجات {الماركة}».
        $products = $brand->products()->published()
            ->with(['primaryImage', 'category', 'brand'])
            ->orderByDesc('updated_at')->take(8)->get();

        return view('pages.brand', [
            'brand'        => $brand,
            'title'        => $brand->name_ar,
            'subtitle'     => $brand->excerpt,
            'canonical'    => $this->seo()->getCanonical(),
            'services'     => $services,
            'serviceIcons' => $this->serviceIcons(),
            'brandServiceSlugs' => $brandServiceSlugs,
            'areas'        => $areas,
            'products'     => $products,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $brand->name_ar, 'url' => $path],
            ],
        ]);
    }
}
