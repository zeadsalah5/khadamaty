<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CategoryAttribute;
use App\Support\Seo;
use App\Support\Store\Compare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مقارنة المنتجات (المرحلة 6D) — session، لحد 3 من نفس القسم. noindex.
 */
class CompareController extends Controller
{
    public function index(): View
    {
        $products = Compare::products();

        // مواصفات القسم (لو متعرّفة) عشان نبني صفوف المقارنة بترتيب ثابت.
        $attributes = collect();
        if ($products->isNotEmpty() && ($categoryId = Compare::categoryId())) {
            $attributes = CategoryAttribute::query()
                ->where('product_category_id', $categoryId)
                ->orderBy('position')
                ->get(['key', 'label_ar', 'unit']);
        }

        app(Seo::class)->title('مقارنة المنتجات')->canonical('/compare')->robots('noindex,nofollow');

        return view('shop.compare', [
            'title'       => 'مقارنة المنتجات',
            'products'    => $products,
            'attributes'  => $attributes,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'المتجر', 'url' => '/shop'],
                ['label' => 'المقارنة', 'url' => '/compare'],
            ],
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        $result = Compare::toggle((int) $data['product_id']);

        return back()->with(...$this->flashFor($result));
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        Compare::remove((int) $data['product_id']);

        return back()->with('compare_success', 'تم الحذف من المقارنة.');
    }

    public function clear(): RedirectResponse
    {
        Compare::clear();

        return back()->with('compare_success', 'تم تفريغ المقارنة.');
    }

    /** رسالة الفلاش المناسبة لحالة الإضافة. */
    protected function flashFor(string $result): array
    {
        return match ($result) {
            'added'             => ['compare_success', 'اتضاف للمقارنة.'],
            'removed'           => ['compare_success', 'اتشال من المقارنة.'],
            'exists'            => ['compare_success', 'المنتج موجود في المقارنة بالفعل.'],
            'full'              => ['compare_error', 'المقارنة بتوصل 3 منتجات بس. شيل واحد الأول.'],
            'category_mismatch' => ['compare_error', 'المقارنة بتكون بين منتجات من نفس القسم.'],
            default             => ['compare_error', 'المنتج ده مش متاح للمقارنة.'],
        };
    }
}
