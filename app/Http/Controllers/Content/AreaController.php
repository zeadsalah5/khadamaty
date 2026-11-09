<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Content\Concerns\LinksServices;
use App\Http\Controllers\Content\Concerns\PreviewsDrafts;
use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use Illuminate\View\View;

class AreaController extends PageController
{
    use LinksServices, PreviewsDrafts;

    protected string $modelClass = Area::class;
    protected string $basePath = '/areas';
    protected string $indexTitle = 'مناطق الخدمة';
    protected string $indexSubtitle = '';
    protected string $singleType = 'area';

    public function __construct()
    {
        $this->indexSubtitle = 'بنغطّي ' . site('areas_label')
            . ' والمناطق المحيطة — اختر منطقتك واعرف الخدمات الأكثر طلباً فيها.';
    }

    /** هب المناطق — المناطق المنشورة (محتواها كامل) بكروت وروابط للتفاصيل. */
    public function index(): View
    {
        $areas = Area::query()->published()->orderBy('name_ar')->get();

        // عناصر الـ ItemList schema (روابط نسبية، المكوّن بيحوّلها لمطلقة).
        $items = $areas->map(fn (Area $a) => [
            'name' => $a->name_ar,
            'url'  => $this->basePath . '/' . $a->getRouteKey(),
        ])->all();

        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots(empty($items) ? 'noindex,follow' : 'index,follow');

        return view('pages.areas-index', [
            'title'        => $this->indexTitle,
            'subtitle'     => $this->indexSubtitle,
            'itemListName' => $this->indexTitle,
            'areas'        => $areas,
            'items'        => $items,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    public function show(Area $area): View
    {
        $path = $this->basePath . '/' . $area->getRouteKey();

        $this->seo()
            ->title($area->seo_title ?: ('صيانة تكييف ' . $area->name_ar . ' — فني تكييف قريب منك'))
            ->description($area->seo_description ?: $area->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->robots($area->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        $draft = $this->previewDraft($area);

        // الخدمات الأكثر طلباً في المنطقة (المنشورة بس) — بترتيب ما اتكتبت.
        $services = $this->publishedServices($area->top_services ?? []);

        // slugs الخدمات اللي ليها صفحة (منطقة × خدمة) منشورة — الكارت يوصّل للصفحة المخصّصة.
        $areaServiceSlugs = AreaServicePage::query()->published()
            ->where('area_id', $area->id)
            ->with('service:id,slug')
            ->get()
            ->map(fn (AreaServicePage $p) => $p->service?->slug)
            ->filter()->values()->all();

        // الماركات المنشورة للروابط الداخلية (المنطقة => الماركات).
        $brands = Brand::query()->published()->orderBy('name_ar')->get();

        return view('pages.area', [
            'draft'       => $draft,
            'area'        => $area,
            'title'       => $area->name_ar,
            'subtitle'    => $area->excerpt,
            'canonical'   => $this->seo()->getCanonical(),
            'services'    => $services,
            'serviceIcons' => $this->serviceIcons(),
            'areaServiceSlugs' => $areaServiceSlugs,
            'brands'      => $brands,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $area->name_ar, 'url' => $path],
            ],
        ]);
    }
}
