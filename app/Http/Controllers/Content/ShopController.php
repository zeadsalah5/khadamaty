<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Seo;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * واجهة المتجر (المرحلة 6B).
 *
 *   /shop                         هب المتجر (أقسام + ماركات + عروض + أحدث)
 *   /shop/{category}              فهرس القسم (بحث/فلاتر/ترتيب/pagination)
 *   /shop/{category}/{product}    صفحة المنتج
 *   /used                         صفحة ثابتة قابلة للفهرسة للتكييفات المستعملة
 *
 * كل الأسعار من الداتابيز (حقيقية). الروابط slug إنجليزي والمحتوى عربي.
 */
class ShopController extends Controller
{
    protected string $basePath = '/shop';
    protected string $indexTitle = 'المتجر وقطع الغيار';
    protected string $indexSubtitle = 'تكييفات جديدة ومستعملة وقطع غيار ومستلزمات بأسعار واضحة.';

    /** بارامترات الفلترة اللي وجودها بيخلي الصفحة noindex وبيرجّع الكانونيكال للأساس. */
    protected array $filterKeys = ['q', 'brand', 'condition', 'min', 'max', 'sort', 'page'];

    protected function seo(): Seo
    {
        return app(Seo::class);
    }

    // ───────────────────────── هب المتجر ─────────────────────────

    public function index(): View
    {
        // الأقسام المنشورة اللي فيها منتجات منشورة فعلاً.
        $categories = ProductCategory::query()->published()
            ->whereNull('parent_id')
            ->with(['children'])
            ->orderBy('position')->orderBy('name_ar')
            ->get()
            ->filter(fn (ProductCategory $c) => $c->publishedProductsQuery()->exists())
            ->values();

        // الماركات اللي ليها منتجات منشورة.
        $brands = Brand::query()->published()
            ->whereHas('products', fn ($q) => $q->where('is_published', true))
            ->orderBy('name_ar')->get();

        // العروض: منتجات عليها خصم حقيقي.
        $offers = Product::query()->published()
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->with(['primaryImage', 'category', 'brand'])
            ->orderByDesc('updated_at')->take(8)->get();

        // أحدث المنتجات.
        $newest = Product::query()->published()
            ->with(['primaryImage', 'category', 'brand'])
            ->orderByDesc('published_at')->orderByDesc('id')->take(8)->get();

        $hasProducts = Product::query()->published()->exists();

        $this->seo()
            ->title($this->indexTitle)
            ->description($this->indexSubtitle)
            ->canonical($this->basePath)
            ->robots($hasProducts ? 'index,follow' : 'noindex,follow');

        return view('shop.index', [
            'title'       => $this->indexTitle,
            'subtitle'    => $this->indexSubtitle,
            'categories'  => $categories,
            'brands'      => $brands,
            'offers'      => $offers,
            'newest'      => $newest,
            'hasProducts' => $hasProducts,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => $this->indexTitle, 'url' => $this->basePath],
            ],
        ]);
    }

    // ───────────────────────── فهرس القسم ─────────────────────────

    public function category(Request $request, string $categorySlug): View|RedirectResponse
    {
        $category = ProductCategory::query()->published()->where('slug', $categorySlug)->first();

        // مش قسم؟ يمكن يكون slug منتج قديم (روابط 6A) => 301 للكانونيكال الجديد.
        if (! $category) {
            if ($redirect = $this->legacyProductRedirect($categorySlug)) {
                return $redirect;
            }
            abort(404);
        }

        $category->load(['children', 'attributes', 'parent']);

        $base = $category->path();

        // استعلام منتجات القسم (والأقسام الفرعية) المنشورة.
        $query = $category->publishedProductsQuery()->with(['primaryImage', 'category', 'brand']);

        $this->applyFilters($query, $request, $category);
        $this->applySort($query, $request->query('sort'));

        $products = $query->paginate(12)->withQueryString();

        // الماركات المتاحة في القسم (للفلتر) — من منتجات القسم المنشورة بس.
        $brandIds = $category->publishedProductsQuery()->whereNotNull('brand_id')->distinct()->pluck('brand_id');
        $brands = Brand::query()->whereIn('id', $brandIds)->orderBy('name_ar')->get();

        $hasFilters = $this->hasActiveFilters($request);
        $hasProducts = $category->publishedProductsQuery()->exists();

        // الكانونيكال دايماً على صفحة القسم الأساسية (من غير query)؛
        // أي فلتر/ترتيب/صفحة => noindex,follow. القسم الفاضي => noindex كمان.
        $this->seo()
            ->title($category->seo_title ?: $category->name_ar)
            ->description($category->seo_description ?: $category->excerpt)
            ->canonical($base)
            ->robots(($hasFilters || ! $hasProducts) ? 'noindex,follow' : 'index,follow');

        $breadcrumbs = [['label' => 'الرئيسية', 'url' => '/'], ['label' => $this->indexTitle, 'url' => $this->basePath]];
        if ($category->parent) {
            $breadcrumbs[] = ['label' => $category->parent->name_ar, 'url' => $category->parent->path()];
        }
        $breadcrumbs[] = ['label' => $category->name_ar, 'url' => $base];

        return view('shop.category', [
            'category'    => $category,
            'title'       => $category->name_ar,
            'subtitle'    => $category->excerpt,
            'products'    => $products,
            'brands'      => $brands,
            'filters'     => $this->activeFilters($request),
            'hasProducts' => $hasProducts,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    // ───────────────────────── صفحة المنتج ─────────────────────────

    public function show(string $categorySlug, string $productSlug): View|RedirectResponse
    {
        $product = Product::query()->published()
            ->where('slug', $productSlug)
            ->with(['category.attributes', 'category.parent', 'brand', 'images', 'usedDetails'])
            ->first();

        // منتج منشور من غير قسم (حالة نادرة) => مفيش مسار كانوني => 404.
        abort_if(! $product || ! $product->category, 404);

        // لو القسم في الرابط مش مطابق لقسم المنتج الحالي => 301 للكانونيكال الصحيح.
        if ($product->category->slug !== $categorySlug) {
            return redirect($product->path(), 301);
        }

        $path = $product->path();

        // المستعمل المباع من أكتر من ٦٠ يوم => noindex (بيفضل متاح كـ 200).
        $this->seo()
            ->title($product->seo_title ?: $product->name_ar)
            ->description($product->seo_description ?: $product->excerpt)
            ->canonical($path)
            ->ogType('website')
            ->ogImage($product->imageUrl())
            ->robots($product->isIndexable() ? 'index,follow' : 'noindex,follow');

        // منتجات مشابهة: نفس القسم، منشورة، مش هي نفسها.
        $related = Product::query()->published()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage', 'category', 'brand'])
            ->orderByDesc('updated_at')->take(4)->get();

        // مناطق التغطية (للتوصيل) — روابط داخلية.
        $areas = \App\Models\Area::query()->published()->orderBy('name_ar')->get();

        $breadcrumbs = [['label' => 'الرئيسية', 'url' => '/'], ['label' => $this->indexTitle, 'url' => $this->basePath]];
        if ($product->category->parent) {
            $breadcrumbs[] = ['label' => $product->category->parent->name_ar, 'url' => $product->category->parent->path()];
        }
        $breadcrumbs[] = ['label' => $product->category->name_ar, 'url' => $product->category->path()];
        $breadcrumbs[] = ['label' => $product->name_ar, 'url' => $path];

        return view('shop.product', [
            'product'     => $product,
            'title'       => $product->name_ar,
            'subtitle'    => $product->excerpt,
            'canonical'   => $this->seo()->getCanonical(),
            'related'     => $related,
            'areas'       => $areas,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    // ───────────────────────── /used (صفحة ثابتة قابلة للفهرسة) ─────────────────────────

    public function used(): View
    {
        $products = Product::query()->published()
            ->where('condition', 'used')
            ->with(['primaryImage', 'category', 'brand', 'usedDetails'])
            ->orderByDesc('updated_at')->get();

        $title = 'تكييفات مستعملة مضمونة';
        $subtitle = 'أجهزة مفحوصة فنياً بدرجات حالة واضحة وضمان — اشترِ وأنت مطمّن.';

        // صفحة محتوى ثابتة: بتفضل قابلة للفهرسة حتى لو القايمة فاضية (محتواها فريد).
        $this->seo()
            ->title($title)
            ->description($subtitle)
            ->canonical('/used')
            ->robots('index,follow');

        return view('shop.used', [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'products'    => $products,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'تكييفات مستعملة', 'url' => '/used'],
            ],
        ]);
    }

    // ───────────────────────── مساعدات الفلترة ─────────────────────────

    /** يطبّق فلاتر البحث/الماركة/الحالة/السعر + فلاتر المواصفات على الاستعلام. */
    protected function applyFilters(Builder $query, Request $request, ProductCategory $category): void
    {
        // بحث نصّي (عربي) — substring على الاسم/النبذة/الموديل/الـ SKU.
        if (filled($q = trim((string) $request->query('q')))) {
            $query->where(function ($sub) use ($q) {
                foreach (['name_ar', 'excerpt', 'description', 'model_number', 'sku'] as $col) {
                    $sub->orWhere($col, 'like', '%' . $q . '%');
                }
            });
        }

        // الماركة (slug).
        if (filled($brandSlug = $request->query('brand'))) {
            $brand = Brand::query()->where('slug', $brandSlug)->first();
            $query->where('brand_id', $brand?->id ?? 0);
        }

        // الحالة: جديد / مستعمل.
        if (in_array($condition = $request->query('condition'), ['new', 'used'], true)) {
            $query->where('condition', $condition);
        }

        // نطاق السعر (بالسعر المعروض: العرض لو موجود وإلا الأساسي).
        $priceExpr = 'COALESCE(NULLIF(sale_price, 0), price)';
        if (is_numeric($min = $request->query('min'))) {
            $query->whereRaw("$priceExpr >= ?", [(float) $min]);
        }
        if (is_numeric($max = $request->query('max'))) {
            $query->whereRaw("$priceExpr <= ?", [(float) $max]);
        }

        // فلاتر المواصفات من حقول القسم (select/boolean) — query key = attr_{key}.
        foreach ($category->attributes as $attr) {
            if (! in_array($attr->type, ['select', 'boolean'], true)) {
                continue;
            }
            $value = $request->query('attr_' . $attr->key);
            if (filled($value)) {
                $query->where('specs->' . $attr->key, $value);
            }
        }
    }

    protected function applySort(Builder $query, ?string $sort): void
    {
        $priceExpr = 'COALESCE(NULLIF(sale_price, 0), price)';

        match ($sort) {
            'price_asc'  => $query->orderByRaw("$priceExpr asc"),
            'price_desc' => $query->orderByRaw("$priceExpr desc"),
            default      => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }

    /** فيه فلتر/ترتيب/صفحة فعّالة؟ (يحدد noindex). */
    protected function hasActiveFilters(Request $request): bool
    {
        foreach ($this->filterKeys as $key) {
            if ($key === 'page') {
                if ((int) $request->query('page', 1) > 1) {
                    return true;
                }
                continue;
            }
            if (filled($request->query($key))) {
                return true;
            }
        }

        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'attr_') && filled($value)) {
                return true;
            }
        }

        return false;
    }

    /** الفلاتر الفعّالة الحالية (لعرض القيم المختارة في النموذج). */
    protected function activeFilters(Request $request): array
    {
        $filters = [];
        foreach (['q', 'brand', 'condition', 'min', 'max', 'sort'] as $key) {
            $filters[$key] = $request->query($key);
        }
        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'attr_')) {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /** روابط 6A القديمة (/shop/{productSlug}) => 301 للكانونيكال الجديد. */
    protected function legacyProductRedirect(string $slug): ?RedirectResponse
    {
        $product = Product::query()->published()->where('slug', $slug)->with('category')->first();

        return $product ? redirect($product->path(), 301) : null;
    }
}
