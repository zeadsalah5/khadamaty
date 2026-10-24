<?php

namespace App\Http\Controllers\Content;

use App\Models\BrandServicePage;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends PageController
{
    protected string $modelClass = Service::class;
    protected string $basePath = '/services';
    protected string $indexTitle = 'خدماتنا';
    protected string $indexSubtitle = 'صيانة وتركيب وشحن وإصلاح التكييفات والأجهزة — بأيدي فنيين متخصصين.';
    protected string $singleType = 'service';

    /** خريطة أيقونة لكل خدمة (slug => مفتاح أيقونة في service-icon). */
    protected array $icons = [
        'ac-maintenance'         => 'maintenance',
        'ac-repair'              => 'repair',
        'ac-cleaning'            => 'cleaning',
        'ac-installation'        => 'install',
        'ac-gas-refill'          => 'freon',
        'ac-leak-repair'         => 'diagnose',
        'split-ac-maintenance'   => 'maintenance',
        'central-ac-maintenance' => 'contract',
        'ac-piping-installation' => 'pipes',
    ];

    /**
     * خريطة الخدمة => نوع الخدمة في فورم الحجز (لازم تطابق config/site.booking_services).
     * بتستخدم في زرار "احجز" عشان الخدمة تتحدد تلقائياً في الفورم.
     */
    protected array $bookingMap = [
        'ac-maintenance'         => 'صيانة دورية',
        'ac-repair'              => 'إصلاح عطل',
        'ac-cleaning'            => 'غسيل وتنظيف',
        'ac-installation'        => 'تركيب تكييف',
        'ac-gas-refill'          => 'شحن فريون',
        'ac-leak-repair'         => 'إصلاح عطل',
        'split-ac-maintenance'   => 'صيانة دورية',
        'central-ac-maintenance' => 'صيانة دورية',
        'ac-piping-installation' => 'تركيب تكييف',
    ];

    /** صفحة الخدمات — هب غني بيعرض الخدمات المنشورة من الداتابيز. */
    public function index(): View
    {
        $services = Service::query()->published()
            ->orderByDesc('updated_at')
            ->get();

        // عناصر الـ ItemList schema (روابط نسبية، المكوّن بيحوّلها لمطلقة).
        $items = $services->map(fn (Service $s) => [
            'name' => $s->name_ar,
            'url'  => $this->basePath . '/' . $s->getRouteKey(),
        ])->all();

        // فهرس فاضي => noindex,follow ومش بيدخل السايت ماب.
        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots(empty($items) ? 'noindex,follow' : 'index,follow');

        return view('pages.services-index', [
            'title'        => $this->indexTitle,
            'subtitle'     => $this->indexSubtitle,
            'itemListName' => $this->indexTitle,
            'services'     => $services,
            'items'        => $items,
            'icons'        => $this->icons,
            'breadcrumbs'  => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    public function show(Service $service): View
    {
        $path = $this->basePath . '/' . $service->getRouteKey();

        // عنوان/وصف الـ SEO: تجاوز من الداتابيز لو موجود، وإلا الاسم/الـ excerpt.
        $this->seo()
            ->title($service->seo_title ?: $service->name_ar)
            ->description($service->seo_description ?: $service->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->robots($service->hasCompleteContent() ? 'index,follow' : 'noindex,follow');

        // الخدمات المرتبطة (المنشورة بس).
        $related = collect($service->related_services ?? [])
            ->filter()
            ->pipe(fn ($slugs) => $slugs->isEmpty()
                ? collect()
                : Service::query()->published()->whereIn('slug', $slugs->all())->get());

        // المناطق المنشورة للروابط الداخلية + areaServed في الـ schema.
        $areas = (array) site('areas', []);

        // صفحات (ماركة × خدمة) المنشورة لنفس الخدمة — قسم «حسب الماركة» (روابط داخلية).
        $brandPages = BrandServicePage::query()->published()
            ->where('service_id', $service->id)
            ->with('brand:id,slug,name_ar')
            ->get()
            ->filter(fn (BrandServicePage $p) => $p->brand !== null)
            ->map(fn (BrandServicePage $p) => [
                'name' => $p->brand->name_ar,
                'url'  => '/brands/' . $p->brand->slug . '/' . $service->slug,
            ])->values();

        return view('pages.service', [
            'service'        => $service,
            'title'          => $service->name_ar,
            'subtitle'       => $service->excerpt,
            'canonical'      => $this->seo()->getCanonical(),
            'icon'           => $service->icon ?: ($this->icons[$service->slug] ?? 'maintenance'),
            'bookingService' => $service->booking_service ?: ($this->bookingMap[$service->slug] ?? null),
            'related'        => $related,
            'relatedIcons'   => $this->icons,
            'areas'          => $areas,
            'brandPages'     => $brandPages,
            'breadcrumbs'    => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
                ['label' => $service->name_ar, 'url' => $path],
            ],
        ]);
    }
}
