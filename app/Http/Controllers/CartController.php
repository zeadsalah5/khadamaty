<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use App\Support\Store\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * السلة (session للضيف) — المرحلة 6C.
 * كل الأسعار بتتقرا من الداتابيز وقت العرض (مفيش سعر من العميل).
 */
class CartController extends Controller
{
    /** صفحة السلة /cart — noindex (صفحة معاملات). */
    public function show(): View
    {
        app(Seo::class)
            ->title('سلة المشتريات')
            ->canonical('/cart')
            ->robots('noindex,nofollow');

        $lines = Cart::lines();

        return view('shop.cart', [
            'title'       => 'سلة المشتريات',
            'lines'       => $lines,
            'subtotal'    => Cart::subtotal(),
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/'],
                ['label' => 'المتجر', 'url' => '/shop'],
                ['label' => 'السلة', 'url' => '/cart'],
            ],
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty'        => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $added = Cart::add((int) $data['product_id'], (int) ($data['qty'] ?? 1));

        if (! $added) {
            return back()->with('cart_error', 'المنتج ده مش متاح للطلب حالياً.');
        }

        return redirect()->route('cart.show')
            ->with('cart_success', 'اتضاف للسلة. تقدر تكمّل تسوّق أو تتمّم الطلب.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty'        => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        Cart::update((int) $data['product_id'], (int) $data['qty']);

        return redirect()->route('cart.show')->with('cart_success', 'تم تحديث السلة.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer']]);

        Cart::remove((int) $data['product_id']);

        return redirect()->route('cart.show')->with('cart_success', 'تم حذف المنتج من السلة.');
    }
}
