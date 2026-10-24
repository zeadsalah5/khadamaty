<?php

namespace App\Http\Controllers;

use App\Models\Appliance;
use App\Models\Area;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Post;
use App\Models\Product;
use App\Models\Service;
use App\Models\Vendor;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * خريطة الأنواع: المفتاح = اسم الساب-سايت ماب، والقيمة = [الموديل, المسار].
     * بس العناصر المنشورة (published) بتدخل.
     */
    protected function types(): array
    {
        return [
            'services'   => [Service::class,   '/services'],
            'appliances' => [Appliance::class, '/appliances'],
            'areas'      => [Area::class,      '/areas'],
            'brands'     => [Brand::class,     '/brands'],
            'blog'       => [Post::class,      '/blog'],
            'products'   => [Product::class,   '/shop'],
            'vendors'    => [Vendor::class,    '/vendors'],
        ];
    }

    protected function base(): string
    {
        return rtrim(app(Seo::class)->baseUrl(), '/');
    }

    // مدة كاش نتايج الاستعلامات (ثواني). SEO مش محتاج lastmod لحظي.
    protected int $ttl = 600;

    /** sitemap index — بيشاور بس على الساب-سايت مابس اللي فيها روابط فعلاً. */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index:' . md5($this->base()), $this->ttl, fn () => $this->buildIndex());

        return $this->xml($xml);
    }

    protected function buildIndex(): string
    {
        $base = $this->base();
        $sitemaps = [];

        // صفحة pages: دايماً فيها الرئيسية على الأقل.
        $sitemaps[] = [
            'loc'     => $base . '/sitemap-pages.xml',
            'lastmod' => $this->pagesLastmod(),
        ];

        foreach ($this->types() as $name => [$model, $path]) {
            $query = $model::query()->published();
            if ($query->count() === 0) {
                continue; // مفيش منشور => مانعملش ساب-سايت ماب فاضي
            }
            $sitemaps[] = [
                'loc'     => $base . "/sitemap-{$name}.xml",
                'lastmod' => $this->iso($query->max('updated_at')),
            ];
        }

        // صفحات (ماركة × خدمة): بتظهر بس لو فيه صفحة واحدة مكتملة ومنشورة على الأقل.
        $brandServices = $this->indexableBrandServices();
        if ($brandServices->isNotEmpty()) {
            $sitemaps[] = [
                'loc'     => $base . '/sitemap-brand-services.xml',
                'lastmod' => $this->iso($brandServices->max('updated_at')),
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($sitemaps as $s) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . e($s['loc']) . '</loc>' . "\n";
            if ($s['lastmod']) {
                $xml .= '    <lastmod>' . $s['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '  </sitemap>' . "\n";
        }
        $xml .= '</sitemapindex>' . "\n";

        return $xml;
    }

    /** الصفحات الثابتة + فهارس الأقسام اللي فيها محتوى منشور (indexable فقط). */
    public function pages(): Response
    {
        $xml = Cache::remember('sitemap:pages:' . md5($this->base()), $this->ttl, fn () => $this->buildPages());

        return $this->xml($xml);
    }

    protected function buildPages(): string
    {
        $base = $this->base();

        $urls = [[
            'loc'     => $base . '/',
            'lastmod' => $this->pagesLastmod(),
        ]];

        // الصفحات التعريفية/الوظيفية الثابتة (indexable). صفحات "قريباً"/الفاضية
        // (زي /reviews وهي فاضية) مش مدرجة هنا عمداً عشان تفضل noindex.
        foreach (['/about', '/prices', '/faq', '/contact', '/request-service', '/diagnose'] as $staticPath) {
            $urls[] = [
                'loc'     => $base . $staticPath,
                'lastmod' => $this->pagesLastmod(),
            ];
        }

        // /used — صفحة محتوى ثابتة قابلة للفهرسة (بتدخل لو فيه منتجات أصلاً في المتجر).
        if (Product::query()->published()->count() > 0) {
            $urls[] = [
                'loc'     => $base . '/used',
                'lastmod' => $this->pagesLastmod(),
            ];
        }

        // فهرس كل نوع بيدخل بس لو فيه عناصر منشورة (غير كده هو noindex).
        foreach ($this->types() as [$model, $path]) {
            $query = $model::query()->published();
            if ($query->count() === 0) {
                continue;
            }
            $urls[] = [
                'loc'     => $base . $path,
                'lastmod' => $this->iso($query->max('updated_at')),
            ];
        }

        // صفحات أقسام المتجر اللي فيها منتجات منشورة (محتوى فريد indexable).
        foreach ($this->publishedShopCategoryUrls($base) as $catUrl) {
            $urls[] = $catUrl;
        }

        return $this->urlsetXml($urls);
    }

    /** ساب-سايت ماب لنوع واحد: روابط العناصر المنشورة مع lastmod من updated_at. */
    public function type(string $type): Response
    {
        // صفحات (ماركة × خدمة): بنية URL مختلفة فمش ضمن types() العادية.
        if ($type === 'brand-services') {
            $xml = Cache::remember(
                'sitemap:type:brand-services:' . md5($this->base()),
                $this->ttl,
                fn () => $this->buildBrandServices()
            );

            return $this->xml($xml);
        }

        abort_unless(isset($this->types()[$type]), 404);

        $xml = Cache::remember(
            "sitemap:type:{$type}:" . md5($this->base()),
            $this->ttl,
            fn () => $this->buildType($type)
        );

        return $this->xml($xml);
    }

    protected function buildType(string $type): string
    {
        [$model, $path] = $this->types()[$type];
        $base = $this->base();

        // المنتجات: المسار الكانوني /shop/{category}/{slug}، والمباع القديم (٦٠ يوم+) بيخرج.
        if ($type === 'products') {
            $urls = Product::query()->published()
                ->with('category')
                ->orderByDesc('updated_at')
                ->get()
                ->filter(fn (Product $p) => $p->isIndexable() && $p->category)
                ->map(fn (Product $p) => [
                    'loc'     => $base . $p->path(),
                    'lastmod' => $this->iso($p->updated_at),
                ])->values()->all();

            return $this->urlsetXml($urls);
        }

        $urls = $model::query()->published()
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($m) => [
                'loc'     => $base . $path . '/' . $m->getRouteKey(),
                'lastmod' => $this->iso($m->updated_at),
            ])->all();

        // المدونة: ضمّ صفحات التصنيفات اللي فيها مقالات منشورة (indexable).
        // التصنيف الفاضي بيبقى noindex فمابيدخلش السايت ماب.
        if ($type === 'blog') {
            foreach ($this->blogCategoryUrls($base) as $catUrl) {
                $urls[] = $catUrl;
            }
        }

        // مفيش منشور => urlset فاضي (الـ index مابيشاورش عليه أصلاً).
        return $this->urlsetXml($urls);
    }

    /** روابط صفحات تصنيفات المدونة اللي فيها مقالات منشورة. */
    protected function blogCategoryUrls(string $base): array
    {
        return \App\Models\PostCategory::query()
            ->whereHas('posts', fn ($q) => $q->where('is_published', true))
            ->orderBy('sort')
            ->get()
            ->map(fn ($c) => [
                'loc'     => $base . '/blog/category/' . $c->getRouteKey(),
                'lastmod' => $this->iso($c->posts()->where('is_published', true)->max('updated_at')),
            ])->all();
    }

    /** روابط صفحات أقسام المتجر اللي فيها منتجات منشورة (indexable). */
    protected function publishedShopCategoryUrls(string $base): array
    {
        return \App\Models\ProductCategory::query()->published()
            ->orderBy('position')
            ->get()
            ->filter(fn ($c) => $c->publishedProductsQuery()->exists())
            ->map(fn ($c) => [
                'loc'     => $base . $c->path(),
                'lastmod' => $this->iso($c->publishedProductsQuery()->max('updated_at')),
            ])->values()->all();
    }

    /**
     * صفحات (ماركة × خدمة) القابلة للفهرسة: منشورة + محتواها كامل + الماركة والخدمة منشورتين.
     * (نفس منطق المنتجات: فلترة الـ indexable في PHP بعد تحميل العلاقات.)
     */
    protected function indexableBrandServices(): \Illuminate\Support\Collection
    {
        return BrandServicePage::query()->published()
            ->with(['brand', 'service'])
            ->orderByDesc('updated_at')
            ->get()
            ->filter(fn (BrandServicePage $p) => $p->hasCompleteContent()
                && $p->brand && $p->brand->is_published
                && $p->service && $p->service->is_published);
    }

    /** ساب-سايت ماب لصفحات (ماركة × خدمة) القابلة للفهرسة. */
    protected function buildBrandServices(): string
    {
        $base = $this->base();

        $urls = $this->indexableBrandServices()
            ->map(fn (BrandServicePage $p) => [
                'loc'     => $base . '/brands/' . $p->brand->slug . '/' . $p->service->slug,
                'lastmod' => $this->iso($p->updated_at),
            ])->values()->all();

        return $this->urlsetXml($urls);
    }

    protected function pagesLastmod(): ?string
    {
        // أحدث تعديل في الخدمات/المناطق (المحتوى الأساسي المنشور) أو دلوقتي.
        $latest = collect([
            Service::query()->published()->max('updated_at'),
            Area::query()->published()->max('updated_at'),
        ])->filter()->max();

        return $this->iso($latest ?: now());
    }

    protected function urlsetXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . e($u['loc']) . '</loc>' . "\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . '</lastmod>' . "\n";
            }
            $xml .= '  </url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        return $xml;
    }

    protected function iso($value): ?string
    {
        if (! $value) {
            return null;
        }

        return ($value instanceof Carbon ? $value : Carbon::parse($value))->toAtomString();
    }

    protected function xml(string $body): Response
    {
        return response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
