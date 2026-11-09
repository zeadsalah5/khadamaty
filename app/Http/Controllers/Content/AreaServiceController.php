<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Content\Concerns\LinksServices;
use App\Http\Controllers\Content\Concerns\PreviewsDrafts;
use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Service;
use Illuminate\View\View;

/**
 * صفحة (منطقة × خدمة) — K2 المناطق (SEO عضوي).
 * المسار: /areas/{area}/{service}. المنطقة بتتربط منشورة فقط (Publishable)،
 * والخدمة والصفحة بيتفلتروا للمنشور بس للزائر — الأدمن بيشوف المسوّدة كمعاينة.
 */
class AreaServiceController extends PageController
{
    use LinksServices, PreviewsDrafts;

    protected string $modelClass = AreaServicePage::class;
    protected string $basePath = '/areas';
    protected string $indexTitle = 'مناطق الخدمة';
    protected string $singleType = 'area-service';

    public function show(Area $area, string $service): View
    {
        $preview = can_preview_drafts();

        $serviceModel = Service::query()
            ->when(! $preview, fn ($q) => $q->published())
            ->where('slug', $service)->firstOrFail();

        $page = AreaServicePage::query()
            ->when(! $preview, fn ($q) => $q->published())
            ->where('area_id', $area->id)
            ->where('service_id', $serviceModel->id)
            ->firstOrFail();

        $path = $this->basePath . '/' . $area->slug . '/' . $serviceModel->slug;
        $areaPath = $this->basePath . '/' . $area->slug;
        $servicePath = '/services/' . $serviceModel->slug;

        $this->seo()
            ->title($page->seo_title ?: $page->h1)
            ->description($page->seo_description
                ?: ('صيانة ' . $serviceModel->name_ar . ' في ' . $area->name_ar . ' — فني تكييف قريب منك، والدفع بعد الشغل.'))
            ->canonical($path)
            ->ogType('website')
            ->robots($page->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        // مسوّدة لو الصفحة أو الخدمة أو المنطقة مش منشورة (معاينة أدمن).
        $draft = ! $page->is_published || ! $serviceModel->is_published || ! $area->is_published;
        if ($draft) {
            $this->seo()->noindex();
            request()->attributes->set('draft_preview', true);
        }

        return view('pages.area-service', [
            'draft'          => $draft,
            'page'           => $page,
            'area'           => $area,
            'service'        => $serviceModel,
            'title'          => $page->h1,
            'canonical'      => $this->seo()->getCanonical(),
            'serviceIcon'    => $serviceModel->icon ?: ($this->serviceIcons()[$serviceModel->slug] ?? 'maintenance'),
            'bookingService' => $serviceModel->booking_service,
            'areaPath'       => $areaPath,
            'servicePath'    => $servicePath,
            'breadcrumbs'    => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $area->name_ar, 'url' => $areaPath],
                ['label' => $serviceModel->name_ar, 'url' => $path],
            ],
        ]);
    }
}
