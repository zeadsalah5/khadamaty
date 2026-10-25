<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Content\Concerns\LinksServices;
use App\Http\Controllers\Content\Concerns\PreviewsDrafts;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Service;
use Illuminate\View\View;

/**
 * صفحة (ماركة × خدمة) — المرحلة K1 (SEO عضوي).
 * المسار: /brands/{brand}/{service}. الماركة بتتربط منشورة فقط (Publishable)،
 * والخدمة والصفحة بيتفلتروا للمنشور بس — غير كده 404.
 */
class BrandServiceController extends PageController
{
    use LinksServices, PreviewsDrafts;

    protected string $modelClass = BrandServicePage::class;
    protected string $basePath = '/brands';
    protected string $indexTitle = 'الماركات';
    protected string $singleType = 'brand-service';

    public function show(Brand $brand, string $service): View
    {
        $preview = can_preview_drafts();

        // الخدمة لازم تكون منشورة (أو معاينة أدمن)، وإلا 404.
        $serviceModel = Service::query()
            ->when(! $preview, fn ($q) => $q->published())
            ->where('slug', $service)->firstOrFail();

        // صفحة التقاطع لازم تكون موجودة (ومنشورة للزائر العادي)، وإلا 404.
        $page = BrandServicePage::query()
            ->when(! $preview, fn ($q) => $q->published())
            ->where('brand_id', $brand->id)
            ->where('service_id', $serviceModel->id)
            ->firstOrFail();

        $path = $this->basePath . '/' . $brand->slug . '/' . $serviceModel->slug;
        $brandPath = $this->basePath . '/' . $brand->slug;
        $servicePath = '/services/' . $serviceModel->slug;

        $this->seo()
            ->title($page->seo_title ?: $page->h1)
            ->description($page->seo_description ?: $serviceModel->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->robots($page->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        // مسوّدة لو الصفحة أو الخدمة أو الماركة مش منشورة (معاينة أدمن).
        $draft = ! $page->is_published || ! $serviceModel->is_published || ! $brand->is_published;
        if ($draft) {
            $this->seo()->noindex();
            request()->attributes->set('draft_preview', true);
        }

        // المناطق المنشورة: روابط داخلية + areaServed في الـ schema.
        $areas = Area::query()->published()->orderBy('name_ar')->get();

        return view('pages.brand-service', [
            'draft'          => $draft,
            'page'           => $page,
            'brand'          => $brand,
            'service'        => $serviceModel,
            'title'          => $page->h1,
            'canonical'      => $this->seo()->getCanonical(),
            'serviceIcon'    => $serviceModel->icon ?: ($this->serviceIcons()[$serviceModel->slug] ?? 'maintenance'),
            'bookingService' => $serviceModel->booking_service,
            'areas'          => $areas,
            'brandPath'      => $brandPath,
            'servicePath'    => $servicePath,
            'breadcrumbs'    => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $brand->name_ar, 'url' => $brandPath],
                ['label' => $serviceModel->name_ar, 'url' => $path],
            ],
        ]);
    }
}
